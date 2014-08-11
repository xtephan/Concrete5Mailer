<?php
/**
 * options.php
 * (C) stefanfodor @ 2014
 * SF
 */
defined('C5_EXECUTE') or die("Access Denied.");

//Set the header and dashboard theme
$title=t('Email Configuration');
echo Loader::helper('concrete/dashboard')->getDashboardPaneHeaderWrapper($title, false, 'span10 offset1', false);
?>
<style>
    .controls-2-in {
        margin-left: 20px!important;
    }
</style>
    <form method="post" class="form-horizontal" action="<?php  echo $this->action('update_config') ?>">
        <div class="ccm-pane-body">
            <?php  echo $this->controller->token->output('update_email_config')?>

            <fieldset>
                <legend style="margin-bottom: 0px"><?php  echo t('Sender')?></legend>

                <div class="control-group">
                    <label class="control-label" for="SENDER_NAME">Name</label>
                    <div class="controls">
                        <input type="text" name="SENDER_NAME" value="<?php echo $sender_name ?>" style="height: 20px; width: 250px;"/>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label" for="SENDER_ADDRESS">Email Address</label>
                    <div class="controls">
                        <input type="text" name="SENDER_ADDRESS" value="<?php echo $sender_address ?>" style="height: 20px; width: 250px;"/>
                    </div>
                </div>

            </fieldset>

            <fieldset>
                <legend style="margin-bottom: 0px"><?php  echo t('Contact Info')?></legend>

                <div class="control-group">
                    <label class="control-label" for="CONTACT_PHONE">Phone number:</label>
                    <div class="controls">
                        <input type="text" name="CONTACT_PHONE" value="<?php echo $contact_phone ?>" style="height: 20px; width: 250px;"/>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label" for="CONTACT_EMAIL">Email Address:</label>
                    <div class="controls">
                        <input type="text" name="CONTACT_EMAIL" value="<?php echo $contact_email ?>" style="height: 20px; width: 250px;"/>
                    </div>
                </div>

            </fieldset>

            <fieldset>
                <legend style="margin-bottom: 0px"><?php  echo t('Social Links')?></legend>

                <div class="control-group">
                    <label class="control-label" for="SOCIAL_FACEBOOK">Facebook:</label>
                    <div class="controls">
                        <input type="text" name="SOCIAL_FACEBOOK" value="<?php echo $social_facebook ?>" style="height: 20px; width: 250px;"/>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label" for="SOCIAL_TWITTER">Twitter:</label>
                    <div class="controls">
                        <input type="text" name="SOCIAL_TWITTER" value="<?php echo $social_twitter ?>" style="height: 20px; width: 250px;"/>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label" for="SOCIAL_GPLUS">Google Plus:</label>
                    <div class="controls">
                        <input type="text" name="SOCIAL_GPLUS" value="<?php echo $social_gplus ?>" style="height: 20px; width: 250px;"/>
                    </div>
                </div>

            </fieldset>


            <fieldset id="my-vars">
                <legend style="margin-bottom: 0px"><?php  echo t('Your Variables')?></legend>
                <?php foreach( $custom_vars as $custom_key=>$custom_value) { ?>
                    <div class="control-group">
                        <div class="controls controls-row controls-2-in">
                            <input class="span2" type="text" name="custom_key[]" placeholder="key" value="<?php echo $custom_key ?>">
                            <input class="span3" type="text" name="custom_value[]" placeholder="value" value="<?php echo $custom_value ?>">
                        </div>
                    </div>
                <?php } ?>
            </fieldset>

        </div>
        <div class="ccm-pane-footer">
            <a href="javascript:void(0)" class="btn" id="new-var-trigger"><?php echo t('Add new email variable')?></a>
            <input type="submit" class="btn ccm-button-v2 primary ccm-button-v2-right" value="<?php echo t('Save'); ?>">
        </div>
    </form>

<div class="control-group my-var-matrix" style="display: none;">
    <div class="controls controls-row controls-2-in">
        <input class="span2" type="text" name="custom_key[]" placeholder="key">
        <input class="span3" type="text" name="custom_value[]" placeholder="value">
    </div>
</div>

<script>
$(function(){
    $("#new-var-trigger").click(function(){

        $clone = $(".my-var-matrix").clone();

        $clone.removeClass("my-var-matrix");

        $("#my-vars").append( $clone );
        $clone.show();

    });
});
</script>

<?php  echo Loader::helper('concrete/dashboard')->getDashboardPaneFooterWrapper(false);?>