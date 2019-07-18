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
use Bakame\Period\Visualizer\ConsoleStdout;
use Bakame\Period\Visualizer\LetterLabel;
use Bakame\Period\Visualizer\Viewer;
use League\Period\Datepoint;
use League\Period\Period;
use League\Period\Sequence;
use PHPUnit\Framework\TestCase;
use function fopen;
use function rewind;
use function stream_get_contents;

/**
 * @coversDefaultClass \Bakame\Period\Visualizer\Viewer
 */
final class ViewerTest extends TestCase
{
    /**
     * @var Viewer
     */
    private $view;

    /**
     * @var resource
     */
    private $stream;

    public function setUp(): void
    {
        $this->stream = $this->setStream();
        $this->view = new Viewer(
            new LetterLabel('A'),
            new ConsoleOutput(
                ConsoleConfig::createFromRandom(),
                new ConsoleStdout($this->stream)
            )
        );
    }

    public function tearDown(): void
    {
        fclose($this->stream);
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
     * @param resource $stream
     */
    private function getContent($stream): string
    {
        rewind($stream);

        /** @var string $data  */
        $data = stream_get_contents($stream);

        return $data;
    }

    /**
     * @covers ::__construct
     * @covers ::sequence
     * @covers ::view
     * @covers \Bakame\Period\Visualizer\ConsoleStdout::colorize
     * @covers \Bakame\Period\Visualizer\ConsoleStdout::writeln
     */
    public function testDisplaySequence(): void
    {
        $viewer = new Viewer();

        $this->view->sequence(new Sequence(
            new Period('2018-01-01', '2018-01-15'),
            new Period('2018-01-15', '2018-02-01')
        ));

        $data = $this->getContent($this->stream);

        self::assertStringContainsString('A [--------------------------)', $data);
        self::assertStringContainsString('B                            [-------------------------------)', $data);
    }

    /**
     * @covers ::sequence
     * @covers ::view
     */
    public function testDisplayEmptySequence(): void
    {
        $this->view->sequence(new Sequence());

        $data = $this->getContent($this->stream);

        self::assertEmpty($data);
    }

    /**
     * @covers ::intersections
     * @covers ::view
     * @covers \Bakame\Period\Visualizer\ConsoleStdout::colorize
     * @covers \Bakame\Period\Visualizer\ConsoleStdout::writeln
     */
    public function testDisplayIntersection(): void
    {
        $this->view->intersections(new Sequence(
            new Period('2018-01-01', '2018-01-15'),
            new Period('2018-01-10', '2018-02-01')
        ));

        $data = $this->getContent($this->stream);

        self::assertStringContainsString('A             [--------------------------)', $data);
        self::assertStringContainsString('B                              [-----------------------------------------)', $data);
        self::assertStringContainsString('INTERSECTIONS                  [---------)', $data);
    }

    /**
     * @covers ::gaps
     * @covers ::view
     */
    public function testGaps(): void
    {
        $this->view->gaps(new Sequence(
            new Period('2018-01-01', '2018-01-10'),
            new Period('2018-01-15', '2018-02-01', Period::EXCLUDE_ALL)
        ), '');

        $data = $this->getContent($this->stream);

        self::assertStringContainsString('A      [----------------)', $data);
        self::assertStringContainsString('B                                 (-------------------------------)', $data);
        self::assertStringContainsString('RESULT                  [---------]', $data);
    }

    /**
     * @covers ::sequence
     * @covers ::view
     */
    public function testSingleUnitIntervalLength(): void
    {
        $this->view->sequence(new Sequence(
            new Period('2018-01-01', '2018-02-01'),
            new Period('2017-01-01', '2019-01-01', Period::INCLUDE_ALL)
        ));

        $data = $this->getContent($this->stream);

        self::assertStringContainsString('A                               [-)', $data);
        self::assertStringContainsString('B [----------------------------------------------------------]', $data);
    }

    /**
     * @covers ::diff
     */
    public function testDiff(): void
    {
        $config = (new ConsoleConfig())->withColors('white');
        $view = new Viewer(new LetterLabel(), new ConsoleOutput($config, new ConsoleStdout($this->stream)));
        $view->diff(
            new Period('2018-01-01', '2018-02-01'),
            new Period('2017-12-01', '2018-03-01')
        );

        $data = $this->getContent($this->stream);

        self::assertStringContainsString('A                        [--------------------)', $data);
        self::assertStringContainsString('B    [----------------------------------------------------------)', $data);
        self::assertStringContainsString('DIFF [-------------------)                    [-----------------)', $data);
    }

    /**
     * @covers ::unions
     */
    public function testUnion(): void
    {
        $sequence = new Sequence(
            Datepoint::create('2018-11-29')->getYear(),
            Datepoint::create('2018-11-29')->getMonth(),
            Period::around('2016-06-01', '3 MONTHS')
        );

        $this->view->unions($sequence);

        $data = $this->getContent($this->stream);

        self::assertStringContainsString('A                                            [--------------------)', $data);
        self::assertStringContainsString('B                                                              [-) ', $data);
        self::assertStringContainsString('C      [---------)', $data);
        self::assertStringContainsString('UNIONS [---------)                           [--------------------)', $data);
    }
}
