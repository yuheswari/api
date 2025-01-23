<?php

require_once('Database.class.php');
require '../vendor/autoload.php';

use MailerSend\MailerSend;
use MailerSend\Helpers\Builder\Recipient;
use MailerSend\Helpers\Builder\EmailParams;

class Signup
{

    private $username;
    private $password;
    private $email;

    private $db;
    private $id;
    private $token;

    public function __construct($username, $password, $email)
    {
        $this->db = Database::getConnection();
        $this->username = $username;
        $this->password = $password;
        $this->email = $email;
        if ($this->userExists()) {
            throw new Exception("User already exists");
        }
        $bytes = random_bytes(16);
        $this->token = $token = bin2hex($bytes); //to verify  users over email
        $password = $this->hashPassword();



        $query = "INSERT INTO `auth` (`username`, `password`, `email`, `active`,`token`, `signup_time`) VALUES ('$username','$password','$email',1,'$token', now());";
        if (!mysqli_query($this->db, $query)) {
            throw new Exception("Unable to signup!!");
        } else {
            $this->id = mysqli_insert_id($this->db);
            // $this->sendVerificationMail();
        }
    }

    function sendVerificationMail()
    {
        $config_json = file_get_contents('../../env.json');
        $config = json_decode($config_json, true);

        $token = $this->token;
        $apikey = getenv($config['email_api_key']);

        echo $apikey;

        $mailersend = new MailerSend(['api_key' => 'mlsn.1727dfddc903831a263019d079311282d9cac45188035e0c091ff2459bece564']);

        $recipients = [
            new Recipient($this->email, $this->username),
        ];

        $emailParams = (new EmailParams())
            ->setFrom('yuheswari2525@gmail.com')
            ->setFromName('API DEVELOPMENT')
            ->setRecipients($recipients)
            ->setSubject('VERIFY UR ACCOUNT')
            ->setHtml('<strong>Please verify your account by <a href=\"https://apibyuk.selfmade.plus/verify?token='.$token.'\">clicking here</a> or open this URL manually: <a href=\"https://apibyuk.selfmade.plus/verify?token='.$token.'\">https://apibyuk.selfmade.plus/verify?token='.$token.'</a></strong>')
            ->setText('please verify your account at https://apibyuk.selfmade.plus/verify?token=token->token');

        $mailersend->email->send($emailParams);
    }

    function sendVerificationMailOld()
    {
        $config_json = file_get_contents('../../env.json');
        $config = json_decode($config_json, true);
        $token = $this->token;
        $email = new \SendGrid\Mail\Mail();
        $email->setFrom("yuheswari2525@gmail.com", "API DEVOLPMENT");
        $email->setSubject("VERIFY UR ACCOUNT");
        $email->addTo($this->email, $this->username);
        $email->addContent("text/plain", "please verify your account at https://apibyuk.selfmade.plus/verify?token=token->token");
        $email->addContent(
            "text/html",
            "<strong>Please verify your account by <a href=\"https://apibyuk.selfmade.plus/verify?token=$token\">clicking here</a> or open this URL manually: <a href=\"https://apibyuk.selfmade.plus/verify?token=$token\">https://apibyuk.selfmade.plus/verify?token=$token</a></strong>"
        );

        $sendgrid = new \SendGrid(getenv($config['email_api_key']));
        try {
            $response = $sendgrid->send($email);
            // print $response->statusCode() . "\n";
            // print_r($response->headers());
            // print $response->body() . "\n";
        } catch (Exception $e) {
            echo 'Caught exception: ' . $e->getMessage() . "\n";
        }
    }

    public function getInsertID()
    {
        return $this->id;
    }
    public function userExists()
    {
        return false;
    }

    public function hashPassword($cost = 10)
    {
        //echo $this->password;
        $options = [
            'cost' => $cost,
        ];
        return password_hash($this->password, PASSWORD_BCRYPT, $options);
    }
}
