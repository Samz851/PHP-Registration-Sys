
<?php include '../views/header.php'; ?>
<?php
$msg = "";
	// This is the page the user arrives at to set new password
$user = new USER();
if(isset($_GET['reset'])){
	if(empty($_GET['id']) && empty($_GET['code'])){
		$user->redirect('http://localhost/php-repos/registration/');
	} else {
		$code = $_GET['id'];
		$token = $_GET['token'];
	
		$stmt = $user->runQuery("SELECT * FROM users WHERE temp_code=:code AND temptoken=:token");
		$stmt->execute(array(":code"=>$code,":token"=>$token));
		$rows = $stmt->fetch(PDO::FETCH_ASSOC);
	
		if($stmt->rowCount() > 0) {
			if(isset($_POST['submit_new_password'])) {
				$pass = $_POST['new_password'];
				$cpass = $_POST['confirm'];
				if($cpass!==$pass) {
					$msg = "<div class='alert alert-block'>
							<button class='close' data-dismiss='alert'>&times;</button>
							<strong>Sorry!</strong>  Password Doesn't match. 
							</div>";
				} else {
					if($user->update_password($pass, $code)){
					$msg = "<div class='alert alert-success'>
						<button class='close' data-dismiss='alert'>&times;</button>
						Password Changed Please login again.
						</div>";
					} else {
						$msg = "<div class='alert alert-warning'>
						<button class='close' data-dismiss='alert'>&times;</button>
						Failed to reset password.
						</div>";
					}
				}
			}	
		} else {
			$msg = "<div class='alert alert-success'>
			<button class='close' data-dismiss='alert'>&times;</button>
			No Account Found, Try again
			</div>";		
		};
		$user=null;
	}
}
?>
<div class="jumbotron">
	<div class="row main">
		<div class="main-login main-center">
			<h5>Secure PHP Registration System</h5>
				<form class="" method="post" action="" >
					<div class="form-group">
						<label for="email" class="cols-sm-2 control-label">Enter a new password</label>
						<div class="cols-sm-10">
							<div class="input-group">
								<span class="input-group-addon"><i class="fa fa-envelope fa" aria-hidden="true"></i></span>
								<input type="password" class="form-control" name="new_password" placeholder="Enter password"/>
								<input type="password" class="form-control" name="confirm" placeholder="Re-enter password"/>
							</div>
						</div>
					</div>
					<div class="form-group ">
						<input type="submit" name = "submit_new_password" class="btn btn-primary"></input>
					</div>
				</form>
			<div id="msg"><?php echo $msg; ?> </div>
		</div>
	</div>
</div>
<?php include '../views/footer.php'?>