#/bin/sh

# Look ma, SVN update!
cd ~/packages-all/
/usr/bin/svn up ~/packages-all

# And the unit tests runneth
/usr/local/bin/php tests.php

# http://github.com/smalyshev/migrate/blob/master/migrate.php
/usr/local/bin/php ~/migrate.php . > unit-test-results/deprecated.txt
