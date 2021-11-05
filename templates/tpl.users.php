<?php include(TEMPLATES_DIR.'tpl.header.php'); ?>
<?php include(TEMPLATES_DIR.'tpl.tree-list.php'); ?>

<div>
	<div class="content-box">
		<h3><?php L('UsersManagement') ?></h3>
		<span id="add_new_user" class="command" onclick="f_show_form('<?php ln('user_get/0') ?>');"><?php L('AddUser') ?></span>

		<table id="table" class="main-table" width="100%">
			<thead>
			<tr>
				<th width="5%">ID</th>
				<th width="20%"><?php L('Login') ?></th>
				<th width="20%"><?php L('Mail') ?></th>
				<th width="20%"><?php L('Operations') ?></th>
			</tr>
			</thead>
			<tbody id="table-data">
			<?php $i = 0; if($users) foreach($users as &$row) { ?>
				<tr id="<?php eh("row".$row['id']); ?>" data-id=<?php eh($row['id']);?>>
					<td><?php eh($row['id']); ?></td>
					<td><?php eh($row['login']); ?></td>
					<td><?php eh($row['mail']); ?></td>
					<td>
						<span class="command" onclick="f_show_form('<?php ln('user_get/'.$row['id']) ?>');"><?php L('Edit') ?></span>
						<?php if($row['flags'] & 0x0001) { ?>
						<span class="command" onclick="f_activate_user(event);"><?php L('Activate') ?></span>
						<?php } else { ?>
						<span class="command" onclick="f_deactivate_user(event);"><?php L('Deactivate') ?></span>
						<?php } ?>
					</td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
	</div>
</div>
		<br />
		<br />

<?php include(TEMPLATES_DIR.'tpl.universal-form.php'); ?>
<?php include(TEMPLATES_DIR.'tpl.footer.php'); ?>
