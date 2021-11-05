<?php if(!defined('Z_PROTECTED')) exit; ?>
		<div id="message-box" class="modal-container" style="display: none">
			<span class="close white" onclick="this.parentNode.style.display='none'">&times;</span>
			<div class="modal-content">
				<h3 id="message-text"></h3>
				<br />
				<center><button class="button-accept" type="button" onclick="this.parentNode.parentNode.parentNode.style.display='none'; window.location = window.location;">OK</button></center>
			</div>
		</div>
		<div id="loading" class="modal-container" style="display: none">
			<div class="modal-content">
				<h3><?php L('Loading') ?></h3>
			</div>
		</div>
<?php if(!defined('Z_HIDE_COPYRIGHT')) { ?>
		<div id="codedby"><small>Coded by Dmitry V. Zimin. Project on <a href="https://github.com/pfzim/ldap-phonebook">GitHub</a></small></div>
<?php } ?>
	</body>
</html>
