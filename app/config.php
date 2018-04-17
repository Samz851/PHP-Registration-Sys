<?php

  class Config
  {
    private $host = "us-cdbr-iron-east-05.cleardb.net";    //
    private $db_name = "php_user_sys";   // Database access credentials - change
    private $db_username = "b29f2f0fd1f9b8";     //
    private $db_password = "1830114a";
    private $secret_key = "OepxL2YvgC747SSsT2"; 		//Generate your own key string from https://www.random.org/strings/
    private $server_name = "php-registrtion-system.herokuapp.com";		// Change to your domain name
    private $mail_host = "smtp.mailgun.org";            // Change to your stmp service
    private $mail_username = "postmaster@sandboxd3612106dbc141b7a5134f0fddce769a.mailgun.org";  //Change to mailgun username
    private $mail_password = "12345";
    private $from_email = "kalimas158@gmail.com";
    private $reply_email = "kalimas158@gmail.com";
    private $domain = "https://php-registrtion-system.herokuapp.com";    //Your domain without baclslash

    //GETTERS
    public function get_host(){
        return $this->host;
    }
    public function get_db_name(){
        return $this->db_name;
    }
    public function get_db_username(){
        return $this->db_username;
    }
    public function get_db_password(){
        return $this->db_password;
    }
    public function get_secret_key(){
        return $this->secret_key;
    }
    public function get_server_name(){
        return $this->server_name;
    }
    public function get_mail_host(){
        return $this->mail_host;
    }
    public function get_mail_username(){
        return $this->mail_username;
    }
    public function get_mail_password(){
        return $this->mail_password;
    }
    public function get_from_email(){
        return $this->from_email;
    }
    public function get_reply_email(){
        return $this->reply_email;
    }
    public function get_domain(){
        return $this->domain;
    }

  }