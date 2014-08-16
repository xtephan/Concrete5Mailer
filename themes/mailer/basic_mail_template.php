<?php
/**
 * basic.php
 * (C) stefanfodor @ 2014
 * SF
 */
defined('C5_EXECUTE') or die("Access Denied.");
global $inMail;
 ?>
<?php if( !$inMail ) { ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <!-- If you delete this meta tag, Half Life 3 will never be released. -->
    <meta name="viewport" content="width=device-width" />

    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

    <title>Edit System Page</title>
    <?php  Loader::element('header_required'); ?>

    <link rel="stylesheet" type="text/css" href="<?php echo $this->getStyleSheet('typography.css')?>" />
    <link rel="stylesheet" type="text/css" href="<?php echo $this->getStyleSheet('css/basic_mail_template.css')?>" />

</head>
<?php } ?>

<body bgcolor="#FFFFFF">

<!-- HEADER -->
<table class="head-wrap" bgcolor="#999999">
    <tr>
        <td></td>
        <td class="header container" >

            <div class="content">
                <table bgcolor="#999999">
                    <tr>
                        <td><?php $a = new Area("Top Left Image"); $a->display($c);?></td>
                        <td align="right"><h6 class="collapse"><?php $a = new Area("Top Right Name"); $a->display($c);?></h6></td>
                    </tr>
                </table>
            </div>

        </td>
        <td></td>
    </tr>
</table><!-- /HEADER -->


<!-- BODY -->
<table class="body-wrap">
    <tr>
        <td></td>
        <td class="container" bgcolor="#FFFFFF">

            <div class="content">
                <table>
                    <tr>
                        <td>

                            <?php
                            $a = new Area("Main");
                            $a->display($c);
                            ?>

                            <?php if(
                                !empty($contact_phone) ||
                                !empty($contact_email) ||
                                !empty($social_facebook) ||
                                !empty($social_twitter) ||
                                !empty($social_gplus)
                            ) {
                            ?>
                            <!-- social & contact -->
                            <table class="social" width="100%">
                                <tr>
                                    <td>

                                        <?php if(
                                            !empty($social_facebook) ||
                                            !empty($social_twitter) ||
                                            !empty($social_gplus)
                                        ) {
                                            ?>
                                        <!-- column 1 -->
                                        <table align="left" class="column">
                                            <tr>
                                                <td>

                                                    <h5 class="">Connect with Us:</h5>
                                                    <p class="">

                                                        <?php if( !empty($social_facebook) ) { ?>
                                                            <a href="<?php echo $social_facebook ?>" class="soc-btn fb">Facebook</a>
                                                        <?php } ?>

                                                        <?php if( !empty($social_twitter) ) { ?>
                                                            <a href="<?php echo $social_twitter ?>" class="soc-btn tw">Twitter</a>
                                                        <?php } ?>

                                                        <?php if( !empty($social_gplus) ) { ?>
                                                            <a href="<?php echo $social_gplus ?>" class="soc-btn gp">Google+</a>
                                                        <?php } ?>
                                                    </p>


                                                </td>
                                            </tr>
                                        </table><!-- /column 1 -->
                                        <?php } ?>

                                        <?php if(
                                            !empty($contact_phone) ||
                                            !empty($contact_email)
                                        ) {
                                            ?>
                                        <!-- column 2 -->
                                        <table align="left" class="column">
                                            <tr>
                                                <td>

                                                    <h5 class="">Contact Info:</h5>
                                                    <p>
                                                        <?php if( !empty($contact_phone) ) { ?>
                                                            Phone: <strong><?php echo $contact_phone ?></strong><br/>
                                                        <?php } ?>

                                                        <?php if( !empty($contact_email) ) { ?>
                                                            Email: <strong><a href="emailto:<?php echo $contact_email ?>"><?php echo $contact_email ?></a></strong>
                                                        <?php } ?>
                                                    </p>

                                                </td>
                                            </tr>
                                        </table><!-- /column 2 -->
                                        <?php } ?>

                                        <span class="clear"></span>

                                    </td>
                                </tr>
                            </table><!-- /social & contact -->

                        </td>
                    </tr>
                </table>
                <?php } ?>
            </div><!-- /content -->

        </td>
        <td></td>
    </tr>
</table><!-- /BODY -->

<?php if( !$inMail ) { ?>
<?php  Loader::element('footer_required'); ?>
</body>
</html>
<?php } ?>