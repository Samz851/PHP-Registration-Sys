<?php

require_once 'dbconfig.php';
require_once 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\ValidationData;

class USER
{	
	public $server_name = "PHP-User-Registration";		// Change to your domain name
	private $conn;
	//secret for jwt signature
	private $secret_key = "OepxL2YvgC747SSsT2"; 		//Generate your own key string from https://www.random.org/strings/

	
	public function __construct()
	{
		$database = new Database();
		$db = $database->dbConnection();
		$this->conn = $db;
		$this->signer = new Sha256();
    }
	
	public function runQuery($sql)
	{
		$stmt = $this->conn->prepare($sql);
		return $stmt;
	}

	private function get_ip_address(){
		$client  = @$_SERVER['HTTP_CLIENT_IP'];
    	$forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
    	$remote  = $_SERVER['REMOTE_ADDR'];
    	if(filter_var($client, FILTER_VALIDATE_IP))
    	{
        	$ip = $client;
    	}
    	elseif (filter_var($forward, FILTER_VALIDATE_IP))
    	{
        	$ip = $forward;
    	} else
    	{
        	$ip = $remote;
    	}
		return $ip;		// on localhost this should return by default on windows IPv6 format (::1) or the IPv4 (127.0.0.1);
	}

	public function check_email($email){
		// prepare statment //
		$stmt = $this->runQuery("SELECT * FROM users WHERE email=:email_id");	// Missing email confirmation
		// searches for email in db for verification
		$stmt->execute(array(":email_id"=>$email));
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		if($stmt->rowCount() > 0){			
				return False;
		} else {
			return True;
		};
	}

	public function register($reg_data) // Set variables according to your form
	{
		try
		{	$token =md5(uniqid(rand()));
            $code = mt_rand(12000, 999999999);
			$params = array(
			':name' => $reg_data['name'],
			':username' => $reg_data['username'],
			':email' => $reg_data['email'],
			':upass' => password_hash($reg_data['pass'], PASSWORD_DEFAULT),
			':dob' => $reg_data['date_of_birth'],
			':temp_token' => $token,
			':temp_code' => $code
		);					
			$stmt = $this->conn->prepare("INSERT INTO users(name, username, email, upass, date_of_birth, temptoken, temp_code) 
			 VALUES(:name, :username, :email, :upass, :dob, :temp_token, :temp_code)");
			$stmt->execute($params);
			
			// send activation email
			$message = "
						Hello ".$params[':name'].",
						<br /><br />
						This is your verification link, Please follow the link to complete Registration<br/>
						<br /><br />
						<a href='http://localhost/php-repos/registration/index.php?activate&id=$code&token=$token'>Click here to activate</a>
						<br /><br />";
			$subject = "Welcome to PHP User System";

			$this->send_mail($params[':email'], $message, $subject);
			return True;
		}
		catch(PDOException $ex)
		{
			echo $ex->getMessage();
			return False;
		}
	}
	
	public function login($input_email,$input_password)
	{
		try
		{
			$stmt = $this->conn->prepare("SELECT * FROM users WHERE email=:email");
			$stmt->execute(array(":email"=>$input_email));
			$userRow=$stmt->fetch(PDO::FETCH_ASSOC);
			
			if($stmt->rowCount() < 1){
				return false;	
			} else {
				$user_pass = $input_password;
				//prepare password for comparison
				if (password_verify($user_pass, $userRow['upass']) && $userRow['is_verified']!==0){
					$payload = array(
						"id" => $userRow['user_id'],
						"dob" => $userRow['date_of_birth'],
						"exp" => time() + (60*60*24)				// set expirey
					);
					$token = $this->generate_token($payload);
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
					$_SESSION['msg'] = "Input do not match \n the password you entered is: ".password_hash($input_password, PASSWORD_BCRYPT)." and the stored is: ".$userRow["upass"];
					return false;
				}
			}
						
		}
		catch(PDOException $ex)
		{
			echo $ex->getMessage();
		}
	}
	
	public function redirect($url)
	{
		header("Location: $url");
	}
	
	public function logout($s_token)
	{
		session_destroy();
		$this->destroy_cookie($s_token);
	}

	public function send_pass_reset($email){
		// Send email
		try{
			$stmt = $this->runQuery("SELECT * FROM users WHERE email=:email");
			$stmt->execute(array(':email'=>$email));
			$row = $stmt->fetch(PDO::FETCH_ASSOC);	
			if($stmt->rowCount() == 1) {
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
					<a href='http://localhost/php-repos/registration/views/resetpass.php?reset&id=$code&token=$token'>click here to reset your password</a>
					<br /><br />
					thank you :)
					";
				$subject = "Password Reset";
			
				$this->send_mail($email,$message,$subject);
				return True;
			} else {
				return False;
			}
		} catch(PDOException $ex){
			echo $ex->getMessage();
			return False;
		}
		
	}

