<?php if(!defined('Z_PROTECTED')) exit; ?>
<?php echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'; ?>

<phonebook>
<?php $i = 0; foreach($result as &$row) { $i++; ?>
	<contact>
<?php foreach($row as $key => $value) { ?>
		<<?php eh($key); ?>><?php eh($value); ?></<?php eh($key); ?>>
<?php } ?>
	</contact>
<?php } ?>
</phonebook>
