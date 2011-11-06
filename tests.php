<?php

/**
 * Runs the phpt and PHPUnit tests for all PEAR packages in the
 * svn.php.net repository
 *
 * NOTE:  the source code of this script is managed in a GitHub repository:
 * https://github.com/pear/phpuc/
 *
 * @author Daniel O'Connor <clockwerx@php.net>
 * @author Daniel Convissor <danielc@php.net>
 */

/**
 *
 */
function collect_package_directories(RecursiveDirectoryIterator $dir) {
    $dirs = array();
    foreach ($dir as $file) {
        if (is_dir($file)) {
            $trunk_dir = $file . "/trunk";
            if (file_exists($trunk_dir)) {
                $dirs[] = $trunk_dir;
            }
        }
    }
    return $dirs;
}

function collect_package_all_directories(RecursiveDirectoryIterator $dir) {
    $dirs = array();
    foreach ($dir as $file) {
        if (is_dir($file)) {
            $trunk_dir = $file;
            if (file_exists($trunk_dir)) {
                $dirs[] = $trunk_dir;
            }
        }
    }
    return $dirs;
}

function run_phpunit_tests($path, $output_path) {
    $test_dir = $path . '/tests/AllTests.php';

    if (file_exists($test_dir)) {
        $cmd = 'cd ' . $path;
        $cmd .= ' && echo "php '. $test_dir . '" >> ' . $output_path;
        $cmd .= ' && php ' . $test_dir . ' >> ' . $output_path;
        exec($cmd);
    }
}

function run_pear_tests($path, $output_path) {
    $test_dir = $path . '/tests/';

    if (file_exists($test_dir)) {
        $cmd = 'cd ' . $path;
        $cmd .= ' && echo "pear run-tests -r ' . $test_dir . '" >> ' . $output_path;
        $cmd .= ' && pear run-tests -r ' . $test_dir . ' >> ' .  $output_path;
        exec($cmd);
    }
}


$output_dir = __DIR__ . '/packages-all/unit-test-results/';
if (!file_exists($output_dir)) {
    if (!mkdir($output_dir, 0777)) {
        die("Failed to create " . $output_dir);
    }
}

$file_phpunit = $output_dir . 'phpunit_results.txt';
$file_phpt = $output_dir . 'phpt_results.txt';
$time = date(DATE_ATOM);


$packages_all_dir = __DIR__ . '/packages-all';
if (is_dir($packages_all_dir)) {
    $dir = new RecursiveDirectoryIterator($packages_all_dir);
    $packages = collect_package_all_directories($dir);
} else {
    $dir = new RecursiveDirectoryIterator(__DIR__);
    $packages = collect_package_directories($dir);
}


if (file_exists($file_phpunit)) {
    unlink($file_phpunit);
}
file_put_contents($file_phpunit, "PEAR PHPUnit tests for $time\n\n");
$phpunit_tests = array();
$start = time();
foreach ($packages as $package) {
    $phpunit_tests[] = run_phpunit_tests($package, $file_phpunit);
}
$end = time();
$minutes = ($end - $start) / 60;
file_put_contents($file_phpunit, "\n\nFINISHED!  It took $minutes minutes.", FILE_APPEND);


if (file_exists($file_phpt)) {
    unlink($file_phpt);
}
file_put_contents($file_phpt, "PEAR phpt tests for $time\n\n");
$pear_tests = array();
$start = time();
foreach ($packages as $package) {
    $pear_tests[] = run_pear_tests($package, $file_phpt);
}
$end = time();
$minutes = ($end - $start) / 60;
file_put_contents($file_phpt, "\n\nFINISHED!  It took $minutes minutes.", FILE_APPEND);
