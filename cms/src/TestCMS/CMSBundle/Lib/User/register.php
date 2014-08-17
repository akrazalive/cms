<?php

require_once __DIR__ . "/../Email/class.phpmailer.php";
require_once __DIR__ . "/../common.php";


/**
 * Tries to send an email using PHPMailer.
 * 
 * @param unknown $from
 * @param unknown $reply_to
 * @param unknown $to
 */
function send_email($from, $reply_to, $to) {
  $result = array("error"=> "", "success"=> "");
  $mail = new PHPMailer();
  $params = get_gmail_params_from_config();
  
 
  $mail->IsSMTP();  // telling the class to use SMTP
  $mail->SMTPAuth   = true; // SMTP authentication
  $mail->Host       = $params["SMTP_server_address"]; // SMTP server
  $mail->Port       = $params["SMTP_port_TLS"]; // SMTP Port
  $mail->Username   = $params["SMTP_user_name"]; // SMTP account username
  $mail->Password   = $params["SMTP_password"];        // SMTP account password
  
  $mail->SetFrom($from);
  $mail->AddReplyTo($reply_to);
  
  $mail->AddAddress($to); // recipient email
  
  $mail->Subject    = "Thanks for registering"; // email subject
  $mail->Body       = "Registering message here";
  
  if(!$mail->Send()) {
    $result["error"] .= "Message was not sent. Mailer error: " . $mail->ErrorInfo;
  } 
  else {
    $result["success"] = "Message has been sent.";
  }
  return $result;
}