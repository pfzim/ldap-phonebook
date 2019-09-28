<?php if(!defined('Z_PROTECTED')) exit; ?>
		<div id="contact-container" class="modal-container" style="display: none">
			<span class="close white" onclick="this.parentNode.style.display='none'">&times;</span>
			<div class="modal-content">
				<span class="close" onclick="this.parentNode.parentNode.style.display='none'">&times;</span>
				<form id="contact">
				<h3><?php eh($lang["formEditContact"]) ?></h3>
				<input name="id" type="hidden" value=""/>
				<div class="form-title"><label for="firstname"><?php eh($lang["formEditFirstName"]) ?></label></div>
				<input class="form-field" id="firstname" name="firstname" type="edit" value=""/>
				<div id="firstname-error" class="form-error"></div>

				<div class="form-title"><label for="lastname"><?php eh($lang["formEditLastName"]) ?></label></div>
				<input class="form-field" id="lastname" name="lastname" type="edit" value=""/>
				<div id="lastname-error" class="form-error"></div>

				<div class="form-title"><label for="company"><?php eh($lang["formEditCompany"]) ?></label></div>
				<input class="form-field" id="company" name="company" type="edit" value=""/>
				<div id="company-error" class="form-error"></div>

				<div class="form-title"><label for="department"><?php eh($lang["formEditDepartment"]) ?></label></div>
				<input class="form-field" id="department" name="department" type="edit" value=""/>
				<div id="department-error" class="form-error"></div>

				<div class="form-title"><label for="position"><?php eh($lang["formEditPosition"]) ?></label></div>
				<input class="form-field" id="position" name="position" type="edit" value=""/>
				<div id="position-error" class="form-error"></div>

				<div class="form-title"><label for="phone"><?php eh($lang["formEditPhone"]) ?></label></div>
				<input class="form-field" id="phone" name="phone" type="edit" value=""/>
				<div id="phone-error" class="form-error"></div>
				
				<div class="form-title"><label for="phonecity"><?php eh($lang["formEditPhoneCity"]) ?></label></div>
				<input class="form-field" id="phonecity" name="phonecity" type="edit" value=""/>
				<div id="phonecity-error" class="form-error"></div>

				<div class="form-title"><label for="mobile"><?php eh($lang["formEditMobile"]) ?></label></div>
				<input class="form-field" id="mobile" name="mobile" type="edit" value=""/>
				<div id="mobile-error" class="form-error"></div>

				<div class="form-title"><label for="mail"><?php eh($lang["formEditEMail"]) ?></label></div>
				<input class="form-field" id="mail" name="mail" type="edit" value=""/>
				<div id="mail-error" class="form-error"></div>

				<div class="form-title"><label for="bday"><?php eh($lang["formEditBirthday"]) ?></label></div>
				<input class="form-field" id="bday" name="bday" type="edit" value=""/>
				<div id="bday-error" class="form-error"></div>

				<div class="form-title"><?php eh($lang["formEditIcon"]) ?></div>
				<select class="form-field" name="type">
				<?php for($i = 0; $i < count($g_icons); $i++) { ?>
					<option value="<?php eh($i); ?>" <?php if($i == 0) { echo ' selected="selected"'; } ?>><?php eh($g_icons[$i]); ?></option>
				<?php } ?>
				</select>
				<div id="type-error" class="form-error"></div>

				</form>
				<div class="f-right">
					<button class="button-accept" type="button" onclick="f_save('contact');"><?php eh($lang["formEditSave"]) ?></button>
					&nbsp;
					<button class="button-decline" type="button" onclick="this.parentNode.parentNode.parentNode.style.display='none'"><?php eh($lang["formEditCancel"]) ?></button>
				</div>
			</div>
		</div>
