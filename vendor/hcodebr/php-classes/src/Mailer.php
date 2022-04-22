<?php  
namespace Hcode;

use \Rain\Tpl;

class Mailer
{
	const USERNAME = "edumeru46@gmail.com";
	const PASSWORD = "eduycran17171_$";
	const NAME_FROM = "Eduweb Store";

	private $mail;

	//--Direccion a envíar el Mail, Destinatario, Asunto, Templete y Datos--// 
	public function __construct($toAddress, $toName, $subject, $tplName, $datos = array())
	{

		$config = array(
		    "base_url"      => null,
		    "tpl_dir"       => $_SERVER['DOCUMENT_ROOT']."/views/email/",
		    "cache_dir"     => $_SERVER['DOCUMENT_ROOT']."/views-cache/",
		    "debug"         => false
		);

		Tpl::configure( $config );

		$tpl = new Tpl();

		//--Pasamos los datos al tpl--//
		foreach ($datos as $key => $value) 
		{
			$tpl->assign($key, $value);
		}
		$html = $tpl->draw($tplName, true);


		//Create a new PHPMailer instance
		$this->mail = new \PHPMailer();

		//--Till PHPMailer() to user SMTP()--//
		$this->mail->isSMTP();

		//--Enable SMTP debugging--//
		//--(0)SMTP::DEBUG_OFF = off (for production use)
		//--(1)SMTP::DEBUG_CLIENT = client messages
		//--(2)SMTP::DEBUG_SERVER = client and server messages
		//mail->SMTPDebug = SMTP::DEBUG_SERVER;
		$this->mail->SMTPDebug = 0;

		//--Ask for HTNL-friendly debug output--//
		$this->mail->Debugoutput = 'html';

		//Set the hostname of the mail server
		$this->mail->Host = 'smtp.gmail.com';
		//Use `$mail->Host = gethostbyname('smtp.gmail.com');`
		//if your network does not support SMTP over IPv6,
		//though this may cause issues with TLS

		//Set the SMTP port number:
		// - 465 for SMTP with implicit TLS, a.k.a. RFC8314 SMTPS or
		// - 587 for SMTP+STARTTLS
		$this->mail->Port = 587;

		//Set the encryption mechanism to use:
		// - SMTPS (implicit TLS on port 465) or
		// - STARTTLS (explicit TLS on port 587)
		//$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
		$this->mail->SMTPSecure = 'tls';


		//Whether to use SMTP authentication
		$this->mail->SMTPAuth = true;

		//Username to use for SMTP authentication - use full email address for gmail
		//$mail->Username = 'username@gmail.com';
		$this->mail->Username = Mailer::USERNAME;

		//Password to use for SMTP authentication
		//$mail->Password = 'yourpassword';
		$this->mail->Password = Mailer::PASSWORD;

		//Set who the message is to be sent from
		//Note that with gmail you can only use your account address (same as `Username`)
		//or predefined aliases that you have configured within your account.
		//Do not use user-submitted addresses in here
		$this->mail->setFrom(Mailer::USERNAME, Mailer::NAME_FROM);

		//Set an alternative reply-to address
		//This is a good place to put user-submitted addresses
		//$mail->addReplyTo('replyto@example.com', 'First Last');

		//Set who the message is to be sent to
		$this->mail->addAddress($toAddress, $toName);

		//Set the subject line
		$this->mail->Subject = $subject;

		//Read an HTML message body from an external file, convert referenced images to embedded,
		//convert HTML into a basic plain-text alternative body
		$this->mail->msgHTML($html);;

		//Replace the plain text body with one created manually
		$this->mail->AltBody = 'This is a plain-text message body';

		//Attach an image file
		//$mail->addAttachment('images/phpmailer_mini.png');

	} 

	public function sent()
	{
		//if (!$this->mail->send())
		//{
		//	echo 'Error No Se Pudo Envíar :' .$this->$mail->ErrorInfo;
		//}
		//else
		//{
			return $this->mail->send();
		//}	

	}

}

?>