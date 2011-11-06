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

$file_phpunit = $output_dir . 'phpunit_results.txt';
$file_phpt = $output_dir . 'phpt_results.txt';
$time = date(DATE_ATOM);


$dir = new RecursiveDirectoryIterator(dirname(__FILE__));
if (basename(dirname(__FILE__)) == 'packages-all') {
    $packages = collect_package_all_directories($dir);
} else {
    $packages = collect_package_directories($dir);
}


if (file_exists($file_phpunit)) {
    unlink($file_phpunit);
}
file_put_contents($file_phpunit, "PEAR PHPUnit tests for $time\n\n");
$phpunit_tests = array();
foreach ($packages as $package) {
    $phpunit_tests[] = run_phpunit_tests($package, $file_phpunit);
}


if (file_exists($file_phpt)) {
    unlink($file_phpt);
}
file_put_contents($file_phpt, "PEAR phpt tests for $time\n\n");
$pear_tests = array();
foreach ($packages as $package) {
    $pear_tests[] = run_pear_tests($package, $file_phpt);
}

