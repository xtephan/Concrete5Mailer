<?php
/**
 * options.php
 * (C) stefanfodor @ 2014
 * SF
 */
defined('C5_EXECUTE') or die("Access Denied.");

class DashboardMailOptionsController extends DashboardBaseController {


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

        //figure out if we have custom vars
        $custom_vars = array();

        $custom_keys = $co->get('custom_keys');
        if( !empty($custom_keys) ) {
            $cka = explode('+',$custom_keys);
            foreach( $cka as $thisCustomKey ) {
                $value = $co->get( $thisCustomKey );
                $custom_vars[ $thisCustomKey ] = $value;
            }
        }

        $this->set( 'custom_vars', $custom_vars );
    }

    /**
     * Task that updates Configuration
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

                //new variables?
                $custom_keys = $this->post('custom_key');
                $custom_values = $this->post('custom_value');

                $custom_keys_good = array();

                if( !empty($custom_keys) ) {

                    $custom_no = count( $custom_keys );

                    for( $i = 0; $i<$custom_no; $i++ ) {
                        if( !empty($custom_keys[$i]) ) {
                            $co->save($custom_keys[$i], $custom_values[$i]);
                            $custom_keys_good[] = $custom_keys[$i];
                        }
                    }

                }

                $co->save('custom_keys', implode('+',$custom_keys_good));

                $this->redirect( "/dashboard/mail/options/success" );
            }
        } else {
            $this->redirect( "/dashboard/mail/options/token_error" );
        }
    }

}