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

/**
 * A class to output to the console the matrix.
 */
interface Graph
{
    /**
     * Builds a string to visualize one or more intervals.
     */
    public function display(Pairs $pairs): void;
}
