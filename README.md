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

use Bakame\Period\Visualizer\ConsoleGraph;
use Bakame\Period\Visualizer\Dataset;
use League\Period\Datepoint;
use League\Period\Period;
use League\Period\Sequence;

$sequence = new Sequence(
    Datepoint::create('2018-11-29')->getYear(Period::EXCLUDE_START_INCLUDE_END),
    Datepoint::create('2018-05-29')->getMonth()->expand('3 MONTH'),
    Datepoint::create('2017-01-13')->getQuarter(Period::EXCLUDE_ALL),
    Period::around('2016-06-01', '3 MONTHS', Period::INCLUDE_ALL)
);
$dataset = Dataset::fromSequence($sequence);
$dataset->append('gaps', $sequence->gaps());
(new ConsoleGraph())->display($dataset);
~~~

results:

~~~bash
 A                                          (--------------------]
 B                                            [-----------)       
 C                     (----)                                     
 D    [---------]                                                 
 gaps           (------]    [---------------]  
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

To generate a graph you need to give to the `Dataset` constructor a list of pairs. Each pair is an `array` containing 2 values:

- the value at key `0` represents the label
- the value at key `1` is a `League\Period\Period` or a `League\Period\Sequence` object 

~~~php
<?php

use Bakame\Period\Visualizer\ConsoleGraph;
use Bakame\Period\Visualizer\Dataset;
use League\Period\Period;

$dataset = new Dataset([
    ['A', new Period('2018-01-01', '2018-02-01')],
    ['B', new Period('2018-01-15', '2018-02-01')], 
]);
(new ConsoleGraph())->display($dataset);
~~~

results:

~~~bash
 A [------------------------------------------------------------------------------)
 B                                     [------------------------------------------)
~~~

### Appending items to display

If you want to display a `Sequence` and some of its operations. You can append the operation results using the `Dataset::append` method.

~~~php
<?php

use Bakame\Period\Visualizer\ConsoleGraph;
use Bakame\Period\Visualizer\Dataset;
use League\Period\Period;
use League\Period\Sequence;

$sequence = new Sequence(
    new Period('2018-01-01', '2018-03-01'),
    new Period('2018-05-01', '2018-08-01')
);
$dataset = new Dataset();
$dataset->append('A', $sequence[0]);
$dataset->append('B', $sequence[1]);
$dataset->append('GAPS', $sequence->gaps());
(new ConsoleGraph())->display($dataset);
~~~

results:

~~~bash
 A    [---------------------)                                                         
 B                                                 [---------------------------------)
 GAPS                       [----------------------)    
~~~

The `Dataset` implements the `Countable` and the `IteratorAggregate` interface. It also exposes the following methods:

~~~php
<?php
public function Dataset::fromSequence(Sequence $sequence, ?LabelGenerator $labelGenerator = null): self; //Creates a new Dataset from a Sequence and a LabelGenerator.
public function Dataset::fromCollection(): self; //Creates a new Dataset from a generic iterable structure.
public function Dataset::isEmpty(): bool; //Tells whether the collection is empty.
public function Dataset::labels(): string[]; //the current labels used
public function Dataset::items(): array<Period|Sequence>; //the current objects inside the Dataset
public function Dataset::boundaries(): ?Period;  //Returns the collection boundaries or null if it is empty.
public function Dataset::labelMaxLength(): int;  //Returns the label max length.
public function Dataset::labelize(LabelGenerator $labelGenerator): self; //Update the labels used for the dataset.
~~~

## Setting the Dataset labels

By default you are required to provide a label per item added present in a `Dataset` object.
The package provides a `LabelGenerator` interface that ease generating and creating labels for your visualization.

A `LabelGenerator` implementing class is needed for the following methods

- The `Dataset::fromSequence`, to create a new instance from a `Sequence` object;
- The `Dataset::labelize` to update the associated labels in the current instance;

*By default when using `Dataset::fromSequence` if no `LabelGenerator` class is supplied the `LatinLetter` label generator will be used.*

The current package comes bundle with the following `LabelGenerator` implementing class:

### LatinLetter

Generates labels according the the latin alphabet.

~~~php
<?php

use Bakame\Period\Visualizer\ConsoleGraph;
use Bakame\Period\Visualizer\LatinLetter;
use Bakame\Period\Visualizer\Dataset;
use League\Period\Period;
use League\Period\Sequence;

$dataset = Dataset::fromSequence(
    new Sequence(new Period('2018-01-01', '2018-02-01'), new Period('2018-01-15', '2018-02-01')),
    new LatinLetter('aa')
);
(new ConsoleGraph())->display($dataset);
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

