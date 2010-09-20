<?php
/**
 * PEAR_PackageFileManager_Cli
 *
 * PHP versions 4 and 5
 *
 * A command line interface to managing package files.
 *
 * @category   PEAR
 * @package    PEAR_PackageFileManager_Cli
 * @author     David Sanders <shangxiao@php.net>
 * @license    http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 * @version    Release: 0.3.0
 * @link       
 */

include_once 'PEAR/PackageFileManager2.php';

if (!class_exists('PEAR_PackageFileManager2')) {
    die("Missing PEAR_PackageFileManager. Please check your PEAR installation.\n");
}

PEAR::setErrorHandling(PEAR_ERROR_DIE);

define('WINDOWS', (substr(PHP_OS, 0, 3) == 'WIN'));
$default_package_path = '.';
$default_package_filename = 'package.xml';

// ref http://pear.php.net/group/docs/20040402-la.php
$licenses = array(
    'Apache'    => 'http://www.apache.org/licenses/',
    'BSD Style' => 'http://www.opensource.org/licenses/bsd-license.php',
    'LGPL'      => 'http://www.gnu.org/licenses/lgpl.html',
    'MIT'       => 'http://www.opensource.org/licenses/mit-license.html',
    'PHP'       => 'http://www.php.net/license/',
);

// these were taken out of PEAR/Task/Replace.php
$package_info_params = array(
    'name',
    'summary',
    'channel',
    'notes',
    'extends',
    'description',
    'release_notes',
    'license',
    'release-license',
    'license-uri',
    'version',
    'api-version',
    'state',
    'api-state',
    'release_date',
    'date',
    'time',
);

// are all these necessary?
$pear_config_params = array(
    'auto_discover',
    'default_channel',
    'http_proxy',
    'master_server',
    'preferred_mirror',
    'remote_config',
    'bin_dir',
    'doc_dir',
    'ext_dir',
    'php_dir',
    'cache_dir',
    'data_dir',
    'download',
    'php_bin',
    'php_ini',
    'temp_dir',
    'test_dir',
    'cache_ttl',
    'preferred_state',
    'umask',
    'verbose',
    'password',
    'sig_bin',
    'sig_keydir',
    'sig_keyid',
    'sig_type',
);

$tty = WINDOWS ? @fopen('\con', 'r') : @fopen('/dev/tty', 'r');

if (!$tty) {
    $tty = fopen('php://stdin', 'r');
}

function readTTY($trim = true)
{
    global $tty;
    $return = fgets($tty, 1024);
    return $trim ? trim($return) : $return;
}

function getList($array)
{
    if (empty($array)) {
        return $array;
    }

    foreach ($array as $key => $val) {
        break;
    }

    if ($key === 0) {
        return $array;
    } else {
        return array($array);
    }
}

// This'd be a nice addition to core php
function file_append_contents($filename, $contents)
{
    $fp = fopen($filename, 'a') or die('Error opening file for appending: ' . $filename . "\n");
    fwrite($fp, $contents) or die('Error writing to file: ' . $filename . "\n");
}

function write_package_comments($package_path, $cli_options)
{
    $comments = "<!-- PEAR_PackageFileManager_Cli options\n";
    $comments .= serialize($cli_options);
    $comments .= "\n-->";
    global $default_package_filename;
    file_append_contents($package_path . DIRECTORY_SEPARATOR . $default_package_filename, $comments);
}

function determineFileListGenerator($package_path)
{
    $dir = opendir($package_path);
    while (($file = readdir($dir)) !== false) {

        switch ($file) {

            case 'CVS':
            return 'cvs';

            case '.svn':
            return 'svn';

            default:
            break;
        }
    }

    return 'file';
}




