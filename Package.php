<?php
class Package {
    public $package;
    public $source;
    public $cruisecontrol;

    /**
     * @param string $package Package Name (ie: IPv6)
     * @param string $source Path to packages-all checkout (http://svn.php.net/repository/pear/packages-all)
     * @param string $cruisecontrol Path to cruise control installation (~/cruisecontrol)
     */
    public function __construct($package, $source, $cruisecontrol) {
        $this->package = $package;
        $this->source = $source;
        $this->cruisecontrol = $cruisecontrol;
    }
}