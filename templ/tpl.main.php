<?php include("tpl.header.php"); ?>
		<h3 align="center">LDAP phonebook</h3>
		<table id="table" class="main-table">
			<thead>
			<tr>
				<th width="20%">Name</th>
				<th width="10%">Phone</th>
				<th width="10%">Mobile</th>
				<th width="25%">E-Mail</th>
				<th width="10%">Position</th>
				<th width="10%">Department</th>
				<?php if($uid) { ?>
				<th width="5%">Op</th>
				<?php } ?>
			</tr>
			</thead>
			<tbody>
		<?php $i = 0; if($res !== FALSE) foreach($res as $row) { $i++; ?>
			<tr id="<?php eh("row".$row[0]);?>">
				<td><?php eh($row[2].' '.$row[3]); ?></td>
				<td><?php eh($row[7]); ?></td>
				<td><?php eh($row[8]); ?></td>
				<td><a href="mailto:<?php eh($row[9]); ?>"><?php eh($row[9]); ?></a></td>
				<td><?php eh($row[6]); ?></td>
				<td><?php eh($row[4]); ?></td>
				<?php if($uid) { ?>
				<td class="command">Hide</td>
				<?php } ?>
			</tr>
		<?php } ?>
			</tbody>
		</table>		
<?php include("tpl.footer.php"); ?>
