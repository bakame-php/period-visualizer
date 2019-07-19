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
     * @var array<int, array{0:string, 1:Sequence|Period}>.
     */
    private $pairs = [];

    /**
     * Tuple constructor.
     */
    public function __construct(iterable $pairs = [])
    {
        foreach ($pairs as [$offset, $value]) {
            $this->append($offset, $value);
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
            $tuple->append($labels[$offset], $period);
        }

        return $tuple;
    }

    /**
     * Creates a new collection from a generic iterable structure.
     */
    public static function fromIterable(iterable $iterable): self
    {
        $tuple = new self();
        foreach ($iterable as $offset => $value) {
            $tuple->append($offset, $value);
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
        foreach ($this->pairs as [$name, $item]) {
            if ($item instanceof Period) {
                $sequence->push($item);
                continue;
            }
            $sequence->push(...$item);
        }

        return $sequence->boundaries();
    }

    /**
     * Add a new pair to the collection.
     *
     * @param mixed $label any stringable structure.
     * @param mixed $input if the input is not a Period or a Sequence instance it is not added.
     */
    public function append($label, $input): void
    {
        if (!is_scalar($label) && !method_exists($label, '__toString')) {
            return;
        }

        if ($input instanceof Period || $input instanceof Sequence) {
            $this->pairs[] = [(string) $label, $input];
        }
    }
}
