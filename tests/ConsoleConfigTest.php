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
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Bakame\Period\Visualizer\ConsoleConfig
 */
final class ConsoleConfigTest extends TestCase
{
    /**
     * @var ConsoleConfig
     */
    private $config;

    public function setUp(): void
    {
        $this->config = new ConsoleConfig();
    }

    public function testNewInstance(): void
    {
        self::assertSame('[', $this->config->startIncluded());
        self::assertSame('(', $this->config->startExcluded());
        self::assertSame(']', $this->config->endIncluded());
        self::assertSame(')', $this->config->endExcluded());
        self::assertSame('=', $this->config->body());
        self::assertSame(' ', $this->config->space());
        self::assertSame(60, $this->config->width());
        self::assertSame(['default'], $this->config->colors());
    }

    public function testCreateFromRandom(): void
    {
        $config = ConsoleConfig::createFromRandom();
        self::assertContains($config->colors()[0], ConsoleConfig::COLORS);
    }

    public function testCreateFromRainbow(): void
    {
        $config = ConsoleConfig::createFromRainbow();
        self::assertSame($config->colors(), ConsoleConfig::COLORS);
    }
    /**
     * @dataProvider widthProvider
     */
    public function testWidth(int $size, int $expected): void
    {
        self::assertSame($expected, $this->config->withWidth($size)->width());
    }
    public function widthProvider(): array
    {
        return [
            '0 size' => [0, 10],
            'negative size' => [-23, 10],
            'basic usage' => [23, 23],
            'default value' => [60, 60],
        ];
    }

    /**
     * @dataProvider providerChars
     */
    public function testBody(string $char, string $expected): void
    {
        self::assertSame($expected, $this->config->withBody($char)->body());
    }

    /**
     * @dataProvider providerChars
     */
    public function testEndExcluded(string $char, string $expected): void
    {
        self::assertSame($expected, $this->config->withEndExcluded($char)->endExcluded());
    }

    /**
     * @dataProvider providerChars
     */
    public function testEndIncluded(string $char, string $expected): void
    {
        self::assertSame($expected, $this->config->withEndIncluded($char)->endIncluded());
    }

    /**
     * @dataProvider providerChars
     */
    public function testStartExcluded(string $char, string $expected): void
    {
        self::assertSame($expected, $this->config->withStartExcluded($char)->startExcluded());
    }

    /**
     * @dataProvider providerChars
     */
    public function testStartIncluded(string $char, string $expected): void
    {
        self::assertSame($expected, $this->config->withStartIncluded($char)->startIncluded());
    }

    /**
     * @dataProvider providerChars
     */
    public function testSpace(string $char, string $expected): void
    {
        self::assertSame($expected, $this->config->withSpace($char)->space());
    }

    public function providerChars(): array
    {
        return [
            ['=', '='],
            ['[', '['],
            [']', ']'],
            [')', ')'],
            ['(', '('],
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
        self::assertSame($expected, $this->config->withColors($char)->colors()[0]);
    }

    public function colorsProvider(): array
    {
        return [
            ['=', 'default'],
            ['white', 'white'],
        ];
    }

    public function testWithColorsReturnSameInstance(): void
    {
        self::assertSame($this->config, $this->config->withColors());
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
        $this->config->withBody($input);
    }
}
