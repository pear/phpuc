<?php
require_once 'Log.php';
require_once 'Log/console.php';

/**
 * A quick and dirty cruise control project maker for pear.
 *
 * @todo Check what's up with PHPUC's version and why it didn't work
 */

 class Package {
    public $package;
    public $source;
    public $cruisecontrol;

    /**
     * @param string $package Package Name (ie: IPv6)
     * @param string $source Path to packages-all checkout (http://svn.php.net/repository/pear/packages-all)
     * @param string $cruisecontrol Path to cruise control installation (~/cruisecontrol)
     */
    public function __construct($package, $source, $cruisecontrol) {
        $this->package = $package;
        $this->source = $source;
        $this->cruisecontrol = $cruisecontrol;
    }
}

class GenerateApplication {
    protected $log;

    public function __construct(Log $log) {
        $this->log = $log;
    }

    /**
     * Generate a template for a build.xml
     */
    public function generate_build(Package $p) {
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
            <arg line="--report=checkstyle --standard=PEAR"/>
        </exec>
    </target>
    <target name="phpmd">
        <exec executable="phpmd" dir="${basedir}">
            <arg line="./src xml codesize,unusedcode,naming"/>
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

    function generate_project(Package $p) {
        ob_start();
        ?>
<project name="<?php print $p->package; ?>" buildafterfailed="false">
    <plugin name="svn" classname="net.sourceforge.cruisecontrol.sourcecontrols.SVN" /> 
    <modificationset quietperiod="60">
        <svn localWorkingCopy="<?php print $p->source; ?>/<?php print $p->package; ?>"/>
    </modificationset> 

    <schedule interval="120">
        <ant anthome="apache-ant-1.7.0" buildfile="projects/${project.name}/build.xml"/>
    </schedule>

    <log dir="logs/${project.name}">
        <merge dir="projects/${project.name}/build/logs/"/>
    </log> 

    <publishers>
        <artifactspublisher dir="projects/${project.name}/build/coverage" dest="artifacts/${project.name}" subdirectory="coverage"/>
        <execute command="phpuc graph logs/${project.name} artifacts/${project.name}"/> 
    </publishers> 
</project>
         <?php
         return ob_get_clean();
    }

    public function create_project(Package $p) {
        if (!file_exists($p->cruisecontrol)) {
            throw new Exception("Doesnt exist: " . $p->cruisecontrol);
        }
        if (!file_exists($p->cruisecontrol . '/projects/')) {
            throw new Exception("Doesnt exist: " . $p->cruisecontrol . "/projects");
        }

        if (!is_writable($p->cruisecontrol . '/projects/')) {
            throw new Exception("Not writable: " . $p->cruisecontrol . "/projects");
        }

        // Required directories
        $paths = array();
        $paths[] = $p->cruisecontrol . '/projects/' . $p->package;
        $paths[] = $p->cruisecontrol . '/projects/' . $p->package . '/build';
        $paths[] = $p->cruisecontrol . '/projects/' . $p->package . '/build/logs';
        $paths[] = $p->cruisecontrol . '/projects/' . $p->package . '/logs';

        foreach ($paths as $path) {
            if (!file_exists($path)) {
                mkdir($path);
                $this->log->log($path . " created");
            } else {
                $this->log->log($path . " already exists, skipping");
            }
        }
    }



    /**
     * Install both project / build documents
     */
    function install_project(Package $p, DOMDocument $build, DOMDocument $project) {
        $build->save($p->cruisecontrol . '/projects/' . $p->package . '/build.xml');

        $config = new DOMDocument();
        $config->load($p->cruisecontrol . '/config.xml');

        $new_node_list = $project->getElementsByTagName("project");
        $old_node_list = $config->getElementsByTagName("project");

        $existing_projects = array();
        for ($i = 0; $i < $old_node_list->length; $i++) {
            $node = $old_node_list->item($i);
            $existing_projects[] = $node->getAttribute("name");
        }

        $node_list = $project->getElementsByTagName("project");
        for ($i = 0; $i < $node_list->length; $i++) {
            $node = $node_list->item($i);

            if (in_array($node->getAttribute("name"), $existing_projects)) {
                $this->log->log($node->getAttribute("name") . " already exists in config.xml, skipping");
                continue;
            }

            $new_node = $config->importNode($node, true);

            $config->getElementsByTagName('cruisecontrol')->item(0)->appendChild($new_node);
        }

        $config->save($p->cruisecontrol . '/config.xml');
    }


    public function execute(Package $p) {
        try {
            $this->create_project($p);

            $build = new DOMDocument();
            $build->loadXML($this->generate_build($p));

            $project = new DOMDocument();
            $project->loadXML($this->generate_project($p));

            $this->install_project($p, $build, $project);

        } catch (Exception $e) {
            $this->log->log($e->getMessage());
            exit(1);
        }
    }
}



$p = new Package("Net_IPv7", dirname(__FILE__), dirname(__FILE__));

$app = new GenerateApplication(new Log_console("Output"));
$app->execute($p);
