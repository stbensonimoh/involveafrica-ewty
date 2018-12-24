<?php
// ini_set('display_errors',1);
// Pull in the Database Configuration file
require 'dbconfig.php';
require 'sendpulse-rest-api-php/ApiInterface.php';
require 'sendpulse-rest-api-php/ApiClient.php';
require 'sendpulse-rest-api-php/Storage/TokenStorageInterface.php';
require 'sendpulse-rest-api-php/Storage/FileStorage.php';
require 'sendpulse-rest-api-php/Storage/SessionStorage.php';
require 'sendpulse-rest-api-php/Storage/MemcachedStorage.php';
require 'sendpulse-rest-api-php/Storage/MemcacheStorage.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
use Sendpulse\RestApi\ApiClient;
use Sendpulse\RestApi\Storage\FileStorage;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

$uniqueNumber;


$SPApiClient = new ApiClient(API_USER_ID, API_SECRET, new FileStorage());

//  Capture Post Data that is sent from the form through the main.js file
$firstName = $_POST['firstName'];
$lastName = $_POST['lastName'];
$email = $_POST['email'];
$phone = $_POST['phone'];
$gender = $_POST['gender'];
$age = $_POST['age'];
$state = $_POST['state']; 

//  Connect to the Database using PDO
$dsn = "mysql:host=$host;dbname=$db";
//Create PDO Connection with the dbconfig data
$conn = new PDO($dsn, $username, $password);

    // count the users in the database and create a unique number by one increment
    $userCount = "SELECT * FROM ewty";
    // prepare the query
    $userCountQuery = $conn->prepare($userCount);
    //Execute the Count Query
    $userCountQuery->execute();
    //Fetch the Result
    $countNumber = $userCountQuery->fetchColumn();
    //Increment it and create the $uniqueNumber
    $uniqueNumber = $countNumber + 1;

// Check to see if the user is in the database already
$usercheck = "SELECT * FROM ewty WHERE email=?";
// prepare the Query
$usercheckquery = $conn->prepare($usercheck);
//Execute the Query
$usercheckquery->execute(array("$email"));
//Fetch the Result
$usercheckquery->rowCount();

