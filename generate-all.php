<?php
require_once 'Log.php';
require_once 'Log/console.php';
require_once 'Package.php';
require_once 'GenerateApplication.php';


$source = isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : dirname(__FILE__);
$cruisecontrol = isset($_SERVER['argv'][2]) ? $_SERVER['argv'][2] : dirname(__FILE__);

$dir = new RecursiveDirectoryIterator($source);

$strategy = new NormalUnitTests();
$app = new GenerateApplication(new Log_console("Output"), $strategy);

$dirs = $strategy->collect_directories($dir);

foreach ($dirs as $file) {
    $p = new Package($file->getFileName(), $file->getPath(), $cruisecontrol);
    $app->execute($p);
}
