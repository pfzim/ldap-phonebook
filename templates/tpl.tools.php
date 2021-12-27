<?php include(TEMPLATES_DIR.'tpl.header.php'); ?>

<h3><?php L('Tools') ?></h3>

<a href="<?php ln('contacts_sync') ?>" onclick="return f_confirm_async(this);"><?php L('Sync') ?></a><br />
<a href="<?php ln('contacts_hide_disabled') ?>" onclick="return f_confirm_async(this);"><?php L('HideDisabledContacts') ?></a><br />
<a href="<?php ln('contacts_show_all_enabled') ?>" onclick="return f_confirm_async(this);"><?php L('ShowEnabledContacts') ?></a><br />
<a href="<?php ln('contacts_export') ?>"><?php L('Export') ?></a><br />
<a href="<?php ln('contacts_dump_db') ?>"><?php L('DumpDB') ?></a><br />
<a href="<?php ln('contacts_export_xml') ?>"><?php L('ExportXML') ?></a><br />
<a href="<?php ln('contacts_import_xml') ?>" onclick="f_import_xml(); return false;"><?php L('ImportXML') ?></a><br />

<p>
	<?php L('CurrentUserToken') ?>: <b><?php eh($core->UserAuth->get_token()); ?></b><br />
	<?php L('TokenNote') ?>
</p>
<p><?php L('UsageExample') ?>:</p>
<pre>
  curl --silent --cookie <?php eh('"zl='.$core->UserAuth->get_login().';zh='.$core->UserAuth->get_token().'"'); ?> --output /dev/null "http://localhost/<?php ln('contacts_sync') ?>"
  php -f <?php eh($_SERVER['SCRIPT_FILENAME']); ?> -- --user <?php eh($core->UserAuth->get_login()); ?> --token <?php eh($core->UserAuth->get_token()); ?> --path contacts_sync
  php -f <?php eh($_SERVER['SCRIPT_FILENAME']); ?> -- --user <?php eh($core->UserAuth->get_login()); ?> --token <?php eh($core->UserAuth->get_token()); ?> --path contacts_hide_disabled
  php -f <?php eh($_SERVER['SCRIPT_FILENAME']); ?> -- --user <?php eh($core->UserAuth->get_login()); ?> --password &lt;password&gt; --path contacts_sync
</pre>

<br />
<?php if(defined('USE_MEMCACHED') && USE_MEMCACHED) { ?>
<a href="<?php ln('flush_memcached') ?>" onclick="return f_async(this);"><?php L('FlushMemcached') ?></a><br />
<?php } ?>


<?php if(!$core->UserAuth->is_ldap_user() && $core->UserAuth->get_id()) { ?>
<a href="<?php ln('password_change_form') ?>" onclick="f_show_form('<?php ln('password_change_form') ?>'); return false;"><?php L('ChangePassword') ?></a><br />
<?php } ?>

<form method="post" id="form-file-upload" name="form-file-upload">
	<input id="file-upload" type="file" name="file" style="display: none"/>
</form>

<?php include(TEMPLATES_DIR.'tpl.universal-form.php'); ?>
<?php include(TEMPLATES_DIR.'tpl.footer.php'); ?>

