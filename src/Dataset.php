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
use Bakame\Period\Visualizer\Contract\Pairs;
use Countable;
use Iterator;
use League\Period\Period;
use League\Period\Sequence;
use function array_column;
use function count;
use function is_scalar;
use function method_exists;
use function strlen;

final class Dataset implements Pairs
{
    /**
     * @var array<int, array{0:string, 1:Sequence|Period}>.
     */
    private $pairs = [];

    /**
     * @var int
     */
    private $labelMaxLength = 0;

    /**
     * @var Period|null
     */
    private $boundaries;

    /**
     * constructor.
     */
    public function __construct(iterable $pairs = [])
    {
        $this->appendAll($pairs);
    }

    /**
     * {@inheritDoc}
     */
    public function appendAll(iterable $pairs): void
    {
        foreach ($pairs as [$label, $item]) {
            $this->append($label, $item);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function append($label, $item): void
    {
        if (!is_scalar($label) && !method_exists($label, '__toString')) {
            return;
        }

        if (!$item instanceof Period && !$item instanceof Sequence) {
            return;
        }

        $label = (string) $label;
        $this->setLabelMaxLength($label);
        $this->setBoundaries($item);

        $this->pairs[] = [$label, $item];
    }

    private function setLabelMaxLength(string $label): void
    {
        $labelLength = strlen($label);
        if ($this->labelMaxLength < $labelLength) {
            $this->labelMaxLength = $labelLength;
        }
    }

    /**
     * @param Period|Sequence $item
     */
    private function setBoundaries($item): void
    {
        if ($item instanceof Period) {
            if (null === $this->boundaries) {
                $this->boundaries = $item;

                return;
            }

            $this->boundaries = $this->boundaries->merge($item);
        }

        if ($item instanceof Sequence) {
            if (null === $this->boundaries) {
                $this->boundaries = $item->boundaries();

                return;
            }

            $this->boundaries = $this->boundaries->merge(...$item);
        }
    }

    /**
     * Creates a new collection from a countable iterable structure.
     *
     * @param array|(Countable&iterable) $sequence
     * @param ?LabelGenerator            $labelGenerator
     */
    public static function fromSequence($sequence, ?LabelGenerator $labelGenerator = null): self
    {
        $labelGenerator = $labelGenerator ?? new LatinLetter();
        $labels = $labelGenerator->generate(count($sequence));
        $index = 0;
        $dataset = new self();
        foreach ($sequence as $item) {
            $dataset->append($labels[$index], $item);
            ++$index;
        }

        return $dataset;
    }

    /**
     * Creates a new collection from a generic iterable structure.
     */
    public static function fromCollection(iterable $iterable): self
    {
        $dataset = new self();
        foreach ($iterable as $label => $item) {
            $dataset->append($label, $item);
        }

        return $dataset;
    }

    /**
     * {@inheritDoc}
     */
    public function count(): int
    {
        return count($this->pairs);
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator(): Iterator
    {
        foreach ($this->pairs as $pair) {
            yield $pair;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function isEmpty(): bool
    {
        return [] === $this->pairs;
    }

    /**
     * {@inheritDoc}
     */
    public function labels(): array
    {
        return array_column($this->pairs, 0);
    }

    /**
     * {@inheritDoc}
     */
    public function items(): array
    {
        return array_column($this->pairs, 1);
    }

    /**
     * {@inheritDoc}
     */
    public function boundaries(): ?Period
    {
        return $this->boundaries;
    }

    /**
     * {@inheritDoc}
     */
    public function labelMaxLength(): int
    {
        return $this->labelMaxLength;
    }

    /**
     * {@inheritDoc}
     */
    public function withLabels(LabelGenerator $labelGenerator): Pairs
    {
        return self::fromSequence($this->items(), $labelGenerator);
    }
}
