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
use function preg_replace;
use function preg_replace_callback;
use function stripos;
use const PHP_EOL;
use const PHP_OS;

final class Stdout implements Writer
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
     * @var callable
     */
    private $writer;

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
        $this->writer = $this->setWriter();
    }

    /**
     * Set the writing method depending on the underlying platform.
     */
    private function setWriter(): callable
    {
        $regexp = ',<<\s*((('.implode('|', array_keys(self::POSIX_COLOR_CODES)).')(\s*))+)>>,Umsi';
        if (0 === stripos(PHP_OS, 'WIN')) {
            return function (string $str) use ($regexp): string {
                return ' '.preg_replace($regexp, '', $str);
            };
        }

        return function (string $str) use ($regexp): string {
            $formatter = static function (array $matches) {
                $str = (string) preg_replace('/(\s+)/msi', ';', (string) $matches[1]);

                return chr(27).'['.strtr($str, self::POSIX_COLOR_CODES).'m';
            };

            return ' '.preg_replace_callback($regexp, $formatter, $str);
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
     * @inheritDoc
     */
    public function writeln($message): int
    {
        if (is_string($message)) {
            $message = [$message];
        }

        $bytes = 0;
        foreach ($message as $line) {
            $str = ($this->writer)($line);
            $bytes += fwrite($this->stream, $str.PHP_EOL);
        }

        fflush($this->stream);

        return $bytes;
    }
}
