# Changelog

All Notable changes to `Period Visualizer` will be documented in this file

## Next - TBD

- Adding `TypeError` on `Dataset::append` method when expected type are not given.

## 0.6.0 - 2019-09-20

- The `OutputWriter` is now a property of `GanttChartConfig`
- Renamed `Graph` to `Chart`
- Renamed `Graph::display` to `Chart::display`
- Renamed `ConsoleGraph` to `GanttChart`
- Renamed `ConsoleConfig` to `GanttChartConfig`
- Adding `Dataset::appendAll`
- Adding `GanttChartConfig::leftMargin`
- Adding `GanttChartConfig::withLeftMargin`
- Improved `GanttChart` implementation
- Improved `ConsoleOutput` implementation
- Changed `OutputWriter::writeln` signature

## 0.5.1 - 2019-07-28

- Bug fix `Dataset::labelMaxLength` when `Dataset` is empty
- Bug fix `GanttChart::display` when `Dataset` only contains empty `Sequence` instances.
- Improve `GanttChart` implementation.

## 0.5.0 - 2019-07-25

- Moved all interfaces into a `Contract` dedicated namespace
- Added the `Dataset` class.
- Added the `Graph` interface and the `ConsoleGraph` implementation.
- Added the `OutputWriter` interface and the `ConsoleOutput` implementation, `ConsoleOutput` no longer relies on `echo`
- Added support for label and basicGraph gutter in `ConsoleConfig` class
- Added support for label alignment in `ConsoleConfig` class
- Moved basicGraph features out of the `ConsoleOutput` class to `ConsoleGraph`
- Merged the `Matrix` class feature into the `ConsoleGraph` and removed it
- Renamed the `LabelGenerator` implementing classes and moved them in the main namespace
- Removed the `Viewer` class

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
- Improved `Matrix` code.

## 0.2.2 - 2018-12-20

- Bug Fix `ConsoleOutput::render` method. No newline character must be added at the end of the line.

## 0.2.1 - 2018-12-20

- Bug Fix `ConsoleOutput::render` method. Once the matrix is created we just use it and we non longer rely on the submitted data.

## 0.2.0 - 2018-12-20

- The `OutputInterface::render` and the `OutputInterface::display` array signature has changed.

The array format is that of a dataset where:
	- the first value represents the period or the sequence label
	- the second value represents the period or the sequence

- The `Matrix` and the `Viewer` class are updated accordingly with refactoring to improve the calculation speed.
- The `ConsoleConfig` accepts a `default` color keywords to tell that the basicGraph should fellow the default colors from the basicGraph.
- The `default` color keyword replaces the `white` color keyword as the default keyword used if no color is specified.

## 0.1.0 - 2018-12-19

- Initial release
