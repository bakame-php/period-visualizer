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
use League\Period\Sequence;
use function array_reverse;

final class ReverseLabel implements LabelGenerator
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
    public function generate(Sequence $sequence): array
    {
        return array_reverse($this->labelGenerator->generate($sequence));
    }

    /**
     * {@inheritdoc}
     */
    public function format($str): string
    {
        return $this->labelGenerator->format($str);
    }
}
