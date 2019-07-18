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
use League\Period\Period;
use League\Period\Sequence;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Bakame\Period\Visualizer\DecimalNumber;
 */
final class DecimalNumberTest extends TestCase
{
    /**
     * @dataProvider providerLetter
     */
    public function testGetLabels(Sequence $sequence, int $label, array $expected): void
    {
        $generator = new DecimalNumber($label);
        self::assertSame($expected, $generator->generate($sequence));
    }

    public function providerLetter(): iterable
    {
        return [
            'empty labels' => [
                'sequence' => new Sequence(),
                'label' => 1,
                'expected' => [],
            ],
            'labels starts at 3' => [
                'sequence' => new Sequence(new Period('2018-01-01', '2018-02-01')),
                'label' => 3,
                'expected' => ['3'],
            ],
            'labels starts ends at 4' => [
                'sequence' => new Sequence(
                    new Period('2018-01-01', '2018-02-01'),
                    new Period('2018-02-01', '2018-03-01')
                ),
                'label' => 4,
                'expected' => ['4', '5'],
            ],
            'labels starts at 0 (1)' => [
                'sequence' => new Sequence(new Period('2018-01-01', '2018-02-01')),
                'label' => -1,
                'expected' => ['1'],
            ],
            'labels starts at 0 (2)' => [
                'sequence' => new Sequence(new Period('2018-01-01', '2018-02-01')),
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
        self::assertSame('', $generator->format([]));
    }

    public function testZeroLeftPaddingSetterAndStatus(): void
    {
        $generator = new DecimalNumber(42, 42);
        self::assertFalse($generator->isPadded());

        $newGenerator = $generator->withPadding();
        self::assertTrue($newGenerator->isPadded());
        self::assertSame($newGenerator, $newGenerator->withPadding());

        $wrongGenerator = $newGenerator->withoutPadding();
        self::assertFalse($wrongGenerator->isPadded());
        self::assertSame($wrongGenerator, $wrongGenerator->withoutPadding());
    }

    public function testAddingLeadingZero(): void
    {
        $generator = new DecimalNumber(98, DecimalNumber::LEFT_PAD);
        $labels = $generator->generate(new Sequence(
            Period::before('yesterday', '1 DAY'),
            Period::after('yesterday', '1 DAY'),
            Period::around('yesterday', '1 DAY')
        ));

        /** @var string $label */
        foreach ($labels as $label) {
            self::assertSame(3, strlen($label));
        }
    }
}
