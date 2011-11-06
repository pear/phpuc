<?php
/**
 * A quick script clone all github packages.
 */
if (count($_SERVER['argv']) < 2) {
    die("Usage: php checkout-github.php organisation [--debug]\n\nCreates a 'github-[organisation]-all' directory with all public repositories.\n\nSpecify --debug to see the generated commands without executing\n");
}

$organisation = $_SERVER['argv'][1];
$debug = isset($_SERVER['argv'][2]) && $_SERVER['argv'][2] == '--debug';

$result = json_decode(file_get_contents('https://github.com/api/v2/json/organizations/' . $organisation . '/public_repositories'));
foreach ($result as $data) {
    foreach ($data as $item) {
        $cmd = "git clone git://github.com/" . $organisation . "/" . $item->name . ".git github-" . $organisation . "-all/" . $item->name . "\n";

        if ($debug) {
            print $cmd;
        } else {
            exec($cmd);
        }
    }
}
