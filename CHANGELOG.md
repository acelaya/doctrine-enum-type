## CHANGELOG

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com), and this project adheres to [Semantic Versioning](https://semver.org).

## [2.5.0] - 2021-01-25
### Added
* [#60](https://github.com/acelaya/doctrine-enum-type/issues/60) Added support for doctrine-dbal 3.0.

### Changed
* [#57](https://github.com/acelaya/doctrine-enum-type/issues/57) Automated releases.
* [#58](https://github.com/acelaya/doctrine-enum-type/issues/58) Migrated build from travis to Github Actions.

### Deprecated
* *Nothing*

### Removed
* *Nothing*

### Fixed
* *Nothing*


## [2.4.0] - 2020-10-31
### Added
* *Nothing*

### Changed
* [#51](https://github.com/acelaya/doctrine-enum-type/issues/51) Updated all dependencies and added support for composer 2.
* [#50](https://github.com/acelaya/doctrine-enum-type/issues/50) Added support for PHP 8.

### Deprecated
* *Nothing*

### Removed
* [#52](https://github.com/acelaya/doctrine-enum-type/issues/52) Dropped support for PHP 7.2 and 7.3

### Fixed
* *Nothing*


## [2.3.0] - 2019-12-14
### Added
* [#43](https://github.com/acelaya/doctrine-enum-type/issues/43) Added PHP 7.4 to the build matrix.

### Changed
* [#41](https://github.com/acelaya/doctrine-enum-type/issues/41) Updated infection to v0.15 and phpstan 0.12
* [#44](https://github.com/acelaya/doctrine-enum-type/issues/44) Updated to [shlinkio/php-coding-standard](https://github.com/shlinkio/php-coding-standard) v2.0.

### Deprecated
* *Nothing*

### Removed
* [#40](https://github.com/acelaya/doctrine-enum-type/issues/40) Dropped support for PHP 7.1

### Fixed
* *Nothing*


## [2.2.3] - 2019-02-08
### Added
* [#30](https://github.com/acelaya/doctrine-enum-type/issues/30) Added PHP 7.3 to the build matrix.

### Changed
* [#36](https://github.com/acelaya/doctrine-enum-type/issues/36) Updated dev dependencies.
* [#31](https://github.com/acelaya/doctrine-enum-type/issues/31) Performance and maintainability slightly improved by enforcing via code sniffer that all global namespace classes, functions and constants are explicitly imported.
* [#32](https://github.com/acelaya/doctrine-enum-type/issues/32) Updated infection to v0.11
* [#34](https://github.com/acelaya/doctrine-enum-type/issues/34) Added dependency on [Shlinkio](https://github.com/shlinkio/php-coding-standard) coding standard.

### Deprecated
* *Nothing*

### Removed
* *Nothing*

### Fixed
* *Nothing*


## [2.2.2] - 2018-10-02
### Added
* *Nothing*

### Changed
* *Nothing*

### Deprecated
* *Nothing*

### Removed
* *Nothing*

### Fixed
* [#28](https://github.com/acelaya/doctrine-enum-type/issues/28) Fixed a defect preventing the `length` property from the field declaration to be applied.

    Now, if you declare your field like this `@ORM\Column(type=Action::class, length=16)` the field will respect the length and be mapped to a `VARCHAR(16)`.


## [2.2.1] - 2018-09-02
### Added
* *Nothing*

### Changed
* [#24](https://github.com/acelaya/doctrine-enum-type/issues/24) Documented how to register custom types for schema operations.
* [#25](https://github.com/acelaya/doctrine-enum-type/issues/25) Updated to Infection 0.10
* [#26](https://github.com/acelaya/doctrine-enum-type/issues/26) Improved badges in readme file.

### Deprecated
* *Nothing*

### Removed
* *Nothing*

### Fixed
* *Nothing*


## [2.2.0] - 2018-03-12
### Added
* [#9](https://github.com/acelaya/doctrine-enum-type/issues/9) Allowed enums with values other than strings to be loaded from the database

### Changed
* [#18](https://github.com/acelaya/doctrine-enum-type/issues/18) Added infection to the ci pipeline

### Deprecated
* *Nothing*

### Removed
* *Nothing*

### Fixed
* *Nothing*


## [2.1.0] - 2018-02-04
### Added
* *Nothing*

### Changed
* [#17](https://github.com/acelaya/doctrine-enum-type/issues/17) Improved required coding standards

### Deprecated
* *Nothing*

### Removed
* [#16](https://github.com/acelaya/doctrine-enum-type/issues/16) Dropped support for PHP 5.6 and 7.0

### Fixed
* *Nothing*


## [2.0.3] - 2017-12-06
### Added
* *Nothing*

### Changed
* *Nothing*

### Deprecated
* *Nothing*

### Removed
* *Nothing*

### Fixed
* [#14](https://github.com/acelaya/doctrine-enum-type/issues/14) Required SQL comment hint


## [2.0.2] - 2017-12-06
### Added
* [#11](https://github.com/acelaya/doctrine-enum-type/issues/11) Add gitattributes file to exclude content from distribution

### Changed
* *Nothing*

### Deprecated
* *Nothing*

### Removed
* *Nothing*

### Fixed
* [#13](https://github.com/acelaya/doctrine-enum-type/issues/13) Fixed create a new custom type
