<?php

/**
 * League.Period Visualizer (https://github.com/bakame-php/period-visualizer).
 *
 * (c) Ignace Nyamagana Butera <nyamsprod@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bakame\Period\Visualizer\Label;

use League\Period\Sequence;

interface LabelGenerator
{
    /**
     * Returns the label to associate with each view row.
     *
     * @return string[]
     */
    public function getLabels(Sequence $sequence): array;
}
