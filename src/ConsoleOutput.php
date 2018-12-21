<?php

/**
 * League.Period Visualizer (https://github.com/bakame-php/period-visualizer).
 *
 * (c) Ignace Nyamagana Butera <nyamsprod@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bakame\Period\Visualizer;

use League\Period\Period;
use League\Period\Sequence;
use function array_column;
use function array_keys;
use function array_map;
use function chr;
use function count;
use function implode;
use function max;
use function preg_replace;
use function preg_replace_callback;
use function str_pad;
use function strpos;
use function strtolower;
use function strtr;
use const PHP_OS;

/**
 * A class to output to the console the matrix.
 */
final class ConsoleOutput
{
    private const POSIX_COLOR_CODES = [
        'reset'      => '0',
        'bold'       => '1',
        'dim'        => '2',
        'underscore' => '4',
        'blink'      => '5',
        'reverse'    => '7',
        'hidden'     => '8',
        'black'      => '30',
        'red'        => '31',
        'green'      => '32',
        'yellow'     => '33',
        'blue'       => '34',
        'magenta'    => '35',
        'cyan'       => '36',
        'white'      => '37',
        'blackbg'    => '40',
        'redbg'      => '41',
        'greenbg'    => '42',
        'yellowbg'   => '43',
        'bluebg'     => '44',
        'magentabg'  => '45',
        'cyanbg'     => '46',
        'whitebg'    => '47',
    ];

    /**
     * @var string
     */
    private $regexp;

    /**
     * @var string
     */
    private $newline;

    /**
     * @var string
     */
    private $writerMethod;

    /**
     * @var ConsoleConfig
     */
    private $config;

    /**
     * New instance.
     */
    public function __construct(ConsoleConfig $config = null)
    {
        $this->config = $config ?? new ConsoleConfig();
        $this->regexp = ',<<\s*((('.implode('|', array_keys(self::POSIX_COLOR_CODES)).')(\s*))+)>>,Umsi';
        $this->writerMethod = 'posixWrite';
        $this->newline = "\n";
        if (false !== strpos(strtolower(PHP_OS), 'win')) {
            $this->writerMethod = 'windowsWrite';
            $this->newline = "\r\n";
        }
    }

    /**
     * Builds a string to visualize one or more
     * periods and/or sequences in a more
     * human readable / parsable manner.
     *
     * The submitted array values represent a tuple where
     * the first value is the identifer and the second value
     * the intervals represented as Period or Sequence instances.
     *
     * the returned string can be represented like the following:
     *
     * A       [========]
     * B                    [==]
     * C                            [=====]
     * D              [===============]
     * OVERLAP        [=]   [==]    [=]
     *
     * @param array<int, array<int|string, Period|Sequence>> $blocks
     */
    public function display(array $blocks): string
    {
        $matrix = Matrix::build($blocks, $this->config->getWidth());
        if ([] === $matrix) {
            return '';
        }

        ob_start();
        foreach ($this->render($matrix) as $row) {
            echo $row.$this->newline;
        }

        return (string) ob_get_clean();
    }

    /**
     * Builds an Iterator to visualize one or more
     * periods and/or collections in a more
     * human readable / parsable manner.
     *
     * The submitted array values represent a tuple where
     * the first value is the identifer and the second value
     * the periods represented as Period or Sequence instances.
     *
     * This method returns one output line at a time.
     *
     * @param array<int, array<int|string, mixed>> $matrix
     */
    private function render(array $matrix): iterable
    {
        $nameLength = max(...array_map('strlen', array_column($matrix, 0)));
        $colorOffsets = $this->config->getColors();
        $key = -1;
        foreach ($matrix as [$name, $row]) {
            $color = $colorOffsets[++$key % count($colorOffsets)];
            $line = str_pad($name, $nameLength, ' ').'    '.$this->toLine($row);
            if ('default' !== $color) {
                $line = "<<$color>>$line<<reset>>";
            }

            yield $this->write($line);
        }
    }

    /**
     * Turns a series of boolean values into bars representing the interval.
     *
     * @param array<bool> $row
     */
    private function toLine(array $row): string
    {
        $tmp = [];
        foreach ($row as $offset => $curr) {
            $prev = $row[$offset - 1] ?? null;
            $next = $row[$offset + 1] ?? null;

            // Small state machine to build the string
            switch (true) {
                // The current period is only one unit long so display a "="
                case $curr && $curr !== $prev && $curr !== $next:
                    $tmp[] = $this->config->getBody();
                    break;

                // We've hit the start of a period
                case $curr && $curr !== $prev && $curr === $next:
                    $tmp[] = $this->config->getTail();
                    break;

                // We've hit the end of the period
                case $curr && $curr !== $next:
                    $tmp[] = $this->config->getHead();
                    break;

                // We're adding segments to the current period
                case $curr && $curr === $prev:
                    $tmp[] = $this->config->getBody();
                    break;

                // Otherwise it's just empty space
                default:
                    $tmp[] = $this->config->getSpace();
                    break;
            }
        }

        return implode('', $tmp);
    }

    /**
     * @inheritdoc
     *
     * Inspired by Aura\Cli\Stdio\Formatter (https://github.com/auraphp/Aura.Cli).
     */
    private function write(string $str): string
    {
        return $this->{$this->writerMethod}($str);
    }

    /**
     * Write a line to windows console.
     */
    private function windowsWrite(string $str): string
    {
        return ' '.preg_replace($this->regexp, '', $str);
    }

    /**
     * Write a line to Posix compliant console.
     */
    private function posixWrite(string $str): string
    {
        $formatter = static function (array $matches) {
            $str = (string) preg_replace('/(\s+)/msi', ';', (string) $matches[1]);

            return chr(27).'['.strtr($str, self::POSIX_COLOR_CODES).'m';
        };

        return ' '.preg_replace_callback($this->regexp, $formatter, $str);
    }
}
