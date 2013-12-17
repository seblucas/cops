<?php

require_once ("config.php");
require_once "resources/PHPMailer/class.phpmailer.php";
require_once "book.php";

if (is_null ($config['cops_mail_configuration']) ||
    !is_array ($config['cops_mail_configuration']) ||
    empty ($config['cops_mail_configuration']["smtp.host"]) ||
    empty ($config['cops_mail_configuration']["address.from"])) {
    echo "NOK. bad configuration of $config ['cops_mail_configuration']";
    exit;
}

$idData = $_REQUEST["data"];
if (empty ($idData)) {
    echo 'No data sent.';
    exit;
}
$emailDest = $_REQUEST["email"];
if (empty ($emailDest)) {
    echo 'No email sent.';
    exit;
}

$book = Book::getBookByDataId($idData);
$data = $book->getDataById ($idData);

if (filesize ($data->getLocalPath ()) > 10 * 1024 * 1024) {
    echo 'Attachment too big';
    exit;
}

$mail = new PHPMailer;

$mail->IsSMTP();
$mail->Timeout = 30; // 30 seconds as some files can be big
$mail->Host = $config['cops_mail_configuration']["smtp.host"];
if (!empty ($config['cops_mail_configuration']["smtp.secure"])) {
    $mail->SMTPSecure = $config['cops_mail_configuration']["smtp.secure"];
    $mail->Port = 465;
}
$mail->SMTPAuth = !empty ($config['cops_mail_configuration']["smtp.username"]);
if (!empty ($config['cops_mail_configuration']["smtp.username"])) $mail->Username = $config['cops_mail_configuration']["smtp.username"];
if (!empty ($config['cops_mail_configuration']["smtp.password"])) $mail->Password = $config['cops_mail_configuration']["smtp.password"];
if (!empty ($config['cops_mail_configuration']["smtp.secure"])) $mail->SMTPSecure = $config['cops_mail_configuration']["smtp.secure"];

$mail->From = $config['cops_mail_configuration']["address.from"];
$mail->FromName = $config['cops_title_default'];

foreach (explode (";", $emailDest) as $emailAddress) {
    if (empty ($emailAddress)) { continue; }
    $mail->AddAddress($emailAddress);
}

$mail->AddAttachment($data->getLocalPath ());

$mail->IsHTML(true);
$mail->Subject = 'Sent by COPS : ' . $data->getUpdatedFilename ();
$mail->Body    = "<h1>" . $book->title . "</h1><h2>" . $book->getAuthorsName () . "</h2>" . $book->getComment ();
$mail->AltBody = "Sent by COPS";

if (!$mail->Send()) {
   echo localize ("mail.messagenotsent");
   echo 'Mailer Error: ' . $mail->ErrorInfo;
   exit;
}

echo localize ("mail.messagesent");

