<?php
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


$SPApiClient = new ApiClient(API_USER_ID, API_SECRET, new FileStorage());

//  Capture Post Data that is sent from the form through the main.js file
$firstName = $_POST['firstName'];
$lastName = $_POST['lastName'];
$email = $_POST['email'];
$phone = $_POST['phone'];
$gender = $_POST['gender'];
$state = $_POST['state'];
$uniqueId = 

//  Connect to the Database using PDO
$dsn = "mysql:host=$host;dbname=$db";
//Create PDO Connection with the dbconfig data
$conn = new PDO($dsn, $username, $password);
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
    $enteruser = "INSERT into ewty (firstName, lastName, email, phone, gender, state) VALUES (:firstName, :lastName, :email, :phone, :gender, :state)";
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
            "state" => $state
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
        $body = "Hello {$firstName} {$lastName}. Thank you for registering as a participant at our forthcoming event - 'An Evening With the Youth' holding at the Shell Hall, Muson Center, Lagos on the 9th of February 2019 by 4pm. We look forward to seeing you there.";
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

        echo "success";
    }
}
