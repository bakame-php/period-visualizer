# Changelog

All Notable changes to `Period Visualizer` will be documented in this file

# 0.5.0 - TBD

- Added the `Tuple` class.
- Added the `Writer` interface and the `ConsoleStdout` implementation, output is no longer done using `echo`
- Added the `Viewer::view` method as the most generic method
- Added gap and padding configuration settings in `ConsoleConfig` class
- Removed `Viewer::getLabelGenerator` and `Viewer::getOutput` methods 
- Refactored the `ConsoleOutput` to improve decoupling
- Moved all interfaces into a `Contract` dedicated namespace
- Refactored the interfaces to make them simpler to implement
- Renamed the `LabelGenerator` implementing classes
- Removed the `Matrix` class

## 0.4.0 - 2019-06-17

- Added support for boundary type in:
    - Added `Matrix::TOKEN_*` public constants
    - Changed `Matrix::build` returned array content
    - Added the following `ConsoleConfig` methods:
        - `ConsoleConfig::endIncluded`
        - `ConsoleConfig::withEndIncluded`
        - `ConsoleConfig::startExcluded`
        - `ConsoleConfig::withStartIncluded`
    - Renamed the following `ConsoleConfig` methods:
        - `ConsoleConfig::getTail` with `ConsoleConfig::startIncluded` 
        - `ConsoleConfig::withTail` with `ConsoleConfig::withEndExcluded` 
        - `ConsoleConfig::getHead` with `ConsoleConfig::endExcluded` 
        - `ConsoleConfig::withHead` with `ConsoleConfig::withEndExcluded`
- Improve Package UX/DX
    - Removed the `get` prefix from all `ConsoleConfig` getter methods
    - Changed the suffix from all `LabelGenerator` classes from `Type` to `Generator`
    - Changed `LabelGenerator::getLabels` method name to `LabelGenerator::generate`
    - Added `LabelGenerator::format` method to format a single label
    - Made `ConsoleOutput` optional in the `Viewer` constructor method
    - Added `Viewer::unions`
    - Added `Viewer::setLabelGenerator` and `Viewer::setOutput` are chainable
    - Changed arguments order in `Viewer::__construct`
    - Added `$prefix` and `$suffix` parameters to the `AffixGenerator`

## 0.3.1 - 2018-12-21

- Enforces strict types

## 0.3.0 - 2018-12-21

- Removed the `OutputInterface`.
- Made `ConsoleOutput::render` private only `ConsoleOutput::display` stays public.
- Improve `Matrix` code.

## 0.2.2 - 2018-12-20

- Bug Fix `ConsoleOutput::render` method. No newline character must be added at the end of the line.

## 0.2.1 - 2018-12-20

- Bug Fix `ConsoleOutput::render` method. Once the matrix is created we just use it and we non longer rely on the submitted data.

## 0.2.0 - 2018-12-20

- The `OutputInterface::render` and the `OutputInterface::display` array signature has changed.

The array format is that of a tuple where:
	- the first value represents the period or the sequence label
	- the second value represents the period or the sequence

- The `Matrix` and the `Viewer` class are updated accordingly with refactoring to improve the calculation speed.
- The `ConsoleConfig` accepts a `default` color keywords to tell that the output should fellow the default colors from the console.
- The `default` color keyword replaces the `white` color keyword as the default keyword used if no color is specified.

## 0.1.0 - 2018-12-19

- Initial release
