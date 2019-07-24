Period Visualizer
------

[![Author][ico-author]][link-author]
[![Build Status][ico-travis]][link-travis]
[![Total Downloads][ico-packagist]][link-packagist]
[![Latest Stable Version][ico-release]][link-release]
[![Software License][ico-license]][link-license]

This package contains a visualizer for [League Period](https://period.thephpleague.com).

It is inspired from the work of [@thecrypticace](https://github.com/thecrypticace) on the following PR [Visualization Helper](https://github.com/spatie/period/pull/10).

~~~php
<?php

use Bakame\Period\Visualizer\Console;
use Bakame\Period\Visualizer\Tuple;
use League\Period\Period;
use League\Period\Sequence;

$tuple = Tuple::fromSequence(
    new Sequence(new Period('2018-01-01', '2018-02-01'), new Period('2018-01-15', '2018-02-01'))
);
(new Console())->display($tuple);
~~~

results:

~~~bash
 A    [------------------------------------------------------------------------------)
 B                                        [------------------------------------------)
~~~

System Requirements
-------

You need:

- **PHP >= 7.2** but the latest stable version of PHP is recommended
- **League/Period 4.4+** but the latest stable version is recommended

Installation
--------

```bash
$ composer require bakame/period-visualizer
```

Usage
--------

## Basic Usage

### Generate a simple graph.

To generate a graph you need to give to the `Tuple` constructor a list of pairs. Each pair is an `array` containing 2 values:

- the value at key `0` represents the label
- the value at key `1` is a `League\Period\Period` or a `League\Period\Sequence` object 

~~~php
<?php

use Bakame\Period\Visualizer\Console;
use Bakame\Period\Visualizer\Tuple;
use League\Period\Period;

$tuple = new Tuple([
    ['A', new Period('2018-01-01', '2018-02-01')],
    ['B', new Period('2018-01-15', '2018-02-01')], 
]);
(new Console())->display($tuple);
~~~

results:

~~~bash
 A [------------------------------------------------------------------------------)
 B                                     [------------------------------------------)
~~~

### Appending items to display

If you want to display a `Sequence` and some of its operations. You can append the operation results using the `Tuple::append` method.

~~~php
<?php

use Bakame\Period\Visualizer\Console;
use Bakame\Period\Visualizer\Tuple;
use League\Period\Period;
use League\Period\Sequence;

$sequence = new Sequence(
    new Period('2018-01-01', '2018-03-01'),
    new Period('2018-05-01', '2018-08-01')
);
$tuple = Tuple::fromSequence($sequence);
$tuple->append('GAPS', $sequence->gaps());
(new Console())->display($tuple);
~~~

*Of Note: We are using the `Tuple::fromSequence` which is a handy named constructor to inject the `Sequence` members into the `Tuple` object*

results:

~~~bash
 A    [---------------------)                                                         
 B                                                 [--------------------------------=)
 GAPS                       [----------------------)    
~~~

The `Tuple` implements the `Countable` and the `IteratorAggregate` interface. It also exposes the following methods:

~~~php
<?php
public function Tuple::fromCollection(): self; //Creates a new Tuple from a generic iterable structure.
public function Tuple::labels(): string[]; //the current labels used
public function Tuple::items(): array<Period|Sequence>; //the current objects inside the Tuple
public function Tuple::isEmpty(): bool; //Tells whether the collection is empty.
public function Tuple::labelize(LabelGenerator $labelGenerator): self; //Update the labels used for the tuple.
public function Tuple::boundaries(): ?Period;  //Returns the collection boundaries or null if it is empty.
~~~

## Customize the line labels

The `Bakame\Period\Visualizer\Tuple::fromSequence` can be further formatted by providing a object to improve line index generation.
By default the class is instantiated with the letter index strategy which starts incrementing the labels from  the 'A' index. 
You can choose between the following strategies to modify the default behaviour:

### Letter strategy

~~~php
<?php

use Bakame\Period\Visualizer\Console;
use Bakame\Period\Visualizer\LatinLetter;
use Bakame\Period\Visualizer\Tuple;
use League\Period\Period;
use League\Period\Sequence;

$tuple = Tuple::fromSequence(
    new Sequence(new Period('2018-01-01', '2018-02-01'), new Period('2018-01-15', '2018-02-01')),
    new LatinLetter('aa')
);
(new Console())->display($tuple);
~~~

results:

~~~bash
 aa [------------------------------------------------------------------------------)
 ab [-----------------------------------)     
~~~

The `LatinLetter` also exposes the following methods:

~~~php
<?php

public function LatinLetter::startingAt(): string; //returns the first letter to be used
public function LatinLetter::startsWith(): self;  //returns a new object with a new starting letter
~~~

### Decimal Number Strategy

~~~php
<?php

use Bakame\Period\Visualizer\Console;
use Bakame\Period\Visualizer\DecimalNumber;
use Bakame\Period\Visualizer\Tuple;
use League\Period\Period;
use League\Period\Sequence;

$tuple = Tuple::fromSequence(
    new Sequence(new Period('2018-01-01', '2018-02-01'), new Period('2018-01-15', '2018-02-01')),
    new DecimalNumber(42)
);
(new Console())->display($tuple);
~~~

results:

~~~bash
 42 [------------------------------------------------------------------------------)
 43 [-----------------------------------)
~~~

The `DecimalNumber` also exposes the following methods:

~~~php
<?php

public function DecimalNumber::startingAt(): string; //returns the first decimal number to be used
public function DecimalNumber::startsWith(): self;  //returns a new object with a new starting decimal number
~~~

### Roman Numeral Strategy

~~~php
<?php

use Bakame\Period\Visualizer\Console;
use Bakame\Period\Visualizer\DecimalNumber;
use Bakame\Period\Visualizer\RomanNumber;
use Bakame\Period\Visualizer\Tuple;
use League\Period\Period;
use League\Period\Sequence;

$labelGenerator = new RomanNumber(new DecimalNumber(5), RomanNumber::LOWER);

$tuple = Tuple::fromSequence(
    new Sequence(new Period('2018-01-01', '2018-02-01'), new Period('2018-01-15', '2018-02-01')),
    $labelGenerator
);
(new Console())->display($tuple);
~~~

results:

~~~bash
 v  [------------------------------------------------------------------------------)
 vi [-----------------------------------)
~~~

The `RomanNumber` also exposes the following methods:

~~~php
<?php
const RomanNumber::UPPER = 1;
const RomanNumber::LOWER = 2;
public function RomanNumber::startingAt(): string; //returns the first decimal number to be used
public function RomanNumber::startsWith(): self;  //returns a new object with a new starting decimal number
public function RomanNumber::withLetterCase(int $lettercase): self;  //returns a new object with a new letter casing
public function RomanNumber::isUpper(): bool;  //Tells whether the roman letter is upper cased.
public function RomanNumber::isLower(): bool;  //Tells whether the roman letter is lower cased.
~~~

### Affix Label Strategy

~~~php
<?php

use Bakame\Period\Visualizer\AffixLabel;
use Bakame\Period\Visualizer\Console;
use Bakame\Period\Visualizer\DecimalNumber;
use Bakame\Period\Visualizer\RomanNumber;
use Bakame\Period\Visualizer\Tuple;
use League\Period\Period;
use League\Period\Sequence;

$labelGenerator = new AffixLabel(
    new RomanNumber(new DecimalNumber(5), RomanNumber::LOWER),
    '*', //prefix
    '.)'    //suffix
);
$tuple = Tuple::fromSequence(
    new Sequence(new Period('2018-01-01', '2018-02-01'), new Period('2018-01-15', '2018-02-01')),
    $labelGenerator
);
(new Console())->display($tuple);
~~~

results:

~~~bash
 * v .)  [----------------------------------------------------------)
 * vi .) [--------------------------) 
