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

use Bakame\Period\Visualizer\DecimalNumber;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Bakame\Period\Visualizer\DecimalNumber;
 */
final class DecimalNumberTest extends TestCase
{
    /**
     * @dataProvider providerLetter
     */
    public function testGetLabels(int $nbLabels, int $label, array $expected): void
    {
        $generator = new DecimalNumber($label);
        self::assertSame($expected, $generator->generate($nbLabels));
    }

    public function providerLetter(): iterable
    {
        return [
            'empty labels' => [
                'nbLabels' => 0,
                'label' => 1,
                'expected' => [],
            ],
            'labels starts at 3' => [
                'nbLabels' => 1,
                'label' => 3,
                'expected' => ['3'],
            ],
            'labels starts ends at 4' => [
                'nbLabels' => 2,
                'label' => 4,
                'expected' => ['4', '5'],
            ],
            'labels starts at 0 (1)' => [
                'nbLabels' => 1,
                'label' => -1,
                'expected' => ['1'],
            ],
            'labels starts at 0 (2)' => [
                'nbLabels' => 1,
                'label' => 0,
                'expected' => ['1'],
            ],
        ];
    }

    public function testStartWith(): void
    {
        $generator = new DecimalNumber(42);
        self::assertSame(42, $generator->startingAt());
        $new = $generator->startsWith(69);
        self::assertNotSame($new, $generator);
        self::assertSame(69, $new->startingAt());
        self::assertSame($generator, $generator->startsWith(42));
        self::assertSame(1, (new DecimalNumber(-3))->startingAt());
        self::assertSame(1, $generator->startsWith(-3)->startingAt());
    }

    public function testFormat(): void
    {
        $generator = new DecimalNumber(42);
        self::assertSame('', $generator->format(''));
    }
}
