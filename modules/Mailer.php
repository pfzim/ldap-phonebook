<?php
/*
    Mail class - PHPMailer.
    Copyright (C) 2021 Dmitry V. Zimin

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once ROOT_DIR.'libs/PHPMailer/src/Exception.php';
require_once ROOT_DIR.'libs/PHPMailer/src/PHPMailer.php';
require_once ROOT_DIR.'libs/PHPMailer/src/SMTP.php';

class Mailer
{
	private $core = NULL;

	function __construct(&$core)
	{
		$this->core = &$core;
	}

	public function send_mail($to, $subject, $html, $plain)
	{
		//require_once 'libs/PHPMailer/PHPMailerAutoload.php';
		//require_once(ROOT_DIR.DIRECTORY_SEPARATOR.'libs/PHPMailer/class.phpmailer.php');
		//require_once(ROOT_DIR.DIRECTORY_SEPARATOR.'libs/PHPMailer/class.smtp.php');
		//require_once(ROOT_DIR.'libs/PHPMailer/PHPMailer.php');

		$mail = new PHPMailer;

		$mail->isSMTP();
		$mail->Host = MAIL_HOST;
		$mail->SMTPAuth = MAIL_AUTH;
		if(MAIL_AUTH)
		{
			$mail->Username = MAIL_LOGIN;
			$mail->Password = MAIL_PASSWD;
		}

		$mail->SMTPOptions = array(
			'ssl' => array(
				'verify_peer' => MAIL_VERIFY_PEER,
				'verify_peer_name' => MAIL_VERIFY_PEER_NAME,
				'allow_self_signed' => MAIL_ALLOW_SELF_SIGNED
			)
		);

		$mail->SMTPSecure = MAIL_SECURE;
		$mail->Port = MAIL_PORT;

		$mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
		foreach($to as &$address)
		{
			$mail->addAddress($address, $address);
		}
		//$mail->addReplyTo('helpdesk@example.com', 'Information');

		$mail->isHTML(true);

		$mail->Subject = $subject;
		$mail->Body    = $html;
		$mail->AltBody = $plain;
		//$mail->ContentType = 'text/html; charset=utf-8';
		$mail->CharSet = 'UTF-8';
		//$mail->SMTPDebug = 4;

		return $mail->send();
	}
}
