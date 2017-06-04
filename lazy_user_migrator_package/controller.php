<?php
/**
 * Lazy User Migrator Package Controller File.
 *
 * @author   Joshua Koudys, Christopher Ottley <cottley@gmail.com>
 * @license  See attached license file
 */
namespace Concrete\Package\LazyUserMigratorPackage;

use Concrete\Core\Block\BlockType\BlockType;
use Core;
use Concrete\Core\Foundation\Service\ProviderList;
use Concrete\Core\Package\Package;
use Illuminate\Filesystem\Filesystem;
use Concrete\Core\Page\Single as SinglePage;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * Package Controller Class.
 *
 * Quick and simple way to export your concrete5 users from one site and import them into another.
 *
 * @author   Joshua Koudys, Christopher Ottley <cottley@gmail.com>
 * @license  See attached license file
 */
class Controller extends Package
{
    /**
     * Minimum version of concrete5 required to use this package.
     * 
     * @var string
     */
    protected $appVersionRequired = '5.7.5';

    /**
     * Does the package provide a full content swap?
     * This feature is often used in theme packages to install 'sample' content on the site.
     *
     * @see https://goo.gl/C4m6BG
     * @var bool
     */
    protected $pkgAllowsFullContentSwap = false;

    /**
     * Does the package provide thumbnails of the files 
     * imported via the full content swap above?
     *
     * @see https://goo.gl/C4m6BG
     * @var bool
     */
    protected $pkgContentProvidesFileThumbnails = false;

    /**
     * Should we remove 'Src' from classes that are contained 
     * ithin the packages 'src/Concrete' directory automatically?
     *
     * '\Concrete\Package\MyPackage\Src\MyNamespace' becomes '\Concrete\Package\MyPackage\MyNamespace'
     *
     * @see https://goo.gl/4wyRtH
     * @var bool
     */
    protected $pkgAutoloaderMapCoreExtensions = false;

    /**
     * Package class autoloader registrations
     * The package install helper class, included with this boilerplate, 
     * is activated by default.
     *
     * @see https://goo.gl/4wyRtH
     * @var array
     */
    protected $pkgAutoloaderRegistries = [
        //'src/MyVendor/Statistics' => '\MyVendor\ConcreteStatistics'
    ];

    /**
     * The packages handle.
     * Note that this must be unique in the 
     * entire concrete5 package ecosystem.
     * 
     * @var string
     */
    protected $pkgHandle = 'lazy_user_migrator_package';

    /**
     * The packages version.
     * 
     * @var string
     */
    protected $pkgVersion = '0.9.0';

    /**
     * The packages name.
     * 
     * @var string
     */
    protected $pkgName = 'Lazy User Migrator Package';

    /**
     * The packages description.
     * 
     * @var string
     */
    protected $pkgDescription = 'Quick and simple way to export your concrete5 users from one site and import them into another.';

    /**
     * The packages on start hook that is fired as the CMS is booting up.
     * 
     * @return void
     */
    public function on_start()
    {
        // Add custom logic here that needs to be executed during CMS boot, things
        // such as registering services, assets, etc.
    }

    /**
     * The packages install routine.
     * 
     * @return \Concrete\Core\Package\Package
     */
    public function install()
    {
        // Add your custom logic here that needs to be executed BEFORE package install.

        $pkg = parent::install();

        //$theme = BlockType::installBlockType('lazy_user_migrator', $pkg);
		SinglePage::add('/dashboard/system/permissions/user_import', $pkg);
		SinglePage::add('/dashboard/system/permissions/user_export', $pkg);

        // Add your custom logic here that needs to be executed AFTER package install.

        return $pkg;
    }

    /**
     * The packages upgrade routine.
     * 
     * @return void
     */
    public function upgrade()
    {
        // Add your custom logic here that needs to be executed BEFORE package install.

        parent::upgrade();

        // Add your custom logic here that needs to be executed AFTER package upgrade.
    }

    /**
     * The packages uninstall routine.
     * 
     * @return void
     */
    public function uninstall()
    {
        // Add your custom logic here that needs to be executed BEFORE package uninstall.

        parent::uninstall();

        // Add your custom logic here that needs to be executed AFTER package uninstall.
    }
}
