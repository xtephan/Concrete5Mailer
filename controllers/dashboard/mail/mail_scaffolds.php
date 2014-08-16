<?php
/**
 * mail_scaffolds.php
 * (C) stefanfodor @ 2014
 * SF
 */

defined('C5_EXECUTE') or die("Access Denied.");

class DashboardMailMailScaffoldsController extends DashboardBaseController {

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

        //scaffold regex
        $re_filename = "/[a-zA-Z0-9_]+_mail_template\\.php/i";
        $re_ct = "/[a-zA-Z0-9_]+_mail_template/i";
        $suffix_size = strlen('_mail_template.php');

        if( $msg == "install_ok" ) {
            $this->set("message", t('Email scaffold installed successfully!'));
        }

        if( $msg == "remove_ok" ) {
            $this->set("message", t('Email scaffold removed successfully!'));
        }

        if( $msg == "remove_fail" ) {
            $this->set("message", t('Fail to remove email scaffold! Please make sure no page is using it and clean up the trash and the page versions.'));
        }

        //error msg
        if( $msg == "token_error" ) {
            $this->error = t('Invalid security token!');
        }

        /*
         * Installed
         */
        $installed_scaffolds = array();

        //get a list of all the installed packages
        $installed_raw = CollectionType::getListByPackage( $this->mailer_pkg );

        //and process it
        foreach( $installed_raw as $thisScaffold ) {
            /* @var $thisScaffold CollectionType */

            //does not satisfy the naming convention
            if( !preg_match_all($re_ct, $thisScaffold->getCollectionTypeHandle()) ) {
                continue;
            }

            $tmp = array(
                'name' => $thisScaffold->getCollectionTypeName(),
                'used_on' => $this->getScaffoldUsage( $thisScaffold->getCollectionTypeHandle() ),
            );


            // save the key in the value
            $installed_scaffolds[ $thisScaffold->getCollectionTypeHandle() ] = $tmp;
        }

        $this->set('installed_scaffolds', $installed_scaffolds);


        /*
         * Awaiting install
         */
        $awaiting_install = array();

        //get a list of files in page type directory
        $available_page_types = array_diff(scandir(DIR_PAGE_TYPES), array('..', '.'));

        $txt_helper = Loader::helper('text');
        /* @var $txt_helper TextHelper */

        foreach( $available_page_types as $thisAvailablePageType ) {

            //does not satisfy the naming convention
            if( !preg_match_all($re_filename, $thisAvailablePageType) ) {
                continue;
            }

            //get the scaffold key key
            $scaffold_key = substr( $thisAvailablePageType, 0, 0-$suffix_size );

            //is it already installed?
            if( $installed_scaffolds[$scaffold_key.'_mail_template'] ) {
                continue;
            }

            //finally, add it to array
            $awaiting_install[] = array(
                'handle' => $scaffold_key,
                'name' =>  $txt_helper->unhandle( $scaffold_key ),
                'filename' => $thisAvailablePageType
            );
        }

        $this->set('awaiting_install', $awaiting_install);

    }

    /**
     * Removes a scaffold
     * @param $scaffold_key
     * @param $token
     */
    public function remove_scaffold( $scaffold_key, $token ) {

        if( $this->token->validate( 'scaffold_edit', $token ) ) {

            //remove
            $ct = CollectionType::getByHandle( $scaffold_key );

            $ct->delete();

            $this->redirect( "/dashboard/mail/mail_scaffolds/remove_ok" );

        } else {
            $this->redirect( "/dashboard/mail/mail_scaffolds/token_error" );
        }
    }

    /**
     * Installs a scaffold
     * @param $scaffold_key
     * @param $token
     */
    public function install_scaffold( $scaffold_key, $token ) {

        if( $this->token->validate( 'scaffold_edit', $token ) ) {

            $txt_helper = Loader::helper('text');
            /* @var $txt_helper TextHelper */

            //install
            $new_ct = CollectionType::add(
                array(
                    'ctHandle' => $scaffold_key . '_mail_template',
                    'ctName' => $txt_helper->unhandle( $scaffold_key . '_mail_template' )
                )
                , $this->mailer_pkg
            );

            //move to internals
            Loader::db()->execute(
                'UPDATE PageTypes SET ctIsInternal = 1 WHERE ctID = ?',
                array( $new_ct->getCollectionTypeID() )
            );

            $this->redirect( "/dashboard/mail/mail_scaffolds/install_ok" );

        } else {
            $this->redirect( "/dashboard/mail/mail_scaffolds/token_error" );
        }
    }


    /**
     * Finds all usages of a scaffold
     * @param $ct_handle
     * @return array
     */
    private function getScaffoldUsage( $ct_handle ) {

        Loader::model('page_list');

        $pl = new PageList();
        $pl->includeSystemPages();
        $pl->filterByCollectionTypeHandle( $ct_handle );

        $pages = $pl->get();

        $result = array();
        foreach( $pages as $thisPage ) {
            /* @var $thisPage Page */

            $result[] = array(
                'id'  =>  $thisPage->getCollectionID(),
                'name'  =>  $thisPage->getCollectionName(),
            );

        }

        return $result;
    }


} 