<?Php
require_once 'Log.php';
require_once 'NormalUnitTests.php';

class GenerateApplication {
    protected $log;
    protected $build_strategy;

    public function __construct(Log $log, $build_strategy) {
        $this->log = $log;
        $this->build_strategy = $build_strategy;
    }

    function generate_project_svn(Package $p) {
        ob_start();
        ?>
<?xml version='1.0' encoding='UTF-8'?>
<project>
  <actions/>
  <description></description>
  <keepDependencies>false</keepDependencies>
  <scm class="hudson.scm.SubversionSCM">
    <locations>
      <hudson.scm.SubversionSCM_-ModuleLocation>
        <remote>http://svn.php.net/repository/pear/packages/<?php print $p->package; ?>/trunk</remote>
        <local>.</local>
      </hudson.scm.SubversionSCM_-ModuleLocation>
    </locations>
    <excludedRegions></excludedRegions>
    <includedRegions></includedRegions>
    <excludedUsers></excludedUsers>
    <excludedRevprop></excludedRevprop>
    <excludedCommitMessages></excludedCommitMessages>
    <workspaceUpdater class="hudson.scm.subversion.UpdateUpdater"/>
  </scm>
  <canRoam>true</canRoam>
  <disabled>false</disabled>
  <blockBuildWhenDownstreamBuilding>false</blockBuildWhenDownstreamBuilding>
  <blockBuildWhenUpstreamBuilding>false</blockBuildWhenUpstreamBuilding>
  <triggers class="vector">
    <hudson.triggers.TimerTrigger>
      <spec>@weekly</spec>
    </hudson.triggers.TimerTrigger>
    <hudson.triggers.SCMTrigger>
      <spec></spec>
    </hudson.triggers.SCMTrigger>
  </triggers>
  <concurrentBuild>false</concurrentBuild>
  <builders>
    <hudson.tasks.Shell>
      <command>phpunit --coverage-html build/coverage/ --coverage-clover build/logs/clover.xml --log-junit build/logs/junit.xml tests/</command>
    </hudson.tasks.Shell>
  </builders>
  <publishers>
    <org.jenkinsci.plugins.cloverphp.CloverPublisher>
      <publishHtmlReport>true</publishHtmlReport>
      <reportDir>build/coverage</reportDir>
      <xmlLocation>build/logs/clover.xml</xmlLocation>
      <disableArchiving>false</disableArchiving>
      <healthyTarget>
        <methodCoverage>70</methodCoverage>
        <statementCoverage>80</statementCoverage>
      </healthyTarget>
      <unhealthyTarget/>
      <failingTarget/>
    </org.jenkinsci.plugins.cloverphp.CloverPublisher>
    <hudson.tasks.junit.JUnitResultArchiver>
      <testResults>build/logs/junit.xml</testResults>
      <keepLongStdio>false</keepLongStdio>
      <testDataPublishers/>
    </hudson.tasks.junit.JUnitResultArchiver>
  </publishers>
  <buildWrappers/>
</project>
<?php
         return ob_get_clean();
    }

