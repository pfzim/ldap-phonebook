<?php include(TEMPLATES_DIR.'tpl.header.php'); ?>

<h3><?php L('Tools') ?></h3>

<a href="<?php ln('contacts_sync') ?>"><?php L('Sync') ?></a>
<a href="<?php ln('contacts_export') ?>"><?php L('Export') ?></a>
<a href="<?php ln('contacts_hide_disabled') ?>"><?php L('HideDisabledContacts') ?></a><br />
<a href="<?php ln('contacts_dump_db') ?>"><?php L('DumpDB') ?></a><br />
<a href="<?php ln('contacts_export_xml') ?>"><?php L('ExportXML') ?></a><br />
<a href="#" onclick="f_import_xml(); return false;"><?php L('ImportXML') ?></a><br />

<p>
	<?php L('CurrentUserToken') ?>: <b><?php eh($core->UserAuth->get_token()); ?></b><br />
	<?php L('TokenNote') ?>
</p>
<p><?php L('UsageExample') ?>:</p>
<pre>
  curl --silent --cookie <?php eh('"zl='.$core->UserAuth->get_login().';zh='.$core->UserAuth->get_token().'"'); ?> --output /dev/null "http://localhost<?php ln('sync') ?>"
  php -f <?php eh($_SERVER['SCRIPT_FILENAME']); ?> -- --user <?php eh($core->UserAuth->get_login()); ?> --token <?php eh($core->UserAuth->get_token()); ?> --path sync
  php -f <?php eh($_SERVER['SCRIPT_FILENAME']); ?> -- --user <?php eh($core->UserAuth->get_login()); ?> --token <?php eh($core->UserAuth->get_token()); ?> --path sync_jobs
  php -f <?php eh($_SERVER['SCRIPT_FILENAME']); ?> -- --user <?php eh($core->UserAuth->get_login()); ?> --token <?php eh($core->UserAuth->get_token()); ?> --path sync_jobs/&lt;runbook guid&gt;
  php -f <?php eh($_SERVER['SCRIPT_FILENAME']); ?> -- --user <?php eh($core->UserAuth->get_login()); ?> --token <?php eh($core->UserAuth->get_token()); ?> --path start_runbook --data 'guid=00000000-0000-0000-0000-000000000000&amp;param[00000000-0000-0000-0000-000000000000]=value'
  php -f <?php eh($_SERVER['SCRIPT_FILENAME']); ?> -- --user <?php eh($core->UserAuth->get_login()); ?> --password &lt;password&gt; --path sync
</pre>

<br />
<?php if(defined('USE_MEMCACHED') && USE_MEMCACHED) { ?>
<a href="<?php ln('flush_memcached') ?>" onclick="return f_async(this);"><?php L('FlushMemcached') ?></a><br />
<?php } ?>


<?php if(!$core->UserAuth->is_ldap_user() && $core->UserAuth->get_id()) { ?>
<a href="#" onclick="f_show_form('<?php ln('password_form') ?>');"><?php L('ChangePassword') ?></a><br />
<?php } ?>

<form method="post" id="form-file-upload" name="form-file-upload">
	<input id="file-upload" type="file" name="file" style="display: none"/>
</form>

<?php include(TEMPLATES_DIR.'tpl.universal-form.php'); ?>
<?php include(TEMPLATES_DIR.'tpl.footer.php'); ?>

