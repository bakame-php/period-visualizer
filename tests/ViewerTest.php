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

use Bakame\Period\Visualizer\ConsoleConfig;
use Bakame\Period\Visualizer\ConsoleOutput;
use Bakame\Period\Visualizer\Label\LetterGenerator;
use Bakame\Period\Visualizer\Viewer;
use League\Period\Datepoint;
use League\Period\Period;
use League\Period\Sequence;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Bakame\Period\Visualizer\Viewer
 */
final class ViewerTest extends TestCase
{
    /**
     * @var Viewer
     */
    private $view;

    public function setUp(): void
    {
        $this->view = new Viewer();
    }

    /**
     * @covers ::__construct
     */
    public function testConstructor(): void
    {
        $viewer = new Viewer();
        self::assertInstanceOf(LetterGenerator::class, $viewer->getLabelGenerator());
        self::assertEquals(new ConsoleOutput(), $viewer->getOutput());
    }

    /**
     * @covers ::getLabelGenerator
     * @covers ::setLabelGenerator
     */
    public function testLabelGenerator(): void
    {
        $labelGenerator = $this->view->getLabelGenerator();
        self::assertInstanceOf(LetterGenerator::class, $labelGenerator);
        $this->view->setLabelGenerator(new LetterGenerator('bb'));
        self::assertNotSame($labelGenerator, $this->view->getLabelGenerator());
    }

    /**
     * @covers ::getOutput
     * @covers ::setOutput
     */
    public function testOutput(): void
    {
        $output = $this->view->getOutput();
        $this->view->setOutput(new ConsoleOutput(ConsoleConfig::createFromRainbow()));
        self::assertNotSame($output, $this->view->getOutput());
    }

    /**
     * @covers ::sequence
     * @covers ::filterResultLabel
     * @covers ::addLabels
     */
    public function testDisplaySequence(): void
    {
        $data = $this->view->sequence(new Sequence(
            new Period('2018-01-01', '2018-01-15'),
            new Period('2018-01-15', '2018-02-01')
        ));

        self::assertStringContainsString('A    [--------------------------)', $data);
        self::assertStringContainsString('B                               [-------------------------------)', $data);
    }

    /**
     * @covers ::sequence
     */
    public function testDisplayEmptySequence(): void
    {
        $data = $this->view->sequence(new Sequence());
        self::assertEmpty($data);
    }

    /**
     * @covers ::intersections
     * @covers ::filterResultLabel
     * @covers ::addLabels
     */
    public function testDisplayIntersection(): void
    {
        $data = $this->view->intersections(new Sequence(
            new Period('2018-01-01', '2018-01-15'),
            new Period('2018-01-10', '2018-02-01')
        ));

        self::assertStringContainsString('A                [--------------------------)', $data);
        self::assertStringContainsString('B                                 [-----------------------------------------)', $data);
        self::assertStringContainsString('INTERSECTIONS                     [---------)', $data);
    }

    /**
     * @covers ::gaps
     * @covers ::filterResultLabel
     * @covers ::addLabels
     */
    public function testGaps(): void
    {
        $data = $this->view->gaps(new Sequence(
            new Period('2018-01-01', '2018-01-10'),
            new Period('2018-01-15', '2018-02-01', Period::EXCLUDE_ALL)
        ), '');

        self::assertStringContainsString('A         [----------------)', $data);
        self::assertStringContainsString('B                                    (-------------------------------)', $data);
        self::assertStringContainsString('RESULT                     [---------]', $data);
    }

    /**
     * @covers ::sequence
     * @covers ::addLabels
     */
    public function testSingleUnitIntervalLength(): void
    {
        $data = $this->view->sequence(new Sequence(
            new Period('2018-01-01', '2018-02-01'),
            new Period('2017-01-01', '2019-01-01', Period::INCLUDE_ALL)
        ));

        self::assertStringContainsString('A                                  [-)', $data);
        self::assertStringContainsString('B    [----------------------------------------------------------]', $data);
    }

    /**
     * @covers ::diff
     * @covers ::addLabels
     */
    public function testDiff(): void
    {
        $config = (new ConsoleConfig())->withColors('white');
        $view = new Viewer(new LetterGenerator(), new ConsoleOutput($config));
        $data = $view->diff(
            new Period('2018-01-01', '2018-02-01'),
            new Period('2017-12-01', '2018-03-01')
        );

        self::assertStringContainsString('A                           [--------------------)', $data);
        self::assertStringContainsString('B       [----------------------------------------------------------)', $data);
        self::assertStringContainsString('DIFF    [-------------------)                    [-----------------)', $data);
    }

    /**
     * @covers ::unions
     * @covers ::addLabels
     */
    public function testUnion(): void
    {
        $sequence = new Sequence(
            Datepoint::create('2018-11-29')->getYear(),
            Datepoint::create('2018-11-29')->getMonth(),
            Period::around('2016-06-01', '3 MONTHS')
        );

        $data = $this->view->unions($sequence);
        self::assertStringContainsString('A                                               [--------------------)', $data);
        self::assertStringContainsString('B                                                                 [-) ', $data);
        self::assertStringContainsString('C         [---------)', $data);
        self::assertStringContainsString('UNIONS    [---------)                           [--------------------)', $data);
    }
}
