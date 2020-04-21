<?php

namespace Core;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use Core\Database;
use Controller\NotificationController;
class Mailer
{
   private $db;

   private $host; // sets GMAIL as the SMTP server
   private $username; // GMAIL username
   private $password;
   private $port; // 587;
   private $secure ;
   private $debug;
   private $hash;

   private $mail;
   private $emailFrom;
   private $nameFrom;
   private $ccAddresses = [];
   private $bccAddresses = [];

   private function init(){
      $this->host     = getenv('SMTP_HOST', ''); // sets GMAIL as the SMTP server
      $this->username = getenv('SMTP_USERNAME', ''); // GMAIL username
      $this->password = getenv('SMTP_PASSWORD', '');
      $this->port     = getenv('SMTP_PORT', ''); // 587;
      $this->secure   = getenv('SMTP_ENCRYPTION', 'tls');
      $this->debug    = boolval(getenv('DEBUG', false));
   }

   function __construct() {

      $this->init();
      if($this->debug){
         $mail = new PHPMailer(true);
         $mail->SMTPDebug = SMTP::DEBUG_SERVER;
      } else {
         $mail = new PHPMailer();
      }
      // 
      $mail->isSMTP();
      $mail->Host       = $this->host;                    // Set the SMTP server to send through
      $mail->Username   = $this->username;                     // SMTP username
      $mail->Password   = $this->password;                               // SMTP password        // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
      $mail->SMTPSecure = $this->secure;
      $mail->SMTPAuth   = true;    // Enable SMTP authentication 
      $mail->Port       = $this->port;                                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above
      $mail->isHTML(true);    
      $mail->CharSet = 'UTF-8';
      $this->mail = $mail;      
      $this->db = Database::getInstance();                        // Set email format to HTML
   }

   public function setFrom($address, $name = '', $auto = true) {
      $this->emailFrom = $address;
      $this->nameFrom = $name;
      $this->mail->setFrom($address, $name, $auto);
   }

   public function addAddress($address, $name = '') {
      $this->mail->addAddress($address, $name);
   }

   public function addCC($address, $name = '') {
      $this->ccAddresses[] = $address;
      $this->mail->addCC($address, $name);
   }

   public function addBCC($address, $name = '') {
      $this->bccAddresses[] = $address;
      $this->mail->addBCC($address, $name);
   }

   public function addReplyTo($address, $name = '') {
      $this->mail->addReplyTo($address, $name);
   }

   public function addCustomHeader($name, $value) {
      $this->mail->addCustomHeader($name, $value);
   }

   public function addAttachment($path, $name = '', $encoding = PHPMailer::ENCODING_BASE64, $type = '', $disposition = 'attachment') {
      $this->mail->addAttachment($path, $name, $encoding, $type, $disposition);
   }

   public function setSubject($subject = '') {
      $this->mail->Subject = $subject;
   }

   public function setBody($body = '') {
      $this->mail->Body = $body;
   }

   public function setAltBody($altBody = '') {
      $this->mail->AltBody = $altBody;
   }

   private function setHash(){
      $hash=sha1(strval(time()));
      $traceUserId = "<span id='[[HASH:--{$hash}--]]' style='color: white !important'></span>";
      $this->mail->Body .= $traceUserId;
      $this->hash = $hash;
   }

   public function send() {

      try {
         $this->setHash();
         return $this->mail->send();
      } catch (Exception $e) {
         if($this->debug){
            echo "Message could not be sent. Mailer Error: {$this->mail->ErrorInfo}";
         }
         return false;
      }
   }

   private function saveAttachments($folder, $id){
      $attachments = $this->mail->getAttachments();
      $strSQLArray=[];
      foreach($attachments as $attachment){
         $tempFileLocation = $attachment[0];
         $fileName = $attachment[2];
         $ext = explode('.', $fileName);
         $ext = count($ext) > 1 ? end($ext) : '';
         $saveName = time() . '.' . $ext;
         $saveAs = "{$_SERVER['DOCUMENT_ROOT']}/../{$folder}/{$saveName}";
         $saved = move_uploaded_file($tempFileLocation, $saveAs);
         if($saved){
            $strSQLArray[] = "INSERT INTO email_attachments (`email_id`, `name`, `save_name`) VALUES ('{$id}', '{$fileName}', '{$saveName}')";
         }
      }

      if(count($strSQLArray)){
         $strSQL = implode(';', $strSQLArray);
         $this->db->query($strSQL)->execute();
      }
   }

   public function save($folder = 'uploads/attachments'){

      $data['user_id'] = $_SESSION['USER_ID'];
      $data['token'] = $this->hash;
      $data['subject'] = $this->mail->Subject;
      $data['message'] = addslashes($this->mail->Body);
      $data['name'] = $this->nameFrom;
      $data['from'] = $this->emailFrom;
      $data['to'] = implode(',', array_keys($this->mail->getAllRecipientAddresses()));
      $data['cc'] = implode(',', $this->ccAddresses);
      $data['bcc'] = implode(',', $this->bccAddresses);
      $data['reply'] = $_POST['reply'];
      $data['sended'] = 1;
      $data['viewed'] = 1;
      $data['attachment'] = $this->mail->attachmentExists() ? 1 : 0;
      $data['message_id'] = $this->mail->getLastMessageID();

      $fields = [];
      $values = [];
      foreach(array_keys($data) as $value){
         $fields[] = "`{$value}`";
         $values[] = "'{$data[$value]}'";
      }

      $strSQL="INSERT INTO emails (" . implode(',', $fields) . ") VALUES (" . implode(',', $values) . ")";
      $this->db->query($strSQL)->execute();
      $id = $this->db->lastInsertId();
      $this->saveAttachments($folder, $id);
      NotificationController::send('Mensaje enviado!', 'Su mensaje ha sido enviado satisfactoriamente. Clic para ver mensaje.', '/' . $id, 'sent');
   }

   public function addEmbeddedImage($filename, $name = 'firma') {
      $this->mail->addEmbeddedImage($filename, $name);
   }
}
