<?php include(TEMPLATES_DIR.'tpl.header.php'); ?>
		<h3 align="center"><?php L('Phonebook') ?></h3>

		<form id="search_form" action="<?php ln('handshakes/0'); ?>" method="get" onsubmit="return f_search(this);">
			<?php L('Find') ?>: <input type="text" id="search" class="form-field" placeholder="<?php L('Search') ?>..." value="<?php if(isset($search)) eh($search); ?>">
			<input class="button-other" type="submit" value="<?php L('Search') ?>" /><br />
		</form>

		<table id="table" class="main-table">
			<thead>
			<tr>
				<th width="20%"><?php L('Date') ?></th>
				<th width="20%"><?php L('User') ?></th>
				<th width="20%"><?php L('Computer') ?></th>
				<th width="20%"><?php L('IP') ?></th>
			</tr>
			</thead>
			<tbody id="table-data">
		<?php $i = 0; foreach($handshakes as &$row) { $i++; ?>
			<tr id="<?php eh("row".$row[0]);?>" data-id=<?php eh($row[0]);?>>
				<td><?php eh($row[1]); ?></td>
				<td><?php eh($row[2]); ?></td>
				<td><a href="msraurl:<?php eh($row[3]); ?>"><?php eh($row[3]); ?></a></td>
				<td><?php eh($row[4]); ?></td>
			</tr>
		<?php } ?>
			</tbody>
		</table>

		<a class="page-number<?php if($offset == 0) eh(' boldtext'); ?>" href="<?php ln('handshakes/0/'.urlencode($search)); ?>">1</a>
		<?php 
			$min = max(100, $offset - 1000);
			$max = min($offset + 1000, $total - ($total % 100));

			if($min > 100) { echo '&nbsp;...&nbsp;'; }

			for($i = $min; $i <= $max; $i += 100)
			{
			?>
				<a class="page-number<?php if($offset == $i) eh(' boldtext'); ?>" href="<?php ln('handshakes/'.$i.'/'.urlencode($search)); ?>"><?php eh($i/100 + 1); ?></a>
			<?php
			}

			$max = $total - ($total % 100);
			if($i < $max)
			{
			?>
				&nbsp;...&nbsp;<a class="page-number<?php if($offset == $max) eh(' boldtext'); ?>" href="<?php ln('handshakes/'.$max.'/'.urlencode($search)); ?>"><?php eh($max/100 + 1); ?></a>
			<?php
			}
		?>

		<br />
		<br />

<?php include(TEMPLATES_DIR.'tpl.menu-contact.php'); ?>
<?php include(TEMPLATES_DIR.'tpl.footer.php'); ?>
