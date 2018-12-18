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

namespace BakameTest\Period\Visualizer;

use Bakame\Period\Visualizer\Configuration;
use Bakame\Period\Visualizer\Label\LetterType;
use Bakame\Period\Visualizer\SequenceViewer;
use Bakame\Period\Visualizer\Visualizer;
use League\Period\Period;
use League\Period\Sequence;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass Bakame\Period\Visualizer\SequenceViewer
 */
final class SequenceViewerTest extends TestCase
{
    /**
     * @var SequenceViewer
     */
    private $view;

    public function setUp(): void
    {
        $this->view = new SequenceViewer();
    }

    public function testLabelGeneratorAccess(): void
    {
        $labelGenerator = $this->view->getLabelGenerator();
        self::assertInstanceOf(LetterType::class, $labelGenerator);
        $this->view->setLabelGenerator(new LetterType('bb'));
        self::assertNotSame($labelGenerator, $this->view->getLabelGenerator());
    }

    public function testVisualizerAccess(): void
    {
        $visualizer = $this->view->getVisualizer();
        self::assertInstanceOf(Visualizer::class, $visualizer);
        $this->view->setVisualizer(new Visualizer(Configuration::createFromRainbow()));
        self::assertNotSame($visualizer, $this->view->getVisualizer());
    }

    public function testDisplaySequence(): void
    {
        $data = $this->view->sequence(new Sequence(
            new Period('2018-01-01', '2018-01-15'),
            new Period('2018-01-15', '2018-02-01')
        ));

        self::assertContains('A    [===]', $data);
        self::assertContains('B        [====]', $data);
    }

    public function testDisplayEmptySequence(): void
    {
        $data = $this->view->sequence(new Sequence());
        self::assertEmpty($data);
    }

    public function testDisplayIntersection(): void
    {
        $data = $this->view->intersections(new Sequence(
            new Period('2018-01-01', '2018-01-15'),
            new Period('2018-01-10', '2018-02-01')
        ));

        self::assertContains('A                [===]', $data);
        self::assertContains('B                  [======]', $data);
        self::assertContains('INTERSECTIONS      [=]', $data);
    }

    public function testGaps(): void
    {
        $data = $this->view->gaps(new Sequence(
            new Period('2018-01-01', '2018-01-10'),
            new Period('2018-01-15', '2018-02-01')
        ));

        self::assertContains('A       [=]', $data);
        self::assertContains('B           [====]', $data);
        self::assertContains('GAPS      [=]', $data);
    }

    public function testSingleUnitIntervalLength(): void
    {
        $data = $this->view->sequence(new Sequence(
            new Period('2018-01-01', '2018-02-01'),
            new Period('2017-01-01', '2019-01-01')
        ));
        self::assertContains('A         =    ', $data);
        self::assertContains('B    [========]', $data);
    }
}
