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

use Bakame\Period\Visualizer\Contract\Writer;
use InvalidArgumentException;
use function array_filter;
use function array_key_exists;
use function array_map;
use function mb_convert_encoding;
use function mb_strlen;
use function preg_match;
use function preg_replace;
use function sprintf;
use const STR_PAD_BOTH;
use const STR_PAD_LEFT;
use const STR_PAD_RIGHT;

/**
 * A class to configure the console output settings.
 */
final class ConsoleConfig
{
    private const REGEXP_UNICODE = '/\\\\u(?<unicode>[0-9A-F]{1,4})/i';

    /**
     * @var string[]
     */
    private $colorCodeIndexes = [Writer::DEFAULT_COLOR_CODE_INDEX];

    /**
     * @var int
     */
    private $width = 60;

    /**
     * @var string
     */
    private $endExcluded = ')';

    /**
     * @var string
     */
    private $startIncluded = '[';

    /**
     * @var string
     */
    private $endIncluded = ']';

    /**
     * @var string
     */
    private $startExcluded = '(';
    /**
     * @var string
     */
    private $body = '-';

    /**
     * @var string
     */
    private $space = ' ';

    /**
     * @var int
     */
    private $gapSize = 1;

    /**
     * @var int
     */
    private $padding = STR_PAD_RIGHT;

    /**
     * Create a Cli Renderer to Display the millipede in Rainbow.
     */
    public static function createFromRandom(): self
    {
        $config = new self();

        /** @var string $colorCode */
        $colorCode = array_rand(Writer::POSIX_COLOR_CODES);

        return $config->withColors($colorCode);
    }

    /**
     * Create a Cli Renderer to Display the millipede in Rainbow.
     */
    public static function createFromRainbow(): self
    {
        $config = new self();

        return $config->withColors(...array_keys(Writer::POSIX_COLOR_CODES));
    }

    /**
     * Retrieve the row width.
     */
    public function width(): int
    {
        return $this->width;
    }

    /**
     * Retrieve the gap sequence between the label and the line.
     */
    public function gapSize(): int
    {
        return $this->gapSize;
    }

    /**
     * Tell whether left padding is applied.
     */
    public function padding(): int
    {
        return $this->padding;
    }

    /**
     * Retrieve the start excluded block character.
     */
    public function startExcluded(): string
    {
        return $this->startExcluded;
    }
    /**
     * Retrieve the start included block character.
     */
    public function startIncluded(): string
    {
        return $this->startIncluded;
    }

    /**
     * Retrieve the body block character.
     */
    public function body(): string
    {
        return $this->body;
    }

    /**
     * Retrieve the excluded end block character.
     */
    public function endExcluded(): string
    {
        return $this->endExcluded;
    }

    /**
     * Retrieve the excluded end block character.
     */
    public function endIncluded(): string
    {
        return $this->endIncluded;
    }

    /**
     * Retrieve the row space character.
     */
    public function space(): string
    {
        return $this->space;
    }

    /**
     * The selected colors for each rows.
     *
     * @return string[]
     */
    public function colors(): array
    {
        return $this->colorCodeIndexes;
    }

    /**
     * Return an instance with the specified row width.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified width.
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
     * Return an instance with the end excluded pattern.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified end excluded character.
     */
    public function withEndExcluded(string $endExcluded): self
    {
        $endExcluded = $this->filterPattern($endExcluded, 'endExcluded');
        if ($endExcluded === $this->endExcluded) {
            return $this;
        }

        $clone = clone $this;
        $clone->endExcluded = $endExcluded;

        return $clone;
    }

    /**
     * Return an instance with the end included pattern.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified end included character.
     */
    public function withEndIncluded(string $endIncluded): self
    {
        $endIncluded = $this->filterPattern($endIncluded, 'endIncluded');
        if ($endIncluded === $this->endIncluded) {
            return $this;
        }

        $clone = clone $this;
        $clone->endIncluded = $endIncluded;

        return $clone;
    }
    /**
     * Return an instance with the specified body block.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified body pattern.
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
     * Return an instance with the start excluded pattern.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified start excluded character.
     */
    public function withStartExcluded(string $startExcluded): self
    {
        $startExcluded = $this->filterPattern($startExcluded, 'startExcluded');
        if ($startExcluded === $this->startExcluded) {
            return $this;
        }

        $clone = clone $this;
        $clone->startExcluded = $startExcluded;

        return $clone;
    }

    /**
     * Return an instance with the start included pattern.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified start included character.
     */
    public function withStartIncluded(string $startIncluded): self
    {
        $startIncluded = $this->filterPattern($startIncluded, 'startIncluded');
        if ($startIncluded === $this->startIncluded) {
            return $this;
        }

        $clone = clone $this;
        $clone->startIncluded = $startIncluded;

        return $clone;
    }

    /**
     * Return an instance with the space pattern.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified space character.
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
     * Return an instance with a new gap sequence.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified gap sequence.
     */
    public function withGapSize(int $size): self
    {
        if ($size === $this->gapSize) {
            return $this;
        }

        if ($size < 0) {
            $size = 1;
        }

        $clone = clone $this;
        $clone->gapSize = $size;

        return $clone;
    }

    /**
     * Return an instance with a new color palette.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified color palette.
     *
     * @param string... $colorCodeIndexes
     */
    public function withColors(string ...$colorCodeIndexes): self
    {
        $filter = static function ($value) {
            return array_key_exists($value, Writer::POSIX_COLOR_CODES);
        };

        $colorCodeIndexes = array_filter(array_map('strtolower', $colorCodeIndexes), $filter);
        if ([] === $colorCodeIndexes) {
            $colorCodeIndexes = [ConsoleStdout::DEFAULT_COLOR_CODE_INDEX];
        }

        if ($colorCodeIndexes === $this->colorCodeIndexes) {
            return $this;
        }

        $clone = clone $this;
        $clone->colorCodeIndexes = $colorCodeIndexes;

        return $clone;
    }

    /**
     * Return an instance with a left padding.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that set a left padding to the line label.
     */
    public function withPadding(int $padding): self
    {
        if (!in_array($padding, [STR_PAD_LEFT, STR_PAD_RIGHT, STR_PAD_BOTH], true)) {
            $padding = STR_PAD_RIGHT;
        }

        if ($this->padding === $padding) {
            return $this;
        }

        $clone = clone $this;
        $clone->padding = $padding;

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
}
