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

use Bakame\Period\Visualizer\Console;
use Bakame\Period\Visualizer\ConsoleConfig;
use Bakame\Period\Visualizer\ConsoleStdout;
use Bakame\Period\Visualizer\Dataset;
use League\Period\Period;
use League\Period\Sequence;
use PHPUnit\Framework\TestCase;
use function fopen;
use function rewind;
use function stream_get_contents;

/**
 * @coversDefaultClass \Bakame\Period\Visualizer\Console
 */
final class ConsoleTest extends TestCase
{
    /**
     * @var Console
     */
    private $output;

    /**
     * @var resource
     */
    private $stream;

    public function setUp(): void
    {
        $this->stream = $this->setStream();

        $this->output = new Console(
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
     */
    public function testConstructor(): void
    {
        $output = new Console();
        self::assertNotEquals($this->output, $output);
    }

    /**
     * @covers ::display
     * @covers ::buildMatrix
     */
    public function testDisplayEmptyDataset(): void
    {
        $this->output->display(new Dataset());
        rewind($this->stream);
        $data = stream_get_contents($this->stream);

        self::assertSame('', $data);
    }

    /**
     * @covers ::display
     * @covers ::matrixToLine
     * @covers ::tokenToCharacters
     * @covers ::buildMatrix
     * @covers ::addPeriodToRow
     * @covers \Bakame\Period\Visualizer\ConsoleStdout
     */
    public function testDisplayPeriods(): void
    {
        $this->output->display(new Dataset([
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
     * @covers ::matrixToLine
     * @covers ::tokenToCharacters
     * @covers ::buildMatrix
     * @covers ::addPeriodToRow
     */
    public function testDisplaySequence(): void
    {
        $dataset = new Dataset([
            ['A', new Sequence(new Period('2018-01-01', '2018-01-15'))],
            ['B', new Sequence(new Period('2018-01-15', '2018-02-01'))],
        ]);

        $this->output->display($dataset);

        rewind($this->stream);
        /** @var string $data */
        $data = stream_get_contents($this->stream);

        self::assertStringContainsString('A [--------------------------)', $data);
        self::assertStringContainsString('B                            [-------------------------------)', $data);
    }
}
