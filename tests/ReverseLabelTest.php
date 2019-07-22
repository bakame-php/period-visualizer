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

use Bakame\Period\Visualizer\LatinLetter;
use Bakame\Period\Visualizer\ReverseLabel;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Bakame\Period\Visualizer\ReverseLabel;
 */
final class ReverseLabelTest extends TestCase
{
    /**
     * @dataProvider providerLetter
     */
    public function testGetLabels(int $nbLabels, string $letter, array $expected): void
    {
        $generator = new ReverseLabel(new LatinLetter($letter));
        self::assertSame($expected, $generator->generate($nbLabels));
    }

    public function providerLetter(): iterable
    {
        return [
            'empty labels' => [
                'nbLabels' => 0,
                'letter' => 'i',
                'expected' => [],
            ],
            'labels starts at i' => [
                'nbLabels' => 2,
                'letter' => 'i',
                'expected' => ['j', 'i'],
            ],
            'labels starts ends at ab' => [
                'nbLabels' => 2,
                'letter' => 'aa',
                'expected' => ['ab', 'aa'],
            ],
        ];
    }

    public function testFormat(): void
    {
        $generator = new ReverseLabel(new LatinLetter('AA'));
        self::assertSame('', $generator->format([]));
    }
}
