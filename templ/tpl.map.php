<?php include(ABSPATH.'tpl.header.php'); ?>
		<script>
			map = <?php eh(intval($id));?>;
			map_count = <?php eh(intval(PB_MAPS_COUNT));?>;
		</script>
		<h3 align="center">Map<?php for($i = 1; $i <= PB_MAPS_COUNT; $i++) { ?>&nbsp;<a href="?action=map&amp;id=<?php eh($i);?>"><?php eh(empty($map_names[$i-1])?$i:$map_names[$i-1]);?></a><?php } ?></h3>
		<div style="position: relative;">
				<img id="map-image-drag" src="templ/map<?php eh($id);?>.png" style="left: 0px; top: 0px;"/>
		<?php $i = 0; foreach($db->data as &$row) { $i++; ?>
				<img id="<?php eh('u'.$row[0]);?>" class="mm" src="templ/marker-static-<?php eh($row[14]);?>.png" data-id=<?php eh($row[0]);?> data-name="<?php eh($row[2].' '.$row[3]); ?>" data-position="<?php eh($row[6]); ?>" data-phone="<?php eh($row[7]); ?>" data-photo=<?php eh($row[10]); ?> style="position: absolute; <?php eh('left: '.($row[12]-16).'px; top: '.($row[13]-22).'px');?>" onmouseenter="si(event)" onmouseleave="document.getElementById('popup').style.display='none'" onmousemove="mi(event);" onmousedown="f_drag(event);" ondragstart="return false;"/>
		<?php } ?>
		</div>
		<div id="popup" class="tooltip-user" style="display: none;">
			<img id="u_photo"/>
			<span id="u_name" class="boldtext"></span><br />
			<span id="u_position"></span><br />
			tel.&nbsp;<span id="u_phone"></span>
		</div>

<?php include(ABSPATH.'tpl.form-edit.php'); ?>
<?php include(ABSPATH.'tpl.form-upload.php'); ?>
<?php include(ABSPATH.'tpl.map-container.php'); ?>
<?php include(ABSPATH.'tpl.menu-contact.php'); ?>

		<script>
			document.addEventListener('contextmenu',function(ev) {
				var el_src = ev.target || ev.srcElement;
				//console.log(el_src.className);
				if(el_src.className == 'mm')
				{
					ev.preventDefault();
				}
			}, false);
		</script>
<?php include(ABSPATH.'tpl.footer.php'); ?>
