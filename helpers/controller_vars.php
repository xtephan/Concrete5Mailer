<?php
/**
 * controller_vars.php
 * (C) stefanfodor @ 2014
 * SF
 */

/**
 * Class ControllerVarsHelper
 */
class ControllerVarsHelper {

    /**
     * Sets in the controller, the vars saved by the user
     * @param $controller
     */
    public function setVars( $controller ) {

        //set the package
        $pkg = Package::getByHandle("c5mailer");
        $co = new Config();
        $co->setPackageObject($pkg);

        //default contacts
        $controller->set( 'contact_phone', $co->get('contact_phone') );
        $controller->set( 'contact_email', $co->get('contact_email') );

        $controller->set( 'social_facebook', $co->get('social_facebook') );
        $controller->set( 'social_twitter', $co->get('social_twitter') );
        $controller->set( 'social_gplus', $co->get('social_gplus') );

        //additional vars from the user
        $custom_keys = $co->get('custom_keys');
        if( !empty($custom_keys) ) {
            $cka = explode('+',$custom_keys);
            foreach( $cka as $thisCustomKey ) {
                $controller->set( $thisCustomKey, $co->get( $thisCustomKey ) );
            }
        }

    }

}