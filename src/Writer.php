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

    public const POSIX_COLOR_CODES = [
        'reset'      => '0',
        'bold'       => '1',
        'dim'        => '2',
        'underscore' => '4',
        'blink'      => '5',
        'reverse'    => '7',
        'hidden'     => '8',
        'black'      => '30',
        'red'        => '31',
        'green'      => '32',
        'yellow'     => '33',
        'blue'       => '34',
        'magenta'    => '35',
        'cyan'       => '36',
        'white'      => '37',
        'blackbg'    => '40',
        'redbg'      => '41',
        'greenbg'    => '42',
        'yellowbg'   => '43',
        'bluebg'     => '44',
        'magentabg'  => '45',
        'cyanbg'     => '46',
        'whitebg'    => '47',
    ];

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
