<?php
/**
 * mail_scaffolds.php
 * (C) stefanfodor @ 2014
 * SF
 */
defined('C5_EXECUTE') or die("Access Denied.");

//Set the header and dashboard theme
$title=t('Email Scaffolds');
echo Loader::helper('concrete/dashboard')->getDashboardPaneHeaderWrapper($title, false, 'span10 offset1', false);
?>
<div class="ccm-pane-body">

<h3>Installed Scaffolds</h3>
<?php if( empty($installed_scaffolds) ) { ?>
    <p>You have no installed templates.</p>
<?php } else { ?>

    <table class="table table-striped table-bordered">
        <thead>
        <tr>
            <th>Name</th>
            <th>Used on...</th>
            <th style="width: 100px;">Action</th>
        </tr>
        </thead>

        <tbody>
            <?php foreach( $installed_scaffolds as $thisScaffoldHandle=>$thisScaffold) { ?>
                <tr>
                    <td><?php echo $thisScaffold['name']; ?></td>

                    <td>
                        <?php if( empty($thisScaffold['used_on']) ) { ?>
                            <i>Not in use</i>
                        <?php } else { ?>
                            <?php foreach( $thisScaffold['used_on'] as $thisPage ) { ?>
                                <a href="/index.php?cID=<?php echo $thisPage['id']; ?>" target="_blank"><?php echo $thisPage['name'];?></a><br>
                            <?php } ?>
                        <?php } ?>
                    </td>

                    <td>
                        <?php
                        $additional_classes = '';
                        $remove_link = '#';

                        //we can only remove a scaffold if it is not used anywhere else
                        if( !empty($thisScaffold['used_on']) ) {
                            $additional_classes .= 'disabled';
                        } else {
                            $remove_link = $this->action('remove_scaffold') . '/' . $thisScaffoldHandle;
                        }

                        ?>
                        <a href="<?php echo $remove_link ?>" class="btn btn-danger <?php echo $additional_classes ?>">
                            <i class="icon-trash icon-white"></i> Remove
                        </a>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

<?php } ?>


<h3>Awaiting Installation</h3>
<?php if( empty($awaiting_install) ) { ?>
    <p>You have no installed templates.</p>
<?php } else { ?>

    <table class="table table-striped table-bordered">
        <thead>
        <tr>
            <th>Name</th>
            <th>Filename</th>
            <th style="width: 100px;">Action</th>
        </tr>
        </thead>

        <tbody>
            <?php foreach( $awaiting_install as $thisAwaiting) { ?>
                <tr>
                    <td><?php echo $thisAwaiting['name']; ?></td>
                    <td><?php echo $thisAwaiting['filename']; ?></td>
                    <td>
                        <?php
                        $additional_classes = '';
                        $install_link = $this->action('install_scaffold') . '/' . $thisAwaiting;
                        ?>
                        <a href="<?php echo $install_link ?>" class="btn btn-success <?php echo $additional_classes ?>">
                            <i class="icon-folder-open icon-white"></i> Install
                        </a>
                    </td>
                </tr>
            <?php } ?>
        </tbody>

    </table>

<?php } ?>

</div>
<?php  echo Loader::helper('concrete/dashboard')->getDashboardPaneFooterWrapper(false);?>