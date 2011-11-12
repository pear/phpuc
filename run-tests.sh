#! /bin/sh

if [ -x /usr/local/bin/php ] ; then
    php=/usr/local/bin/php
elif [ -x /usr/bin/php ] ; then
    php=/usr/bin/php
else
    echo "No php executable found."
    exit 1
fi

# Ensure the test results home page is up to date
cp unit-test-results/index.html packages-all/unit-test-results

# Look ma, SVN update!
/usr/bin/svn up packages-all

# And the unit tests runneth
$php tests.php

# http://github.com/smalyshev/migrate/blob/master/migrate.php
$php migrate.php packages-all > packages-all/unit-test-results/deprecated.txt