~~~

The `AffixLabel` also exposes the following methods:

~~~php
<?php

public function AffixLabel::prefix(): string; //returns the current prefix
public function AffixLabel::suffix(): string;  //returns the current suffix
public function AffixLabel::withPrefix(string $prefix): self;  //returns a new object with a new prefix
public function AffixLabel::withSuffix(string $suffix): self;  //returns a new object with a new suffix
~~~

### Reverse Label Strategy

~~~php
<?php

use Bakame\Period\Visualizer\AffixLabel;
use Bakame\Period\Visualizer\Console;
use Bakame\Period\Visualizer\DecimalNumber;
use Bakame\Period\Visualizer\ReverseLabel;
use Bakame\Period\Visualizer\RomanNumber;
use Bakame\Period\Visualizer\Tuple;
use League\Period\Period;
use League\Period\Sequence;

$labelGenerator = new DecimalNumber(5);
$labelGenerator = new RomanNumber($labelGenerator, RomanNumber::LOWER);
$labelGenerator = new AffixLabel($labelGenerator, '', '.');
$labelGenerator = new ReverseLabel($labelGenerator);

$tuple = Tuple::fromSequence(
    new Sequence(new Period('2018-01-01', '2018-02-01'), new Period('2018-01-15', '2018-02-01')),
    $labelGenerator
);
(new Console())->display($tuple);
~~~

