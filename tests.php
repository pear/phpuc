<?php

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


$output_dir = dirname(__FILE__) . '/unit-test-results/';
if (!file_exists($output_dir)) {
    if (!mkdir($output_dir, 0777)) {
        die("Failed to create " . $output_dir);
    }
}

$dir = new RecursiveDirectoryIterator(dirname(__FILE__));
if (basename(dirname(__FILE__)) == 'packages-all') {
    $packages = collect_package_all_directories($dir);
} else {
    $packages = collect_package_directories($dir);
}

if (file_exists($output_dir . 'phpunit_results.txt')) {
    unlink($output_dir . 'phpunit_results.txt');

    file_put_contents($output_dir . 'phpunit_results.txt', "Unit tests for " . date(DATE_ATOM) . "\n\n");
}
if (file_exists($output_dir . 'phpt_results.txt')) {
    unlink($output_dir . 'phpt_results.txt');

    file_put_contents($output_dir . 'phpt_results.txt', "Unit tests for " . date(DATE_ATOM) . "\n\n");

}

$phpunit_tests = array();
foreach ($packages as $package) {
    $phpunit_tests[] = run_phpunit_tests($package, $output_dir . 'phpunit_results.txt');
}

$pear_tests = array();
foreach ($packages as $package) {
    $pear_tests[] = run_pear_tests($package, $output_dir . 'phpt_results.txt');
}

