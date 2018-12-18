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

use Bakame\Period\Visualizer\Label\GeneratorInterface;
use Bakame\Period\Visualizer\Label\LetterLabel;
use League\Period\Period;
use League\Period\Sequence;

final class SequenceViewer
{
    /**
     * @var VisualizerInterface
     */
    private $visualizer;

    /**
     * @var GeneratorInterface
     */
    private $labelGenerator;

    /**
     * Create a new visualizer.
     *
     * @param ?VisualizerInterface $visualizer
     * @param ?GeneratorInterface  $label
     */
    public function __construct(?VisualizerInterface $visualizer = null, ?GeneratorInterface $label = null)
    {
        $this->setVisualizer($visualizer ?? new Visualizer());
        $this->setLabelGenerator($label ?? new LetterLabel());
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
    public function getLabelGenerator(): GeneratorInterface
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
    public function setLabelGenerator(GeneratorInterface $label): void
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
