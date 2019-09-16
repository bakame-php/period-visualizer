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

use League\Period\Period;
use League\Period\Sequence;
use function array_column;
use function count;
use function is_scalar;
use function method_exists;
use function strlen;

final class Dataset implements \Countable, \IteratorAggregate, \JsonSerializable
{
    /**
     * @var array<int, array{0:string, 1:Sequence}>.
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
     * Add a collection of pairs to the collection.
     */
    public function appendAll(iterable $pairs): void
    {
        foreach ($pairs as [$label, $item]) {
            $this->append($label, $item);
        }
    }

    /**
     * Add a new pair to the collection.
     *
     * @param mixed $label if the label is not stringable it is not added (the null value excluded).
     * @param mixed $item  if the item is not a League\Period\Period or a League\Period\Sequence instance
     *                     it is not added.
     */
    public function append($label, $item): void
    {
        if (!is_scalar($label) && !method_exists($label, '__toString')) {
            return;
        }

        if ($item instanceof Period) {
            $item = new Sequence($item);
        }

        if (!$item instanceof Sequence) {
            return;
        }

        $label = (string) $label;
        $this->setLabelMaxLength($label);
        $this->setBoundaries($item);

        $this->pairs[] = [$label, $item];
    }

    /**
     * Computes the label maximum length for the dataset.
     */
    private function setLabelMaxLength(string $label): void
    {
        $labelLength = strlen($label);
        if ($this->labelMaxLength < $labelLength) {
            $this->labelMaxLength = $labelLength;
        }
    }

    /**
     * Computes the Period boundary for the dataset.
     */
    private function setBoundaries(Sequence $sequence): void
    {
        if (null === $this->boundaries) {
            $this->boundaries = $sequence->boundaries();

            return;
        }

        $this->boundaries = $this->boundaries->merge(...$sequence);
    }

    /**
     * Creates a new collection from a countable iterable structure.
     *
     * @param array|(\Countable&iterable) $sequence
     * @param ?LabelGenerator             $labelGenerator
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
     * Returns the number of pairs.
     */
    public function count(): int
    {
        return count($this->pairs);
    }

    /**
     * Returns the pairs.
     *
     * @return \Iterator<int, array{0: string, 1: Sequence}>
     */
    public function getIterator(): \Iterator
    {
        foreach ($this->pairs as $pair) {
            yield $pair;
        }
    }

    public function jsonSerialize(): array
    {
        return array_map(function (array $pair) {
            return ['label' => $pair[0], 'item' => $pair[1]];
        }, $this->pairs);
    }

    /**
     * Tells whether the collection is empty.
     */
    public function isEmpty(): bool
    {
        return [] === $this->pairs;
    }

    /**
     * @return string[]
     */
    public function labels(): array
    {
        return array_column($this->pairs, 0);
    }

    /**
     * @return Sequence[]
     */
    public function items(): array
    {
        return array_column($this->pairs, 1);
    }

    /**
     * Returns the dataset boundaries.
     */
    public function boundaries(): ?Period
    {
        return $this->boundaries;
    }

    /**
     * Returns the label maximum length.
     */
    public function labelMaxLength(): int
    {
        return $this->labelMaxLength;
    }

    /**
     * Update the labels used for the dataset.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the newly specified labels.
     */
    public function withLabels(LabelGenerator $labelGenerator): self
    {
        return self::fromSequence($this->items(), $labelGenerator);
    }
}
