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

namespace BakameTest\Period\Visualizer;

use Bakame\Period\Visualizer\ConsoleOutput;
use Bakame\Period\Visualizer\Dataset;
use Bakame\Period\Visualizer\GanttChart;
use Bakame\Period\Visualizer\GanttChartConfig;
use League\Period\Period;
use League\Period\Sequence;
use PHPUnit\Framework\TestCase;
use function fopen;
use function rewind;
use function stream_get_contents;

/**
 * @coversDefaultClass \Bakame\Period\Visualizer\GanttChart
 */
final class GanttChartTest extends TestCase
{
    /**
     * @var GanttChart
     */
    private $graph;

    /**
     * @var resource
     */
    private $stream;

    public function setUp(): void
    {
        $this->stream = $this->setStream();

        $this->graph = new GanttChart(
            (new GanttChartConfig())->withColors('red'),
            new ConsoleOutput($this->stream)
        );
    }

    /**
     * @return resource
     */
    private function setStream()
    {
        /** @var resource $stream */
        $stream = fopen('php://memory', 'r+');

        return $stream;
    }

    /**
     * @covers ::__construct
     */
    public function testConstructor(): void
    {
        $graph = new GanttChart();
        self::assertNotEquals($this->graph, $graph);
    }

    /**
     * @covers ::display
     * @covers ::setChartScale
     */
    public function testDisplayEmptyDataset(): void
    {
        $this->graph->display(new Dataset());
        rewind($this->stream);
        $data = stream_get_contents($this->stream);

        self::assertSame('', $data);
    }

    /**
     * @covers ::display
     * @covers ::setChartScale
     * @covers ::drawDataPortion
     * @covers \Bakame\Period\Visualizer\ConsoleOutput
     */
    public function testDisplayPeriods(): void
    {
        $this->graph->display(new Dataset([
            ['A', new Period('2018-01-01', '2018-01-15')],
            ['B', new Period('2018-01-15', '2018-02-01')],
        ]));

        rewind($this->stream);
        /** @var string $data */
        $data = stream_get_contents($this->stream);

        self::assertStringContainsString('A [--------------------------)', $data);
        self::assertStringContainsString('B                            [-------------------------------)', $data);
    }

    /**
     * @covers ::display
     * @covers ::setChartScale
     * @covers ::drawDataPortion
     */
    public function testDisplaySequence(): void
    {
        $dataset = new Dataset([
            ['A', new Sequence(new Period('2018-01-01', '2018-01-15'))],
            ['B', new Sequence(new Period('2018-01-15', '2018-02-01'))],
        ]);

        $this->graph->display($dataset);

        rewind($this->stream);
        /** @var string $data */
        $data = stream_get_contents($this->stream);

        self::assertStringContainsString('A [--------------------------)', $data);
        self::assertStringContainsString('B                            [-------------------------------)', $data);
    }

    /**
     * @covers ::display
     * @covers ::setChartScale
     * @covers ::drawDataPortion
     */
    public function testDisplayEmptySequence(): void
    {
        $dataset = new Dataset();
        $dataset->append('sequenceA', new Sequence());
        $dataset->append('sequenceB', new Sequence());
        $this->graph->display($dataset);

        rewind($this->stream);
        /** @var string $data */
        $data = stream_get_contents($this->stream);

        self::assertStringContainsString('sequenceA                                  ', $data);
        self::assertStringContainsString('sequenceB                                  ', $data);
    }
}
