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

use Bakame\Period\Visualizer\Contract\LabelGenerator;
use League\Period\Sequence;
use function array_map;
use function count;
use function in_array;
use function is_scalar;
use function method_exists;
use function range;
use function str_pad;
use function strlen;
use const STR_PAD_LEFT;

final class DecimalNumber implements LabelGenerator
{
    public const NO_PADDING = 0;

    public const LEFT_PAD = 1;

    /**
     * @var int
     */
    private $int;

    /**
     * @var int
     */
    private $padding;

    /**
     * New instance.
     */
    public function __construct(int $int = 1, int $padding = self::NO_PADDING)
    {
        if (0 >= $int) {
            $int = 1;
        }

        if (!in_array($padding, [self::NO_PADDING, self::LEFT_PAD], true)) {
            $padding = self::NO_PADDING;
        }

        $this->int = $int;
        $this->padding = $padding;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(Sequence $sequence): array
    {
        $nbItems = count($sequence);
        if (0 === $nbItems) {
            return [];
        }

        $end = $this->int + $nbItems - 1;
        $values = range($this->int, $end);

        if (self::NO_PADDING === $this->padding) {
            return array_map([$this, 'format'], $values);
        }

        $pad_length = strlen((string) $end);
        $mapper = function (int $value) use ($pad_length) {
            return $this->format(str_pad((string) $value, $pad_length, '0', STR_PAD_LEFT));
        };

        return array_map($mapper, $values);
    }

    /**
     * {@inheritdoc}
     */
    public function format($str): string
    {
        if (is_scalar($str) || method_exists($str, '__toString') || null === $str) {
            return (string) $str;
        }

        return '';
    }

    /**
     * Returns the starting Letter.
     */
    public function startingAt(): int
    {
        return $this->int;
    }

    /**
     * Tell whether left padding with zerofill value is used.
     */
    public function isPadded(): bool
    {
        return $this->padding === self::LEFT_PAD;
    }

    /**
     * Return an instance with the starting Letter.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the starting Letter.
     */
    public function startsWith(int $int): self
    {
        if (0 >= $int) {
            $int = 1;
        }

        if ($int === $this->int) {
            return $this;
        }

        $clone = clone $this;
        $clone->int = $int;

        return $clone;
    }

    /**
     * Return an instance with the new padding setting.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance with padding.
     */
    public function withPadding(): self
    {
        if (self::LEFT_PAD === $this->padding) {
            return $this;
        }

        $clone = clone $this;
        $clone->padding = self::LEFT_PAD;

        return $clone;
    }

    /**
     * Return an instance with the new padding setting.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance without padding.
     */
    public function withoutPadding(): self
    {
        if (self::NO_PADDING === $this->padding) {
            return $this;
        }

        $clone = clone $this;
        $clone->padding = self::NO_PADDING;

        return $clone;
    }
}