    function generate_project_git(Package $p) {
        ob_start();
        ?>
<?xml version='1.0' encoding='UTF-8'?>
<project>
  <actions/>
  <description></description>
  <keepDependencies>false</keepDependencies>
  <properties>
    <com.coravy.hudson.plugins.github.GithubProjectProperty>
      <projectUrl>http://github.com/pear/<?php print $p->package; ?>/</projectUrl>
    </com.coravy.hudson.plugins.github.GithubProjectProperty>
  </properties>
  <scm class="hudson.plugins.git.GitSCM">
    <configVersion>2</configVersion>
    <userRemoteConfigs>
      <hudson.plugins.git.UserRemoteConfig>
        <name>origin</name>
        <refspec>+refs/heads/*:refs/remotes/origin/*</refspec>
        <url>git://github.com/pear/<?php print $p->package; ?>.git</url>
      </hudson.plugins.git.UserRemoteConfig>
    </userRemoteConfigs>
    <branches>
      <hudson.plugins.git.BranchSpec>
        <name>master</name>
      </hudson.plugins.git.BranchSpec>
    </branches>
    <recursiveSubmodules>false</recursiveSubmodules>
    <doGenerateSubmoduleConfigurations>false</doGenerateSubmoduleConfigurations>
    <authorOrCommitter>false</authorOrCommitter>
    <clean>false</clean>
    <wipeOutWorkspace>false</wipeOutWorkspace>
    <pruneBranches>false</pruneBranches>
    <remotePoll>false</remotePoll>
    <buildChooser class="hudson.plugins.git.util.DefaultBuildChooser"/>
    <gitTool>Default</gitTool>
    <submoduleCfg class="list"/>
    <relativeTargetDir></relativeTargetDir>
    <excludedRegions></excludedRegions>
    <excludedUsers></excludedUsers>
    <gitConfigName></gitConfigName>
    <gitConfigEmail></gitConfigEmail>
    <skipTag>false</skipTag>
    <scmName></scmName>
  </scm>
  <canRoam>true</canRoam>
  <disabled>false</disabled>
  <blockBuildWhenDownstreamBuilding>false</blockBuildWhenDownstreamBuilding>
  <blockBuildWhenUpstreamBuilding>false</blockBuildWhenUpstreamBuilding>
  <triggers class="vector">
    <hudson.triggers.TimerTrigger>
      <spec>@weekly</spec>
    </hudson.triggers.TimerTrigger>
    <com.cloudbees.jenkins.GitHubPushTrigger>
      <spec></spec>
    </com.cloudbees.jenkins.GitHubPushTrigger>
  </triggers>
  <concurrentBuild>false</concurrentBuild>
  <builders>
    <hudson.tasks.Shell>
      <command>phpunit --coverage-html build/coverage/ --coverage-clover build/logs/clover.xml --log-junit build/logs/junit.xml tests/</command>
    </hudson.tasks.Shell>
  </builders>
  <publishers>
    <org.jenkinsci.plugins.cloverphp.CloverPublisher>
      <publishHtmlReport>true</publishHtmlReport>
      <reportDir>build/coverage</reportDir>
      <xmlLocation>build/logs/clover.xml</xmlLocation>
      <disableArchiving>false</disableArchiving>
      <healthyTarget>
        <methodCoverage>70</methodCoverage>
        <statementCoverage>80</statementCoverage>
      </healthyTarget>
      <unhealthyTarget/>
      <failingTarget/>
    </org.jenkinsci.plugins.cloverphp.CloverPublisher>
    <hudson.tasks.junit.JUnitResultArchiver>
      <testResults>build/logs/junit.xml</testResults>
      <keepLongStdio>false</keepLongStdio>
      <testDataPublishers/>
    </hudson.tasks.junit.JUnitResultArchiver>
  </publishers>
  <buildWrappers/>
</project>
<?php
         return ob_get_clean();
    }

    public function create_project(Package $p) {
        if (!file_exists($p->jenkins)) {
            throw new Exception("Doesnt exist: " . $p->jenkins);
        }
        if (!file_exists($p->jenkins . '/jobs/')) {
            throw new Exception("Doesnt exist: " . $p->jenkins . "/jobs");
        }

        if (!is_writable($p->jenkins . '/jobs/')) {
            throw new Exception("Not writable: " . $p->jenkins . "/jobs");
        }

        // Required directories
        $paths = array();
        $paths[] = $p->jenkins . '/jobs/' . $p->package;

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
        //$build->save($p->jenkins . '/jobs/' . $p->package . '/build.xml');

        $new_node_list = $project->getElementsByTagName("project");

        $existing_projects = array();
        $dir = new DirectoryIterator($p->jenkins . '/jobs/');
        foreach ($dir as $file) {
            if (!$file->isDir() || (string)$file == '.' || (string)$file == '..') {
                continue;
            }

            $existing_projects[] = (string)$file;
        }

        $project->save($p->jenkins . '/jobs/' . $p->package . '/config.xml');
    }


    public function execute(Package $p) {
        try {
            $this->create_project($p);

            $build = new DOMDocument();

            $build->loadXML($this->build_strategy->build($p));

            $project = new DOMDocument();
            if  ($p->determineSCM() == 'git') {
                $project->loadXML($this->generate_project_git($p));
                $this->install_project($p, $build, $project);
            }

            if  ($p->determineSCM() == 'svn') {
                $project->loadXML($this->generate_project_svn($p));
                $this->install_project($p, $build, $project);
            }

        } catch (Exception $e) {
            $this->log->log($e->getMessage());
            exit(1);
        }
    }
}
