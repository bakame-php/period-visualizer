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

use TypeError;

interface Writer
{
    public const DEFAULT_COLOR_NAME = 'default';

    /**
     * @param string[]|string $message
     *
     * @throws TypeError If the message type is not supported.
     */
    public function writeln($message): int;

    /**
     * Returns a colorize line if the underlying console allows it.
     */
    public function colorize(string $line, string $color): string;
}
