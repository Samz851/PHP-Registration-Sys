<?php
/**
 * The USER class contains all logic to register, send email, activate, generate tokens, set cookies, authenticate sessions,
 * reset password, and logout/destroy session
 * MUST SET INSTANCE TO NULL AT END OF SESSION
 * 
 * @category  Registration
 * @package   Samz851\USER
 * @author    Samer Alotaibi <sam.otb@hotmail.ca>
 * @copyright 2018 Samer Alotaibi
 * @version   1.0.0
 * PHP version => 7
 */

namespace Samz851\USER;

require_once 'dbconfig.php';
require_once '../vendor/autoload.php';
require_once 'config.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\ValidationData;

/**
 * Class USER
 * 
 * @category Class
 * @package  Samz851\USER
 * @author   Samer Alotaibi <sam.otb@hotmail.ca>
 */
class USER
{
    /**
     *
     * @var string $url is the domain string from Config.php file
     */
    private $_conn;
    private $_config;
    public $url;
    private $_secret_key;

    /**
     * Class Constructor
     */
    public function __construct() 
    {
        $this->_config = new Config();
        $database = new Database();
        $db = $database->dbConnection();
        $this->_conn = $db;
        $this->signer = new Sha256();
        $this->url = $this->_config->getDomain();
        $this->_secret_key = $this->_config->getSecretKey();
    }

    /**
     * Query Generator
     *
     * @param string $sql query to be prepared
     * 
     * @return PDOStatement|PDOException
     */
    public function runQuery($sql)
    {
        $stmt = $this->_conn->prepare($sql);
        return $stmt;
    }

    /**
     * Fetches user's ip address
     *
     * @return string IPv4 or IPv6 format address
     */
    private function _getIPAddress()
    {
        $client  = @$_SERVER['HTTP_CLIENT_IP'];
        $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
        $remote  = $_SERVER['REMOTE_ADDR'];
        if (filter_var($client, FILTER_VALIDATE_IP)) {
            $ip = $client;
        } elseif (filter_var($forward, FILTER_VALIDATE_IP)) {
            $ip = $forward; 
        } else {
            $ip = $remote;
        }
        return $ip;
    }

    /**
     * Checks email against database
     *
     * @param string $email entered by user
     * 
     * @return boolean True or False
     */
    public function check_email($email)
    {
        // prepare statment //
        $stmt = $this->runQuery("SELECT * FROM users WHERE email=:email_id");    // Missing email confirmation
        // searches for email in db for verification
        $stmt->execute(array(":email_id"=>$email));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($stmt->rowCount() > 0) {
                return false;
        } else {
            return true;
        };
    }

    /**
     * Registration
     *
     * @param array $reg_data from registration form
     * 
     * @return boolean
     */
    public function register($reg_data) // Set variables according to your form
    {
        try{
            $token =md5(uniqid(rand()));
            $code = mt_rand(12000, 999999999);
            $params = array(
            ':name' => $reg_data['name'],
            ':username' => $reg_data['username'],
            ':email' => $reg_data['email'],
            ':upass' => password_hash($reg_data['pass'], PASSWORD_DEFAULT),
            ':dob' => $reg_data['date_of_birth'],
            ':temp_token' => $token,
            ':temp_code' => $code);
            $stmt = $this->_conn->prepare(
                "INSERT INTO users(name, username, email, upass, date_of_birth, temptoken, temp_code)
             VALUES(:name, :username, :email, :upass, :dob, :temp_token, :temp_code)"
            );
            $stmt->execute($params);
            
            // send activation email
            $message = "
                        Hello ".$params[':name'].",
                        <br /><br />
                        This is your verification link, 
                        Please follow the link to complete Registration<br/>
                        <br /><br />
                        <a href='http://localhost/php-repos/registration/index.php?activate&id=$code&token=$token'>
                        Click here to activate</a>
                        <br /><br />";
            $subject = "Welcome to PHP User System";

            $this->_send_mail($params[':email'], $message, $subject);
            return true;
        }
        catch(PDOException $ex) {
            echo $ex->getMessage();
            return false;
        }
    }
    
    /**
     * Login
     *
     * @param string $input_email    entered by user
     * @param string $input_password entered by user
     * 
     * @return boolean|PDOException and sets msg in $_SESSION['msg']
     */
    public function login($input_email,$input_password) 
    {
        try
        {
            $stmt = $this->_conn->prepare("SELECT * FROM users WHERE email=:email");
            $stmt->execute(array(":email"=>$input_email));
            $userRow=$stmt->fetch(PDO::FETCH_ASSOC);
            if ($stmt->rowCount() < 1) {
                return false;
            } else {
                $user_pass = $input_password;
                //prepare password for comparison
                if (password_verify($user_pass, $userRow['upass']) && $userRow['is_verified']!==0) {
                    $payload = array(
                        "id" => $userRow['user_id'],
                        "dob" => $userRow['date_of_birth'],
                        "exp" => time() + (60*60*24)    // set expirey
                    );
                    $token = $this->_generate_token($payload);
                    $this->set_cookie($token);
                    $_SESSION['last_log'] = $userRow['last_logged_in'];
                    $_SESSION['last_ip'] = $userRow['last_ip'];
                    $_SESSION['user_id'] = $userRow['user_id'];
                    $new_log_ip = 1;
                    return true;
                } else if ($userRow["is_verified"] == 0) {
                    $_SESSION['msg'] = 'Your account must be activated first';
                    return false;
                } else {
                    $_SESSION['msg'] = "Input do not match \n the password you entered is: "
                    .password_hash($input_password, PASSWORD_BCRYPT)." and the stored is: ".$userRow["upass"];
                    return false;
                }
            }
        }
        catch(PDOException $ex) {
            echo $ex->getMessage();
        }
    }
    
