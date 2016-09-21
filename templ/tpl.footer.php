<?php if(!defined("Z_PROTECTED")) exit; ?>
		<div id="popup" class="popup">
			<a class="close" href = "#" onclick = "gi('popup').style.display='none';gi('fade').style.display='none'">Close</a>
			<h3 id="caption" align="center">Message here</h3>			
			<div id="status">Message here</div>			
		</div>
		<div id="fade" class="black_overlay"></div>

		<?php if($uid) { ?>
		<div id="codedby"><small>Coded by Dmitry V. Zimin. Project on <a href="https://github.com/pfzim/ldap-phonebook">GitHub</a></small></div>		
		<?php } ?>
	</body>
</html>
