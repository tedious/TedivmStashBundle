
## Tedivm StashBundle v0.4 Changelog

### 0.4.1

*   Full HHVM Support.

*   Removed PEAR Support.

*   Added metadocuments like LICENSE, CONTRIBUTORS, and docs/index.srt.

*   Updated Readme with more project status news and to reflect the changes in this version.

*   Massive Documentation Update, with complete DocBlock and file level documentation.

*   Full Doctrine Cache Support via DoctrineAdapter.

*   Improved Redis support.



#### Test Suite

*   98% code coverage.

    Seriously, so much testing. Well more than half of the changes from this update come from improved test suites.

*   Extends Parent Test Suites.

    In addition to the bundle specific tests, this suite is now tested against relevant upstream code. This means, for
    example, that the CacheService passes all of the Stash\Pool tests, CacheItem the Stash\Item tests, and the
    DoctrineAdapter passes all of the available Doctrine Cache tests.

*   Enforcement of Formatting and Coding Standards.

*   Refactored Internal API for easier testing.

*   Improved Travis-CI Integration.

*   Coveralls Support.



#### API

*   Renamed CacheLogger to CacheTracker

    This is to prevent collision with the setLogger(\Psr\Logger) functionality.

*   Renamed Handlers to Drivers (including HandlerFactory to DriverFactory

    This maintains parity with Stash, which refers to the back end interface code as drivers.

*   Removed CacheResultObject wrapper in favor of extending the Stash\Item class

    This means the Service\CacheService returns Service\CacheItems instead, which are extensions of the Stash\Item
    class. This reduces the abstraction between Stash and the developer down to the absolute minimum.

*   Reduced dependencies significantly

    This was done by only including directly used projects in composer.lock instead of using metapackages like the
    symfony-framework-bundle.