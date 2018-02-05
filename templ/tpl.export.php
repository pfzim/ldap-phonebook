<?php if(!defined("Z_PROTECTED")) exit; ?><?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<phonebook>
<?php $i = 0; foreach($result as &$row) { $i++; ?>
	<contact>
		<name><?php eh($row[3].' '.$row[2]); ?></name>
		<phone1><?php eh($row[7]); ?></phone1>
		<phone2><?php eh($row[8]); ?></phone2>
		<phone3></phone3>
		<mail><?php eh($row[9]); ?></mail>
		<organisation><?php eh($row[5]); ?></organisation>
		<departament><?php eh($row[4]); ?></departament>
		<section></section>
		<position><?php eh($row[6]); ?></position>
	</contact>
<?php } ?>
</phonebook>
