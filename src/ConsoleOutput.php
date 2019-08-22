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

use TypeError;
use function array_keys;
use function chr;
use function fflush;
use function fwrite;
use function implode;
use function preg_replace;
use function preg_replace_callback;
use function stripos;
use function strtolower;
use const PHP_EOL;
use const PHP_OS;

final class ConsoleOutput implements OutputWriter
{
    private const REGEXP_POSIX_PLACEHOLDER = '/(\s+)/msi';

    private const POSIX_COLOR_CODES = [
        self::COLOR_DEFAULT => '0',
        self::COLOR_BLACK   => '30',
        self::COLOR_RED     => '31',
        self::COLOR_GREEN   => '32',
        self::COLOR_YELLOW  => '33',
        self::COLOR_BLUE    => '34',
        self::COLOR_MAGENTA => '35',
        self::COLOR_CYAN    => '36',
        self::COLOR_WHITE   => '37',
    ];

    /**
     * @var callable
     */
    private static $formatter;

    /**
     * @var string
     */
    private static $regexp;

    /**
     * @var resource
     */
    private $stream;

    /**
     * Stdout constructor.
     *
     * @param resource|mixed $resource
     */
    public function __construct($resource)
    {
        if (!is_resource($resource)) {
            throw new TypeError(sprintf('Argument passed must be a stream resource, %s given', gettype($resource)));
        }

        if ('stream' !== ($type = get_resource_type($resource))) {
            throw new TypeError(sprintf('Argument passed must be a stream resource, %s resource given', $type));
        }

        $this->stream = $resource;
    }

    /**
     * {@inheritDoc}
     */
    public function writeln(string $message = '', string $color = self::COLOR_DEFAULT): void
    {
        $line = $this->format($this->colorize($message, $color)).PHP_EOL;
        fwrite($this->stream, $line);
        fflush($this->stream);
    }

    /**
     * Colorizes the given string.
     */
    private function colorize(string $characters, string $color): string
    {
        if (!isset(self::POSIX_COLOR_CODES[strtolower($color)])) {
            return $characters;
        }

        return "<<$color>>$characters<<".OutputWriter::COLOR_DEFAULT.'>>';
    }

    /**
     * Returns a formatted line.
     */
    private function format(string $str): string
    {
        self::$formatter = self::$formatter ?? $this->formatter();
        self::$regexp = self::$regexp ?? ',<<\s*((('.implode('|', array_keys(self::POSIX_COLOR_CODES)).')(\s*))+)>>,Umsi';

        return (string) preg_replace_callback(self::$regexp, self::$formatter, $str);
    }

    /**
     * Return a writer formatter depending on the OS.
     */
    private function formatter(): callable
    {
        if (0 !== stripos(PHP_OS, 'WIN')) {
            return function (array $matches): string {
                $str = (string) preg_replace(self::REGEXP_POSIX_PLACEHOLDER, ';', (string) $matches[1]);

                return chr(27).'['.strtr($str, self::POSIX_COLOR_CODES).'m';
            };
        }

        return function (array $matches): string {
            return (string) $matches[0];
        };
    }
}
