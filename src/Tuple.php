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
use function is_scalar;
use function method_exists;

final class Tuple implements Countable, IteratorAggregate
{
    /**
     * The tuple collection.
     *
     * Each tuple is an array with two value.
     * The first value at index 0 represents the label name and is a string.
     * The second value at index 1 represents a Period or a Sequence object.
     *
     * @var array<int, array<int, Period|Sequence|string>>.
     */
    private $pairs = [];

    /**
     * Tuple constructor.
     */
    public function __construct(iterable $pairs = [])
    {
        foreach ($pairs as [$offset, $value]) {
            $this->addPair($offset, $value);
        }
    }

    /**
     * Creates a new collection from a Sequence and a LabelGenerator.
     */
    public static function fromSequence(Sequence $sequence, LabelGenerator $labelGenerator): self
    {
        $tuple = new self();
        if ($sequence->isEmpty()) {
            return $tuple;
        }

        $labels = $labelGenerator->generate($sequence);
        foreach ($sequence as $offset => $period) {
            $tuple->addPair($labels[$offset], $period);
        }

        return $tuple;
    }

    /**
     * A a new pair to the collection.
     *
     * @param mixed $label any stringable structure.
     * @param mixed $input if the input is not a Period or a Sequence instance it is not added.
     */
    public function addPair($label, $input): void
    {
        if (null !== $label && !is_scalar($label) && !method_exists($label, '__toString')) {
            return;
        }

        if ($input instanceof Period || $input instanceof Sequence) {
            $this->pairs[] = [(string) $label, $input];
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
     * Returns the collection boundaries.
     */
    public function boundaries(): ?Period
    {
        $sequence = new Sequence();
        foreach ($this->pairs as [$name, $item]) {
            if ($item instanceof Period) {
                $sequence->push($item);
            } elseif ($item instanceof Sequence) {
                $sequence->push(...$item);
            }
        }

        return $sequence->boundaries();
    }
}
