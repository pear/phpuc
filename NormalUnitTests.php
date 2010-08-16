<?php
class NormalUnitTests {
    public function build(Package $p) {
        ob_start();
        ?>
<?xml version="1.0" encoding="UTF-8"?>
<project name="<?php print $p->package; ?>" basedir="<?php print $p->source; ?>/<?php print $p->package; ?>" default="build">
     <target name="checkout">
        <exec executable="svn" dir="${basedir}">
            <arg line="up" />
        </exec>
    </target>
    <target name="php-codesniffer">
        <exec executable="phpcs" dir="${basedir}" output="<?php print $p->cruisecontrol; ?>/projects/<?php print $p->package; ?>/build/logs/checkstyle.xml">
            <arg line="--report=checkstyle --standard=PEAR <?php print $p->source; ?>/<?php print $p->package; ?>"/>
        </exec>
    </target>

    <target name="phpmd">
        <exec executable="phpmd" dir="${basedir}">
            <arg line="<?php print $p->source; ?>/<?php print $p->package; ?> xml codesize,unusedcode,naming"/>
        </exec>
    </target>

    <target name="phpunit">
    <exec executable="phpunit" dir="${basedir}" failonerror="on">
        <arg line="--log-junit <?php print $p->cruisecontrol; ?>/projects/<?php print $p->package; ?>/build/logs/junit.xml tests/AllTests.php" /> 
    </exec>
    </target>
    <target name="build" depends="checkout,php-codesniffer,phpmd,phpunit" />
</project>
        <?php
        return ob_get_clean();
    }
}