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
use Countable;
use Iterator;
use IteratorAggregate;
use League\Period\Period;
use League\Period\Sequence;
use function array_column;
use function array_map;
use function count;
use function is_scalar;
use function max;
use function method_exists;

final class Tuple implements Countable, IteratorAggregate
{
    /**
     * @var array<int, array{0:string, 1:Sequence|Period}>.
     */
    private $pairs = [];

    /**
     * Tuple constructor.
     */
    public function __construct(iterable $pairs = [])
    {
        foreach ($pairs as [$label, $item]) {
            $this->append($label, $item);
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
        $tuple = new self();
        foreach ($sequence as $item) {
            $tuple->append($labels[$index], $item);
            ++$index;
        }

        return $tuple;
    }

    /**
     * Creates a new collection from a generic iterable structure.
     */
    public static function fromCollection(iterable $iterable): self
    {
        $tuple = new self();
        foreach ($iterable as $label => $item) {
            $tuple->append($label, $item);
        }

        return $tuple;
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
     * Tells whether the collection is empty.
     */
    public function isEmpty(): bool
    {
        return [] === $this->pairs;
    }

    /**
     * Returns the collection boundaries.
     */
    public function boundaries(): ?Period
    {
        $sequence = new Sequence();
        foreach ($this->pairs as [$label, $item]) {
            if ($item instanceof Period) {
                $sequence->push($item);
                continue;
            }
            $sequence->push(...$item);
        }

        return $sequence->boundaries();
    }

    /**
     * @return string[]
     */
    public function labels(): array
    {
        return array_column($this->pairs, 0);
    }

    /**
     * Returns the label maximum length.
     */
    public function labelMaxLength(): int
    {
        return  max(...array_map('strlen', array_column($this->pairs, 0)));
    }

    /**
     * @return array<Period|Sequence>
     */
    public function items(): array
    {
        return array_column($this->pairs, 1);
    }

    /**
     * Add a new pair to the collection.
     *
     * @param mixed $label any stringable structure.
     * @param mixed $item  if the input is not a Period or a Sequence instance it is not added.
     */
    public function append($label, $item): void
    {
        if (!is_scalar($label) && !method_exists($label, '__toString')) {
            return;
        }

        $label = (string) $label;
        if ($item instanceof Period || $item instanceof Sequence) {
            $this->pairs[] = [$label, $item];
        }
    }

    /**
     * Update the labels used for the tuple.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the newly specified labels.
     */
    public function labelize(LabelGenerator $labelGenerator): self
    {
        return self::fromSequence($this->items(), $labelGenerator);
    }
}
