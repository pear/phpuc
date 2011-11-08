<?php
/**
 * A quick script clone all github packages.
 */
if (count($_SERVER['argv']) < 2) {
    die("Usage: php checkout-github.php organisation [--debug]\n\nCreates a 'github-[organisation]-all' directory with all public repositories.\n\nSpecify --debug to see the generated commands without executing\n");
}

$organisation = $_SERVER['argv'][1];
$debug = isset($_SERVER['argv'][2]) && $_SERVER['argv'][2] == '--debug';

$dir_name = "github-" . $organisation . "-all/";
$package_names = array();
$result = json_decode(file_get_contents('https://github.com/api/v2/json/organizations/' . $organisation . '/public_repositories'));
foreach ($result as $data) {
    foreach ($data as $item) {
        $package_names[] = $item->name;
        $path = $dir_name . $item->name;

        if (file_exists($path)) {
            echo $item->name . ": PULLING\n";
            $cmd = "cd " . $path . " && git pull && cd " . getcwd() . "\n";        
        } else  {
            echo $item->name . ": CLONING\n";
            $cmd = "git clone git://github.com/" . $organisation . "/" . $item->name . ".git " . $path . "\n";
        }

        if ($debug) {
            print $cmd;
        } else {
            exec($cmd);
        }
    }
}

$dir = new DirectoryIterator($dir_name);
foreach ($dir as $file) {
    if ($file->isDir() && !$file->isDot()
        && !in_array($file->getFilename(), $package_names))
    {
        // Use OS commands because rmdir() only works on empty dirs.
        echo $file->getFilename() . ": DELETING\n";
        $cmd = 'rm -rf ' . $dir_name . $file->getFilename();
        if ($debug) {
            print "$cmd\n";
        } else {
            exec($cmd);
        }
    }
}