results:

~~~bash
 vi. [------------------------------------------------------------------------------)
 v.  [-----------------------------------)
~~~

### Custom Strategy

You can create your own strategy by implementing the `Bakame\Period\Visualizer\Contract\LabelGenerator` interface like shown below:

~~~php
<?php

use Bakame\Period\Visualizer\AffixLabel;
use Bakame\Period\Visualizer\Contract\LabelGenerator;
use Bakame\Period\Visualizer\Console;
use Bakame\Period\Visualizer\Tuple;
use League\Period\Period;
use League\Period\Sequence;

$samelabel = new class implements LabelGenerator {
    public function generate(int $nbLabels): array
    {
        return array_fill(0, $nbLabels, $this->format('foobar'));
    }
        
    public function format($str): string
    {
        return (string) $str;
    }
};

$labelGenerator = new AffixLabel($samelabel, '', '.');
$tuple = Tuple::fromSequence(
    new Sequence(new Period('2018-01-01', '2018-02-01'), new Period('2018-01-15', '2018-02-01')),
    $labelGenerator
);
(new Console())->display($tuple);
~~~

results:

~~~bash
 foobar. [------------------------------------------------------------------------------)
 foobar. [-----------------------------------)
~~~

## Output

The `Console` class is responsible for outputting your graph.

~~~php
<?php

use Bakame\Period\Visualizer\Console;
use Bakame\Period\Visualizer\Tuple;
use League\Period\Period;

$console = new Console();
echo $console->display(new Tuple([
    ['first', new Period('2018-01-01 08:00:00', '2018-01-01 12:00:00')],
    ['last', new Period('2018-01-01 10:00:00', '2018-01-01 14:00:00')],
]));
~~~

results:

~~~bash
 first [----------------------------------------------------)                          
 last                            [----------------------------------------------------)
~~~

The `Console::display` methods expects a `Tuple` object as its unique argument where:

The `Console` class can be customized by providing a `ConsoleConfig` which defines the console settings.

~~~php
<?php

use Bakame\Period\Visualizer\ConsoleConfig;
use Bakame\Period\Visualizer\Console;
use Bakame\Period\Visualizer\Contract\LabelGenerator;
use Bakame\Period\Visualizer\Tuple;
use League\Period\Period;
use League\Period\Sequence;

