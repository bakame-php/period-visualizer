<?php

/**
 * League.Period Visualizer (https://github.com/bakame-php/period-visualizer)
 *
 * (c) Ignace Nyamagana Butera <nyamsprod@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

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
use function ob_get_clean;
use function ob_start;
use function preg_replace;
use function preg_replace_callback;
use function str_pad;
use function strpos;
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
     * @var callable
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
        $this->writerMethod = $this->setWriterMethod();
    }

    /**
     * Set the writing method depending on the underlying platform.
     */
    private function setWriterMethod(): callable
    {
        if (false !== strpos(PHP_OS, 'WIN')) {
            return function (string $str): string {
                return ' '.preg_replace($this->regexp, '', $str);
            };
        }

        return function (string $str): string {
            $formatter = static function (array $matches) {
                $str = (string) preg_replace('/(\s+)/msi', ';', (string) $matches[1]);

                return chr(27).'['.strtr($str, self::POSIX_COLOR_CODES).'m';
            };

            return ' '.preg_replace_callback($this->regexp, $formatter, $str);
        };
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
    public function display(iterable $blocks): string
    {
        $matrix = Matrix::build($blocks, $this->config->width());
        if ([] === $matrix) {
            return '';
        }

        ob_start();
        foreach ($this->render($matrix) as $row) {
            echo $row.PHP_EOL;
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
        $colorOffsets = $this->config->colors();
        $key = -1;
        foreach ($matrix as [$name, $row]) {
            $color = $colorOffsets[++$key % count($colorOffsets)];
            $line = str_pad($name, $nameLength, ' ').'    '.$this->toLine($row);
            if ('default' !== $color) {
                $line = "<<$color>>$line<<reset>>";
            }

            yield ($this->writerMethod)($line);
        }
    }

    /**
     * Turns a series of boolean values into bars representing the interval.
     *
     * @param array<int> $row
     */
    private function toLine(array $row): string
    {
        $tmp = [];
        foreach ($row as $token) {
            switch ($token) {
                case Matrix::TOKEN_BODY:
                    $tmp[] = $this->config->body();
                    break;
                case Matrix::TOKEN_START_EXCLUDED:
                    $tmp[] = $this->config->startExcluded();
                    break;
                case Matrix::TOKEN_START_INCLUDED:
                    $tmp[] = $this->config->startIncluded();
                    break;
                case Matrix::TOKEN_END_EXCLUDED:
                    $tmp[] = $this->config->endExcluded();
                    break;
                case Matrix::TOKEN_END_INCLUDED:
                    $tmp[] = $this->config->endIncluded();
                    break;
                case Matrix::TOKEN_SPACE:
                    $tmp[] = $this->config->space();
                    break;
            }
        }

        return implode('', $tmp);
    }
}
