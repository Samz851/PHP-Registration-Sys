<?php ob_start(); ?>
<?php include 'header.php'; ?>
<!-- Login Controller -->
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$user = null;
$msg = " ";
$modal = False;
$user = new USER(); // create new Object of class

// Returning users redirected to Members' page
if(isset($_COOKIE['jwt'])){
    $s_token = $_COOKIE['jwt'];
    if($user->validate_token($s_token)){
        $user->redirect('http://localhost/php-repos/registration/views/members.php');
    }
};

// Handle reset password
if(isset($_GET['reset'])){
	//show modal
	$modal = '<div class="alert alert-success custom-modal main-center">
    			<button class="close" data-dismiss="alert">&times;</button>
					<form method="post" action="" >
					<div class="form-group">
						<label for="email" class="cols-sm-2 control-label">Please enter your Email</label>
						<div class="cols-sm-10">
							<div class="input-group">
								<span class="input-group-addon"><i class="fa fa-envelope fa" aria-hidden="true"></i></span>
								<input type="text" class="form-control" name="reset_email" placeholder="Enter your Email"/>
								<input type="submit" name = "reset_submit" class="btn btn-success"></input>
							</div>
						</div>
					</div>
				</form>
  			</div>';
}
// Check if empty fields
    // Required field names
$required = array('email', 'pass');
	// Loop over field names, make sure each one exists and is not empty
if(isset($_POST ['submit'])){
    $error = false;
    foreach($required as $field) {
        if (empty($_POST[$field])) {
            $error = true;
        }
    };
    if($error){
        $msg = "Please fill out all required fields";
    } else {
		$user = new USER();
		// login function
		$input_email = $_POST['email'];
		$input_password = $_POST['pass'];
		//Compare passwords
		if($user->login($input_email, $input_password)) {
			header("Location: http://localhost/php-repos/registration/views/members.php");
		} else {
			$msg = $_SESSION['msg'];
		}

    }
}

//Send reset Password
if(isset($_POST['reset_submit'])){
	$modal = false;
		if($user->send_pass_reset($_POST['reset_email'])){
			$modal = '<div class="alert alert-success custom-modal main-center">
						<button class="close" data-dismiss="alert">&times;</button>
						<div class="alert-info">
						<strong>Success !</strong>  Please check your email for an activation link
						</div>
					</div>';
		} else {
			$modal = '<div class="alert alert-danger custom-modal main-center">
						<button class="close" data-dismiss="alert">&times;</button>
						<div class="alert-info">
						<strong>Failed !</strong>  No User found with this email
						</div>
					</div>';
		}
}

?>
<div class="jumbotron">

	<h3 class="display-3">Please Login</h3>
	<div id="modalmsg"><?php if($modal){
	echo $modal;
}; ?>
<div>
	<div class="row main">
		<div class="main-login main-center">
			<h5>Secure PHP Registration System</h5>
				<form class="" method="post" action="" >
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
						<label for="password" class="cols-sm-2 control-label">Password</label>
						<div class="cols-sm-10">
							<div class="input-group">
								<span class="input-group-addon"><i class="fa fa-lock fa-lg" aria-hidden="true"></i></span>
								<input type="password" class="form-control" name="pass" id="password"  placeholder="Enter your Password"/>
							</div>
						</div>
					</div>

					<div class="form-group ">
						<input type="submit" name = "submit" class="btn btn-primary"></input>
						<button type="button" class="btn btn-danger pull-right"><a href="?reset" style="color:white;">Forgot Password?</a></button> 
					</div>
				</form>
			<div id="msg"><?php echo $msg; ?> </div>
		</div>
	</div>
</div>
<?php include 'footer.php'?>