<?php include("tpl.header.php"); ?>
		<script>
			map = <?php eh($id);?>;
			map_count = <?php eh(PB_MAPS_COUNT);?>;
		</script>
		<h3 align="center">Map<?php for($i = 1; $i <= PB_MAPS_COUNT; $i++) { ?>&nbsp;<a href="?action=map&amp;id=<?php eh($i);?>"><?php eh($i);?></a><?php } ?></h3>
		<div style="position: relative;">
				<img id="map-image" src="templ/map<?php eh($id);?>.png" style="left: 0px; top: 0px;"/>
		<?php $i = 0; foreach($db->data as $row) { $i++; ?>
				<img id="<?php eh('u'.$row[0]);?>" src="templ/marker-static.png" data-id=<?php eh($row[0]);?> data-name="<?php eh($row[2].' '.$row[3]); ?>" data-position="<?php eh($row[6]); ?>" data-phone="<?php eh($row[7]); ?>" data-photo=<?php eh($row[10]); ?> style="position: absolute; <?php eh('left: '.($row[12]-16).'px; top: '.($row[13]-22).'px');?>" onmouseenter="si(event)" onmouseleave="document.getElementById('popup').style.display='none'" onmousemove="mi(event);" onmousedown="f_drag(event);" ondragstart="return false;"/>
		<?php } ?>
		</div>
		<div id="popup" class="tooltip-user" style="display: none;">
			<img id="u_photo"/>
			<span id="u_name" class="boldtext"></span><br />
			<span id="u_position"></span><br />
			tel.&nbsp;<span id="u_phone"></span>
		</div>
<?php include("tpl.footer.php"); ?>
