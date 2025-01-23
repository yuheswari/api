<?php

require_once('Database.class.php');
require '../vendor/autoload.php';

class Signup {

    private $username;
    private $password;
    private $email;

    private $db;
    private $id;

    public function __construct($username, $password, $email){
        $this->db = Database::getConnection();
        $this->username = $username;
        $this->password = $password;
        $this->email = $email;
        if($this->userExists()){
            throw new Exception("User already exists");
        }
        $bytes = random_bytes(16);
        $this->$token =$token=bin2hex($bytes);//to verify  users over email
        $password = $this->hashPassword();


        
        $query = "INSERT INTO `auth` (`username`, `password`, `email`, `active`,`token`, `signup_time`) VALUES ('$username','$password','$email',0,'$token', now());";
        if(!mysqli_query($this->db,$query)){
            throw new Exception("Unable to signup!!");
        
        }else{
            $this->id=mysqli_insert_id($this->db);

        }
    }

    function sendVerificationMail(){
        $config_json = file_get_contents('../../env.json');
        $config = json_decode($config_json, true);
    }

    public function getInsertID(){
        return $this->id;


    }
    public function userExists(){
        return false;
    }

    public function hashPassword($cost = 10){
        //echo $this->password;
        $options = [
            'cost' => $cost,
        ];
        return password_hash($this->password, PASSWORD_BCRYPT, $options);
    }

}