<?php if(!defined("Z_PROTECTED")) exit; ?>
<!XML>
<phonebook>
	<?php $i = 0; if($db->data !== FALSE) foreach($db->data as $row) { $i++; ?>
	<contact>
		<name><?php eh($row[0]); ?></name>
	</contact>
	<?php } ?>
</phonebook>