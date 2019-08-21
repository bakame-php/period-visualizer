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

namespace Bakame\Period\Visualizer;

interface OutputWriter
{
    public const DEFAULT_COLOR_CODE_INDEX = 'reset';

    public const POSIX_COLOR_CODES = [
        self::DEFAULT_COLOR_CODE_INDEX => '0',
        'black'   => '30',
        'red'     => '31',
        'green'   => '32',
        'yellow'  => '33',
        'blue'    => '34',
        'magenta' => '35',
        'cyan'    => '36',
        'white'   => '37',
    ];

    public function writeln(string $message = '', string $colorCodeIndex = self::DEFAULT_COLOR_CODE_INDEX): void;
}
