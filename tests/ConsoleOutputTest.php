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
use League\Period\Period;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Bakame\Period\Visualizer\ConsoleOutput
 */
final class ConsoleOutputTest extends TestCase
{
    /**
     * @var ConsoleOutput
     */
    private $output;

    public function setUp(): void
    {
        $this->output = new ConsoleOutput((new ConsoleConfig())->withColors('red'));
    }

    /**
     * @covers ::__construct
     * @covers ::setWriter
     */
    public function testConstructor(): void
    {
        $output = new ConsoleOutput();
        self::assertNotEquals($this->output, $output);
    }

    /**
     * @covers ::display
     */
    public function testDisplayEmptyTuple(): void
    {
        self::assertSame('', $this->output->display([]));
    }

    /**
     * @covers ::display
     * @covers ::render
     * @covers ::convertMatrixValue
     */
    public function testDisplaySequence(): void
    {
        $data = $this->output->display([
            ['A', new Period('2018-01-01', '2018-01-15')],
            ['B', new Period('2018-01-15', '2018-02-01')],
        ]);

        self::assertStringContainsString('A    [--------------------------)', $data);
        self::assertStringContainsString('B                               [-------------------------------)', $data);
    }
}
