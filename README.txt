A collection of tools to help with pear's svn, github, etc.

For setup - see INSTALL.txt

General usage:

# Get all pear packages on github
php checkout-github.php pear
php checkout-github.php pear2

# Generate a config.xml for jenkins & install it
php generate-project.php PackageName /path/to/source /path/to/jenkins [/path/to/pyrus]

# Generate a whole lot of configs
php generate-all.php /path/to/packages-all /path/to/jenkins  [/path/to/pyrus]
php generate-all.php /path/to/github-pear-all /path/to/jenkins  [/path/to/pyrus]
php generate-all.php /path/to/github-pear2-all /path/to/jenkins  [/path/to/pyrus]
