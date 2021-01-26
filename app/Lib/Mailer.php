<?php
/**
 * @author      Raiza Rhamdan (Leonardo DaVchezt) <davchezt@gmail.com>
 * @copyright   Copyright (c), 2014 Raiza Rhamdan
 * @license		MIT public license
 */

namespace app\Lib;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

use app\Lib\R;

class Mailer
{

	/**
	 * This method sends a email
	 *
	 * @param	string	$fro	 	Email address origin 
	 * @param	string	$to			Email addess destination
	 * @param	string	$name		Name of addressee
	 * @param	string	$subject	Topic of message
	 * @param	string	$html		Message HTML
	 * @param	string	$text		Message simple
	 *
	 * @return	boolean
	 */
	public static function send($to, $name, $subject, $html, $text)
	{
		$mail = new PHPMailer(true);						// Passing `true` enables exceptions
        $config = R::get('config')['mail'];

		//Server settings
		$mail->SMTPDebug	=	0;							// Enable verbose debug output
		$mail->isSMTP();									// Set mailer to use SMTP
		$mail->Host			=	$config['host'];			// Specify main and backup SMTP servers
		$mail->Username		=	$config['user'];			// SMTP username
		$mail->Password		=	$config['pass'];			// SMTP password
		$mail->SMTPAuth		=	true;						// Enable SMTP authentication
		$mail->SMTPSecure	=	'tls';						// Enable TLS encryption, `ssl` also accepted
		$mail->Port			=	587;						// TCP port to connect to

		//Recipients
		$mail->AddReplyTo($config['user'], $config['name']);// Add a "Reply-To" address (Optional)
		$mail->SetFrom($config['user'], $config['name']);
		$mail->AddAddress($to, $name);						// Add a recipient
		$mail->addBCC($config['user']);						// Add a "BCC" address (Optional)

		//Content
		$mail->isHTML(true);								// Set email format to HTML
		$mail->Subject		=	$subject;
		$mail->Body			=	$html;
		$mail->AltBody		=	$text;
		$mail->CharSet		=	'UTF-8';

		if (filter_var($to, FILTER_VALIDATE_EMAIL) !== false) {
			$result = $mail->send();
		} else {
			return false;
		}

		if ($result) {
			return true;
		} else {
			return false;
		}
	}

}