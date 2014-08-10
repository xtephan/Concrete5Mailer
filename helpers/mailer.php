<?php
/**
 * mailer.php
 * (C) stefanfodor @ 2014
 * SF
 */
defined('C5_EXECUTE') or die(_("Access Denied."));

/**
 * Class MailerHelper
 */
class MailerHelper {

    protected $c = null;

    protected $mail = null;

    /**
     * Constructor
     */
    public function __construct() {

        //init the php mailer library
        Loader::library( '3rdparty/phpmailer/phpmailer', 'c5mailer' );
        $this->mail = new PHPMailer();

        //set up email settings
        $this->mail->isSMTP();
        $this->mail->Host = "127.0.0.1";
        $this->mail->Port = "1025";

    }

    /**
     * Sends the mail
     */
    public function send() {

        //single to the page to skip uneeded parts
        global $inMail;
        $inMail = true;

        //backup the c
        $oldC = $c;

        //render the template
        ob_start();
        $_v = View::getInstance();
        $_v->render( $this->c );
        $html_content = ob_get_clean();

        //restore the c
        $c = $oldC;

        //css style
        ob_start();
        $pagetheme = PageTheme::getByHandle('mailer');
        $pagetheme->outputStyleSheet('css/basic_mail_template.css');
        $css_styles = ob_get_clean();

        //merge the CSS and the Body
        Loader::library( '3rdparty/emogrifier', 'c5mailer' );
        $emo = new Emogrifier();

        $emo->setHTML($html_content);
        $emo->setCSS($css_styles);
        $html_content = $emo->emogrify();

        //we want html, please
        $this->mail->IsHTML(true);
        $this->mail->Body = $html_content;

        //ship the sucker
        if(!$this->mail->send()) {
            throw new Exception("Mailer Error: " . $this->mail->ErrorInfo);
        }

    }

    /**
     * Sets the subject
     * @param $sbj
     */
    public function setSubject( $sbj ) {
        $this->mail->Subject = $sbj;
    }

    /**
     * Sets the subject
     * @param $body
     */
    public function setBody( $body ) {
        $this->mail->Body = $body;
    }

    /**
     * Sets Receiver
     * @param $address
     * @param null|string $name
     */
    public function setReceiver( $address, $name = '' ) {
        $this->mail->addAddress( $address, $name );
    }

    /**
     * Sets Sender
     * @param $address
     * @param null|string $name
     */
    public function setSender( $address, $name = '' ) {

        $this->mail->setFrom( $address, $name );

        $this->mail->addReplyTo( $address, $name );

    }

    /**
     * Sets Sender
     * @param $address
     * @param null|string $name
     */
    public function setReplyTo( $address, $name = '' ) {

        $this->mail->addReplyTo( $address, $name );

    }

    /**
     * Sets mail template from name
     * @param $t_name
     * @throws Exception
     */
    public function setMailTemplate( $t_name ) {

        //page lister
        Loader::model('page_list');
        $pl = new PageList();

        //email types are system pages
        $pl->includeSystemPages();

        //we want exact name
        $pl->filterByName( $t_name, true );

        //get
        $template = $pl->getPage(true);

        if( empty($template) ){
            throw new Exception('Email template not found!');
        }

        //save
        $this->c = $template[0];
    }

    /**
     * Sets a page from which we will take the content
     * @param $page
     * @throws Exception
     */
    public function setPage( $page ) {

        if( !($page instanceof Page) ){
            throw new Exception('MailerHelper::setPage expects a Page as parameter');
        }

        $this->c = $page;
    }


    /**
     * Sets a page ID
     * @param $cid
     * @throws Exception
     */
    public function setPageID( $cid ) {

        if( !is_numeric($cid) ){
            throw new Exception('MailerHelper::setPageID expects a integer as parameter');
        }

        $this->c = Page::getByID( $cid );

        if( $this->c->isError() ) {
            throw new Exception('Page with ID ' . $cid . ' not found!');
        }
    }

}