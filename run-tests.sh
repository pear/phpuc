#/bin/sh

# Ensure the test results home page is up to date
cp unit-test-results/index.html packages-all/unit-test-results

# Look ma, SVN update!
/usr/bin/svn up packages-all

# And the unit tests runneth
/usr/local/bin/php tests.php

# http://github.com/smalyshev/migrate/blob/master/migrate.php
/usr/local/bin/php migrate.php packages-all > packages-all/unit-test-results/deprecated.txt
