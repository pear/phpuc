<?php
class NormalUnitTests {
    public function build(Package $p) {
        ob_start();
        ?>
<?xml version="1.0" encoding="UTF-8"?>
<project name="<?php print $p->package; ?>" basedir="<?php print $p->source; ?>/<?php print $p->package; ?>" default="build">
    <target name="php-codesniffer">
        <exec executable="phpcs" dir="${basedir}" output="<?php print $p->jenkins; ?>/projects/<?php print $p->package; ?>/build/logs/checkstyle.xml">
            <arg line="--report=checkstyle --standard=PEAR --ignore=tests <?php print $p->source; ?>/<?php print $p->package; ?>"/>
        </exec>
    </target>

    <target name="phpmd">
        <exec executable="phpmd" dir="${basedir}">
            <arg line="${basedir} xml codesize,unusedcode,naming --reportfile <?php print $p->jenkins; ?>/projects/<?php print $p->package; ?>/build/logs/pmd.xml"/>
        </exec>
    </target>

    <target name="phpcpd">
        <exec executable="phpcpd" dir="${basedir}">
            <arg line="${basedir} --log-pmd <?php print $p->jenkins; ?>/projects/<?php print $p->package; ?>/build/logs/cpd.xml"/>
        </exec>
    </target>

    <target name="phpunit">
        <exec executable="phpunit" dir="${basedir}" failonerror="on">
            <?php if (extension_loaded('xdebug')) { ?>
                <arg line="-d error_reporting='E_ALL &amp; ~E_STRICT'
                             --log-junit build/logs/junit.xml 
                            <?php print $this->getBootstrap($p); ?>
                            --coverage-clover build/logs/clover.xml
                            --coverage-html build/coverage 
                            <?php print $this->getTestPath($p); ?>" /> 
            <?php } else { ?>
                <arg line="-d error_reporting='E_ALL &amp; ~E_STRICT'
                          --log-junit build/logs/junit.xml <?php print $this->getBootstrap($p); ?>
                            <?php print $this->getTestPath($p); ?>" /> 
            <?php } ?>
        </exec>
    </target>

    <?php if ($p->pyrus) { ?>
    <target name="package">
        <!-- Todo: refactor this, not everyone lives in /home/clockwerx -->
        <exec executable="php" dir="${basedir}">
            <arg line="-d error_reporting='E_ALL &amp; ~E_STRICT' /home/clockwerx/phpuc/make-package.php ${basedir}" />
        </exec>

        <exec executable="php" dir="${basedir}"  failonerror="on">
            <arg line="<?php print $p->pyrus ?> package -o <?php print $p->jenkins; ?>/projects/<?php print $p->package; ?>/build/package/trunk.tar.gz" />
        </exec>

        <exec executable="svn" dir="${basedir}">
            <arg line="revert package.xml" />
        </exec>
    </target>
    <?php } ?>

    <?php if ($p->pyrus) { ?>
    <target name="build" depends="php-codesniffer,phpmd,phpcpd,phpunit,package" />
    <?php } else { ?>
    <target name="build" depends="php-codesniffer,phpmd,phpcpd,phpunit" />
    <?php } ?>
</project>
        <?php
        return ob_get_clean();
    }

    public function getTestPath(Package $p) {
        $path = $p->source . "/" . $p->package . "/tests/AllTests.php";
        if (file_exists($path)) {
            return $path;
        }

        $path = $p->source . "/" . $p->package . "/tests/";
        if (file_exists($path)) {
            return $path;
        }
        return false;
    }

    /** @todo Refactor this when 10 other packages have bootstraps? */
    public function getBootstrap(Package $p) {
        if ($p->package == "Mail_Queue") {
            return "--bootstrap " . $p->source . "/" . $p->package . "/tests/TestInit.php";
        }
        return "";
    }

    /**
     * Iterate over a given directory to find all PHPUnit tests, and phpt tests
     */
    public function collect_directories(RecursiveDirectoryIterator $dir) {
        $dirs = array();
        foreach ($dir as $file) {
            if (!is_dir($file)) {
                continue;
            }

            $test_dir = (string)$file . '/tests/AllTests.php';

            if (file_exists($test_dir)) {
                $dirs[] = $file;
                continue;
            }

            $test_dir = (string)$file . '/tests/';

            if (file_exists($test_dir)) {
                $dirs[] = $file;
                continue;
            }
        }

        return $dirs;
    }
}
