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
use PHPUnit\Framework\TestCase;
use TypeError;
use function curl_init;
use function fopen;
use function rewind;
use function stream_get_contents;

/**
 * @coversDefaultClass \Bakame\Period\Visualizer\ConsoleOutput
 */
final class ConsoleOutputTest extends TestCase
{
    /**
     * @return resource
     */
    private function setStream()
    {
        /** @var resource $stream */
        $stream = fopen('php://memory', 'r+');

        return $stream;
    }

    public function testCreateStreamWithInvalidParameter(): void
    {
        self::expectException(TypeError::class);
        new ConsoleOutput(__DIR__.'/data/foo.csv');
    }

    public function testCreateStreamWithWrongResourceType(): void
    {
        self::expectException(TypeError::class);
        new ConsoleOutput(curl_init());
    }

    /**
     * @dataProvider provideTextToColorize
     */
    public function testColorize(string $string, string $colorCodeIndex, string $expected): void
    {
        $stream = $this->setStream();
        $output = new ConsoleOutput($stream);

        self::assertSame($expected, $output->colorize($string, $colorCodeIndex));
    }

    public function provideTextToColorize(): iterable
    {
        return [
            'default text' => [
                'string' => 'toto',
                'colorCodeIndex' => 'white',
                'expected' => '<<white>>toto<<reset>>',
            ],
            'text with reset' => [
                'string' => 'toto',
                'colorCodeIndex' => 'reset',
                'expected' => 'toto',
            ],
            'text with different casing for color code Index' => [
                'string' => 'toto',
                'colorCodeIndex' => 'WhITe',
                'expected' => '<<white>>toto<<reset>>',
            ],
            'text with invalid color code Index' => [
                'string' => 'toto',
                'colorCodeIndex' => 'foobar',
                'expected' => 'toto',
            ],
        ];
    }

    /**
     * @dataProvider provideWritelnTexts
     * @param string|string[] $message
     */
    public function testWriteln($message, string $expected): void
    {
        $stream = $this->setStream();
        $output = new ConsoleOutput($stream);
        $output->writeln($message);
        rewind($stream);
        /** @var string $data */
        $data = stream_get_contents($stream);

        self::assertStringContainsString($expected, $data);
    }

    public function provideWritelnTexts(): iterable
    {
        $stream = $this->setStream();
        $output = new ConsoleOutput($stream);

        return [
            'empty message' => [
                'message' => '',
                'expected' => '',
            ],
            'simple message' => [
                'message' => "I'm the king of the world",
                'expected' => "I'm the king of the world".PHP_EOL,
            ],
            'multiple string message' => [
                'message' => [
                    "I'm the king",
                    'Of the casa',
                ],
                'expected' => "I'm the king".PHP_EOL.'Of the casa'.PHP_EOL,
            ],
            'message with color' => [
                'message' => $output->colorize('foobar', 'magenta'),
                'expected' => 'foobar',
            ],
        ];
    }
}
