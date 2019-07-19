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

use Bakame\Period\Visualizer\Tuple;

/**
 * A class to output to the console the matrix.
 */
interface Output
{
    /**
     * Builds a string to visualize one or more intervals.
     *
     * The generated string can be represented like the following
     * and depends on the Configuration used
     *
     * The returned int represents the number of bytes used to
     * generated the display.
     *
     * A       [--------)
     * B                    [--)
     * C                            [-----)
     * D              [---------------)
     * RESULT         [-)   [--)    [-)
     */
    public function display(Tuple $tuple): int;
}