//
// Refresh the contents with new files, updated md5sums
//
function generateContents(&$pfm)
{
    // refresh the contents
    $pfm->generateContents();

    // refresh the filelist
    $filelist = $pfm->getFilelist(true);

    // foreach script in the scripts dir, remove the baseinstalldir and as an install-as
    foreach ($filelist as $file_details) {
        $file = $file_details['attribs'];

        if ($file['role'] === 'script') {

            $installed_as = false;
            if (isset($pfm->_packageInfo['phprelease']['filelist']['install'])) {
                foreach (getList($pfm->_packageInfo['phprelease']['filelist']['install']) as $install) {
                    if ($install['attribs']['name'] === $file['name']) {
                        $installed_as = true;
                    }
                }
            }

            if (!$installed_as) {
                $path_parts = pathinfo($file['name']);
                $pfm->addInstallAs($file['name'], $path_parts['basename']);
            }

            if ($file['baseinstalldir'] !== '') {
                // clear the baseinstalldir
                $pfm->setFileAttribute($file['name'], 'baseinstalldir', '');
            }
        }
    }
}

function clean_html($match) {
    $type = (string)$match->td[2];
    $id = (string)$match->td[0]->a[0];
    $title = (string)$match->td[7];
    $fixer = (string)$match->td[8];

    return $type . " #" . $id . ' ' . $title . " - " . $fixer . "\n";
}


function fetch_changelog($package) {
    $data = file_get_contents("http://pear.php.net/bugs/search.php?cmd=display&package_name[]=" . $package . "&status=CRSLR");
    $tidy = tidy_parse_string($data);
    $tidy->cleanRepair();
    
    $dom = new DOMDocument();
    @$dom->loadHTML((string)$tidy);

    $sxe = simplexml_import_dom($dom);

    $parts = array();
    foreach ($sxe->xpath('//tr[@class="bug-result Csd"]') as $match) {
        $parts[] = clean_html($match);
    }

    return implode("\n", $parts);
}

//
// Start Here
//
extension_loaded('tidy') or die('Missing Tidy extension: Please check your PHP installation' . "\n");
extension_loaded('simplexml') or die('Missing SimpleXML extension: Please check your PHP installation' . "\n");

if (!isset($argv[1])) {
    die("php make-package.php path/to/trunk \n");
}

$package_path = $argv[1];


$package_file = $package_path . DIRECTORY_SEPARATOR . $default_package_filename;

if (file_exists($package_file)) {
    //
    // Use the existing package file
    //

    if (!is_writable($package_file)) {
        die("Unable to write package file\n");
    }



    $sxe = simplexml_load_file($package_file);


    $elements = $sxe->xpath("//*[@baseinstalldir]/@baseinstalldir");

    $baseinstalldir = "/";
    if (!empty($elements)) {
        // Always accurate? Probably not; but most of the time, yes.
        $baseinstalldir = $elements[0];
    }
    

    $filelist_generator = determineFileListGenerator($package_path);

    //
    // - Don't clearcontents - this will erase existing file settings like tasks, roles, baseinstalldir, etc
    // - Don't setPackageType() - this will erase the settings in <phprelease>
    // - Do regenerate the contents as this will refresh the md5sums
    // - Do regenerate the filelist from the refreshed contents
    //

    $pfm = &PEAR_PackageFileManager2::importOptions($package_file, array(
        'filelistgenerator' => $filelist_generator,
        'packagedirectory'  => $package_path,
        'baseinstalldir'    => $baseinstalldir,
        'clearcontents'     => false,
        // add scripts dir to the default list
        'dir_roles'         => array(
                                 'docs'     => 'doc',
                                 'examples' => 'doc',
                                 'tests'    => 'test',
                                 'scripts'  => 'script',
                               ),
        ));

    // refresh the contents and filelist
    generateContents($pfm);
} else {
    die("Could not locate package:\n" . $package_file . "\n");
}


$attr = $pfm->getArray();
//print_r($attr);

// Todo: automark as unmaintained?
//        editMaintainers($pfm);

$release_version = $attr['version']['release'];
$parts = explode(".", $release_version);

$minor_version = array_pop($parts);
if (is_numeric($minor_version)) {
    $minor_version++; // Let's hope it's numeric; not 1.0.2beta3
    $parts[] = $minor_version;
}

$pfm->setReleaseVersion(implode(".", $parts));
$pfm->setNotes("Automatically built QA release\n" . fetch_changelog($attr['name']));

//$pfm->debugPackageFile();
$pfm->writePackageFile();
