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

use Bakame\Period\Visualizer\Contract\OutputWriter;
use TypeError;
use function array_key_exists;
use function array_keys;
use function chr;
use function fflush;
use function fwrite;
use function implode;
use function is_iterable;
use function preg_replace;
use function preg_replace_callback;
use function stripos;
use function strtolower;
use const PHP_EOL;
use const PHP_OS;

final class ConsoleStdout implements OutputWriter
{
    private const REGEXP_POSIX_PLACEHOLDER = '/(\s+)/msi';

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
    public function colorize(string $characters, string $colorCodeIndex): string
    {
        $colorCodeIndex = strtolower($colorCodeIndex);
        if (OutputWriter::DEFAULT_COLOR_CODE_INDEX === $colorCodeIndex) {
            return $characters;
        }

        if (array_key_exists($colorCodeIndex, OutputWriter::POSIX_COLOR_CODES)) {
            return "<<$colorCodeIndex>>$characters<<".OutputWriter::DEFAULT_COLOR_CODE_INDEX.'>>';
        }

        return $characters;
    }

    /**
     * {@inheritDoc}
     */
    public function writeln($message = ''): void
    {
        if (!is_iterable($message)) {
            $message = [$message];
        }

        foreach ($message as $line) {
            fwrite($this->stream, $this->format($line).PHP_EOL);
        }

        fflush($this->stream);
    }

    /**
     * Returns a formatted windows line.
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