if ($usercheckquery->rowCount() > 0) {
    echo "user_exists";
} else {
    // Insert the user into the database
    $enteruser = "INSERT into ewty (firstName, lastName, email, phone, gender, age, state, uniqueNumber) VALUES (:firstName, :lastName, :email, :phone, :gender, :age, :state, :uniqueNumber)";
    //  Prepare Query
    $enteruserquery = $conn->prepare($enteruser);
    //  Execute the Query
    $enteruserquery->execute(
        array(
            "firstName" => $firstName,
            "lastName" => $lastName,
            "email" => $email,
            "phone" => $phone,
            "gender" => $gender,
            "age"   =>  $age,
            "state" => $state,
            "uniqueNumber" => $uniqueNumber
        )
    );
    //  Fetch Result
    $enteruserquery->rowCount();
    // Check to see if the query executed successfully
    if ($enteruserquery->rowCount() > 0) {

        // Send an SMS
        // prepare the parameters
        $url = 'https://www.bulksmsnigeria.com/api/v1/sms/create';
        $from = 'InvolveAfri';
        $body = "Hello {$firstName} {$lastName}. Thank you for registering as a participant at our forthcoming event - 'An Evening With the Youth' holding at the Shell Hall, Muson Center, Lagos on the 9th of February 2019 by 4pm. Your identification number is {$uniqueNumber}. We look forward to seeing you there.";
        $myvars = 'api_token=' . $smstoken . '&from=' . $from . '&to=' . $phone . '&body=' . $body;
        //start CURL
        // create curl resource
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $myvars);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_exec($ch);

        /**
         * Add User to the SendPulse mailing List
         */
        $bookID = $sendpulseBookId;
        $emails = array(
                array(
                    'email'         =>  $email,
                    'variables'     =>  array(
                    'phone'         =>  $phone,
                    'name'          =>  $firstName,
                    'lastName'      =>  $lastName,
                    'gender'        =>  $gender,
                    'age'           =>  $age,
                    'state'         =>  $state,
                    'uniqueNumber'  =>  $uniqueNumber
                )
            )
        );
        // Add emails to the List
        $SPApiClient->addEmails($bookID, $emails);

        // Send Message via Email - PHPMailer
        $mail = new PHPMailer(true); // enable exceptions
        // server settings
        $mail->SMTPDebug = false;
        $mail->isSMTP();
        $mail->Host = $mailHost;
        $mail->SMTPAuth = true;
        $mail->Username = $mailUsername;
        $mail->Password = $mailPassword;
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;
        $emailBody = '<table style="background-color: #d5d5d5;" border="0" width="100%" cellspacing="0">
                        <tbody>
                        <tr>
                        <td>
                        <table style="font-family: Helvetica,Arial,sans-serif; background-color: #fff; margin-top: 40px; margin-bottom: 40px;  border-radius: 20px;" border="0" width="600" cellspacing="0" cellpadding="0" align="center">
                        <tbody>
                        <tr>
                        <td style="padding-top: 40px; padding-right: 40px; padding-bottom: 15px;" colspan="2">
                        <p style="text-align: right;"><a href="http://involveafri.org"><img src="https://involveafri.org/emails/logo.jpg" alt="Involve Africa" width="30%" border="0" /></a></p>
                        </td>
                        </tr>
                        <tr>
                        <td style="padding-right: 40px; text-align: right;" colspan="2"><span style="font-size: 12pt;"></span></td>
                        </tr>
                        <tr>
                        <td style="color: #000; font-size: 12pt; font-weight: normal; line-height: 15pt; padding: 40px 40px 80px 40px;" colspan="2" valign="top">Hello ' . $firstName . ' ' . $lastName . ',
                            <p>Thank you for registering for An Evening with The Youth. Your identification number is ' . $uniqueNumber . '.</p>
                            <p>We are ready for you on February 9th 2019. Are you ready? There&#39;s so much we have for you, we can&#39;t wait.</p>
                            <p>This mail or an SMS you received serves as a ticket to the event. Please come with it to the venue. Note that <strong>this ticket is not transferrable.</strong></p>
                            <p>Kindly visit <a href="//involveafri.org">Involve Africa</a> and all our social media platforms for more information and updates. Don&#39;t hesitate to call <strong>08174920270</strong> for further enquiries.</p>
                            <p>We look forward to seeing you at the event.</p>
                            <p>Warm regards!</p>
                            <p>Owolabi Ibrahim <br/>
                            <span style="font-weight:bold;">Convener</span>
                            </p>
                        </td>
                        </tr>
                        <tr>
                        <td style="border-top: 5px solid #ec008b; height: 10px; font-size: 7pt;" colspan="2" valign="top"><span>&nbsp;</span></td>
                        </tr>
                        <tr style="text-align: center;">
                        <td id="s1" style="padding-left: 20px;" valign="top"><span style="text-align: center; color: #333; font-size: 12pt;"><strong>Powered by:</strong> Involve Africa</span></td>
                        </tr>
                        <tr style="text-align: center; padding-left: 20px; padding-right: 20px; padding-bottom: 0;">
                        <td colspan="2" valign="top"><span style="color: #333; font-size: 8pt; font-weight: normal; line-height: 17pt;"><span style="font-size: 12pt;color: #4298f4;">Involve Africa</span><br /> Lagos, Nigeria<br />tel: +2348174920270 &nbsp;<br /><strong>email:&nbsp;</strong>hello@involveafri.org &nbsp;|&nbsp; <strong>www.involveafri.org</strong></span>
                        <p><a href="https://twitter.com/involveafri"><img src="https://s3.amazonaws.com/rkjha/signature-maker/icons/twitter_circle_color-20.png" width="20px" height="20px" /></a><a href="https://www.facebook.com/involveafri/"><img src="https://s3.amazonaws.com/rkjha/signature-maker/icons/facebook_circle_color-20.png" width="20px" height="20px" /></a></p>
                        </td>
                        </tr>
                        <tr>
                        <td id="s3" style="padding-left: 20px; padding-right: 20px;" colspan="2" valign="bottom">
                        <p style="font-family: Helvetica, sans-serif; text-align: center; font-size: 12px; line-height: 21px; color: #333;"><span style="margin-left: 4px;"><span style="opacity: 0.4; color: #333; font-size: 9px;">Disclaimer: This message and any files transmitted with it are confidential and privileged. If you have received it in error, please notify the sender by return e-mail and delete this message from your system. If you are not the intended recipient you are hereby notified that any dissemination, copy or disclosure of this e-mail is strictly prohibited.</span></span></p>
                        </td>
                        </tr>
                        <tr>
                        <td style="border-bottom: 5px solid #7e3f98; height: 5px; font-size: 7pt;border-radius: 20px;" colspan="2" valign="top">&nbsp;</td>
                        </tr>
                        </tbody>
                        </table>
                        </td>
                        </tr>
                        </tbody>
                        </table>';
        //Recipients
        $mail->setFrom($mailUsername, 'Involve Africa');
        $mail->addAddress($email, $firstName.' '.$lastName);
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'You have Successfully Registered for An Evening With The Youth';
        $mail->Body = $emailBody;
        
        $mail->send();



        echo "success";
    }
}
