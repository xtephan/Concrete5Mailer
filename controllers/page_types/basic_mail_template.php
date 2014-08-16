<?php
/**
 * basic_mail_template.php
 * (C) stefanfodor @ 2014
 * SF
 */

class BasicMailTemplatePageTypeController extends Controller {

    /**
     * View
     */
    public function view() {
        Loader::helper(
            'controller_vars',
            'c5mailer'
        )->setVars($this);
    }

}