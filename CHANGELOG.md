# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.3.0] — 2026-05-16

### Added
- UI 2.0 support (Webasyst backend design system with dark mode)
- JSON AJAX controllers for chart refresh without full page reload
- GitHub Actions workflows for PHP version compatibility check and automated releases

### Changed
- Migrated charts from jqPlot to Chart.js 4
- Refactored JavaScript sources to TypeScript; build system switched to Vite
- Filter preferences moved from database to localStorage
- Inline JS and CSS extracted to separate files
- Currency formatting replaced with native `Intl.NumberFormat`
- Timeframe dropdown initialized via UI 2.0 `$.waDropdown()`
- Settings saving logic moved into the plugin class

### Fixed
- `escape:'javascript'` Smarty modifier replaced with `json_encode:256` to prevent XSS

## [2.2.0] — 2014-11-02

### Added
- Average ticket price graph in the orders-by-day report

## [2.1.0] — 2014-10-06

### Fixed
- Incorrect statistics for the last day of the selected period

### Added
- Filter by order status in both reports

## [2.0.0] — 2014-08-19

### Added
- Weekdays statistics report (orders grouped by day of week, using average values)
- Plugin settings: toggle individual table columns and chart series
- Order state filter preferences saved per user
- Toggle between total and average values in charts

## [1.0.0] — 2014-06-10

### Added
- Initial release: orders report grouped by creation date
- Date range selector
- English and Russian localization
