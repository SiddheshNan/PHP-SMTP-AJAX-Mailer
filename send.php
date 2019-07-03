<?php
/*
PHP-SMTP-AJAX-Mailer Script w/ Validator
Original author: SiddheshNan(https://siddhesh.me)
https://github.com/SiddheshNan/PHP-SMTP-AJAX-Mailer
*/

// Robots huh.
header("X-Robots-Tag: noindex, nofollow", true);

// Turning off error reporting. (only for production)
error_reporting(0);
ini_set('display_errors',0);
  
// Checking if incoming request is POST, else response rejected;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

// Declareing Variables
$userIP = $_SERVER["REMOTE_ADDR"];
$recaptchaResponse = $_POST['g-recaptcha-response'];

// Checking for recaptcha exists
if ((!isset($_POST['g-recaptcha-response'])) || (empty($_POST['g-recaptcha-response']))){
       $CaptchaError->Err = "Captcha Error";
		header('Content-Type: application/json');
        echo json_encode($CaptchaError);
        exit;
}

//  ENTER YOUR RE-CAPTCHA SECRET KEY HERE.
$secretKey = '';

// verifying user
$request = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$secretKey}&response={$recaptchaResponse}&remoteip={$userIP}");
    
// if Captcha is Invalid, send error
if(!strstr($request, "true")){
       $CaptchaInv->Err = "Captcha Error";
		header('Content-Type: application/json');
        echo json_encode($CaptchaInv);
        exit;
      }

else{
  
// Else Sanitizing the data
if ((!empty($_POST['firstname'])) || (!empty($_POST['lastname']))){
$firstname = trim(htmlspecialchars($_POST['firstname']));
$lastname = trim(htmlspecialchars($_POST['lastname']));
}

if (!empty($_POST['email'])) {
$email = trim(htmlspecialchars($_POST['email']));
$email = filter_var($email, FILTER_VALIDATE_EMAIL);
	if ($email === false) {
		$InvalidEmail->Err = "Invalid Email";
		header('Content-Type: application/json');
        echo json_encode($InvalidEmail);
        exit;
	}
}
else if((empty($_POST['email'])) || (!isset($_POST['email']))) {
		$NoEmail->Err = "Invalid Email";
		header('Content-Type: application/json');
        echo json_encode($NoEmail);
        exit;
	}

if (!empty($_POST['message'])) {
$message = trim(htmlspecialchars($_POST['message']));
}else {
   $message = 'N/A';
}


// Composing email
$EmailData=
'
FirstName:	'.$firstname.'<br />
LastName:	'.$lastname.'<br />
Email:	'.$email.'<br />
Message:	'.$message.'<br />
Remote IP:      '.$userIP.'<br />
';
    
    // Phpmailer init
    require "PHPMailer-master/class.phpmailer.php";
    $mail = new PHPMailer();
    $mail->IsSMTP();
    $mail->SMTPAuth = true;

    // Enter SMTP DETAILS HERE
    $mail->SMTPSecure = "tls";
    $mail->Host = "smtp.gmail.com"; 
    $mail->Port = 587;
    $mail->Encoding = '7bit';

    // Enter SMTP Username and Password Here
    $mail->Username   = "username";
    $mail->Password   = "password";

    $mail->SetFrom($email, $name);
    $mail->AddReplyTo($email, $name);
    $mail->Subject = "New Contact Form Enquiry";
    $mail->MsgHTML($EmailData);

    //Enter Your Recipient Email Here
    $mail->AddAddress("yourmail@example.com");

    // SEND iT
    $result = $mail->Send();
	$mailResult = $result ? 'Success' : 'Error';
	
	if($mailResult == 'Success'){
	$OutmailResult->Output = "Success";
	header('Content-Type: application/json');
    echo json_encode($OutmailResult);
	}
	else if($mailResult == 'Error'){
	$OutmailResult->Output = "Error";
	header('Content-Type: application/json');
    echo json_encode($OutmailResult);
	}
	
	unset($mail);
	exit;
}
}

else {
    // Invalid Request response;
    // usually the case if user directly (GET) requests send.php
        $MNO->Err = "Method not allowed";
		header('Content-Type: application/json');
        echo json_encode($MNO);
        exit;
}

?>

