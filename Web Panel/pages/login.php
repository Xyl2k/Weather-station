<?php
	if (!defined('SECURE_INCLUDE')) {
		header('HTTP/1.1 403 Forbidden');
		exit;
	}
	
	if (is_connected()) {
		redirect('home');
	}
	
	if (isset($_POST['username']) && isset($_POST['password']) && isset($_POST['captcha'])) {
		$username = $_POST['username'];
		$password = $_POST['password'];
		$captcha  = $_POST['captcha'];
		
		if (is_string($username) && is_string($password) && is_string($captcha)) {
			if ($captcha === $_SESSION['captcha']) {
				if ($username === USERNAME && hash('whirlpool', $password) === PASSWORD) {
					$_SESSION['username'] = USERNAME;
					$_SESSION['password'] = PASSWORD;
					
					redirect('home');
				}
				else {
					$error          = 'Unknown username or bad password!';
					$username_error = true;
				}
			}
			else {
				$error         = 'Invalid captcha!';
				$captcha_error = true;
			}
		}
		else {
			$error = "Don't try anything stupid...";
		}
	}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		
		<title>Weather &ndash; Login</title>
		
		<link href="resources/css/bootstrap.min.css" rel="stylesheet">
		<link href="resources/css/main.css" rel="stylesheet">
	</head>
	
	<body>
		<div class="container">
			<div class="row">
				<div class="col-md-4 col-md-offset-4">
					<div class="login-panel panel panel-default">
						<div class="panel-heading">
							<h3 class="panel-title text-center">Weather &ndash; Login</h3>
						</div>
						
						<div class="panel-body">
							<?php if (isset($error)) { ?>
							<div class="alert alert-danger alert-dismissable">
								<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
								
								<b>Error:</b> <?php display($error); ?>
							</div>
							<?php } ?>
							
							<form role="form" action="" method="POST">
								<fieldset>
									<div class="form-group<?php if (isset($username_error)) { display(' has-error'); } ?>">
										<input class="form-control" placeholder="Username" name="username" type="text" autofocus>
									</div>
									
									<div class="form-group<?php if (isset($username_error)) { display(' has-error'); } ?>">
										<input class="form-control" placeholder="Password" name="password" type="password" value="">
									</div>
									
									<div class="form-group<?php if (isset($captcha_error)) { display(' has-error'); } ?>">
										<label><img class="center" src="index.php?page=captcha&x=<?php display(uniqid()); ?>" alt="captcha" width="197px" height="60px" /></label>
										
										<input class="form-control" placeholder="Captcha" name="captcha" type="text" value="">
									</div>
									
									<input class="btn btn-lg btn-success btn-block" type="submit" value="Login">
								</fieldset>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
		
		<script src="resources/js/jquery-1.10.2.js"></script>
		<script src="resources/js/bootstrap.min.js"></script>
	</body>
</html>