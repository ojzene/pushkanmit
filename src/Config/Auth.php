<?php

namespace App\Config;

use App\Config\RedisDatabase as RD;
use PHPMailer;

class Auth
{
    public $apiKey;
    public $secret;
    public $token;
    public $moneywave_staging_url;
    public $publicKey;
    public $accessToken;
    public $server_api_key;

    public function __construct()
    {
        $this->output_format = "json"; // json or xml
        $this->output_app_format = "application/xml"; // json or xml
        $this->publicKey = 'put public key here';
        $this->accessToken = 'put access token here';

        $this->server_api_key = "put your firebase server api key here";
    }

        // to get token: save time into database after first request, then to make a request - call dis function to check the difference between time save in the db and server time, if

    public function getToken()
    {
        $get_data = (new Auth)->getTokenForAccess($this->apiKey);
        $get_json = $get_data['data'];
        $get_array_data = json_decode($get_json, true);
        $this->token = $get_array_data['token'];

        return $this->token;
    }

    public function hasTokenExpire()
    {

        $get_data = (new Auth)->getTokenForAccess($this->apiKey);
        $get_json = $get_data['data'];
        $get_array_data = json_decode($get_json, true);
        $token_date = $get_array_data['token_expiration'];
        $token = $get_array_data['token'];

        // $today_date = "2017-02-25 10:59:37";
        $today_date = date("Y-m-d h:i:s");

        $timeFirst = strtotime($token_date);
        $timeSecond = strtotime($today_date);
        $differenceInSeconds = $timeFirst - $timeSecond;

        if ($differenceInSeconds > 0) {
            $token_message = ["status" => false, "message" => $token];
        } elseif ($differenceInSeconds <= 0) {
            $token_message = ["status" => true, "message" => ""];
        }

        return $token_message;
    }


    public function checkRedisConnection()
    {

        $is_connected = (new RD)->single_client();
        $connected = $is_connected ? "yes" : "no";
        return $connected;
    }

    public function getAllRedisKeys()
    {

        $redis_errors = "";

        $isConnected = $this->checkRedisConnection();
        if ($isConnected == "yes") {
            $client = (new RD)->single_server();
            $allkeys = $client->keys('*');

            return $allkeys;

        } elseif ($isConnected == "no") {
            $redis_errors = "Oops! Unable to connect Database";
        }
        // return $redis_errors;
    }


    public function checkApikKeyExist($apiKey)
    {

        $redis_errors = "";

        $client = (new RD)->single_server();

        $id_exists = $client->exists($apiKey);   // print_r($client->keys('*')); // get all redis keys

        if ($id_exists === 1) {
            $redis_errors = "";
        } elseif ($id_exists === 0) {
            $redis_errors = "Invalid request, apikey does not exist ";
        }
        return $redis_errors;
    }

    public function apiKey()
    {
        $apiKey = "aG91c2dpcmxfdG9rZW5fZm9yX2F1dGhlbnRpY2F0aW9u";
        return $apiKey;
    }

    public function getTokenForAccess($apiKey)
    {

        $result_error = $this->checkApikKeyExist($apiKey);
        if ($result_error == "" || empty($result_error)) {

            $client = (new RD)->single_server();
            $tracking_key = $apiKey;

            $response = $client->get($tracking_key);

            $result = ['success' => true, 'data' => $response];

        } else {

            $result = ['success' => false, 'data' => $result_error];
        }

        return $result;
    }

    public function saveTokenForAccess($apiKey, $token)
    {
        $redis_errors = "";

        $isConnected = $this->checkRedisConnection();
        if ($isConnected == "yes") {

            $token_expiration = date('Y-m-d h:i:s', strtotime('+2 hour')); //the expiration date will be in two hour from the current moment

            // $token_expiration = date('Y-m-d h:i:sa');

            $client = (new RD)->single_server();
            $track_token = json_encode([
                'apiKey' => $apiKey,
                'token' => $token,
                'token_expiration' => $token_expiration
            ]);

            $client->set($apiKey, $track_token);
            $client->expire($track_token, 1200);
            $client->ttl($track_token);

            $redis_errors = null;

        } elseif ($isConnected == "no") {
            $redis_errors = "Oops! Unable to connect Database";
        }

        return $redis_errors;
    }

    public function validateEmail($email)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        return true;
    }


    public function sendMailVerification($email, $username, $token,$account)
    {
        //Tell PHPMailer to use SMTP
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->SMTPDebug = 2;
        $mail->Debugoutput = 'html';
        $mail->Host = 'smtp.gmail.com';
        $mail->Port = 587;
        $mail->SMTPSecure = 'tls';
        $mail->SMTPAuth = true;
        $mail->Username = "your email addres";
        $mail->Password = "yourpassword";
        $mail->setFrom('from which email', 'Your Name');
        //Set who the message is to be sent to
        $mail->addAddress($email, $username);

        //Set the subject line
        $mail->Subject = "Account Verification";
        $mail->Body = 'Thanks for signing up!
        Your account has been created, you can login with the following credentials
        ------------------------
        Username: ' . $username . '
        ------------------------';

        //send the message, check for errors
        ob_start();
        if (!$mail->send()) {
            ob_get_clean();
            return false;
        } else {
            ob_get_clean();
            return true;
        }
    }

}
