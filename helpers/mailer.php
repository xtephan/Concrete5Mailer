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

    /**
     * C5 Page holding the mail template
     * @var null
     */
    protected $template_page = null;

    /**
     * PHP SMTP Mailer
     * @var null|PHPMailer
     */
    protected $mail = null;

    /**
     * Replacements
     * @var null
     */
    protected $replacements = null;

    /**
     * HTML body
     * @var null
     */
    protected $html_body = null;

    /**
     * Text body
     * @var null
     */
    protected $text_body = null;

    /**
     * List of attachments
     * @var null
     */
    protected $attachments = null;

    /**
     * Remember is sender is set
     * @var bool
     */
    private $has_sender = false;

    /**
     * Remembers if receiver is set
     * @var bool
     */
    private $has_receiver = false;

    /**
     * remembers if subject is set
     * @var bool
     */
    private $has_subject = false;


    /**
     * Constructor
     */
    public function __construct() {

        //init the php mailer library
        Loader::library( '3rdparty/phpmailer/phpmailer', 'c5mailer' );
        $this->mail = new PHPMailer();

        //set up email settings
        $this->mail->isSMTP();

        //get C5 mail settings
        $smtp_server = Config::get('MAIL_SEND_METHOD_SMTP_SERVER');
        $smtp_user =Config::get('MAIL_SEND_METHOD_SMTP_USERNAME');
        $smtp_pass = Config::get('MAIL_SEND_METHOD_SMTP_PASSWORD');
        $smtp_port = Config::get('MAIL_SEND_METHOD_SMTP_PORT');

        //server and port
        $this->mail->Host = $smtp_server;
        $this->mail->Port = $smtp_port;

        //user
        if( !empty($smtp_user) ) {
            $this->mail->SMTPAuth   = true;
            $this->mail->Username   = $smtp_user;
            $this->mail->Password   = $smtp_pass;
        }

    }

    /**
     * Sends the mail
     */
    public function send() {

        //no receiver, no continue
        if( !$this->has_receiver ) {
            throw new Exception('Receiver is required!');
        }

        //no template, no continue
        if( !$this->template_page ) {
            throw new Exception('Template mail missing!');
        }

        //autofill subject if needed
        if( !$this->has_subject ) {
            $this->mail->Subject = $this->template_page->getCollectionDescription();
        }

        //autofill sender
        if( !$this->has_sender ) {

            $pkg = Package::getByHandle("c5mailer");
            $co = new Config();
            $co->setPackageObject($pkg);

            $sender_address = $co->get('sender_address');
            $sender_name = $co->get('sender_name');

            $this->mail->setFrom( $sender_address, $sender_name );
            $this->mail->addReplyTo( $sender_address, $sender_name );

        }

        //generate the body
        $this->generateHTMLBody();

        //fix images servername
        $this->fixImages();

        //make var replacements
        if( !empty($this->replacements) ){
            $this->makeReplacements();
        }

        //add attachments
        if( !empty($this->attachments) ){
            $this->addAttachments();
        }

        //generate the body
        $this->generateTextBody();

        //attach the body to the email
        $this->mail->IsHTML(true);
        $this->mail->Body = $this->html_body;
        $this->mail->AltBody = $this->text_body;

        //and finally, ship the sucker
        if(!$this->mail->send()) {
            throw new Exception("Mailer Error: " . $this->mail->ErrorInfo);
        }

    }

    /**
     * Sets the list of attachments to the email
     */
    private function addAttachments() {

        foreach( $this->attachments as $thisAttachment ) {
            $this->mail->addAttachment(
                $thisAttachment[0], //path
                $thisAttachment[1]  //name
            );
        }

    }

    /**
     * Generates the text body based on the html one
     */
    private function generateTextBody() {
        $this->text_body = $this->strip_html_tags($this->html_body);
        $this->trim_whitespaces();
    }

    /**
     * Trims whitespaces from text body
     */
    private function trim_whitespaces() {
        $this->text_body = preg_replace('/\s+/', ' ', $this->text_body);
    }

    /**
     * Remove HTML tags, including invisible text such as style and
     * script code, and embedded objects.  Add line breaks around
     * block-level tags to prevent word joining after tag removal.
     */
    private function strip_html_tags( $text ){

        $text = preg_replace(
            array(
                // Remove invisible content
                '@<head[^>]*?>.*?</head>@siu',
                '@<style[^>]*?>.*?</style>@siu',
                '@<script[^>]*?.*?</script>@siu',
                '@<object[^>]*?.*?</object>@siu',
                '@<embed[^>]*?.*?</embed>@siu',
                '@<applet[^>]*?.*?</applet>@siu',
                '@<noframes[^>]*?.*?</noframes>@siu',
                '@<noscript[^>]*?.*?</noscript>@siu',
                '@<noembed[^>]*?.*?</noembed>@siu',
                // Add line breaks before and after blocks
                '@</?((address)|(blockquote)|(center)|(del))@iu',
                '@</?((div)|(h[1-9])|(ins)|(isindex)|(p)|(pre))@iu',
                '@</?((dir)|(dl)|(dt)|(dd)|(li)|(menu)|(ol)|(ul))@iu',
                '@</?((table)|(th)|(td)|(caption))@iu',
                '@</?((form)|(button)|(fieldset)|(legend)|(input))@iu',
                '@</?((label)|(select)|(optgroup)|(option)|(textarea))@iu',
                '@</?((frameset)|(frame)|(iframe))@iu',
            ),
            array(
                ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ',
                "\n\$0", "\n\$0", "\n\$0", "\n\$0", "\n\$0", "\n\$0",
                "\n\$0", "\n\$0",
            ),
            $text );
        return strip_tags( $text );
    }


    /**
     * Make var replacements in html body
     */
    private function makeReplacements() {

        foreach ($this->replacements as $variable => $replacement) {
            $this->html_body = str_replace("%".$variable."%", utf8_decode($replacement), $this->html_body);
        }

    }

    /**
     * Fixes path of images
     */
    private function fixImages() {
        $regex = "-(<img[^>]+src\s*=\s*['\"])((?:(?!'|\"|http://).)*)(['\"][^>]*>)-i";
        $domain = "http://".$_SERVER['HTTP_HOST'];
        $this->html_body = preg_replace($regex, "$1".$domain."$2$3", $this->html_body);
    }

    /**
     * Generates the HTML body
     */
    private function generateHTMLBody() {

        //single to the page to skip uneeded parts
        global $inMail;
        $inMail = true;

        //backup the c
        $oldC = $c;

        //render the template
        ob_start();
        $_v = View::getInstance();
        $_v->render( $this->template_page );
        $html_content = ob_get_clean();

        //restore the c
        $c = $oldC;

        //css style
        ob_start();
        $pagetheme = PageTheme::getByHandle('mailer');
        $pagetheme->outputStyleSheet(sprintf('css/%s.css', $this->template_page->getCollectionTypeHandle()));
        $css_styles = ob_get_clean();

        //merge the CSS and the Body
        Loader::library( '3rdparty/emogrifier', 'c5mailer' );
        $emo = new Emogrifier();

        $emo->setHTML($html_content);
        $emo->setCSS($css_styles);
        $this->html_body = $emo->emogrify();
    }

    /**
     * Sets replacements
     * @param $rep
     */
    public function setReplacements( $rep ) {
        $this->replacements = $rep;
    }

    /**
     * Sets the subject
     * @param $sbj
     */
    public function setSubject( $sbj ) {
        $this->mail->Subject = $sbj;
        $this->has_subject = true;
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
        $this->has_receiver = true;
    }

    /**
     * Sets Sender
     * @param $address
     * @param null|string $name
     */
    public function setSender( $address, $name = '' ) {

        $this->mail->setFrom( $address, $name );

        $this->mail->addReplyTo( $address, $name );

        $this->has_sender = true;

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
        $this->template_page = $template[0];
    }

    /**
     * Attaches a file to the email
     * @param $path
     * @param string $name
     */
    public function attachFileByPath( $path, $name = '' ) {
        $this->attachments[] = array(
            $path, $name
        );
    }

    /**
     * Attach a C5 file to the mail
     * @param $file
     * @throws Exception
     */
    public function attachFile( $file ) {

        if( !($file instanceof File) ) {
            throw new Exception('MailerHelper::attachFile expects File as parameter!');
        }

        $fv = $file->getRecentVersion();

        $this->attachments[] = array(
            $fv->getPath(), $fv->getFileName()
        );

    }

    /**
     * Attaches a C5 file by ID
     * @param $fid
     * @throws Exception
     */
    public function attachFileByID( $fid ) {

        if( !is_numeric($fid) ) {
            throw new Exception('MailerHelper::attachFileByID expects integer as parameter!');
        }

        $file = File::getByID($fid);

        if( $file->isError() ) {
            throw new Exception('File for attachment not found!');
        }


        $this->attachFile( $file );
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

        $this->template_page = $page;
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

        $this->template_page = Page::getByID( $cid );

        if( $this->template_page->isError() ) {
            throw new Exception('Page with ID ' . $cid . ' not found!');
        }
    }

}