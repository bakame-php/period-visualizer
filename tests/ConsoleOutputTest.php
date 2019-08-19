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
use function chr;
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
     * @dataProvider provideWritelnTexts
     * @param string|string[] $message
     */
    public function testWriteln($message, string $expected): void
    {
        $stream = $this->setStream();
        $output = new ConsoleOutput($stream, 'blue');
        $output->writeln($message);
        rewind($stream);
        /** @var string $data */
        $data = stream_get_contents($stream);

        self::assertStringContainsString($expected, $data);
    }

    public function provideWritelnTexts(): iterable
    {
        $data = ["I'm the king", 'Of the casa'];
        $writtenData = array_map(function (string $line): string {
            return chr(27).'[34m'.$line.chr(27).'[0m';
        }, $data);

        return [
            'empty message' => [
                'message' => '',
                'expected' => '',
            ],
            'simple message' => [
                'message' => "I'm the king of the world",
                'expected' => chr(27).'[34m'."I'm the king of the world".chr(27).'[0m'.PHP_EOL,
            ],
            'multiple string message' => [
                'message' => $data,
                'expected' => implode(PHP_EOL, $writtenData).PHP_EOL,
            ],
        ];
    }

    public function testWritelnWithUnknownColor(): void
    {
        $message = 'foobar the quick brown fox';
        $stream = $this->setStream();
        $output = new ConsoleOutput($stream, 'pink');
        $output->writeln($message);
        rewind($stream);
        /** @var string $data */
        $data = stream_get_contents($stream);

        self::assertStringContainsString($message.PHP_EOL, $data);
    }
}
