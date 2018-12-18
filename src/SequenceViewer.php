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

use Bakame\Period\Visualizer\Label\LabelGenerator;
use Bakame\Period\Visualizer\Label\LetterType;
use League\Period\Period;
use League\Period\Sequence;

final class SequenceViewer
{
    /**
     * @var VisualizerInterface
     */
    private $visualizer;

    /**
     * @var LabelGenerator
     */
    private $labelGenerator;

    /**
     * Create a new visualizer.
     *
     * @param ?VisualizerInterface $visualizer
     * @param ?LabelGenerator      $label
     */
    public function __construct(?VisualizerInterface $visualizer = null, ?LabelGenerator $label = null)
    {
        $this->setVisualizer($visualizer ?? new Visualizer());
        $this->setLabelGenerator($label ?? new LetterType());
    }

    /**
     * Returns the Visualizer.
     */
    public function getVisualizer(): VisualizerInterface
    {
        return $this->visualizer;
    }

    /**
     * Returns the Label Generator.
     */
    public function getLabelGenerator(): LabelGenerator
    {
        return $this->labelGenerator;
    }

    /**
     * Sets the Visualizer.
     */
    public function setVisualizer(VisualizerInterface $visualizer): void
    {
        $this->visualizer = $visualizer;
    }

    /**
     * Sets the Label Generator.
     */
    public function setLabelGenerator(LabelGenerator $label): void
    {
        $this->labelGenerator = $label;
    }

    /**
     * Returns the sequence view representation.
     */
    public function sequence(Sequence $sequence): string
    {
        $input = $this->getInputData($sequence);

        return $this->visualizer->display($input);
    }

    /**
     * Returns the sequence intersections view representation.
     */
    public function intersections(Sequence $sequence): string
    {
        $input = $this->getInputData($sequence);
        $input['INTERSECTIONS'] = $sequence->getIntersections();

        return $this->visualizer->display($input);
    }

    /**
     * Returns the sequence gaps view representation.
     */
    public function gaps(Sequence $sequence): string
    {
        $input = $this->getInputData($sequence);
        $input['GAPS'] = $sequence->getGaps();

        return $this->visualizer->display($input);
    }

    public function diff(Period $interval1, Period $interval2): string
    {
        $res = $interval1->diff($interval2);
        $sequence = new Sequence($interval1, $interval2);
        $diff = new Sequence();
        foreach ($res as $part) {
            if (null !== $part) {
                $diff->push($part);
            }
        }

        $input = $this->getInputData($sequence);
        $input['DIFF'] = $diff;

        return $this->visualizer->display($input);
    }

    /**
     * Format the sequence data to be shown.
     *
     * @return array<string,Period>
     */
    private function getInputData(Sequence $sequence): array
    {
        return array_combine($this->labelGenerator->getLabels($sequence), $sequence->toArray());
    }
}