	// Reset user password 
	public function update_password($pass, $code){
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

	private function send_mail($uemail,$message,$subject){						
		$mail = new PHPMailer(true);
		$mail->IsSMTP();
		$mail->isHTML(true);
		$mail->Port = '587'; 
		// $mail->SMTPDebug  = 4;                     	// uncomment to enable detailed debuging
		$mail->SMTPAuth   = true;                  		//
		$mail->SMTPSecure = "tls";                 		// Set mailer configuration to your Mail service
		$mail->Host       = "smtp.mailgun.org";      	//           			//
		$mail->addAddress($uemail);
		$mail->Username="postmaster@sandboxd3612106dbc141b7a5134f0fddce769a.mailgun.org";  
		$mail->Password="12345";            
		$mail->From ='sam.otb@hotmail.ca';
		$mail->AddReplyTo("sam.otb@hotmail.ca");
		$mail->Subject    = $subject;
		$mail->Body = $message;
		$mail->Send();
		if(!$mail->send()) {
			echo 'Mailer Error: ' . $mail->ErrorInfo . "\n";
		}
	}

	//Activate user from email 
	public function activate_user($id, $token){		
		$statusY = "1"; // Yes activated account
		$statusN = "0"; // No not activated account
		$empty = " ";
		$empty_code = 0;
		$stmt = $this->runQuery("SELECT temptoken, temp_code, user_id, date_of_birth, is_verified FROM users WHERE temp_code=:id AND temptoken=:token LIMIT 1");
		$stmt->execute(array(":id"=>$id,":token"=>$token));
		$row=$stmt->fetch(PDO::FETCH_ASSOC);
		if($stmt->rowCount() > 0) {
			if($row['is_verified']==$statusN){
				$client_ip = $this->get_ip_address();	
				$stmt = $this->runQuery("UPDATE users SET is_verified=:status, temptoken=:empty, temp_code=:empty_code, last_ip=:client_ip WHERE temp_code=:id");
				$stmt->bindparam(":id", $id);
				$stmt->bindparam(":status",$statusY);
				$stmt->bindparam(":empty", $empty);
				$stmt->bindparam(":empty_code", $empty_code);
				$stmt->bindparam(":client_ip", $client_ip);
				$stmt->execute();	
				if($stmt->execute()){
					$payload = array(
						"id" => $row['user_id'],
						"dob" => $row['date_of_birth'],
						"exp" => time() + (2*60)				// set expirey
					);
					//Get the jwt token
					// encode the payload using our secretkey and return the token
					$jwt_token = $this->generate_token($payload);
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

	//Get user info
	public function fetch_user_datatable($user){
		// prepare statement
		$stmt = $this->conn->prepare("SELECT * FROM users WHERE user_id=:id");
		$stmt->execute(array(":id"=>$user));
		$userRow=$stmt->fetch(PDO::FETCH_ASSOC);
		function verified($int){
			if ($int == 0){
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
	
	private function generate_token ($payload) {
	// using Lcobucci\JWT\Builder JWT Library;
		//prepare claims
		$token = (new Builder())->setIssuer('php-user-system') // Configures the issuer (iss claim)
								->setAudience('http://localhost/php-repos/registration') // Configures the audience (aud claim)
								->setId('4f1g23a12aa', true) // Configures the id (jti claim), replicating as a header item
								->setIssuedAt(time()) // Configures the time that the token was issue (iat claim)
								->setExpiration($payload['exp']) // Configures the expiration time of the token (exp claim)
								->set('uid', $payload['id']) // Configures a new claim, called "uid"
								->set('dob', $payload['dob']) // Configures a new claim, called "dob"
								->sign($this->signer, $this->secret_key) // creates a signature using "testing" as key
								->getToken(); // Retrieves the generated token

		// encode the payload using our secretkey and return the token
		return $token;
	}
	// parse token string back to token object
	private function token_parser($s_token){
		$token = (new Parser())->parse((string) $s_token);
		return $token;
	}

	function retrieve_token_claim($claim){
		$s_token = $_COOKIE['jwt'];
		$token = $this->token_parser($s_token); // Parses from a string
		$token->getHeaders(); // Retrieves the token header
		$retrieved_claim = $token->getClaim($claim); // Retrieves the token claims
		return $retrieved_claim;
	}

	function verify_token($s_token){
		//Prepare algorith to verify
		// $signer = new Sha256();

		$token = $this->token_parser($s_token);

		$signature_status = $token->verify($this->signer, $this->secret_key);
		return $signature_status;
	}

	function validate_token ($s_token){
		if($this->verify_token($s_token)){
			$token = $this->token_parser($s_token);
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
	function set_cookie($token){
		$expirey = time()+60*60*24*7;
		setcookie('jwt', $token, $expirey, '/');
	}

	function destroy_cookie($s_token){
		setcookie('jwt', $s_token, time()-86400*30, '/');
	}
}