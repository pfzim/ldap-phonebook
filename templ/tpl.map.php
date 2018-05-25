<?php include("tpl.header.php"); ?>
		<script>
			map = <?php eh(intval($id));?>;
			map_count = <?php eh(intval(PB_MAPS_COUNT));?>;
		</script>
		<h3 align="center">Map<?php for($i = 1; $i <= PB_MAPS_COUNT; $i++) { ?>&nbsp;<a href="?action=map&amp;id=<?php eh($i);?>"><?php eh(empty($map_names[$i-1])?$i:$map_names[$i-1]);?></a><?php } ?></h3>
		<div style="position: relative;">
				<img id="map-image-grag" src="templ/map<?php eh($id);?>.png" style="left: 0px; top: 0px;"/>
		<?php $i = 0; foreach($db->data as &$row) { $i++; ?>
				<img id="<?php eh('u'.$row[0]);?>" src="templ/marker-static-<?php eh($row[14]);?>.png" data-id=<?php eh($row[0]);?> data-name="<?php eh($row[2].' '.$row[3]); ?>" data-position="<?php eh($row[6]); ?>" data-phone="<?php eh($row[7]); ?>" data-photo=<?php eh($row[10]); ?> style="position: absolute; <?php eh('left: '.($row[12]-16).'px; top: '.($row[13]-22).'px');?>" onmouseenter="si(event)" onmouseleave="document.getElementById('popup').style.display='none'" onmousemove="mi(event);" onmousedown="f_drag(event);" ondragstart="return false;"/>
		<?php } ?>
		</div>
		<div id="popup" class="tooltip-user" style="display: none;">
			<img id="u_photo"/>
			<span id="u_name" class="boldtext"></span><br />
			<span id="u_position"></span><br />
			tel.&nbsp;<span id="u_phone"></span>
		</div>

		<div id="map-container" class="modal-container" style="display:none">
			<span class="close" onclick="this.parentNode.style.display='none'">&times;</span>
			<img id="map-image" class="map-image" src="templ/map1.png"/>
			<img id="map-marker" class="map-marker" src="templ/marker.gif"/>
		</div>
		<form method="post" id="form-file-upload" name="form-file-upload">
			<input id="file-upload" type="file" name="file" style="display: none"/>
		</form>

		<div id="contact-menu" class="contact-menu" data-id="0">
			<ul>
				<?php for($i = 1; $i <= PB_MAPS_COUNT; $i++) { ?>
					<li><a href="#" data-map="<?php eh($i); ?>" onclick="f_map_set(event); return false;">Locate map&nbsp;<?php eh($i); ?></a></li>
				<?php } ?>
				<li><a href="#" onclick="f_get_acs_location(event);">Query ACS</a></li>
				<li><a id="menu-cmd-edit" href="#" onclick="f_edit(event, 'contact'); return false;">Edit</a></li>
				<li><a id="menu-cmd-delete" href="#" onclick="f_delete(event); return false;">Delete</a></li>
				<li><a id="menu-cmd-photo" href="#" onclick="f_photo(event); return false;">Upload photo</a></li>
				<li><a id="menu-cmd-show" href="#" onclick="f_show(event); return false;">Show</a></li>
				<li><a id="menu-cmd-hide" href="#" onclick="f_hide(event); return false;">Hide</a></li>
				<li><a id="menu-cmd-connect-0" href="#">Connect to &lt;comp_name&gt;</a></li>
				<li><a id="menu-cmd-connect-1" href="#">Connect to &lt;comp_name&gt;</a></li>
				<li><a id="menu-cmd-connect-2" href="#">Connect to &lt;comp_name&gt;</a></li>
			</ul>
		</div>

		<script>
			document.addEventListener('contextmenu',function(e) {
            f_notify("You've tried to open context menu"+e.target.className, 'success'); //here you draw your own menu
            e.preventDefault();
        }, false);
		</script>
<?php include("tpl.footer.php"); ?>
