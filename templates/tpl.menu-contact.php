<?php if(!defined('Z_PROTECTED')) exit; ?>
		<div id="contact-menu" class="contact-menu" data-id="0">
			<ul>
				<?php for($i = 1; $i <= PB_MAPS_COUNT; $i++) { ?>
					<li><a href="#" data-map="<?php eh($i); ?>" onclick="f_map_set(event); return false;"><?php L('ContactLocate') ?>&nbsp;<?php eh(empty($map_names[$i-1])?$i:$map_names[$i-1]);?></a></li>
				<?php } ?>
				<hr></hr>
				<li><a href="#" onclick="f_map_unset(event); return false;"><?php L('ContactUnsetLocation') ?></a></li>
				<!-- <li><a href="#" onclick="f_get_acs_location(event);">Query ACS</a></li> -->
				<li><a id="menu-cmd-edit" href="#" onclick="f_edit(event); return false;"><?php L('ContactEdit') ?></a></li>
				<li><a id="menu-cmd-delete" href="#" onclick="f_delete(event); return false;"><?php L('ContactDelete') ?></a></li>
				<li><a id="menu-cmd-photo" href="#" onclick="f_photo(event); return false;"><?php L('ContactUploadPhoto') ?></a></li>
				<li><a id="menu-cmd-delete-photo" href="#" onclick="f_delete_photo(event); return false;"><?php L('ContactDeletePhoto') ?></a></li>
				<li><a id="menu-cmd-show" href="#" onclick="f_show2(event); return false;"><?php L('ContactShow') ?></a></li>
				<li><a id="menu-cmd-hide" href="#" onclick="f_hide2(event); return false;"><?php L('ContactHide') ?></a></li>
				<li><a id="menu-cmd-connect-0" href="#">Connect to &lt;comp_name&gt;</a></li>
				<li><a id="menu-cmd-connect-1" href="#">Connect to &lt;comp_name&gt;</a></li>
				<li><a id="menu-cmd-connect-2" href="#">Connect to &lt;comp_name&gt;</a></li>
				<li><a id="menu-loading" href="#" onclick="return false;"><?php L('ContactLoading') ?></a></li>
			</ul>
		</div>

