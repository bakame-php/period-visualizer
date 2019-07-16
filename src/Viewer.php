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

use Bakame\Period\Visualizer\Label\LabelGenerator;
use Bakame\Period\Visualizer\Label\LetterGenerator;
use League\Period\Period;
use League\Period\Sequence;
use TypeError;
use function trim;

final class Viewer
{
    private const DEFAULT_RESULT_LABEL = 'RESULT';

    /**
     * @var ConsoleOutput
     */
    private $output;

    /**
     * @var LabelGenerator
     */
    private $labelGenerator;

    /**
     * Create a new output.
     *
     * @param ?LabelGenerator $label
     * @param ?ConsoleOutput  $output
     */
    public function __construct(?LabelGenerator $label = null, ?ConsoleOutput $output = null)
    {
        $this->labelGenerator =  $label ?? new LetterGenerator();
        $this->output = $output ?? new ConsoleOutput();
    }

    /**
     * @param Sequence|Period|null $result
     *
     * @throws TypeError
     */
    public function view(Sequence $sequence, $result = null, string $resultLabel = ''): void
    {
        $input = $this->addLabels($sequence);
        $input[] = [$this->filterResultLabel($resultLabel), $result];

        $this->output->display($input);
    }

    /**
     * Attach the labels to the sequence.
     */
    private function addLabels(Sequence $sequence): array
    {
        $labels = $this->labelGenerator->generate($sequence);
        $results = [];
        foreach ($sequence as $offset => $period) {
            $results[] = [$labels[$offset], $period];
        }

        return $results;
    }

    /**
     * Format the result label.
     */
    private function filterResultLabel(string $label): string
    {
        $label = trim($label);
        if ('' === $label) {
            return $this->labelGenerator->format(self::DEFAULT_RESULT_LABEL);
        }

        return $this->labelGenerator->format($label);
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
