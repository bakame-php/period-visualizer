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
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass Bakame\Period\Visualizer\Configuration
 */
final class ConfigurationTest extends TestCase
{
    /**
     * @var Configuration
     */
    private $config;

    public function setUp(): void
    {
        $this->config = new Configuration();
    }

    public function testNewInstance(): void
    {
        self::assertSame(10, $this->config->getWidth());
        self::assertSame(']', $this->config->getHead());
        self::assertSame('=', $this->config->getBody());
        self::assertSame('[', $this->config->getTail());
        self::assertSame(' ', $this->config->getSpace());
        self::assertSame(['white'], $this->config->getColors());
    }

    public function testCreateFromRandom(): void
    {
        $config = Configuration::createFromRandom();
        self::assertContains($config->getColors()[0], Configuration::COLORS);
    }

    public function testCreateFromRainbow(): void
    {
        $config = Configuration::createFromRainbow();
        self::assertSame($config->getColors(), Configuration::COLORS);
    }
    /**
     * @dataProvider widthProvider
     */
    public function testWidth(int $size, int $expected): void
    {
        self::assertSame($expected, $this->config->withWidth($size)->getWidth());
    }
    public function widthProvider(): array
    {
        return [
            '0 size' => [0, 10],
            'negative size' => [-23, 10],
            'basic usage' => [23, 23],
        ];
    }

    /**
     * @dataProvider providerChars
     */
    public function testBody(string $char, string $expected): void
    {
        self::assertSame($expected, $this->config->withBody($char)->getBody());
    }

    /**
     * @dataProvider providerChars
     */
    public function testHead(string $char, string $expected): void
    {
        self::assertSame($expected, $this->config->withHead($char)->getHead());
    }

    /**
     * @dataProvider providerChars
     */
    public function testTail(string $char, string $expected): void
    {
        self::assertSame($expected, $this->config->withTail($char)->getTail());
    }

    /**
     * @dataProvider providerChars
     */
    public function testSpace(string $char, string $expected): void
    {
        self::assertSame($expected, $this->config->withSpace($char)->getSpace());
    }

    public function providerChars(): array
    {
        return [
            ['=', '='],
            ['[', '['],
            [']', ']'],
            [' ', ' '],
            ['#', '#'],
            ["\t", "\t"],
            ['€', '€'],
            ['█', '█'],
            [' ', ' '],
            ['\uD83D\uDE00', '😀'],
        ];
    }

    /**
     * @dataProvider colorsProvider
     */
    public function testColors(string $char, string $expected): void
    {
        self::assertSame($expected, $this->config->withColors($char)->getColors()[0]);
    }

    public function colorsProvider(): array
    {
        return [
            ['=', 'white'],
            ['white', 'white'],
        ];
    }

    public function providerInvalidChars(): array
    {
        return [
            ['coucou'],
            ['\uD83D\uDE00\uD83D\uDE00'],
        ];
    }
    /**
     * @dataProvider providerInvalidChars
     */
    public function testWithHeadBlockThrowsInvalidArgumentException(string $input): void
    {
        self::expectException(InvalidArgumentException::class);
        $this->config->withHead($input);
    }
}
