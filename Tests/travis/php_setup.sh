#!/bin/sh

set -e


echo "**************************"
echo "Setting up PHP Extensions."
echo "**************************"
echo ""
echo "PHP Version: $TRAVIS_PHP_VERSION"

if [ "$TRAVIS_PHP_VERSION" = "hhvm" ]; then
    echo "Unable to install php extensions on current system"

else

    echo ""
    echo "*********************"
    echo "Updating php.ini file"
    echo "*********************"
    echo ""
    echo ""
    phpenv config-add tests/travis/php_extensions.ini

fi