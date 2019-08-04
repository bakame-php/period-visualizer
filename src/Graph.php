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

/**
 * A class to output to the console the matrix.
 */
interface Graph
{
    /**
     * Visualizes one or more intervals in a provided via a Dataset object.
     */
    public function display(Dataset $dataset): void;
}
