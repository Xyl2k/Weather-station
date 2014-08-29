<?php
	if (!defined('SECURE_INCLUDE')) {
		header('HTTP/1.1 403 Forbidden');
		exit;
	}
	
	if (!$need_install) {
		redirect('index');
	}
	else {
		$continue = true;
		$vars     = array('mysql_host', 'mysql_user', 'mysql_pass', 'mysql_db', 'username', 'password', 'arduino_key');
		
		foreach ($vars as $var) {
			if (!isset($_POST[$var]) || !is_string($_POST[$var])) {
				$continue = false;
				break;
			}
		}
		
		if ($continue) {
			$mysql_host  = $_POST['mysql_host'];
			$mysql_user  = $_POST['mysql_user'];
			$mysql_pass  = $_POST['mysql_pass'];
			$mysql_db    = $_POST['mysql_db'];
			$username    = $_POST['username'];
			$password    = $_POST['password'];
			$arduino_key = $_POST['arduino_key'];
			
			if (strlen($mysql_host) > 0) {
				if (strlen($mysql_user) > 0) {
					if (strlen($mysql_db) > 0) {
						if (strlen($username) > 0) {
							if (strlen($password) >= 8) {
								if (strlen($arduino_key) >= 8) {
									$mysql = null;
									
									try {
										$mysql = new PDO('mysql:host=' . $mysql_host . ';dbname=' . $mysql_db, $mysql_user, $mysql_pass);
									}
									catch (Exception $e) {
										$error = 'Cannot connect to MySQL database : ' . $e->getMessage() . '.';
									}
									
									if ($mysql !== null) {
										create_config($mysql_host, $mysql_user, $mysql_pass, $mysql_db, $username, $password, $arduino_key);
										
										$mysql->query(install_table());
										
										redirect('index');
									}
								}
								else if (strlen($arduino_key) > 0) {
									$error = 'Password must be at least 8 characters!';
								}
								else {
									$error = 'Empty Password field!';
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
							$error = 'Empty Username field!';
						}
					}
					else {
						$error = 'Empty MySQL Database field!';
					}
				}
				else {
					$error = 'Empty MySQL Username field!';
				}
			}
			else {
				$error = 'Empty MySQL Hostname field!';
			}
		}
	}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		
		<title>Weather &ndash; Installation</title>
		
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
			</nav>
			
			<nav class="navbar-default navbar-static-side" role="navigation">
				<div class="sidebar-collapse">
					<ul class="nav">
						<li>
							<a href="index.php?page=about"><i class="fa fa-bullhorn fa-fw"></i> About</a>
						</li>
						
						<li>
							<a href="index.php?page=install"><i class="fa fa-rocket fa-fw"></i> Install</a>
						</li>
					</ul>
				</div>
			</nav>
			
			<div id="page-wrapper">
				<div class="row">
					<div class="col-lg-12">
						<h1 class="page-header">Installation</h1>
					</div>
				</div>
				
				<div class="row">
					<div class="col-lg-12">
						<?php if (isset($error)) { ?>
							<div class="alert alert-danger alert-dismissable">
								<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
								
								<b>Error:</b> <?php display($error); ?>
							</div>
						<?php } ?>
						
						<form role="form" action="" method="POST">
							<fieldset>
								<div class="form-group">
									<input class="form-control" placeholder="MySQL Hostname" name="mysql_host" type="text" autofocus autocomplete="off">
								</div>
								
								<div class="form-group">
									<input class="form-control" placeholder="MySQL Username" name="mysql_user" type="text" autocomplete="off">
								</div>
								
								<div class="form-group">
									<input class="form-control" placeholder="MySQL Password" name="mysql_pass" type="text" autocomplete="off">
								</div>
								
								<div class="form-group">
									<input class="form-control" placeholder="MySQL Database" name="mysql_db" type="text" autocomplete="off">
								</div>
								
								<div class="form-group">
									<input class="form-control" placeholder="Username" name="username" type="text" autocomplete="off">
								</div>
								
								<div class="form-group">
									<input class="form-control" placeholder="Password" name="password" type="text" autocomplete="off">
								</div>
								
								<div class="form-group">
									<input class="form-control" placeholder="Arduino Key" name="arduino_key" type="text" value="" autocomplete="off">
								</div>
								
								<input class="btn btn-lg btn-success btn-block" type="submit" value="Install!">
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