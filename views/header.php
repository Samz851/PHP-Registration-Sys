<?php 
    session_start(); 
    require_once __DIR__.'/../app/class.user.php';
    
    $user = null;
    $user_logged_in = False;
    //Handles activation redirect
    if(isset($_GET['activate'])){
        if(isset($_GET['id']) && isset($_GET['token'])){	
	        $user = new USER();
	        $id = $_GET['id'];
	        $token = $_GET['token'];
	        $activation_status = $user->activate_user($id, $token);
	
            if($activation_status == "success"){
                $msg = "<div class='alert alert-success'>
                <button class='close' data-dismiss='alert'>&times;</button>
                <strong>WoW !</strong>  Your Account is Now Activated : <a href='index.php'>Login here</a>
                </div>";
            } else if ($activation_status == "not found"){
                $msg = "<div class='alert alert-error'>
                <button class='close' data-dismiss='alert'>&times;</button>
                <strong>sorry !</strong>  No Account Found : <a href='http://localhost/php-repos/registration/form.php'>Signup here</a>
                </div>";
            } else if ($activation_status == "already activated"){
                $msg = "<div class='alert alert-error'>
                <button class='close' data-dismiss='alert'>&times;</button>
                <strong>sorry !</strong>  Your Account is allready Activated : <a href='index.php'>Login here</a>
                </div>";
            } else {
                $msg = "<div class='alert alert-error'>
                <button class='close' data-dismiss='alert'>&times;</button>
                <strong>Sorry !</strong>  Not enough information provided
                </div>";
            }
        }
    } else if (isset($_GET['goodbye'])){        //Handling post logout
        $msg = "<div class='alert alert-info'>
		<button class='close' data-dismiss='alert'>&times;</button>
		   <strong>Goodbye !</strong>  You have successfully logged out
		</div>";
    } else if(!isset($_COOKIE['jwt']) && !isset($_GET['goodbye']) && !isset($_GET['activate'])){
        $msg = "<div class='alert alert-info'>
        <button class='close' data-dismiss='alert'>&times;</button>
           <strong>Hello Guest</strong>  Sign Up with us today! <a href='/views/form.php'>Register here</a>
        </div>";
    } else if(isset($_COOKIE['jwt']) && isset($_GET['logout'])) {
        //Handle logout
        $user = new USER();
        $s_token = $_COOKIE['jwt'];
        $user->logout($s_token);
        $user_logged_in = False;
        $user->redirect('http://localhost/php-repos/registration/?goodbye');
    } else {
        $user = new USER();
        $s_token = $_COOKIE['jwt'];
        if($user->validate_token($s_token)){
            $user_logged_in = True;
            $now = time();
            $now = date('r', $now);
            $id = $user->retrieve_token_claim('uid');
            $expirey = $user->retrieve_token_claim('exp');
            $expirey = date('r', $expirey);
            $dt = new DateTime(@$expirey);  // convert UNIX timestamp to PHP DateTime
            $dt= $dt->format('Y-m-d H:i:s'); // output = 2017-01-01 00:00:00
            $msg = "Welcome Back! ".$id." You have ".$expirey." until the JWT expires, the time now is: ".$now;
        }else{
            $user->destroy_cookie($s_token);
            $user_logged_in = False;
            $msg = "<div class='alert alert-danger'>
            <button class='close' data-dismiss='alert'>&times;</button>
               <strong>Oops</strong>  You are logged out, please <a href='index.php'>Login here</a>
            </div>";
        }
        
    };
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- Bootstrap CSS and JS -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
        <link href="https://bootswatch.com/4/minty/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.0/js/bootstrap.min.js"></script>
        
		<!-- Website Font style -->
	    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.1/css/font-awesome.min.css">
		<link rel="stylesheet" href="/php-repos/registration/style.css">
		<!-- Google Fonts -->
		<link href='https://fonts.googleapis.com/css?family=Passion+One' rel='stylesheet' type='text/css'>
		<link href='https://fonts.googleapis.com/css?family=Oxygen' rel='stylesheet' type='text/css'>
        <title>Registration App</title>
    </head>
    <body>
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
            <a class="navbar-brand" href="http://localhost/php-repos/registration/">PHP-USER-SYS</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarColor01" aria-controls="navbarColor01" aria-expanded="false" aria-label="Toggle navigation" style="">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarColor01">
                <ul class="navbar-nav mr-auto">
                <li class="nav-item active"><a class="nav-link" href='/php-repos/registration/views/home.php'>Home <span class="sr-only">(current)</span></a></li>
                <li class="nav-item"><a class="nav-link" href='/php-repos/registration/views/form.php'>Registration</a></li>
                <li class="nav-item"><a class="nav-link" href="#">About</a></li>
                <li class="nav-item"><a class="nav-link" href='/php-repos/registration/views/login.php'>Login</a></li>
                <li class="nav-item"><a class="nav-link" href='?logout'>Logout</a></li>
                </ul>
                <form class="form-inline my-2 my-lg-0">
                    <input class="form-control mr-sm-2" placeholder="Search" type="text">
                    <button class="btn btn-secondary my-2 my-sm-0" type="submit">Search</button>
                </form>
            </div>
        </nav>