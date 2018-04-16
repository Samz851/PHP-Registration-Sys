
<?php include 'header.php'; ?>
<!-- Register Controller -->
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Returning users redirected to Members' page
if(isset($_COOKIE['jwt'])){
    $user = new USER();
    $s_token = $_COOKIE['jwt'];
    if($user->validate_token($s_token)){
        $user->redirect('http://localhost/php-repos/registration/views/members.php');
    }
};

$msg = " ";
$password_error = false;
$reg_user = new USER(); // create new Object of class
// Check if empty fields
    // Required field names
$required = array('name', 'username', 'upass', 'confirm', 'email', 'dob');
$regexpressions ='/^(?=.*?[a-z])(?=.*?[A-Z])(?=.*?[\d])(?=.*?[\W]).{1,}$/';
// Loop over field names, make sure each one exists and is not empty


if(isset($_POST ['submit'])){
    $error = false;
    foreach($required as $field) {		//Check for empty fields
        if (empty($_POST[$field])) {
            $error = true;
        }
	};
if(!preg_match($regexpressions, $_POST['upass'])){
	$error = true;
	$password_error = true;
}

    if($error && $password_error){
        $msg = 'Password must contain atleast one letter, one number, and one symbol';
    } else if($error && !$password_error){
		$msg = "Please fill out all required fields";
	} else {
        if($_POST['upass'] == $_POST['confirm']){				// on button click - Checks for password match
            $reg_data = array(
                "name" => $_POST['name'],
                "username" => $_POST['username'],
                "email" => $_POST['email'],
                "pass" => $_POST['upass'],
                "date_of_birth" => $_POST['dob']
            );
			if(!$reg_user->check_email($_POST['email'])){
				$msg = "<div class='alert alert-error'><button class='close' data-dismiss='alet'>&times;</button>
				<strong>Email Already Exist</strong></div>";
			} else {
				if($reg_user->register($reg_data)){
					$msg = "
						<div class='alert alert-success'><button class='close' data-dismiss='alert'>&times;</button><strong>We have sent an email to: ".$_POST['email']." Please click on the verification link</div>";
				} else {
				$msg = "<div class='alert alert-Danger'><button class='close' data-dismiss='alert'>&times;</button><strong>Something went wrong! Please try singing up againg</div>";
				}
		}
        
        }
    }
}

?>
<div class="jumbotron">
	<h3 class="display-3">Sign up with us</h3>
	<div class="row main">
		<div class="main-login main-center">
			<h5>Secure PHP Registration System</h5>
				<form class="" method="post" action="" >
					<div class="form-group">
						<label for="name" class="cols-sm-2 control-label">Your Name</label>
						<div class="cols-sm-10">
							<div class="input-group">
								<span class="input-group-addon"><i class="fa fa-user fa" aria-hidden="true"></i></span>
								<input type="text" class="form-control" name="name" id="name"  placeholder="Enter your Name"/>
							</div>
						</div>
					</div>

					<div class="form-group">
						<label for="email" class="cols-sm-2 control-label">Your Email</label>
						<div class="cols-sm-10">
							<div class="input-group">
								<span class="input-group-addon"><i class="fa fa-envelope fa" aria-hidden="true"></i></span>
								<input type="text" class="form-control" name="email" id="uemail"  placeholder="Enter your Email"/>
							</div>
						</div>
					</div>

					<div class="form-group">
						<label for="username" class="cols-sm-2 control-label">Username</label>
						<div class="cols-sm-10">
							<div class="input-group">
								<span class="input-group-addon"><i class="fa fa-users fa" aria-hidden="true"></i></span>
								<input type="text" class="form-control" name="username" id="username"  placeholder="Enter your Username"/>
							</div>
						</div>
					</div>
					<div class="form-group">
						<label for="udob" class="cols-sm-2 control-label">D.O.B.</label>
						<div class="cols-sm-10">
							<div class="input-group">
								<span class="input-group-addon"><i class="fa fa-calendar fa" aria-hidden="true"></i></span>
								<input type="date" class="form-control" name="dob" id="udob" />
							</div>
						</div>
					</div>

					<div class="form-group">
						<label for="password" class="cols-sm-2 control-label">Password</label>
						<div class="cols-sm-10">
							<div class="input-group">
								<span class="input-group-addon"><i class="fa fa-lock fa-lg" aria-hidden="true"></i></span>
								<input type="password" class="form-control" name="upass" id="password"  placeholder="Enter your Password"/>
							</div>
						</div>
					</div>

					<div class="form-group">
						<label for="confirm" class="cols-sm-2 control-label">Confirm Password</label>
						<div class="cols-sm-10">
							<div class="input-group">
								<span class="input-group-addon"><i class="fa fa-lock fa-lg" aria-hidden="true"></i></span>
								<input type="password" class="form-control" name="confirm" id="confirm"  placeholder="Confirm your Password"/>
							</div>
						</div>
					</div>

					<div class="form-group ">
						<input type="submit" name = "submit" class="btn btn-primary"></input>
					</div>
				</form>
			<div id="msg"><?php echo $msg; ?> </div>
		</div>
	</div>
</div>
<?php include 'footer.php'?>