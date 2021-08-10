<?php
require_once __DIR__."/../config.php";
require_once __DIR__."/../constants.php";
require_once __DIR__."/../include/PHPMailer-5.2.28/PHPMailerAutoload.php";

class EmailHelper {
    
    public static function sendEmail($templateName, $vars, $recipients){
        $emailSubjects = array(
          "testEmail" => "Test email",
        );
        $templatePath = __DIR__ .'/template/'.$templateName.'.html.php';
        
        if(!file_exists($templatePath)){
            throw new Exception("Notification template file does not exist.", Constants::$ERRORCODES['INVALID_VALUE']);
        }
        
        extract($vars);
        if (!defined('VIEWHELPERINCLUDED') || VIEWHELPERINCLUDED !== "yes") {
          require __DIR__.'/../viewHelper.php';
        }
        ob_start();
        include $templatePath;
        $templateContent = ob_get_contents();
        @ob_end_clean();
        if (USE_SMTP) {
            $mail = new PHPMailer(true);

            date_default_timezone_set('Etc/UTC');
            $mail->isSMTP();
            //Enable SMTP debugging
            // 0 = off (for production use)
            // 1 = client messages
            // 2 = client and server messages
            if (SMTP_DEBUG_MESSAGES) {
              $mail->SMTPDebug = 2;
              //Ask for HTML-friendly debug output
              $mail->Debugoutput = 'html';
            } else {
              $mail->SMTPDebug = 0;
            }
            ob_start();
            //Set the hostname of the mail server
            $mail->Host = SMTP_HOST;
            //Set the SMTP port number - likely to be 25, 465 or 587
            //Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
            $mail->Port = 587;

            //Set the encryption system to use - ssl (deprecated) or tls
            $mail->SMTPSecure = 'tls';//Whether to use SMTP authentication
            $mail->SMTPAuth = true;
            //Username to use for SMTP authentication
            $mail->Username = SMTP_USERNAME;
            //Password to use for SMTP authentication
            $mail->Password = SMTP_PASSWORD;
            //Set who the message is to be sent from
            if($vars['from'] != ''){
                $mail->setFrom($vars['from']);
            } else {
                $mail->setFrom(MAIL_FROM_ADDRESS);
            }
            foreach($recipients as $recipient){
                $mail->addAddress($recipient);
            }
            //Set the subject line
            $mail->Subject = $emailSubjects[$templateName];
            $mail->msgHTML($templateContent);

            //send the message, check for errors
            try {
            $ret = $mail->send();
            } catch (Exception $e) {
                $messages = ob_get_contents();
                ob_end_clean();
                throw new Exception("Mailer Error: " . $e->getMessage(). $messages, Constants::$ERRORCODES['SMTP_ERROR']);
            }
            $messages = ob_get_contents();
            ob_end_clean();
            if (!$ret) {
                throw new Exception("Mailer Error: " . $mail->ErrorInfo. $messages, Constants::$ERRORCODES['SMTP_ERROR']);
            }
        } else {
            $to_email = $recipients;
            $subject = $emailSubjects[$templateName];
            $headers = 'From: ';
            if($vars['from'] != ''){
                $headers = $headers.$vars['from'];
            }else{
                $headers = $headers.MAIL_FROM_ADDRESS;
            }
            $headers .= "\r\nContent-Type: text/html";
            foreach($recipients as $recipient){
                $to_email = $recipient;
                mail($to_email,$subject,$templateContent,$headers); 
            }
        }
    }
}
?>
