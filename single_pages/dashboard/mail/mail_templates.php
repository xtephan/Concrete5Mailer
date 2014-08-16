<?php
/**
 * mail_templates.php
 * (C) stefanfodor @ 2014
 * SF
 */
defined('C5_EXECUTE') or die("Access Denied.");

//Set the header and dashboard theme
$title=t('Email Templates');
echo Loader::helper('concrete/dashboard')->getDashboardPaneHeaderWrapper($title, false, 'span10 offset1', false);

$sec_token = $this->controller->token->generate('template_edit');
?>
    <div class="ccm-pane-body">

<h3>Installed Templates</h3>
<?php if( empty($templates) ) { ?>
    <p>You have no installed templates.</p>
<?php } else { ?>

    <table class="table table-striped table-bordered">
    <thead>
    <tr>
        <th>Name</th>
        <th>Scaffold</th>
        <th style="width: 250px;">Action</th>
    </tr>
    </thead>

    <tbody>
    <?php foreach( $templates as $thisTemplate) { ?>
        <tr>
            <td><?php echo $thisTemplate['name']; ?></td>
            <td><?php echo $thisTemplate['scaffold']; ?></td>
            <td>
                <?php
                $edit_link = '/index.php?cID=' . $thisTemplate['id'];
                $test_link = $this->action('test_template') . $thisTemplate['id'];
                $remove_link = $this->action('remove_template') . $thisTemplate['id'] . '/' . $sec_token;
                ?>
                <a href="<?php echo $edit_link ?>" class="btn btn-primary" target="_blank"><i class="icon-edit icon-white"></i> Edit</a>
                <a href="<?php echo $test_link ?>" class="btn btn-warning"><i class="icon-inbox icon-white"></i> Test</a>
                <a href="<?php echo $remove_link ?>" class="btn btn-danger"><i class="icon-trash icon-white"></i> Remove</a>
            </td>
        </tr>
    <?php } ?>
    </tbody>
    </table>
<?php } ?>

<h3>Add new template</h3>
    <form id="new-template-form" method="post" class="form-horizontal" action="<?php  echo $this->action('add_template') ?>">

        <div class="control-group">
            <label class="control-label" for="template_name">Name</label>
            <div class="controls">
                <input type="text" id="new-template-name" name="template_name" placeholder="Email template name..." style="height: 20px; width: 250px;"/>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="scaffold">Scaffold</label>
            <div class="controls">
                <select name="scaffold" id="new-template-scaffold">
                    <option value="0">Choose scaffold...</option>
                    <?php foreach( $scaffolds as $thisScaffold ) {?>
                            <option value="<?php echo $thisScaffold['handle'] ?>"><?php echo $thisScaffold['name'] ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>

        <input type="hidden" name="token" value="<?php echo $sec_token; ?>" />

        <div class="control-group">
            <div class="controls">
                <button type="submit" class="btn btn-primary"><i class="icon-plus icon-white"></i> Create</button>
            </div>
        </div>

    </form>


</div>
<script>
$(function(){
   $("#new-template-form").submit(function(){

       if( $("#new-template-name").val().length == 0 ) {
           alert("Enter template name!");
           return false;
       }

       if( $("#new-template-scaffold").val() == "0" ) {
           alert("Choose a scaffold!");
           return false;
       }

       return true;
   });
});
</script>
<?php  echo Loader::helper('concrete/dashboard')->getDashboardPaneFooterWrapper(false);?>