$config = (new ConsoleConfig())
    ->withStartExcluded('🍕')
    ->withStartIncluded('🍅')
    ->withEndExcluded('🎾')
    ->withEndIncluded('🍔')
    ->withWidth(30)
    ->withSpace('💩')
    ->withBody('😊')
    ->withColors('yellow', 'red')
    ->withGapSize(2)
    ->withLabelAlign(ConsoleConfig::ALIGN_RIGHT)
;

$fixedLabels = new class implements LabelGenerator {
    public function generate(int $nbLabels): array
    {
        return ['first one', 'last'];
    }

    public function format($str): string
    {
        return (string) $str;
    }
};

$tuple = Tuple::fromSequence(new Sequence(
    new Period('2018-01-01 08:00:00', '2018-01-01 12:00:00', Period::EXCLUDE_ALL),
    new Period('2018-01-01 10:00:00', '2018-01-01 14:00:00', Period::INCLUDE_ALL)
), $fixedLabels);

$console = new Console($config);
$console->display($tuple);
~~~

results:

~~~bash
 first one  🍕😊😊😊😊😊😊😊😊😊😊😊😊😊😊😊😊😊😊🎾💩💩💩💩💩💩💩💩💩💩
      last  💩💩💩💩💩💩💩💩💩💩🍅😊😊😊😊😊😊😊😊😊😊😊😊😊😊😊😊😊😊🍔
~~~

*On a POSIX compliant console the first line will be yellow and the second red*

The `ConsoleConfig` class exposes the following additionals methods:

~~~php
<?php
public function ConsoleConfig::startExcluded(): string; //Retrieves the excluded start block character.
public function ConsoleConfig::startIncluded(): string; //Retrieves the included start block character.
public function ConsoleConfig::endExcluded(): string;   //Retrieves the excluded end block character.
public function ConsoleConfig::endIncluded(): string;   //Retrieves the included end block character.
public function ConsoleConfig::width(): int;            //Retrieves the max size width.
public function ConsoleConfig::body(): string;          //Retrieves the body block character.
public function ConsoleConfig::space(): string;         //Retrieves the space block character.
public function ConsoleConfig::colors(): string[];      //The selected colors for each row.
public function ConsoleConfig::gapSize(): int;          //Retrieves the gap sequence between the label and the line.
public function ConsoleConfig::labelAlign(): int;       //Returns how label should be aligned.
~~~

**`ConsoleConfig` is immutable, modifying its properties returns a new instance with the updated values.**

Changelog
-------

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

Contributing
-------

Contributions are welcome and will be fully credited. Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

Testing
-------

The library has a :

- a [PHPUnit](https://phpunit.de) test suite
- a coding style compliance test suite using [PHP CS Fixer](http://cs.sensiolabs.org/).
- a code analysis compliance test suite using [PHPStan](https://github.com/phpstan/phpstan).

To run the tests, run the following command from the project folder.

``` bash
$ composer test
```

Security
-------

If you discover any security related issues, please email nyamsprod@gmail.com instead of using the issue tracker.

Credits
-------

- [ignace nyamagana butera](https://github.com/nyamsprod)
- [jordan pittman](https://github.com/thecrypticace)
- [All Contributors](https://github.com/bakame-php/laravel-domain-parser/contributors)

License
-------

The MIT License (MIT). Please see [License File](LICENSE) for more information.

[ico-author]: https://img.shields.io/badge/author-@nyamsprod-blue.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/bakame-php/period-visualizer/master.svg?style=flat-square
[ico-packagist]: https://img.shields.io/packagist/dt/bakame/period-visualizer.svg?style=flat-square
[ico-release]: https://img.shields.io/github/release/bakame-php/period-visualizer.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square

[link-author]: https://twitter.com/nyamsprod
[link-travis]: https://travis-ci.org/bakame-php/period-visualizer
[link-packagist]: https://packagist.org/packages/bakame/period-visualizer
[link-release]: https://github.com/bakame-php/period-visualizer/releases
[link-license]: https://github.com/bakame-php/period-visualizer/blob/master/LICENSE
