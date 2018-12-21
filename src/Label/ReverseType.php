<?php

/**
 * League.Period Visualizer (https://github.com/bakame-php/period-visualizer).
 *
 * (c) Ignace Nyamagana Butera <nyamsprod@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bakame\Period\Visualizer\Label;

use League\Period\Sequence;
use function array_reverse;

final class ReverseType implements LabelGenerator
{
    /**
     * @var LabelGenerator
     */
    private $labelGenerator;

    /**
     * New instance.
     */
    public function __construct(LabelGenerator $labelGenerator)
    {
        $this->labelGenerator = $labelGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabels(Sequence $sequence): array
    {
        return array_reverse($this->labelGenerator->getLabels($sequence));
    }
}
