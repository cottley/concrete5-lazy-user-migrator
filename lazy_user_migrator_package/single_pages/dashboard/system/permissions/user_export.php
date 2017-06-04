<?php defined('C5_EXECUTE') or die("Access Denied.");?>
<?php Core::make('help')->display('Quick and simple way to export your concrete5 users from one site and import them into another. Intended primarily for in-place migrations. Supports migrations user attributes and user groups. If a group does not exist, it will be created. This is a lazy way to migrate users, not a way to migrate lazy users. There are a few bigger solutions out there intended for non-developers who wish to import large CSV files of users. These solutions seem best-suited for bulk imports from a separate database, e.g. converting your payroll spreadsheet into a csv you can load into concrete5. The intent of this simple tool is to meet a requirement for migration or syncing from one user DB to another. Newly migrated users will have a new random password. All user attributes which are defined with a value() function that does a basic toString() (and can be similarly created) will also be exported/imported.'); ?>
<h1>User Export</h1>
<form method="post" action="<?=$view->action('export')?>">
    <fieldset>
    <div class="form-group">
        <?=t('Press export to export the current user list as either an XML file or JSON file.')?>
    </div>
    <div class="form-group">
<label for="mode" class="launch-tooltip control-label" data-placement="right" title="<?=t('The download format for export must match the download format for import')?>"><?=t('Download Format')?></label>		
<?php
        $options = array(
        'xml' => t('XML'),
        'json' => t('JSON'),
	    );
?>		
		<?= $form->select('mode', $options, $mode)?>
    </div>
    </fieldset>
    <div class="ccm-dashboard-form-actions-wrapper">
    <div class="ccm-dashboard-form-actions">
        <button class="pull-right btn btn-success" type="submit" ><?=t('Export')?></button>
    </div>
    </div>
</form>