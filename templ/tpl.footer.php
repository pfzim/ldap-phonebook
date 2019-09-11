<?php require_once '/language/'.LANGUAGES.'.php'; ?>

<?php if(!defined("Z_PROTECTED")) exit; ?>
		<div id="loading" class="modal-container" style="display: none">
			<div class="modal-content">
				<h3><?php eh($lang["footerLoading"]) ?></h3>
			</div>
		</div>
		<div id="codedby"><small>Coded by Dmitry V. Zimin. Project on <a href="https://github.com/pfzim/ldap-phonebook">GitHub</a></small></div>		
	</body>
</html>
