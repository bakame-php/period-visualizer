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

interface LabelGenerator
{
    /**
     * Returns the labels to associate with all items.
     */
    public function generate(int $nbLabels): array;

    /**
     * Returns a formatted label according to the generator rules.
     */
    public function format(string $label): string;
}
