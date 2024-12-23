# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

- `Added` for new features.
- `Changed` for changes in existing functionality.
- `Deprecated` for soon-to-be removed features.
- `Removed` for now removed features.
- `Fixed` for any bug fixes.
- `Security` in case of vulnerabilities

## [2.1.2]- 2024.12.23

### Added

- Tested up to PHP v8.4.
- Updated GitHub issue templates.

## [2.1.1]- 2023.07.27

### Changed

- Updated order template tags are processed to ensure comments are removed first.
- Updated template tags for `$data` array with default value to check if exists on array before returning string.
- Minor code cleanup.

## [2.1.0]- 2023.04.05

### Changed

- Updated `$data` array available in views to not be in dot notation.

## [2.0.0]- 2023.01.26

### Added

- Added support for PHP 8.

## [1.4.0]- 2022.01.24

### Added

- Added support for `@section` and `@place` template tags.

## [1.3.1]- 2021.09.13

### Fixed

- Fixed bug where template tags were breaking when more than one per line.

## [1.3.0]- 2021.03.13

### Added

- Added new template tag to replace with default string if not existing.

### Fixed

- Fixed bug with sort order of injectables by updating `php-array-helpers` vendor library.

## [1.2.1] - 2020.11.27

### Changed

- Updated `README` and vendor libraries.

## [1.2.0] - 2020.09.04

### Added

- Added `getBasePath` and `setBasePath` methods.

## [1.1.0] - 2020.08.21

### Added

- Added `@markdown:` template tag

## [1.0.0] - 2020.08.17

### Added

- Initial release.