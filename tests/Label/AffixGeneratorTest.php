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

namespace BakameTest\Period\Visualizer\Label;

use Bakame\Period\Visualizer\Label\AffixGenerator;
use Bakame\Period\Visualizer\Label\IntegerGenerator;
use Bakame\Period\Visualizer\Label\LetterGenerator;
use Bakame\Period\Visualizer\Label\RomanGenerator;
use League\Period\Period;
use League\Period\Sequence;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass Bakame\Period\Visualizer\Label\AffixType;
 */
final class AffixGeneratorTest extends TestCase
{
    /**
     * @dataProvider providerLetter
     */
    public function testGetLabels(
        Sequence $sequence,
        string $letter,
        string $prefix,
        string $suffix,
        array $expected
    ): void {
        $generator = (new AffixGenerator(new LetterGenerator($letter)))->withPrefix($prefix)->withSuffix($suffix);
        self::assertSame($expected, $generator->generate($sequence));
    }

    public function providerLetter(): iterable
    {
        return [
            'empty labels' => [
                'sequence' => new Sequence(),
                'letter' => 'i',
                'prefix' => '',
                'suffix' => '',
                'expected' => [],
            ],
            'labels starts at i' => [
                'sequence' => new Sequence(new Period('2018-01-01', '2018-02-01')),
                'letter' => 'i',
                'prefix' => '',
                'suffix' => '.',
                'expected' => ['i.'],
            ],
            'labels starts ends at ab' => [
                'sequence' => new Sequence(
                    new Period('2018-01-01', '2018-02-01'),
                    new Period('2018-02-01', '2018-03-01')
                ),
                'letter' => 'aa',
                'prefix' => '-',
                'suffix' => '',
                'expected' => ['-aa', '-ab'],
            ],
            'labels starts at 0 (1)' => [
                'sequence' => new Sequence(new Period('2018-01-01', '2018-02-01')),
                'letter' => '        ',
                'prefix' => '.',
                'suffix' => '.',
                'expected' => ['.0.'],
            ],
            'labels starts at 0 (2)' => [
                'sequence' => new Sequence(new Period('2018-01-01', '2018-02-01')),
                'letter' => '',
                'prefix' => '.'.PHP_EOL,
                'suffix' => PHP_EOL.'.',
                'expected' => ['.0.'],
            ],
            'labels with an integer' => [
                'sequence' => new Sequence(new Period('2018-01-01', '2018-02-01')),
                'letter' => '1',
                'prefix' => '.'.PHP_EOL,
                'suffix' => PHP_EOL,
                'expected' => ['.A'],
            ],
        ];
    }

    public function testGetter(): void
    {
        $generator = new AffixGenerator(new RomanGenerator(new IntegerGenerator(10)));
        self::assertSame('', $generator->getSuffix());
        self::assertSame('', $generator->getPrefix());
        $new = $generator->withPrefix('o')->withSuffix('');
        self::assertNotSame($new, $generator);
        self::assertSame('o', $new->getPrefix());
        self::assertSame('', $new->getSuffix());
    }
}
