
## Tedivm StashBundle v0.4 Changelog

### 0.4.1

*   Full HHVM Support.

*   Upgrade to Stash v0.12

*   Removed PEAR Support.

*   Added metadocuments like LICENSE, CONTRIBUTORS, and docs/index.srt.

*   Updated Readme with more project status news and to reflect the changes in this version.

*   Massive Documentation Update, with complete DocBlock and file level documentation.

*   Full Doctrine Cache Support via DoctrineAdapter.

*   Improved Redis support.



#### Test Suite

*   98% code coverage- more than half of the changes from this update come from improved test suites.

*   Test against parent/upstream test suites.

*   Enforcement of Formatting and Coding Standards.

*   Refactored internal API for easier testing.

*   Improved Travis-CI Integration.

*   Coveralls Support.



#### API


*   Renamed service container alias from "cache" to "stash".

    This prevents overlap with the Symfony defined "cache" item, which is part of the HTTPCache package.


*   Renamed CacheLogger to CacheTracker

    This is to prevent collision with the setLogger(\Psr\Logger) functionality.


*   Renamed Handlers to Drivers (including HandlerFactory to DriverFactory

    This maintains parity with Stash, which refers to the back end interface code as drivers. Configurations that use
    "handlers" will still work, although new configurations should use the new nomenclature.


*   Removed CacheResultObject wrapper in favor of extending the Stash\Item class

    This means the Service\CacheService returns Service\CacheItems instead, which are extensions of the Stash\Item
    class. This reduces the abstraction between Stash and the developer down to the absolute minimum.


*   Reduced dependencies significantly

    This was done by only including directly used projects in composer.lock instead of using metapackages like the
    symfony-framework-bundle.
