<?php if(!defined("Z_PROTECTED")) exit; ?>
<!XML>
<phonebook>
	<?php $i = 0; if($db->data !== FALSE) foreach($db->data as $row) { $i++; ?>
	<contact>
		<name><?php eh($row[0]); ?></name>
		<phone1><?php eh($row[1]); ?></phone1>
		<phone2><?php eh($row[2]); ?></phone2>
	</contact>
	<?php } ?>
</phonebook>
