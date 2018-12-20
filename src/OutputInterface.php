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

use League\Period\Period;
use League\Period\Sequence;

/**
 * Interface to output the matrix.
 */
interface OutputInterface
{
    /**
     * Builds an Iterator to visualize one or more
     * periods and/or collections in a more
     * human readable / parsable manner.
     *
     * Keys are used as identifiers in the output
     * and the periods are represented with bars.
     *
     * This visualizer is capable of generating
     * output like the following:
     *
     * A       [========]
     * B                    [==]
     * C                            [=====]
     * CURRENT        [===============]
     * OVERLAP        [=]   [==]    [=]
     *
     * @param array<int, array<int|string, Period|Sequence>> $blocks
     */
    public function render(array $blocks): iterable;

    /**
     * Builds a string to visualize one or more
     * periods and/or collections in a more
     * human readable / parsable manner.
     *
     * Keys are used as identifiers in the output
     * and the periods are represented with bars.
     *
     * This visualizer is capable of generating
     * output like the following:
     *
     * A       [========]
     * B                    [==]
     * C                            [=====]
     * CURRENT        [===============]
     * OVERLAP        [=]   [==]    [=]
     *
     * @param array<int, array<int|string, Period|Sequence>> $blocks
     */
    public function display(array $blocks): string;
}
