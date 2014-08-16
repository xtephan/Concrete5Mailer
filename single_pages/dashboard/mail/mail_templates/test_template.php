<?php
/**
 * test_template.php
 * (C) stefanfodor @ 2014
 * SF
 */
defined('C5_EXECUTE') or die('Access Denied.');

/* @var $cdh ConcreteDashboardHelper */
$cdh = Loader::helper('concrete/dashboard');
/* @var $cih ConcreteDashboardHelper */
$cih = Loader::helper('concrete/interface');
/* @var $fh FormHelper */
$fh = Loader::helper('form');


//Set the header and dashboard theme
$title=t('Test Email Template');
echo Loader::helper('concrete/dashboard')->getDashboardPaneHeaderWrapper($title, false, 'span10 offset1', false);
?>
<form method="post" action="<?php echo $this->url('/dashboard/mail/mail_templates/test_send')?>" class="form-horizontal" id="mail-template-test-form">
    <div class="ccm-pane-body">

        <?php echo Loader::helper('validation/token')->output('test_template'); ?>

        <input type="hidden" name="template-id" value="<?php echo $template_id ?>" />

        <fieldset>
            <legend style="margin-bottom: 0px"><?php  echo t('Options')?></legend>
            <div class="control-group">
                <?php echo $fh->label('mailRecipient', t('Send to')); ?>
                <div class="controls">
                    <?php
                    if(!isset($mailRecipient)) {
                        $mailRecipient = '';
                        if(User::isLoggedIn()) {
                            $me = new User();
                            $myInfo = UserInfo::getByID($me->getUserID());
                            $mailRecipient = $myInfo->getUserEmail();
                        }
                    }
                    echo $fh->email('mailRecipient', $mailRecipient, array('required' => 'required')); ?>
                </div>
            </div>
            <div class="control-group">

                <div class="controls">

                    <label for="text-body"><input type="checkbox" value="checked" name="text-body" id="text-body" checked> Send text body separately</label>
                </div>
            </div>
        </fieldset>

        <?php if( !empty($content_vars) ) { ?>
            <fieldset>
                <legend style="margin-bottom: 0px"><?php  echo t('Content Variables')?></legend>
                <?php foreach($content_vars as $thisVar) { ?>
                    <div class="control-group">
                        <?php echo $fh->label($thisVar, $thisVar); ?>
                        <div class="controls">
                            <?php echo $fh->text($thisVar, '%' . $thisVar . '%'); ?>
                        </div>
                    </div>
                <?php } ?>
            </fieldset>
        <?php } ?>

    </div>

    <div class="ccm-pane-footer">
        <a href="<?php echo $this->url('/dashboard/mail/mail_templates')?>" class="btn"><?php echo t('Email Templates')?></a>
        <?php
        if (ENABLE_EMAILS) {
            echo $cih->submit(t('Send'), 'mail-template-test-form', 'right', 'primary');
        }
        ?>
    </div>

</form>
<?php  echo Loader::helper('concrete/dashboard')->getDashboardPaneFooterWrapper(false);?>