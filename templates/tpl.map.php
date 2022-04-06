<?php include(TEMPLATES_DIR.'tpl.header.php'); ?>
		<script>
			map = <?php eh(intval($map_id));?>;
			map_count = <?php eh(intval(PB_MAPS_COUNT));?>;
		</script>
		<h3 align="center">Map<?php for($i = 1; $i <= PB_MAPS_COUNT; $i++) { ?>&nbsp;<a href="<?php ln('map/'.$i) ?>"><?php eh(empty($map_names[$i-1])?$i:$map_names[$i-1]);?></a><?php } ?></h3>
		<div style="position: relative;">
				<img id="map-image-drag" src="<?php ls('templates/map'.$map_id.'.png') ?>" style="left: 0px; top: 0px;"/>
		<?php $i = 0; foreach($contacts as &$row) { $i++; ?>
				<img id="<?php eh('u'.$row['id']);?>" class="mm" src="<?php ls('templates/marker-static-'.$row['type'].'.png') ?>" data-id=<?php eh($row['id']);?> data-name="<?php eh($row['first_name'].' '.$row['last_name']); ?>" data-position="<?php eh($row['position']); ?>" data-phone="<?php eh($row['phone_internal']); ?>" data-flags="<?php eh($row['flags']); ?>" style="position: absolute; <?php eh('left: '.($row['x']-16).'px; top: '.($row['y']-22).'px');?>" onmouseenter="si(event)" onmouseleave="document.getElementById('popup').style.display='none'" onmousemove="mi(event);" onmousedown="f_drag(event);" ondragstart="return false;"/>
		<?php } ?>
		</div>
		<div id="popup" class="tooltip-user" style="display: none;">
			<img id="u_photo"/>
			<span id="u_name" class="boldtext"></span><br />
			<span id="u_position"></span><br />
			tel.&nbsp;<span id="u_phone"></span>
		</div>

<?php include(TEMPLATES_DIR.'tpl.universal-form.php'); ?>
<?php include(TEMPLATES_DIR.'tpl.form-upload.php'); ?>
<?php include(TEMPLATES_DIR.'tpl.map-container.php'); ?>
<?php include(TEMPLATES_DIR.'tpl.menu-contact.php'); ?>

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
<?php include(TEMPLATES_DIR.'tpl.footer.php'); ?>

