<?php
/**
 * options.php
 * (C) stefanfodor @ 2014
 * SF
 */
defined('C5_EXECUTE') or die("Access Denied.");

class DashboardSystemMailOptionsController extends DashboardBaseController {


    /**
     * View task
     * @param string $msg
     */
    public function view( $msg = null ) {

        //sucess message if needed
        if( $msg == "success" ) {
            $this->set("message", t('Configuration updated successfully!'));
        }

        //error msg
        if( $msg == "key_error" ) {
            $this->error = t('API key cannot be null!');
        }

        //error msg
        if( $msg == "token_error" ) {
            $this->error = t('Invalid security token!');
        }

        //grab existing configuration
        $pkg = Package::getByHandle("c5mailer");
        $co = new Config();
        $co->setPackageObject($pkg);

        //send saved config to view
        $this->set( 'sender_name', $co->get('sender_name') );
        $this->set( 'sender_address', $co->get('sender_address') );

        $this->set( 'contact_phone', $co->get('contact_phone') );
        $this->set( 'contact_email', $co->get('contact_email') );


        $this->set( 'social_facebook', $co->get('social_facebook') );
        $this->set( 'social_twitter', $co->get('social_twitter') );
        $this->set( 'social_gplus', $co->get('social_gplus') );

    }

    /**
     * Task that updates Authy Configuration
     */
    public function update_config() {

        if ($this->token->validate("update_email_config")) {
            if ($this->isPost()) {

                //set options on package bases
                $pkg = Package::getByHandle("c5mailer");
                $co = new Config();
                $co->setPackageObject($pkg);

                //save the config
                $co->save('sender_name', $this->post("SENDER_NAME") );
                $co->save('sender_address', $this->post("SENDER_ADDRESS") );

                $co->save('contact_phone', $this->post("CONTACT_PHONE") );
                $co->save('contact_email', $this->post("CONTACT_EMAIL"));

                $co->save('social_facebook', $this->post("SOCIAL_FACEBOOK") );
                $co->save('social_twitter', $this->post("SOCIAL_TWITTER"));
                $co->save('social_gplus', $this->post("SOCIAL_GPLUS"));

                $this->redirect( "/dashboard/system/mail/options/success" );
            }
        } else {
            $this->redirect( "/dashboard/system/mail/options/token_error" );
        }
    }

}