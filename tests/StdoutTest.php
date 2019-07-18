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

use Bakame\Period\Visualizer\ConsoleStdout;
use PHPUnit\Framework\TestCase;
use TypeError;
use function curl_init;
use function fopen;

/**
 * @coversDefaultClass \Bakame\Period\Visualizer\ConsoleStdout
 */
final class StdoutTest extends TestCase
{
    public function testConstructor(): void
    {
        $stream = $this->setStream();
        $stdout = new ConsoleStdout($stream);
        $toto = $stdout->colorize('toto', \Bakame\Period\Visualizer\Contract\Writer::DEFAULT_COLOR_CODE_INDEX);

        self::assertSame('toto', $toto);
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

    public function testCreateStreamWithInvalidParameter(): void
    {
        self::expectException(TypeError::class);
        new ConsoleStdout(__DIR__.'/data/foo.csv');
    }

    public function testCreateStreamWithWrongResourceType(): void
    {
        self::expectException(TypeError::class);
        new ConsoleStdout(curl_init());
    }
}
