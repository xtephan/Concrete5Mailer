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

        if( $msg == "add_ok" ) {
            $this->set("message", t('Email template added successfully!'));
        }

        if( $msg == "remove_ok" ) {
            $this->set("message", t('Email template removed successfully!'));
        }

        if( $msg == "test_ok" ) {
            $this->set("message", t('Test email successfully sent!'));
        }

        //error msg
        if( $msg == "token_error" ) {
            $this->error = t('Invalid security token!');
        }

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


    /**
     * add a template
     */
    public function add_template() {

        if( $this->token->validate( 'template_edit', $this->post('token') ) ) {

            //add
            $ct = CollectionType::getByHandle( $this->post('scaffold') );
            $mail_template_container = Page::getByPath('/mail-templates');

            //create
            $data = array(
                'cHandle' => Loader::helper('text')->handle( $this->post('template_name') ),
                'cName' => $this->post('template_name'),
                'pkgID' => $this->mailer_pkg->getPackageID(),
            );
            $new_template = $mail_template_container->add($ct, $data);


            $this->redirect( "/dashboard/mail/mail_templates/add_ok" );

        } else {
            $this->redirect( "/dashboard/mail/mail_templates/token_error" );
        }
    }


    /**
     * Removes a template
     * @param $id
     * @param $token
     */
    public function remove_template( $id, $token ) {

        if( $this->token->validate( 'template_edit', $token ) ) {

            //remove
            $p = Page::getByID( $id );
            $p->delete();

            $this->redirect( "/dashboard/mail/mail_templates/remove_ok" );

        } else {
            $this->redirect( "/dashboard/mail/mail_templates/token_error" );
        }
    }

    /**
     * Tests a template
     * @param $id
     */
    public function test_template( $id ) {

        /* @var $mailer MailerHelper */
        $mailer = Loader::helper('mailer','c5mailer');

        $mailer->setPageID( $id );

        $mailer->generateHTMLBody();


        $this->set('content_vars', $mailer->extractContentVariables());
        $this->set('template_id', $id);

        $this->render('/dashboard/mail/mail_templates/test_template');
    }

    /**
     * Test the send
     */
    public function test_send() {

        if( $this->token->validate( 'test_template' ) ) {

            //lets try and send a test email
            $mailer = Loader::helper('mailer','c5mailer');

            $mailer->setPageID( $this->post('template-id') );

            $mailer->setReceiver( $this->post('mailRecipient') );

            $mailer->setSubject( Page::getByID($this->post('template-id'))->getCollectionDescription() );

            //Extract the var for replacement from post
            $replacements =  array_diff_key(
                $this->post(),
                array(
                    'ccm_token' => true,
                    'template-id' => true,
                    'mailRecipient' => true,
                    'text-body' => true,
                    'ccm-submit-mail-template-test-form' => true,
                )
            );

            $mailer->setReplacements( $replacements );

            $mailer->send();

            $this->redirect( "/dashboard/mail/mail_templates/test_ok" );

        } else {
            $this->redirect( "/dashboard/mail/mail_templates/token_error" );
        }

    }


} 