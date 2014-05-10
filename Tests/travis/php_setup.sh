#!/bin/sh

set -e


echo "**************************"
echo "Setting up PHP Extensions."
echo "**************************"
echo ""
echo "PHP Version: $TRAVIS_PHP_VERSION"

if [ "$TRAVIS_PHP_VERSION" = "hhvm" ] || [ "$TRAVIS_PHP_VERSION" = "hhvm-nightly" ]; then
    echo "Unable to install php extensions on current system"

else

    echo ""
    echo "*********************"
    echo "Updating php.ini file"
    echo "*********************"
    echo ""
    echo ""
    phpenv config-add Tests/travis/php_extensions.ini

fi