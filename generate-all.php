<?php
require_once 'Log.php';
require_once 'Log/console.php';
require_once 'Package.php';
require_once 'GenerateApplication.php';

/**
 * Iterate over a given directory to find all PHPUnit tests
 */
function collect_package_all_directories(RecursiveDirectoryIterator $dir) {
    $dirs = array();
    foreach ($dir as $file) {
        if (!is_dir($file)) {
            continue;
        }

        $test_dir = (string)$file . '/tests/AllTests.php';

        if (file_exists($test_dir)) {
            $dirs[] = $file;
        }
    }

    return $dirs;

}


$source = isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : dirname(__FILE__);
$cruisecontrol = isset($_SERVER['argv'][2]) ? $_SERVER['argv'][2] : dirname(__FILE__);

$dir = new RecursiveDirectoryIterator($source);

$dirs = collect_package_all_directories($dir);

foreach ($dirs as $file) {
    $p = new Package($file->getFileName(), $file->getPath(), $cruisecontrol);

    $app = new GenerateApplication(new Log_console("Output"));
    $app->execute($p);
}
