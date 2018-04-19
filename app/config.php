<?php

/**
 * Configurations class
 * 
 * @category Configuration
 * @package  User
 * @author   Samer Alotaibi <sam.otb@hotmail.ca>
 */
class Config
{
    private $_host = "mysql://b29f2f0fd1f9b8:1830114a@us-cdbr-iron-east-05.cleardb.net/heroku_33bfe82ec8da9b8?reconnect=true";    //Database Host
    private $_db_name = "heroku_33bfe82ec8da9b8";   // Database access credentials - change
    private $_db_username = "b29f2f0fd1f9b8";
    private $_db_password = "1830114a";
    private $_secret_key = "OepxL2YvgC747SSsT2"; //Generate your own key string from https://www.random.org/strings/
    private $_server_name = "php-registration-system.herokuapp.com"; // Change to your domain name
    private $_mail_host = "smtp.mailgun.org"; // Change to your stmp service
    private $_mail_username = "postmaster@sandboxd3612106dbc141b7a5134f0fddce769a.mailgun.org"; //Change to mailgun username
    private $_mail_password = "12345";
    private $_from_email = "kalimas158@gmail.com";
    private $_reply_email = "kalimas158@gmail.com";
    private $_domain = "https://php-registration-system.herokuapp.com"; //Your domain without baclslash

    /**
     * Get Host
     *
     * @return string _host
     */
    public function getHost()
    {
        return $this->_host;
    }
    /**
     * Get Database name
     *
     * @return string _db_name
     */
    public function getDBName()
    {
        return $this->_db_name;
    }
    /**
     * Get Database Username
     *
     * @return string _db_username
     */
    public function getDBUsername()
    {
        return $this->_db_username;
    }
    /**
     * Get Database Password
     *
     * @return string
     */
    public function getDBPassword()
    {
        return $this->_db_password;
    }
    /**
     * Get Secret Key
     *
     * @return string _secret_key
     */
    public function getSecretKey()
    {
        return $this->_secret_key;
    }
    /**
     * Get Server Name
     *
     * @return string _server_name
     */
    public function getServerName()
    {
        return $this->_server_name;
    }
    /**
     * Get Mail Host
     *
     * @return string _mail_host
     */
    public function getMailHost()
    {
        return $this->_mail_host;
    }
    /**
     * Get Mail Username
     *
     * @return string _mail_username
     */
    public function getMailUsername()
    {
        return $this->_mail_username;
    }
    /**
     * Get Mail Password
     *
     * @return string _mail_password
     */
    public function getMailPassword()
    {
        return $this->_mail_password;
    }
    /**
     * Get From Email
     *
     * @return string _from_email
     */
    public function getFromEmail()
    {
        return $this->_from_email;
    }
    /**
     * Get Reply Email
     *
     * @return string _Reply_email
     */
    public function getReplyEmail()
    {
        return $this->_reply_email;
    }
    /**
     * Get Domain
     *
     * @return string _domain
     */
    public function getDomain()
    {
        return $this->_domain;
    }
}