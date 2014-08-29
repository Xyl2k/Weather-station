<?php
	if (!defined('SECURE_INCLUDE')) {
		header('HTTP/1.1 403 Forbidden');
		exit;
	}
	
	if (!is_connected()) {
		redirect('login');
	}
	
        if (isset($_GET['reset'])) {
            reset_database();
            
            redirect('settings&success');
        }
        
	if (isset($_GET['success'])) {
		$success = true;
	}
	
	$vars     = array('username', 'password', 'arduino_key');
	$continue = true;
	
	foreach ($vars as $var) {
		if (!isset($_POST[$var]) || !is_string($_POST[$var])) {
			$continue = false;
			break;
		}
	}
	
	if ($continue) {
		$username    = $_POST['username'];
		$password    = $_POST['password'];
		$arduino_key = $_POST['arduino_key'];
		
		if (strlen($username) > 0) {
			if (strlen($arduino_key) >= 8) {
				if (strlen($password) === 0) {
					create_config(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_DB, $username, PASSWORD, $arduino_key, false);
					
					redirect('settings&success');
				}
				else if (strlen($password) >= 8) {
					create_config(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_DB, $username, $password, $arduino_key);
					
					redirect('settings&success');
				}
				else {
					$error = 'Password must be at least 8 characters!';
				}
			}
			else if (strlen($arduino_key) > 0) {
				$error = 'Arduino Key must be at least 8 characters!';
			}
			else {
				$error = 'Empty Arduino Key field!';
			}
		}
		else {
			$error = 'Empty Username field.';
		}
	}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		
		<title>Weather &ndash; Settings</title>
		
		<link href="resources/css/bootstrap.min.css" rel="stylesheet">
		<link href="resources/font-awesome/css/font-awesome.min.css" rel="stylesheet">
		<link href="resources/css/main.css" rel="stylesheet">
	</head>
	
	<body>
		<div id="wrapper">
			<nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
				<div class="navbar-header">
					<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".sidebar-collapse">
						<span class="sr-only">Toggle navigation</span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
					
					<a class="navbar-brand" href="index.html">Weather</a>
				</div>
				
				<ul class="nav navbar-top-links navbar-right">
					<li class="dropdown">
						<a class="dropdown-toggle" data-toggle="dropdown" href="#">
							<i class="fa fa-user fa-fw"></i>
							<i class="fa fa-caret-down"></i>
						</a>
						
						<ul class="dropdown-menu dropdown-user">
							<li>
								<a href="index.php?page=logout"><i class="fa fa-sign-out fa-fw"></i> Logout</a>
							</li>
						</ul>
					</li>
				</ul>
			</nav>
			
			<nav class="navbar-default navbar-static-side" role="navigation">
				<div class="sidebar-collapse">
					<ul class="nav">
						<li>
							<a href="index.php?page=home"><i class="fa fa-dashboard fa-fw"></i> Home</a>
						</li>
						
						<li>
							<a href="index.php?page=settings"><i class="fa fa-wrench fa-fw"></i> Settings</a>
						</li>
						
						<li>
							<a href="index.php?page=about"><i class="fa fa-bullhorn fa-fw"></i> About</a>
						</li>
					</ul>
				</div>
			</nav>
			
			<div id="page-wrapper">
				<div class="row">
					<div class="col-lg-12">
						<h1 class="page-header">Settings</h1>
					</div>
				</div>
				
				<div class="row">
					<div class="col-lg-12">
						<?php if (isset($error)) { ?>
							<div class="alert alert-danger alert-dismissable">
								<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
								
								<b>Error:</b> <?php display($error); ?>
							</div>
						<?php } else if (isset($success)) { ?>
							<div class="alert alert-success alert-dismissable">
								<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
								
								<b>Success:</b> Operation done!
							</div>
						<?php } ?>
						
						<form role="form" action="" method="POST">
							<fieldset>
								<div class="form-group">
									<label>Username</label>
									
									<input class="form-control" placeholder="Username" name="username" type="text" value="<?php display(USERNAME, true); ?>" autocomplete="off">
								</div>
								
								<div class="form-group">
									<label>Password</label>
									
									<input class="form-control" placeholder="Password" name="password" type="text" value="" autocomplete="off">
								</div>
								
								<div class="form-group">
									<label>Arduino Key</label>
									
									<input class="form-control" placeholder="Arduino Key" name="arduino_key" type="text" value="<?php display(ARDUINO_KEY, true); ?>" autocomplete="off">
								</div>
								
								<input class="btn btn-lg btn-success btn-block" type="submit" value="Edit">
                                                                
                                                                <a class="btn btn-lg btn-danger btn-block" href="?page=settings&reset">Reset</a>
							</fieldset>
						</form>
						
						<br />
					</div>
				</div>
			</div>
		</div>
		
		<script src="resources/js/jquery-1.10.2.js"></script>
		<script src="resources/js/bootstrap.min.js"></script>
		<script src="resources/js/main.js"></script>
	</body>
</html>