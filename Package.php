<?php
class Package {
    public $package;
    public $source;
    public $jenkins;
    public $pyrus;

    /**
     * @param string $package Package Name (ie: IPv6)
     * @param string $source Path to packages-all checkout (http://svn.php.net/repository/pear/packages-all)
     * @param string $jenkins Path to jenkins installation (/var/lib/jenkins)
     */
    public function __construct($package, $source, $jenkins, $pyrus) {
        $this->package = $package;
        $this->source = $source;
        $this->jenkins = $jenkins;
        $this->pyrus = $pyrus;
    }
}
