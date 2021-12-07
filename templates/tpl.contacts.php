<?php include(TEMPLATES_DIR.'tpl.header.php'); ?>
		<h3 align="center"><?php L('Phonebook') ?></h3>
		<div>
			<span><b><?php L('Brithdays') ?></b></span>
			<?php 
				if($birthdays) {
					foreach($birthdays as &$row) {?>
						<br><span><b><?php eh($row['DayMonth']); ?></b></span>&nbsp;<span><?php eh($row['last_name'].' '.$row['first_name'].' '.$row['middle_name']); ?></span>
			<?php 
					} 
				} else { ?>
					<span><?php L('NoBirthdays') ?></span>
			<?php 
				}?>
		</div>
		<div id="imgblock" class="user-photo"><img id="userphoto" src=""/></div>

		<form id="search_form" action="<?php ln($action.'/search'); ?>" method="get" onsubmit="return f_search(this);">
			<?php L('Find') ?>: <input type="text" id="search" class="form-field" placeholder="<?php L('Search') ?>..." value="<?php if(isset($search)) eh($search); ?>">
			<input class="button-other" type="submit" value="<?php L('Search') ?>" /><br />
		</form>

		<?php if($is_admin) { ?>
		<span class="command f-right" onclick="f_show_form('<?php ln('contact_edit/0') ?>');"><?php L('AddСontact') ?></span>
		<?php } ?>
		<table id="table" class="main-table">
			<thead>
			<tr>
				<?php $i = 0; ?>
				<?php if($is_admin) { ?>
				<th width="1%"><input type="checkbox" onclick="f_select_all(event)"/></th>
				<?php $i++; } ?>
				<th width="20%"><a href="<?php ln($action.'/sort/0/offset/'.$offset.'/search/'.urlencode($search)); ?>"><?php L('Name') ?></a><?php if($sort == 0) { echo ' &UpArrow;'; } ?></th>
				<th width="10%"><a href="<?php ln($action.'/sort/1/offset/'.$offset.'/search/'.urlencode($search)); ?>"><?php L('Phone') ?></a><?php if($sort == 1) { echo ' &UpArrow;'; } ?></th>
				<th width="10%"><a href="<?php ln($action.'/sort/2/offset/'.$offset.'/search/'.urlencode($search)); ?>"><?php L('PhoneCity') ?></a><?php if($sort == 2) { echo ' &UpArrow;'; } ?></th>
				<th width="10%"><a href="<?php ln($action.'/sort/3/offset/'.$offset.'/search/'.urlencode($search)); ?>"><?php L('Mobile') ?></a><?php if($sort == 3) { echo ' &UpArrow;'; } ?></th>
				<th width="15%"><a href="<?php ln($action.'/sort/4/offset/'.$offset.'/search/'.urlencode($search)); ?>"><?php L('Mail') ?></a><?php if($sort == 4) { echo ' &UpArrow;'; } ?></th>
				<th width="10%"><a href="<?php ln($action.'/sort/5/offset/'.$offset.'/search/'.urlencode($search)); ?>"><?php L('Position') ?></a><?php if($sort == 5) { echo ' &UpArrow;'; } ?></th>
				<th width="10%"><a href="<?php ln($action.'/sort/6/offset/'.$offset.'/search/'.urlencode($search)); ?>"><?php L('Department') ?></a><?php if($sort == 6) { echo ' &UpArrow;'; } ?></th>
				<?php if($is_admin) { ?>
				<th width="15%"><?php L('Operations') ?></th>
				<?php } ?>
			</tr>
			</thead>
			<tbody id="table-data">
		<?php $i = 0; foreach($phones as &$row) { $i++; ?>
			<tr id="<?php eh("row".$row['id']);?>" data-id=<?php eh($row['id']);?> data-map=<?php eh($row['map']); ?> data-x=<?php eh($row['x']); ?> data-y=<?php eh($row['y']); ?> data-flags=<?php eh($row['flags']); ?>>
				<?php if($is_admin) { ?>
				<td><input type="checkbox" name="check" value="<?php eh($row['id']); ?>"/></td>
				<?php } ?>
				<td id="<?php eh("nameCell".$row['id']);?>" onclick="f_sw_map(event);" onmouseenter="f_sw_img(event);" onmouseleave="gi('imgblock').style.display = 'none'" onmousemove="f_mv_img(event);" style="cursor: pointer;" class="<?php if(intval($row['flags']) & PB_CONTACT_WITH_PHOTO) { eh('userwithphoto'); } ?>"><?php eh($row['last_name'].' '.$row['first_name'].' '.$row['middle_name']); ?></td>
				<td id="<?php eh("pintCell".$row['id']);?>"><?php eh($row['phone_internal']); ?></td>
				<td id="<?php eh("pcityCell".$row['id']);?>"><?php eh($row['phone_external']); ?></td>
				<td id="<?php eh("pcellCell".$row['id']);?>"><?php eh($row['phone_mobile']); ?></td>
				<td id="<?php eh("mailCell".$row['id']);?>"><a href="mailto:<?php eh($row['mail']); ?>"><?php eh($row['mail']); ?></a></td>
				<td id="<?php eh("posCell".$row['id']);?>"><?php eh($row['position']); ?></td>
				<td id="<?php eh("depCell".$row['id']);?>"><?php eh($row['department']); ?></td>
				<?php if($is_admin) { ?>
				<td id="<?php eh("mainMenuCell".$row['id']);?>">
					<span class="command" onclick="f_menu(event);"><?php L('Menu') ?></span>
				</td>
				<?php } ?>
			</tr>
		<?php } ?>
			</tbody>
		</table>
		
		<?php if($is_admin) { ?>
		<form id="contacts" method="post" action="<?php ln('contacts_export_selected'); ?>">
			<input id="list" type="hidden" name="list" value="" />
		</form>
		<a href="#" onclick="f_export_selected(event); return false;"><?php L('ExportSelected') ?></a>
		<?php } ?>

		<br />

		<a class="page-number<?php if($offset == 0) eh(' boldtext'); ?>" href="<?php ln($action.'/offset/0/search/'.urlencode($search)); ?>">1</a>
		<?php 
			$min = max(100, $offset - 1000);
			$max = min($offset + 1000, $total - ($total % 100));

			if($min > 100) { echo '&nbsp;...&nbsp;'; }

			for($i = $min; $i <= $max; $i += 100)
			{
			?>
				<a class="page-number<?php if($offset == $i) eh(' boldtext'); ?>" href="<?php ln($action.'/offset/'.$i.'/search/'.urlencode($search)); ?>"><?php eh($i/100 + 1); ?></a>
			<?php
			}

			$max = $total - ($total % 100);
			if($i < $max)
			{
			?>
				&nbsp;...&nbsp;<a class="page-number<?php if($offset == $max) eh(' boldtext'); ?>" href="<?php ln($action.'/offset/'.$max.'/search/'.urlencode($search)); ?>"><?php eh($max/100 + 1); ?></a>
			<?php
			}
		?>

		<br />

<?php include(TEMPLATES_DIR.'tpl.universal-form.php'); ?>
<?php include(TEMPLATES_DIR.'tpl.form-upload.php'); ?>
<?php include(TEMPLATES_DIR.'tpl.map-container.php'); ?>
<?php include(TEMPLATES_DIR.'tpl.menu-contact.php'); ?>
<?php include(TEMPLATES_DIR.'tpl.footer.php'); ?>

