
<?php include 'header.php'; ?>


<?php 
	if($user_logged_in){
		$user_id = $user->retrieve_token_claim('uid');
	} else {	};
	
?>
<div class="jumbotron text-center">
<h3 class="display-3">Welcome <?php echo $_SESSION['user_id'] ?> This is Members Area</h3>
	<table class="table table-hover">
		<thead>
			<tr>
				<th scope="col">User ID</th>
				<th scope="col">Name</th>
				<th scope="col">username</th>
				<th scope="col">email</th>
				<th scope="col">D.O.B</th>
				<th scope="col">Verified?</th>
				<th scope="col">Last Login Time</th>
				<th scope="col">Last Login IP(IPv6)</th>
			</tr>
		</thead>
		<tbody>
			<tr class="table-primary">
			<?php echo $user->fetch_user_datatable($user_id); ?>
			</tr>
		</tbody>
	</table>
</div>
<?php include 'footer.php'?>