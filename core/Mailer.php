<?php

namespace Core;

use PHPMailer\PHPMailer\PHPMailer;
// use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Mailer
{
   private $host     = "smtp.gmail.com"; // sets GMAIL as the SMTP server
   private $port     = 587; // 587;->gmail //465 para ssl // set the SMTP port for the GMAIL server

   private $username = "santiagoruizeltiempo@gmail.com"; // GMAIL username
   private $password = "Santiago2020*"; // GMAIL password

   private $mail;

   function __construct()
   {

      // $mail = new PHPMailer(true);
      // $mail->SMTPDebug = SMTP::DEBUG_SERVER;
      $mail = new PHPMailer();
      $mail->isSMTP();
      $mail->Host       = $this->host;                    // Set the SMTP server to send through
      $mail->Username   = $this->username;                     // SMTP username
      $mail->Password   = $this->password;                               // SMTP password
      $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
      $mail->SMTPAuth   = true;    // Enable SMTP authentication 
      $mail->Port       = $this->port;                                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above
      $mail->isHTML(true);    
      $this->mail = $mail;                              // Set email format to HTML
   }

   public function setFrom($address, $name = '', $auto = true)
   {
      $this->mail->setFrom($address, $name, $auto);
   }

   public function addAddress($address, $name = '')
   {
      $this->mail->addAddress($address, $name);
   }

   public function addCC($address, $name = '')
   {
      $this->mail->addCC($address, $name);
   }

   public function addBCC($address, $name = '')
   {
      $this->mail->addBCC($address, $name);
   }

   public function addReplyTo($address, $name = '')
   {
      $this->mail->addReplyTo($address, $name);
   }

   public function addAttachment($path, $name = '', $encoding = PHPMailer::ENCODING_BASE64, $type = '', $disposition = 'attachment')
   {
      $this->mail->addAttachment($path, $name, $encoding, $type, $disposition);
   }

   public function setSubject($subject = '')
   {
      $this->mail->Subject = $subject;
   }

   public function setBody($body = '')
   {
      $this->mail->Body = $body;
   }

   public function setAltBody($altBody = '')
   {
      $this->mail->AltBody = $altBody;
   }

   public function send()
   {
      try {
         return $this->mail->send();
      } catch (Exception $e) {
         if(getenv('DEBUG', false)){
            echo "Message could not be sent. Mailer Error: {$this->mail->ErrorInfo}";
         }
         return false;
      }
   }

   public function addEmbeddedImage($filename, $name = 'firma')
   {
      $this->mail->addEmbeddedImage($filename, $name);
   }
}