### DecimalNumber

Generates labels according to the decimal number system.

~~~php
<?php

use Bakame\Period\Visualizer\ConsoleGraph;
use Bakame\Period\Visualizer\DecimalNumber;
use Bakame\Period\Visualizer\Dataset;
use League\Period\Period;
use League\Period\Sequence;

$dataset = Dataset::fromSequence(
    new Sequence(new Period('2018-01-01', '2018-02-01'), new Period('2018-01-15', '2018-02-01')),
    new DecimalNumber(42)
);
(new ConsoleGraph())->display($dataset);
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

### RomanNumber

Uses the `DecimalNumber` label generator class to generate Roman number labels.

~~~php
<?php

use Bakame\Period\Visualizer\ConsoleGraph;
use Bakame\Period\Visualizer\DecimalNumber;
use Bakame\Period\Visualizer\RomanNumber;
use Bakame\Period\Visualizer\Dataset;
use League\Period\Period;
use League\Period\Sequence;

$labelGenerator = new RomanNumber(new DecimalNumber(5), RomanNumber::LOWER);

$dataset = Dataset::fromSequence(
    new Sequence(new Period('2018-01-01', '2018-02-01'), new Period('2018-01-15', '2018-02-01')),
    $labelGenerator
);
(new ConsoleGraph())->display($dataset);
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

### AffixLabel

Uses any `labelGenerator` implementing class to add prefix and/or suffix string to the generated labels.

~~~php
<?php

use Bakame\Period\Visualizer\AffixLabel;
use Bakame\Period\Visualizer\ConsoleGraph;
use Bakame\Period\Visualizer\DecimalNumber;
use Bakame\Period\Visualizer\RomanNumber;
use Bakame\Period\Visualizer\Dataset;
use League\Period\Period;
use League\Period\Sequence;

$labelGenerator = new AffixLabel(
    new RomanNumber(new DecimalNumber(5), RomanNumber::LOWER),
    '*', //prefix
    '.)'    //suffix
);
$dataset = Dataset::fromSequence(
    new Sequence(new Period('2018-01-01', '2018-02-01'), new Period('2018-01-15', '2018-02-01')),
    $labelGenerator
);
(new ConsoleGraph())->display($dataset);
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

### ReverseLabel

Uses any `labelGenerator` implementing class to reverse the generated labels order.

~~~php
<?php

use Bakame\Period\Visualizer\AffixLabel;
use Bakame\Period\Visualizer\ConsoleGraph;
use Bakame\Period\Visualizer\DecimalNumber;
use Bakame\Period\Visualizer\ReverseLabel;
use Bakame\Period\Visualizer\RomanNumber;
use Bakame\Period\Visualizer\Dataset;
use League\Period\Period;
use League\Period\Sequence;

$labelGenerator = new DecimalNumber(5);
$labelGenerator = new RomanNumber($labelGenerator, RomanNumber::LOWER);
$labelGenerator = new AffixLabel($labelGenerator, '', '.');
$labelGenerator = new ReverseLabel($labelGenerator);

$dataset = Dataset::fromSequence(
    new Sequence(new Period('2018-01-01', '2018-02-01'), new Period('2018-01-15', '2018-02-01')),
    $labelGenerator
);
(new ConsoleGraph())->display($dataset);
~~~

results:

~~~bash
 vi. [------------------------------------------------------------------------------)
 v.  [-----------------------------------)
~~~

### Custom LabelGenerator

You can create your own label generator by implementing the `Bakame\Period\Visualizer\Contract\LabelGenerator` interface like shown below:

~~~php
<?php

use Bakame\Period\Visualizer\AffixLabel;
use Bakame\Period\Visualizer\Contract\LabelGenerator;
use Bakame\Period\Visualizer\ConsoleGraph;
use Bakame\Period\Visualizer\Dataset;
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
$dataset = Dataset::fromSequence(
    new Sequence(new Period('2018-01-01', '2018-02-01'), new Period('2018-01-15', '2018-02-01')),
    $labelGenerator
);
(new ConsoleGraph())->display($dataset);
~~~

results:

~~~bash
 foobar. [------------------------------------------------------------------------------)
 foobar. [-----------------------------------)
~~~

## Displaying the Dataset

The `ConsoleGraph` class is responsible for generating the graph from the `Dataset` by implementing the `Graph` interface for the console.

