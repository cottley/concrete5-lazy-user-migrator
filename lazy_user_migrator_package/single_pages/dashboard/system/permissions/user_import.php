<?php defined('C5_EXECUTE') or die("Access Denied.");?>
<?php Core::make('help')->display('Quick and simple way to export your concrete5 users from one site and import them into another. Intended primarily for in-place migrations. Supports migrations user attributes and user groups. If a group does not exist, it will be created. This is a lazy way to migrate users, not a way to migrate lazy users. There are a few bigger solutions out there intended for non-developers who wish to import large CSV files of users. These solutions seem best-suited for bulk imports from a separate database, e.g. converting your payroll spreadsheet into a csv you can load into concrete5. The intent of this simple tool is to meet a requirement for migration or syncing from one user DB to another. Newly migrated users will have a new random password. All user attributes which are defined with a value() function that does a basic toString() (and can be similarly created) will also be exported/imported.'); ?>
<h1>User Import</h1>
<form method="post" action="<?=$view->action('import')?>" enctype="multipart/form-data">
    <fieldset>
    <div class="form-group">
        <?=t('Press import to import the uploaded user list as either an XML file or JSON file.')?>
    </div>
    <div class="form-group">
<label for="mode" class="launch-tooltip control-label" data-placement="right" title="<?=t('The upload format for import must match the download format exported')?>"><?=t('Upload Format')?></label>		
<?php
        $options = array(
        'xml' => t('XML'),
        'json' => t('JSON'),
	    );
?>		
		<?= $form->select('mode', $options, $mode)?>
    </div>
    <div class="form-group">
      <label for="fileToUpload" class="launch-tooltip control-label" data-placement="right" title="<?=t('Select the file to upload for import')?>"><?=t('Select file')?></label>	
      <input type="file" name="fileToUpload" id="fileToUpload">
    </div>	
    <div class="form-group">
      <label for="restricttousers" class="launch-tooltip control-label" data-placement="right" title="<?=t('You can specify a comma seperated list of usernames to import from the file that has been selected to upload')?>"><?=t('Only import the following users')?></label>	
      <?= $form->textarea('restricttousers', $restricttousers, array('rows' => 5))?>
    </div>	
    <div class="form-group">
      <label for="donotcreategroups" class="launch-tooltip control-label" data-placement="right" title="<?=t('You can specify a comma seperated list of groups that may be in the upload file that you do not want the import to create automatically')?>"><?=t('Exclude the following groups')?></label>	
      <?= $form->textarea('donotcreategroups', $donotcreategroups, array('rows' => 5))?>
    </div>		
    </fieldset>


    <div class="ccm-dashboard-form-actions-wrapper">
    <div class="ccm-dashboard-form-actions">
        <button class="pull-right btn btn-success" type="submit" ><?=t('Import')?></button>
    </div>
    </div>
</form>