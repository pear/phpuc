<?php
if (empty($_SERVER['argv'][1])) {
    die("php changes.php (packagename)");
}
$package = $_SERVER['argv'][1];

$data = file_get_contents("http://pear.php.net/bugs/search.php?cmd=display&package_name[]=" . $package . "&status=CRSLR");
$tidy = tidy_parse_string($data);
$tidy->cleanRepair();

$dom = new DOMDocument();
@$dom->loadHTML((string)$tidy);

$sxe = simplexml_import_dom($dom);

function clean_html($match) {
    $type = (string)$match->td[2];
    $id = (string)$match->td[0]->a[0];
    $title = (string)$match->td[7];
    $fixer = (string)$match->td[8];

    return $type . " #" . $id . ' ' . $title . " - " . $fixer . "\n";
}

foreach ($sxe->xpath('//tr[@class="bug-result Csd"]') as $match) {
    print clean_html($match);
}
