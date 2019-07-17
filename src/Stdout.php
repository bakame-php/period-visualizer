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
use function implode;
use function is_iterable;
use function preg_replace;
use function preg_replace_callback;
use function stripos;
use const PHP_EOL;
use const PHP_OS;

final class Stdout implements Writer
{
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
     * Returns the POSIX color regexp.
     */
    private function regexp(): string
    {
        static $regexp;

        $regexp = $regexp ?? ',<<\s*((('.implode('|', array_keys(self::POSIX_COLOR_CODES)).')(\s*))+)>>,Umsi';

        return $regexp;
    }

    /**
     * Returns a formatted windows line.
     */
    private function write(string $str): string
    {
        static $formatter;

        $formatter = $formatter ?? $this->formatter();

        return (string) preg_replace_callback($this->regexp(), $formatter, $str);
    }

    /**
     * Return a writer formatter depending on the OS.
     */
    private function formatter(): callable
    {
        if (0 !== stripos(PHP_OS, 'WIN')) {
            return function (array $matches): string {
                $str = (string) preg_replace('/(\s+)/msi', ';', (string) $matches[1]);

                return chr(27).'['.strtr($str, self::POSIX_COLOR_CODES).'m';
            };
        }

        return function (array $matches): string {
            return (string) $matches[0];
        };
    }

    /**
     * {@inheritDoc}
     */
    public function colorize(string $line, string $color): string
    {
        if (Writer::DEFAULT_COLOR_NAME !== $color) {
            return "<<$color>>$line<<reset>>";
        }

        return $line;
    }

    /**
     * {@inheritDoc}
     */
    public function writeln($message): int
    {
        if (!is_iterable($message)) {
            $message = [$message];
        }

        $bytes = 0;
        foreach ($message as $line) {
            $bytes += fwrite($this->stream, ' '.$this->write($line).PHP_EOL);
        }

        fflush($this->stream);

        return $bytes;
    }
}