    /**
     * Redirecting users
     *
     * @param string $url to redirect to
     * 
     * @return void
     */
    public function redirect($url) 
    {
        header("Location: $url");
    }
    
    /**
     * Logout
     *
     * @param string $s_token the name of the cookie to be destroyed
     * 
     * @return void
     */
    public function logout($s_token) 
    {
        session_destroy();
        $this->destroy_cookie($s_token);
    }

    /**
     * Password reset email
     *
     * @param string $email to send the link to
     * 
     * @return boolean|PDOException
     */
    public function send_pass_reset($email)
    {
        // Send email
        try{
            $stmt = $this->runQuery("SELECT * FROM users WHERE email=:email");
            $stmt->execute(array(':email'=>$email));
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($stmt->rowCount() == 1) {
                $token =md5(uniqid(rand()));
                $code = mt_rand(12000, 999999999);
                $stmt = $this->runQuery("UPDATE users SET temptoken=:token, temp_code=:code  WHERE email=:email");
                $stmt->execute(array(":token"=>$token, ":code"=> $code, "email"=>$email));
                $message= "
                    Hello , $email
                    <br /><br />
                    You requested to change password, if it was not you, please ignore this email
                    <br /><br />
                    Click the following link to reset your password 
                    <br /><br />
                    <a href='$this->url/views/resetpass.php?reset&id=$code&token=$token'>click here to reset your password</a>
                    <br /><br />
                    thank you :)
                    ";
                $subject = "Password Reset";
            
                $this->_send_mail($email, $message, $subject);
                return true;
            } else {
                return false;
            }
        } catch(PDOException $ex){
            echo $ex->getMessage();
            return false;
        }
        
    }

    /**
     * Update new password
     *
     * @param string $pass new password
     * @param string $code temporary code from url query
     * 
     * @return boolean|PDOException
     */
    public function update_password($pass, $code)
    {
        try {
            $new_password = password_hash($pass, PASSWORD_DEFAULT);
            $stmt = $this->runQuery("UPDATE users SET upass=:newpass, temp_code=:temp, temptoken=:token WHERE temp_code=:code");
            $stmt->execute(array(':newpass'=>$new_password, ':temp'=>0, ':token'=>' ', ':code'=> $code));
            return true;
        } catch(PDOException $ex) {
            echo $ex->getMessage();
            return false;
        }
    }

    /**
     * Private function to send email
     *
     * @param string $uemail  user email
     * @param string $message the message
     * @param string $subject 
     * 
     * @return void
     */
    private function _send_mail($uemail,$message,$subject)
    {
        $mail = new PHPMailer(true);
        $mail->IsSMTP();
        $mail->isHTML(true);
        $mail->Port = '587'; 
        // $mail->SMTPDebug  = 4;    // uncomment to enable detailed debuging
        $mail->SMTPAuth   = true;
        $mail->SMTPSecure = "tls";   // Set mailer configuration to your Mail service
        $mail->Host       = $this->_config->getMailHost();   // From Config
        $mail->addAddress($uemail);
        $mail->Username   =$this->_config->getMailUsername();    // From Config
        $mail->Password   =$this->_config->getMailPassword();   // From Config        
        $mail->From       =$this->_config->getFromEmail();   // From Config
        $mail->AddReplyTo($this->_config->getReplyEmail());   // From Config
        $mail->Subject    = $subject;
        $mail->Body = $message;
        $mail->Send();
        if (!$mail->send()) {
            echo 'Mailer Error: ' . $mail->ErrorInfo . "\n";
        }
    }

    /**
     * Activate new users
     *
     * @param string $id    temporary code from url query
     * @param string $token temporary token from url query
     * 
     * @return void
     */
    public function activate_user($id, $token)
    {
        $statusY = "1"; // Yes activated account
        $statusN = "0"; // No not activated account
        $empty = " ";
        $empty_code = 0;
        $stmt = $this->runQuery("SELECT temptoken, temp_code, user_id, date_of_birth, is_verified FROM users WHERE temp_code=:id AND temptoken=:token LIMIT 1");
        $stmt->execute(array(":id"=>$id,":token"=>$token));
        $row=$stmt->fetch(PDO::FETCH_ASSOC);
        if ($stmt->rowCount() > 0) {
            if ($row['is_verified']==$statusN) {
                $client_ip = $this->_getIPAddress();
                $stmt = $this->runQuery("UPDATE users SET is_verified=:status, temptoken=:empty, temp_code=:empty_code, last_ip=:client_ip WHERE temp_code=:id");
                $stmt->bindparam(":id", $id);
                $stmt->bindparam(":status", $statusY);
                $stmt->bindparam(":empty", $empty);
                $stmt->bindparam(":empty_code", $empty_code);
                $stmt->bindparam(":client_ip", $client_ip);
                $stmt->execute();
                if ($stmt->execute()) {
                    $payload = array(
                        "id" => $row['user_id'],
                        "dob" => $row['date_of_birth'],
                        "exp" => time() + (2*60));    // set expirey
                    //Get the jwt token
                    $jwt_token = $this->_generate_token($payload);
                    // set tokens and message
                    $this->set_cookie($jwt_token);
                    $_SESSION['last_ip'] = $client_ip;
                    return 'success';
                }
            } else {
                return 'already activated';
            }
        } else {
            return 'not found';
        };
    }

    /**
     * Get all user info from database
     *
     * @param int $user user id
     * 
     * @return string HTML content
     */
    public function fetch_user_datatable($user)
    {
        // prepare statement
        $stmt = $this->_conn->prepare("SELECT * FROM users WHERE user_id=:id");
        $stmt->execute(array(":id"=>$user));
        $userRow=$stmt->fetch(PDO::FETCH_ASSOC);
        function verified($int)
        {
            if ($int == 0) {
                return 'False';
            } else {
                return 'True';
            }
        };
            echo '<th scope="row">'.$userRow['user_id'].'</th>
                <td>'.$userRow['name'].'</td>
                <td>'.$userRow['username'].'</td>
                <td>'.$userRow['email'].'</td>
                <td>'.$userRow['date_of_birth'].'</td>
                <td>'.verified($userRow['is_verified']).'</td>
                <td>'.$userRow['last_logged_in'].'</td>
                <td>'.$userRow['last_ip'].'</td>';
    }
    
    /**
     * Generating JWT Tokens
     *
     * @param array $payload an array of claims for the token
     * 
     * @return JWT object returns a token object
     */
    private function _generate_token($payload)
    {
        // using Lcobucci\JWT\Builder JWT Library;
        //prepare claims
        $token = (new Builder())->setIssuer('php-user-system') // Configures the issuer (iss claim)
                                ->setAudience('http://localhost/php-repos/registration') // Configures the audience (aud claim)
                                ->setId('4f1g23a12aa', true) // Configures the id (jti claim), replicating as a header item
                                ->setIssuedAt(time()) // Configures the time that the token was issue (iat claim)
                                ->setExpiration($payload['exp']) // Configures the expiration time of the token (exp claim)
                                ->set('uid', $payload['id']) // Configures a new claim, called "uid"
                                ->set('dob', $payload['dob']) // Configures a new claim, called "dob"
                                ->sign($this->signer, $this->_secret_key) // creates a signature using "testing" as key
                                ->getToken(); // Retrieves the generated token
        // encode the payload using our secretkey and return the token
        return $token;
    }

    /**
     * Parse a JWT token string into Token object
     *
     * @param string $s_token 
     * 
     * @return JWT object
     */
    private function _token_parser($s_token)
    {
        $token = (new Parser())->parse((string) $s_token);
        return $token;
    }

    /**
     * Retrieve specific claims from the JWT token
     *
     * @param string $claim the name of the claim being retrieved
     * 
     * @return string representation of the JWT claim
     */
    function retrieve_token_claim($claim)
    {
        $s_token = $_COOKIE['jwt'];
        $token = $this->_token_parser($s_token); // Parses from a string
        $token->getHeaders(); // Retrieves the token header
        $retrieved_claim = $token->getClaim($claim); // Retrieves the token claims
        return $retrieved_claim;
    }

    /**
     * Verifying JWT tokens
     *
     * @param string $s_token the string representation of JWT token
     * 
     * @return boolean
     */
    function verify_token($s_token)
    {
        $token = $this->_token_parser($s_token);
        $signature_status = $token->verify($this->signer, $this->_secret_key);
        return $signature_status;
    }

    /**
     * Validating JWT token
     * checking token against accepted format to validate
     *
     * @param string $s_token the string representation of JWT token
     * 
     * @return boolean
     */
    function validate_token($s_token)
    {
        if ($this->verify_token($s_token)) {
            $token = $this->_token_parser($s_token);
            // Prepare validation data for comparison
            $data = new ValidationData(); // It will use the current time to validate (iat, nbf and exp)
            $data->setIssuer('php-user-system');
            $data->setAudience('http://localhost/php-repos/registration');
            $validation_status = $token->validate($data);
        } else {
            $validation_status = false;
        }
        return $validation_status;
    }

    /**
     * Setting cookie
     *
     * @param string $token the string representation of JWT token to be stored in cookie
     * 
     * @return void
     */
    function set_cookie($token)
    {
        $expirey = time()+60*60*24*7;
        setcookie('jwt', $token, $expirey, '/');
    }

    /**
     * Destroy cookie/session
     *
     * @param string $s_token name of the cookie/jwt token stored
     * 
     * @return void
     */
    function destroy_cookie($s_token)
    {
        setcookie('jwt', $s_token, time()-86400*30, '/');
    }
}