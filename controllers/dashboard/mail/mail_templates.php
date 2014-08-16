<?php
/**
 * mail_scaffolds.php
 * (C) stefanfodor @ 2014
 * SF
 */

defined('C5_EXECUTE') or die("Access Denied.");

class DashboardMailMailTemplatesController extends DashboardBaseController {

    /**
     * @var $mailer_pkg Package
     */
    private $mailer_pkg = null;


    /**
     * On start is sooo much cooler than __construct
     */
    public function on_start() {

        parent::on_start();

        $this->mailer_pkg = Package::getByHandle('c5mailer');

        //for some reason, c5 does not define a constant for page types location
        //it's file dawg
        if( !defined('DIR_PAGE_TYPES') ){
            define( 'DIR_PAGE_TYPES', DIR_BASE . DIRECTORY_SEPARATOR . DIRNAME_PAGE_TYPES );
        }

    }

    /**
     * View task, get a list of installed and uninstalled scaffolds
     */
    public function view( $msg = null ) {

        /*
         * Existing templates
         */
        $templates = array();

        Loader::model('page_list');

        $pl = new PageList();
        $pl->includeSystemPages();
        $pl->filterByParentID( Page::getByPath('/mail-templates')->getCollectionID() );

        $raw = $pl->get();

        foreach( $raw as $thisRaw ) {
            /* @var $thisRaw Page */

            $templates[] = array(
                'id' => $thisRaw->getCollectionID(),
                'name' => $thisRaw->getCollectionName(),
                'scaffold' => $thisRaw->getCollectionTypeName(),
            );
        }

        $this->set('templates', $templates);

        /*
         * Existing scaffolds
         */
        $scaffolds = array();

        $re_ct = "/[a-zA-Z0-9_]+_mail_template/i";

        $installed_raw = CollectionType::getListByPackage( $this->mailer_pkg );

        foreach( $installed_raw as $thisScaffold ) {
            /* @var $thisScaffold CollectionType */

            //does not satisfy the naming convention
            if( !preg_match_all($re_ct, $thisScaffold->getCollectionTypeHandle()) ) {
                continue;
            }

            $tmp = array(
                'name' => $thisScaffold->getCollectionTypeName(),
                'handle' => $thisScaffold->getCollectionTypeHandle(),
            );


            // save the key in the value
            $scaffolds[] = $tmp;
        }

        $this->set('scaffolds', $scaffolds);
    }

} 