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
     * @var int file ID of the demo top logo
     */
    private $top_logo_fid = 0;

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
        $this->installSinglePages( $pkg );

        //install theme
        $this->installThemes( $pkg );

        //install page types
        $this->installPageTypes( $pkg );

        //install assets
        $this->installAssets( $pkg );

        //install pages
        $this->installPages( $pkg );

        //set default configuration
        $this->installConfig( $pkg );
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
        $dashboardIcons = array();

        //SMTP settings
        $path = '/dashboard/mail/smtp_settings';
        $pkg = Package::getByHandle('c5mailer');
        $p = SinglePage::add($path, $pkg);
        if (is_object($p) && $p->isError() !== false) {
            $p->update(array('cName' => t('SMTP Configuration')));
            $dashboardIcons[$path] = 'icon-wrench';
        }

        //SMTP test settings
        $path = '/dashboard/mail/smtp_settings/test_settings/';
        $pkg = Package::getByHandle('c5mailer');
        $p = SinglePage::add($path, $pkg);
        if (is_object($p) && $p->isError() !== false) {
            $p->update(array('cName' => t('SMTP Test Settings')));
            $dashboardIcons[$path] = 'icon-wrench';
        }

        //install the email options page
        $path = '/dashboard/mail/options';
        $pkg = Package::getByHandle('c5mailer');
        $p = SinglePage::add($path, $pkg);
        if (is_object($p) && $p->isError() !== false) {
            $p->update(array('cName' => t('Email Options')));
            $dashboardIcons[$path] = 'icon-cog';
        }

        //install the email options page
        $path = '/dashboard/mail/mail_templates';
        $pkg = Package::getByHandle('c5mailer');
        $p = SinglePage::add($path, $pkg);
        if (is_object($p) && $p->isError() !== false) {
            $p->update(array('cName' => t('Email Templates')));
            $dashboardIcons[$path] = 'icon-envelope';
        }

        //install the email options page
        $path = '/dashboard/mail/mail_scaffolds';
        $pkg = Package::getByHandle('c5mailer');
        $p = SinglePage::add($path, $pkg);
        if (is_object($p) && $p->isError() !== false) {
            $p->update(array('cName' => t('Email Scaffolds')));
            $dashboardIcons[$path] = 'icon-file';
        }

        //setup the icons set for custom dashboard single pages
        $this->setupDashboardIcons($dashboardIcons);
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

        $ctIDs = array(
            $container->getCollectionTypeID(),
            $basic->getCollectionTypeID(),
            $hero->getCollectionTypeID()
        );

        foreach( $ctIDs as $thisCTID ) {
            $db->execute(
                $query,
                array( $thisCTID )
            );
        }

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

        $mail_template_container->moveToRoot();

        /*
         * Demo Content
         */
        $basic = CollectionType::getByHandle("basic_mail_template");
        //create
        $data = array(
            'cHandle' => "demo-basic-template",
            'cName' => "Demo Basic Template",
            'pkgID' => $pkg->getPackageID(),
        );
        $demo_template = $mail_template_container->add($basic, $data);


        /*
         * Demo Content add blocks
         */
        $bt_content = BlockType::getByHandle('content');
        $bt_html = BlockType::getByHandle('html');
        $bt_image = BlockType::getByHandle('image');

        $main_data = array(
            'content' => '<h3>Hi, %username%</h3><p class="lead">Phasellus %another_var% dictum sapien a neque luctus cursus. Pellentesque sem dolor, fringilla et pharetra vitae.</p><p>Phasellus dictum sapien a neque luctus cursus. Pellentesque sem dolor, fringilla et pharetra vitae. consequat vel lacus. Sed iaculis pulvinar ligula, ornare fringilla ante viverra et. In hac habitasse platea dictumst. Donec vel orci mi, eu congue justo. Integer eget odio est, eget malesuada lorem. Aenean sed tellus dui, vitae viverra risus. Nullam massa sapien, pulvinar eleifend fringilla id, convallis eget nisi. Mauris a sagittis dui. Pellentesque non lacinia mi. Fusce sit amet libero sit amet erat venenatis sollicitudin vitae vel eros. Cras nunc sapien, interdum sit amet porttitor ut, congue quis urna.</p><p class="callout">Phasellus dictum sapien a neque luctus cursus. Pellentesque sem dolor, fringilla et pharetra vitae. <a href="#">Click it! »</a></p>'
        );

        $top_right_data = array(
            'content' => 'Acme Aps'
        );

        $top_left_data = array(
            'fID' => $this->top_logo_fid
        );

        $demo_template->addBlock($bt_content, 'Main', $main_data);
        $demo_template->addBlock($bt_html, 'Top Right Name', $top_right_data);
        $demo_template->addBlock($bt_image, 'Top Left Image', $top_left_data);

        /*
         * Move templates to system pages
         */
        $db =  Loader::db();
        $query = 'update Pages set cIsSystemPage = 1 where cID = ?';

        $db->execute($query, array($mail_template_container->getCollectionID()));
        $db->execute($query, array($demo_template->getCollectionID()));

    }

    /**
     * Installs files and assets
     */
    private function installAssets( $pkg ) {

        //There is an issue in C5 that throws an error when saving a file to set
        //Fileset for email images
        //$fs = FileSet::createAndGetSet('Email Images', FileSet::TYPE_PUBLIC);

        //insert acme logo to file manager
        $top_logo_path = $pkg->getPackagePath() . '/assets/acme_logo.png';

        Loader::library("file/importer");
        $fi = new FileImporter();

        $top_logo = $fi->import($top_logo_path, 'acme_logo.png');

        $this->top_logo_fid = $top_logo->getFileID();

        //add top logo to fileset
        //$fs->addFileToSet( $top_logo );
    }

    /**
     * Sets up default config
     * @param $pkg
     */
    public function installConfig( $pkg ) {

        $co = new Config();
        $co->setPackageObject($pkg);

        $co->save('sender_name', 'Johnny Bravo' );
        $co->save('sender_address', 'johnny@bravo.com' );

        $co->save('contact_phone', '813.298.123' );
        $co->save('contact_email', 'hello@yahoo.com' );

        $co->save('social_facebook', 'https://facebook.com/' );
        $co->save('social_twitter', 'https://twitter.com/' );
        $co->save('social_gplus', 'http://weknowmemes.com/wp-content/uploads/2011/10/meanwhile-on-google-plus.jpg' );

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