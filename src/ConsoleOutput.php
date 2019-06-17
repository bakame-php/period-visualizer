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
    private static $regexp;

    /**
     * @var callable
     */
    private static $writer;

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
        self::$regexp =  self::$regexp ?? ',<<\s*((('.implode('|', array_keys(self::POSIX_COLOR_CODES)).')(\s*))+)>>,Umsi';
        self::$writer = self::$writer ?? self::setWriter();
    }

    /**
     * Set the writing method depending on the underlying platform.
     */
    private static function setWriter(): callable
    {
        if (false === stripos(PHP_OS, 'WIN')) {
            return function (string $str): string {
                return ' '.preg_replace(self::$regexp, '', $str);
            };
        }

        return function (string $str): string {
            $formatter = static function (array $matches) {
                $str = (string) preg_replace('/(\s+)/msi', ';', (string) $matches[1]);

                return chr(27).'['.strtr($str, self::POSIX_COLOR_CODES).'m';
            };

            return ' '.preg_replace_callback(self::$regexp, $formatter, $str);
        };
    }

    /**
     * Builds a string to visualize one or more
     * periods and/or sequences in a more
     * human readable / parsable manner.
     *
     * The submitted iterable structure represent a tuple where
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
     * @param array $blocks
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
     */
    private function render(array $matrix): iterable
    {
        $nameLength = max(...array_map('strlen', array_column($matrix, 0)));
        $colorOffsets = $this->config->colors();
        $key = -1;
        foreach ($matrix as [$name, $row]) {
            $color = $colorOffsets[++$key % count($colorOffsets)];
            $prefix = str_pad($name, $nameLength, ' ');
            $data = implode('', array_map([$this, 'convertMatrixValue'], $row));
            $line = $prefix.'    '.$data;
            if ('default' !== $color) {
                $line = "<<$color>>$line<<reset>>";
            }

            yield (self::$writer)($line);
        }
    }

    /**
     * Turns a series of boolean values into bars representing the interval.
     */
    private function convertMatrixValue(int $token): string
    {
        static $list = [
            Matrix::TOKEN_SPACE => 'space',
            Matrix::TOKEN_BODY => 'body',
            Matrix::TOKEN_START_EXCLUDED => 'startExcluded',
            Matrix::TOKEN_START_INCLUDED => 'startIncluded',
            Matrix::TOKEN_END_INCLUDED => 'endIncluded',
            Matrix::TOKEN_END_EXCLUDED => 'endExcluded',
        ];

        return $this->config->{$list[$token]}();
    }
}
