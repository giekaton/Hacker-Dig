<?php 

//
// REQUIRED CONFIGURATION
//

// Database config
$servername = ""; // UPDATE THIS
$username = ""; // UPDATE THIS
$password = ""; // UPDATE THIS
$dbname = ""; // UPDATE THIS

// Create db connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check db connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

//
// OPTIONAL CONFIGURATION
//

// PHPMailer configuration
require_once 'class-phpmailer.php';
require_once 'class-smtp.php';
$mail = new PHPMailer;
$mail->isSMTP();
$mail->SMTPDebug = 0;
$mail->Host = ''; // UPDATE THIS
$mail->Port = 465; // UPDATE THIS
$mail->SMTPAuth = true;
$mail->SMTPSecure = "ssl"; // UPDATE THIS
$mail->Username = ''; // UPDATE THIS
$mail->Password = ''; // UPDATE THIS
$mail->setFrom('noreply@hackerdig.com', 'Hacker Dig'); // UPDATE THIS

// Admin email
$GLOBALS['adminaddress'] = ''; // UPDATE THIS

// Aylien API config for summary extraction
// Can get a free api key at https://developer.aylien.com/signup
$GLOBALS['appKey'] = ""; // UPDATE THIS
$GLOBALS['appId'] = ""; // UPDATE THIS

// Google RECAPTCHA config
$GLOBALS['reCaptchaSecret'] = ""; // UPDATE THIS