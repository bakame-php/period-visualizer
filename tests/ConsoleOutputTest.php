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
use League\Period\Period;
use League\Period\Sequence;
use PHPUnit\Framework\TestCase;
use function fopen;
use function rewind;
use function stream_get_contents;

/**
 * @coversDefaultClass \Bakame\Period\Visualizer\ConsoleOutput
 */
final class ConsoleOutputTest extends TestCase
{
    /**
     * @var ConsoleOutput
     */
    private $output;

    /**
     * @var resource
     */
    private $stream;

    public function setUp(): void
    {
        $this->stream = $this->setStream();

        $this->output = new ConsoleOutput(
            (new ConsoleConfig())->withColors('red'),
            new ConsoleStdout($this->stream)
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
     * @covers \Bakame\Period\Visualizer\ConsoleStdout::__construct
     */
    public function testConstructor(): void
    {
        $output = new ConsoleOutput();
        self::assertNotEquals($this->output, $output);
    }

    /**
     * @covers ::display
     * @covers ::buildMatrix
     * @covers \Bakame\Period\Visualizer\ConsoleStdout::writeln
     */
    public function testDisplayEmptyTuple(): void
    {
        $this->output->display([]);
        rewind($this->stream);
        $data = stream_get_contents($this->stream);

        self::assertSame('', $data);
    }

    /**
     * @covers ::display
     * @covers ::format
     * @covers ::convertMatrixValue
     * @covers ::buildMatrix
     * @covers ::getBoundaries
     * @covers ::addPeriodToRow
     * @covers \Bakame\Period\Visualizer\ConsoleStdout::colorize
     * @covers \Bakame\Period\Visualizer\ConsoleStdout::formatter
     * @covers \Bakame\Period\Visualizer\ConsoleStdout::write
     * @covers \Bakame\Period\Visualizer\ConsoleStdout::writeln
     * @covers \Bakame\Period\Visualizer\ConsoleStdout::regexp
     */
    public function testDisplayPeriods(): void
    {
        $this->output->display([
            ['A', new Period('2018-01-01', '2018-01-15')],
            ['B', new Period('2018-01-15', '2018-02-01')],
        ]);

        rewind($this->stream);
        /** @var string $data */
        $data = stream_get_contents($this->stream);

        self::assertStringContainsString('A [--------------------------)', $data);
        self::assertStringContainsString('B                            [-------------------------------)', $data);
    }


    /**
     * @covers ::display
     * @covers ::format
     * @covers ::convertMatrixValue
     * @covers ::buildMatrix
     * @covers ::getBoundaries
     * @covers ::addPeriodToRow
     * @covers \Bakame\Period\Visualizer\ConsoleStdout::colorize
     * @covers \Bakame\Period\Visualizer\ConsoleStdout::formatter
     * @covers \Bakame\Period\Visualizer\ConsoleStdout::write
     * @covers \Bakame\Period\Visualizer\ConsoleStdout::writeln
     * @covers \Bakame\Period\Visualizer\ConsoleStdout::regexp
     */
    public function testDisplaySequence(): void
    {
        $this->output->display([
            ['A', new Sequence(new Period('2018-01-01', '2018-01-15'))],
            ['B', new Sequence(new Period('2018-01-15', '2018-02-01'))],
        ]);

        rewind($this->stream);
        /** @var string $data */
        $data = stream_get_contents($this->stream);

        self::assertStringContainsString('A [--------------------------)', $data);
        self::assertStringContainsString('B                            [-------------------------------)', $data);
    }
}
