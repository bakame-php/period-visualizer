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

use function array_keys;
use function array_map;
use function chr;
use function count;
use function implode;
use function max;
use function preg_replace;
use function preg_replace_callback;
use function sprintf;
use function str_pad;
use function strpos;
use function strtolower;
use function strtr;
use const PHP_OS;

/**
 * A class to output to the console the matrix.
 */
final class ConsoleOutput implements OutputInterface
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
    private $method;

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
        $this->newline = "\n";
        $this->method = 'posixWrite';
        if (false !== strpos(strtolower(PHP_OS), 'win')) {
            $this->newline = "\r\n";
            $this->method = 'windowsWrite';
        }
    }

    /**
     * {@inheritdoc}
     */
    public function display(array $blocks): string
    {
        ob_start();
        foreach ($this->render($blocks) as $row) {
            echo $row;
        }

        return (string) ob_get_clean();
    }

    /**
     * {@inheritdoc}
     */
    public function render(array $blocks): iterable
    {
        $matrix = Matrix::build($blocks, $this->config->getWidth());
        if ([] === $matrix) {
            return;
        }

        $nameLength = max(...array_map('strlen', array_keys($matrix)));
        $colorOffsets = $this->config->getColors();
        $key = 0;
        foreach ($matrix as $name => $row) {
            $line = $this->toLine($name, $nameLength, $row);
            $color = $colorOffsets[$key % count($colorOffsets)];
            yield $this->writeln("<<$color>>$line<<reset>>");
            ++$key;
        }
    }

    /**
     * Turn a series of true/false values into bars representing the start/end of periods.
     */
    private function toLine(string $name, int $nameLength, array $row): string
    {
        $tmp = '';

        for ($i = 0, $l = count($row); $i < $l; $i++) {
            $prev = $row[$i - 1] ?? null;
            $curr = $row[$i];
            $next = $row[$i + 1] ?? null;

            // Small state machine to build the string
            switch (true) {
                // The current period is only one unit long so display a "="
                case $curr && $curr !== $prev && $curr !== $next:
                    $tmp .= $this->config->getBody();
                    break;

                // We've hit the start of a period
                case $curr && $curr !== $prev && $curr === $next:
                    $tmp .= $this->config->getTail();
                    break;

                // We've hit the end of the period
                case $curr && $curr !== $next:
                    $tmp .= $this->config->getHead();
                    break;

                // We're adding segments to the current period
                case $curr && $curr === $prev:
                    $tmp .= $this->config->getBody();
                    break;

                // Otherwise it's just empty space
                default:
                    $tmp .= $this->config->getSpace();
                    break;
            }
        }

        return  sprintf('%s    %s', str_pad($name, $nameLength, ' '), $tmp);
    }

    /**
     * @inheritdoc
     *
     * Inspired by Aura\Cli\Stdio\Formatter (https://github.com/auraphp/Aura.Cli).
     */
    private function writeln(string $str): string
    {
        return $this->{$this->method}($str).$this->newline;
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
