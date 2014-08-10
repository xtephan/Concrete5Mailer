<?php
/**
 * controller.php
 * (C) stefanfodor @ 2014
 * SF
 */

/**
 * Class C5mailerPackage
 * Concrete5 package for mailer
 * @author Stefan Fodor
 * @package c5mailer
 */
class C5mailerPackage extends Package {

    /**
     * @var string package handle used in C5 guts
     */
    protected $pkgHandle = 'c5mailer';

    /**
     * @var string min required C5 version
     */
    protected $appVersionRequired = '5.6.1.2';

    /**
     * @var string package version
     */
    protected $pkgVersion = '1.0';

    /**
     * Package description
     * @return string
     */
    public function getPackageDescription() {
        return t("Cool mailing functionality for C5");
    }

    /**
     * Package name
     * @return string
     */
    public function getPackageName() {
        return t("C5 Mailer");
    }

    /**
     * Package handle
     * @return string
     */
    public function getPackageHandle(){
        return 'c5mailer';
    }

    /**
     * On start
     */
    public function on_start() {

        //workaround to render all mail templates with the right theme
        $v = View::getInstance();
        $v->setThemeByPath('/mail-templates/*', 'mailer');

    }

    /**
     * Upgrade
     */
    public function upgrade() {
        /*	Nothing here yet as this is version 1

            parent::upgrade();
            $pkg= Package::getByHandle($this->pkgHandle);
        */
    }

    /**
     * Install the package
     * @return Package|void
     */
    public function install() {

        $pkg = parent::install();

        //install single pages
        //$this->installSinglePages( $pkg );

        //install theme
        $this->installThemes( $pkg );

        //install page types
        $this->installPageTypes( $pkg );

        //install pages
        $this->installPages( $pkg );
    }

    /**
     * Make sure we clean up DB and cache file
     */
    public function uninstall() {

        //remove pages
        $this->uninstallPages();

        parent::uninstall();
    }

    /**
     * Install single pages
     * @param $pkg
     */
    public function installSinglePages( $pkg ){

        //this array will hold all the custom dashboard page paths and their icons.
        //see the setupDashboardIcons method for more info
        /*$dashboardIcons = array();

        $path = '/dashboard/vimeo_website/share';
        $p = SinglePage::add($path, $pkg);
        if (is_object($p) && $p->isError() !== false) {
            $p->update(array('cName' => t('Vimeo Share')));
        }

        // Set the icon for the /dashboard/vimeo_website/share page
        $dashboardIcons[$path] = 'icon-share';

        //setup the icons set for custom dashboard single pages
        $this->setupDashboardIcons($dashboardIcons);*/
    }

    /**
     * Installs page types required
     * @param $pkg
     */
    public function installPageTypes( $pkg ) {

        //type for container
        $container = CollectionType::add(
            array(
                'ctHandle' => 'mail_template_container',
                'ctName' => t('Mail Template Container')
            )
            , $pkg
        );

        //basic mail type
        $basic = CollectionType::add(
            array(
                'ctHandle' => 'basic_mail_template',
                'ctName' => t('Basic Mail Template')
            )
            , $pkg
        );

        //hero image type
        $hero = CollectionType::add(
            array(
                'ctHandle' => 'hero_mail_template',
                'ctName' => t('Hero Image Mail Template')
            )
            , $pkg
        );

        //all fine, now mark this as system
        $db = Loader::db();

        $query = 'UPDATE PageTypes SET ctIsInternal = 1 WHERE ctID = ?';

        $db->execute(
            $query,
            array( $container->getCollectionTypeID() )
        );

        $db->execute(
            $query,
            array( $basic->getCollectionTypeID() )
        );

        $db->execute(
            $query,
            array( $hero->getCollectionTypeID() )
        );

    }


    /**
     * Install pages
     * @param $pkg
     */
    public function installPages( $pkg ) {

        $container = CollectionType::getByHandle("mail_template_container");

        /*
         * Container
         */
        $home = Page::getByID(HOME_CID);
        $data = array(
            'cHandle' => "mail-templates",
            'cName' => "Mail templates",
            'pkgID' => $pkg->getPackageID(),
        );
        $mail_template_container = $home->add($container, $data);

        //exclude from pagelist and nav
        $mail_template_container->setAttribute('exclude_page_list', 1);
        $mail_template_container->setAttribute('exclude_nav', 1);

        //transform the container in a system page
        Loader::db()->execute('update Pages set cParentID = 0 AND cIsSystemPage = 1 where cID = ?', array($mail_template_container->getCollectionID()));

        /*
         * Demo Content
         */
        $basic = CollectionType::getByHandle("basic_mail_template");
        //cretae
        $data = array(
            'cHandle' => "demo-basic-template",
            'cName' => "Demo Basic Template",
            'pkgID' => $pkg->getPackageID(),
        );
        $demo_template = $mail_template_container->add($basic, $data);

        //do not list
        $demo_template->setAttribute('exclude_page_list', 1);
        $demo_template->setAttribute('exclude_nav', 1);

        //add demo content
        $bt = BlockType::getByHandle('content');
        $data = array(
            'content' => 'Hi there, %username%!<br/><br/>This is an <strong>HTML</strong> email.<br/>Have a nice day!'
        );

        $data_txt = array(
            'content' => 'Hi there, %username%!\n\nThis is an plain-text email. Have a nice day!'
        );

        $demo_template->addBlock($bt, 'Main', $data);
        $demo_template->addBlock($bt, 'MainTxt', $data_txt);

    }

    /**
     * Installs themes required
     * @param $pkg
     */
    private function installThemes($pkg) {
        PageTheme::add('mailer', $pkg);
    }


    /**
     * Removes pages
     */
    public function uninstallPages() {

        //mail templates
        $mail_templates = Page::getByPath('/mail-templates');
        $mail_templates->delete();

    }


    /**
     * Takes an associative array of pages to set icons for. This is only for dashboard single pages
     * @param $iconArray
     */
    private function setupDashboardIcons($iconArray) {
        $cak = CollectionAttributeKey::getByHandle('icon_dashboard');
        if (is_object($cak)) {
            foreach($iconArray as $path => $icon) {
                $sp = Page::getByPath($path);
                if (is_object($sp) && (!$sp->isError())) {
                    $sp->setAttribute('icon_dashboard', $icon);
                }
            }
        }
    }

}