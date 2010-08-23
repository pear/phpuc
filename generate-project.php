<?php
require_once 'Log.php';
require_once 'Log/console.php';
require_once 'Package.php';
require_once 'GenerateApplication.php';

/**
 * A quick and dirty cruise control project maker for pear.
 *
 * @todo Check what's up with PHPUC's version and why it didn't work
 */



$name = isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : "Net_IPv6";
$source = isset($_SERVER['argv'][2]) ? $_SERVER['argv'][2] : dirname(__FILE__);
$cruisecontrol = isset($_SERVER['argv'][3]) ? $_SERVER['argv'][3] : dirname(__FILE__);
$pyrus = isset($_SERVER['argv'][4]) ? $_SERVER['argv'][4] : null;

$p = new Package($name, $source, $cruisecontrol);

$app = new GenerateApplication(new Log_console("Output"), new NormalUnitTests(), $pyrus);
$app->execute($p);
