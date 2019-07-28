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

namespace Bakame\Period\Visualizer\Contract;

use Countable;
use IteratorAggregate;
use League\Period\Period;
use League\Period\Sequence;

interface Pairs extends Countable, IteratorAggregate
{
    /**
     * Tells whether the collection is empty.
     */
    public function isEmpty(): bool;

    /**
     * @return string[]
     */
    public function labels(): iterable;

    /**
     * @return array<Period|Sequence>
     */
    public function items(): iterable;

    /**
     * Returns the collection boundaries.
     */
    public function boundaries(): ?Period;

    /**
     * Returns the label maximum length.
     */
    public function labelMaxLength(): int;

    /**
     * Add a new pair to the collection.
     *
     * @param mixed $label any stringable structure.
     * @param mixed $item  if the input is not a Period or a Sequence instance it is not added.
     */
    public function append($label, $item): void;

    /**
     * Update the labels used for the dataset.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the newly specified labels.
     */
    public function labelize(LabelGenerator $labelGenerator): self;
}
