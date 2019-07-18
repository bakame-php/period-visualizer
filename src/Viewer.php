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
use Bakame\Period\Visualizer\Contract\Output;
use Bakame\Period\Visualizer\Contract\Visualizer;
use League\Period\Period;
use League\Period\Sequence;
use function trim;

final class Viewer implements Visualizer
{
    private const DEFAULT_RESULT_LABEL = 'RESULT';

    /**
     * @var Output
     */
    private $output;

    /**
     * @var LabelGenerator
     */
    private $labelGenerator;

    /**
     * Create a new output.
     *
     * @param ?LabelGenerator $labelGenerator
     * @param ?Output         $output
     */
    public function __construct(?LabelGenerator $labelGenerator = null, ?Output $output = null)
    {
        $this->labelGenerator = $labelGenerator ?? new LetterLabel();
        $this->output = $output ?? new ConsoleOutput();
    }

    /**
     * {@inheritDoc}
     */
    public function view(Sequence $sequence, $result = null, string $resultLabel = ''): void
    {
        if ($sequence->isEmpty()) {
            return;
        }

        $tuples = [];
        $labels = $this->labelGenerator->generate($sequence);
        foreach ($sequence as $offset => $period) {
            $tuples[] = [$labels[$offset], $period];
        }

        if ('' === trim($resultLabel)) {
            $resultLabel = self::DEFAULT_RESULT_LABEL;
        }

        $tuples[] = [$this->labelGenerator->format($resultLabel), $result];

        $this->output->display($tuples);
    }

    /**
     * Visualizes a sequence.
     */
    public function sequence(Sequence $sequence): void
    {
        $this->view($sequence);
    }

    /**
     * Visualizes a sequence intersections.
     */
    public function intersections(Sequence $sequence, string $resultLabel = 'INTERSECTIONS'): void
    {
        $this->view($sequence, $sequence->intersections(), $resultLabel);
    }

    /**
     * Visualizes a sequence gaps.
     */
    public function gaps(Sequence $sequence, string $resultLabel = 'GAPS'): void
    {
        $this->view($sequence, $sequence->gaps(), $resultLabel);
    }

    /**
     * Visualizes a sequence unions.
     */
    public function unions(Sequence $sequence, string $resultLabel = 'UNIONS'): void
    {
        $this->view($sequence, $sequence->unions(), $resultLabel);
    }

    /**
     * Visualizes a sequence diff.
     */
    public function diff(Period $interval1, Period $interval2, string $resultLabel = 'DIFF'): void
    {
        $diff = new Sequence();
        foreach ($interval1->diff($interval2) as $part) {
            if (null !== $part) {
                $diff->push($part);
            }
        }

        $this->view(new Sequence($interval1, $interval2), $diff, $resultLabel);
    }
}
