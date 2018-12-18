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

use InvalidArgumentException;

/**
 * A class to configure the Millipede settings.
 */
final class Configuration
{
    /**
     * POSIX color.
     *
     * @var array
     */
    public const COLORS = ['white', 'red', 'yellow', 'green', 'cyan', 'blue', 'magenta'];

    private const REGEXP_UNICODE = '/\\\\u(?<unicode>[0-9A-F]{1,4})/i';

    /**
     * @var string[]
     */
    private $colorOffsets = ['white'];

    /**
     * @var int
     */
    private $width = 10;

    /**
     * @var string
     */
    private $head = ']';

    /**
     * @var string
     */
    private $tail = '[';

    /**
     * @var string
     */
    private $body = '=';

    /**
     * @var string
     */
    private $space = ' ';

    /**
     * Create a Cli Renderer to Display the millipede in Rainbow.
     *
     */
    public static function createFromRandom(): self
    {
        $config = new self();

        return $config->withColors(self::COLORS[array_rand(self::COLORS)]);
    }

    /**
     * Create a Cli Renderer to Display the millipede in Rainbow.
     *
     */
    public static function createFromRainbow(): self
    {
        $config = new self();

        return $config->withColors(...self::COLORS);
    }

    /**
     * Retrieve the row width.
     *
     */
    public function getWidth(): int
    {
        return $this->width;
    }

    /**
     * Retrieve the body block character.
     *
     */
    public function getTail(): string
    {
        return $this->tail;
    }

    /**
     * Retrieve the body block character.
     *
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * Retrieve the head block character.
     *
     */
    public function getHead(): string
    {
        return $this->head;
    }

    /**
     * Retrieve the row space character.
     *
     */
    public function getSpace(): string
    {
        return $this->space;
    }

    /**
     * The selected colors for each rows.
     *
     * @return string[]
     */
    public function getColors(): array
    {
        return $this->colorOffsets;
    }

    public function applyColors(iterable $iterator): iterable
    {
        $colorOffsets = $this->colorOffsets;
        foreach ($iterator as $key => $line) {
            $color = $colorOffsets[$key % count($colorOffsets)];
            yield self::outln("<<$color>>$line<<reset>>");
        }
    }

    /**
     * Return an instance with the specified row width.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified curve.
     */
    public function withWidth(int $width): self
    {
        if ($width < 10) {
            $width = 10;
        }

        if ($width === $this->width) {
            return $this;
        }

        $clone = clone $this;
        $clone->width = $width;

        return $clone;
    }

    /**
     * Return an instance with the head pattern.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified curve.
     */
    public function withHead(string $head): self
    {
        $head = $this->filterPattern($head, 'head');
        if ($head === $this->head) {
            return $this;
        }

        $clone = clone $this;
        $clone->head = $head;

        return $clone;
    }

    /**
     * Return an instance with the specified body block.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified curve.
     */
    public function withBody(string $body): self
    {
        $body = $this->filterPattern($body, 'body');
        if ($body === $this->body) {
            return $this;
        }

        $clone = clone $this;
        $clone->body = $body;

        return $clone;
    }

    /**
     * Return an instance with the tail pattern.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified curve.
     */
    public function withTail(string $tail): self
    {
        $tail = $this->filterPattern($tail, 'tail');
        if ($tail === $this->tail) {
            return $this;
        }

        $clone = clone $this;
        $clone->tail = $tail;

        return $clone;
    }

    /**
     * Return an instance with the head pattern.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified curve.
     */
    public function withSpace(string $space): self
    {
        $space = $this->filterPattern($space, 'space');
        if ($space === $this->space) {
            return $this;
        }

        $clone = clone $this;
        $clone->space = $space;

        return $clone;
    }

    /**
     * Return an instance with a new color palette.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified curve.
     * @param string... $optionals
     */
    public function withColors(string $primary, string ...$optionals): self
    {
        $filter = function ($value) {
            return in_array($value, self::COLORS, true);
        };

        $colorOffsets = array_filter(array_map('strtolower', array_merge([$primary], $optionals)), $filter);

        if ([] === $colorOffsets) {
            $colorOffsets = ['white'];
        }

        if ($colorOffsets === $this->colorOffsets) {
            return $this;
        }

        $clone = clone $this;
        $clone->colorOffsets = $colorOffsets;

        return $clone;
    }

    /**
     * Filter the submitted string.
     *
     * @throws InvalidArgumentException if the pattern is invalid
     */
    private function filterPattern(string $str, string $part): string
    {
        if (1 === mb_strlen($str)) {
            return $str;
        }

        if (1 === preg_match(self::REGEXP_UNICODE, $str)) {
            return $this->filterUnicodeCharacter($str);
        }

        throw new InvalidArgumentException(sprintf('The %s pattern must be a single character', $part));
    }

    /**
     * Decode unicode characters.
     *
     * @see http://stackoverflow.com/a/37415135/2316257
     *
     * @throws InvalidArgumentException if the character is not valid.
     */
    private function filterUnicodeCharacter(string $str): string
    {
        $replaced = (string) preg_replace(self::REGEXP_UNICODE, '&#x$1;', $str);
        $result = mb_convert_encoding($replaced, 'UTF-16', 'HTML-ENTITIES');
        $result = mb_convert_encoding($result, 'UTF-8', 'UTF-16');
        if (1 === mb_strlen($result)) {
            return $result;
        }

        throw new InvalidArgumentException(sprintf('The given string `%s` is not a valid unicode string', $str));
    }

    /**
     * Format the text output.
     *
     * Inspired by Aura\Cli\Stdio\Formatter (https://github.com/auraphp/Aura.Cli).
     */
    public static function outln(string $str): string
    {
        static $formatter;
        static $func;
        static $regex;
        static $codes = [
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

        if (null !== $regex) {
            return ' '.$func($regex, $formatter, $str)."\n";
        }

        $regex = ',<<\s*((('.implode('|', array_keys($codes)).')(\s*))+)>>,Umsi';
        $formatter = '';
        $func = 'preg_replace';
        if (false === strpos(strtolower(PHP_OS), 'win')) {
            $formatter = static function (array $matches) use ($codes) {
                $str = (string) preg_replace('/(\s+)/msi', ';', (string) $matches[1]);

                return chr(27).'['.strtr($str, $codes).'m';
            };
            $func = 'preg_replace_callback';

            return ' '.preg_replace_callback($regex, $formatter, $str)."\n";
        }

        return ' '.preg_replace($regex, $formatter, $str)."\n";
    }
}