The `ConsoleGraph::display` methods expects a `Dataset` object as its unique argument.

If you wish to present the graph on another medium like a web browser or an image, you will need to implement the interface on your implementation.

~~~php
<?php

use Bakame\Period\Visualizer\ConsoleGraph;
use Bakame\Period\Visualizer\Dataset;
use League\Period\Period;

$graph = new ConsoleGraph();
$graph->display(new Dataset([
    ['first', new Period('2018-01-01 08:00:00', '2018-01-01 12:00:00')],
    ['last', new Period('2018-01-01 10:00:00', '2018-01-01 14:00:00')],
]));
~~~

results:

~~~bash
 first [----------------------------------------------------)                          
 last                            [----------------------------------------------------)
~~~

### Customized the graph looks

The `ConsoleGraph` class can be customized by:
 
- providing a `ConsoleConfig` which defines:
    - the graph settings (How the intervals will be created)
        - sets single characters to represent the boundary types
        - sets single characters to represent the body and space
    - the console output settings. (How the intervals will be displayed)
        - sets the graph width
        - sets the graph colors
        - sets the gap between the labels and the rows
        - sets the label alignment

- providing a `OutputWriter` implementing class if you prefer to use a battle tested output library like `League CLImate` or `Symfony Console` 
to output the resulting graph. If you don't, the package ships with a minimal `ConsoleOutput` class which is used if you do not provide you own implementation.

~~~php
<?php

use Bakame\Period\Visualizer\AffixLabel;
use Bakame\Period\Visualizer\ConsoleConfig;
use Bakame\Period\Visualizer\ConsoleGraph;
use Bakame\Period\Visualizer\Dataset;
use Bakame\Period\Visualizer\DecimalNumber;
use Bakame\Period\Visualizer\ReverseLabel;
use Bakame\Period\Visualizer\RomanNumber;
use League\Period\Datepoint;
use League\Period\Period;
use League\Period\Sequence;

$config = ConsoleConfig::createFromRainbow()
    ->withStartExcluded('🍕')
    ->withStartIncluded('🍅')
    ->withEndExcluded('🎾')
    ->withEndIncluded('🍔')
    ->withWidth(30)
    ->withSpace('💩')
    ->withBody('😊')
    ->withGapSize(2)
    ->withLabelAlign(ConsoleConfig::ALIGN_RIGHT)
;

$labelGenerator = new DecimalNumber(42);
$labelGenerator = new RomanNumber($labelGenerator, RomanNumber::UPPER);
$labelGenerator = new AffixLabel($labelGenerator, '', '.');
$labelGenerator = new ReverseLabel($labelGenerator);

$sequence = new Sequence(
    Datepoint::create('2018-11-29')->getYear(Period::EXCLUDE_START_INCLUDE_END),
    Datepoint::create('2018-05-29')->getMonth()->expand('3 MONTH'),
    Datepoint::create('2017-01-13')->getQuarter(Period::EXCLUDE_ALL),
    Period::around('2016-06-01', '3 MONTHS', Period::INCLUDE_ALL)
);
$dataset = Dataset::fromSequence($sequence, $labelGenerator);
$dataset->append($labelGenerator->format('gaps'), $sequence->gaps());
$graph = new ConsoleGraph($config);
$graph->display($dataset);
~~~

result:

~~~bash
   XLV.  💩💩💩💩💩💩💩💩💩💩💩💩💩💩💩💩💩💩💩🍕😊😊😊😊😊😊😊😊😊🍔
  XLIV.  💩💩💩💩💩💩💩💩💩💩💩💩💩💩💩💩💩💩💩💩🍅😊😊😊😊😊🎾💩💩💩
 XLIII.  💩💩💩💩💩💩💩💩🍕😊😊🎾💩💩💩💩💩💩💩💩💩💩💩💩💩💩💩💩💩💩
  XLII.  🍅😊😊😊😊🍔💩💩💩💩💩💩💩💩💩💩💩💩💩💩💩💩💩💩💩💩💩💩💩💩
  GAPS.  💩💩💩💩💩🍕😊😊🍔💩💩🍅😊😊😊😊😊😊😊🍔💩💩💩💩💩💩💩💩💩💩
~~~

*On a POSIX compliant console all lines have different colors*

The `ConsoleConfig` class exposes the following additional constants and methods:

~~~php
<?php
const ConsoleConfig::ALIGN_LEFT = 1;
const ConsoleConfig::ALIGN_RIGHT = 0;
const ConsoleConfig::ALIGN_CENTER = 2;
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
