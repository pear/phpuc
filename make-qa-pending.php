<?php
extension_loaded('tidy') or die('Missing Tidy extension: Please check your PHP installation' . "\n");
extension_loaded('simplexml') or die('Missing SimpleXML extension: Please check your PHP installation' . "\n");

function fetch_packages() {
    $data = file_get_contents('http://pear.php.net/qa/packages_closed_reports_no_release.php');
    $tidy = tidy_parse_string($data);
    $tidy->cleanRepair();
    
    $dom = new DOMDocument();
    @$dom->loadHTML((string)$tidy);

    $sxe = simplexml_import_dom($dom);

    $parts = array();

    list($table) = $sxe->xpath('//table');
    foreach ($table->tr as $tr) {
        $match = trim((string)$tr->td->a);
        if (empty($match)) { continue; }
        if (is_numeric($match)) { continue; }
        $parts[] = $match;
    }

    return $parts;
}
$dir = $_SERVER['argv'][1];

function execute($cmd) {
   print $cmd . "\n";
   return exec($cmd) . "\n";
}

$packages = fetch_packages();
rsort($packages);
foreach ($packages as $package) {
    $path = $dir . "/" . $package;
    print $package . "\n";
    execute("svn up " . $path) . "\n";
    execute("svn revert " . $path . "/trunk") . "\n";

    $cmd = "php " . dirname(__FILE__) . "/make-package.php " . $package  . "/trunk";
    print $cmd . "\n";
    $out = exec($cmd);
    print $out . "\n";
    if ($out == "Error: package.xml <package> tag has no version attribute, or version is not 2.0") {
        execute('pear convert ' . $path . "/trunk");
        execute('mv ' . $path . "/trunk/package2.xml ". $path . "/trunk/package.xml");
        execute($cmd);
    }
    print execute("pear package " . $package  . "/trunk/package.xml") . "\n";

}
