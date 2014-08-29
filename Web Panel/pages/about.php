<?php
	if (!defined('SECURE_INCLUDE')) {
		header('HTTP/1.1 403 Forbidden');
		exit;
	}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		
		<title>Weather &ndash; About</title>
		
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
				<?php if (is_connected()) { ?>
				<ul class="nav navbar-top-links navbar-right">
					<li class="dropdown">
						<a class="dropdown-toggle" data-toggle="dropdown" href="#">
							<i class="fa fa-user fa-fw"></i>  <i class="fa fa-caret-down"></i>
						</a>
						
						<ul class="dropdown-menu dropdown-user">
							<li>
								<a href="index.php?page=logout"><i class="fa fa-sign-out fa-fw"></i> Logout</a>
							</li>
						</ul>
					</li>
				</ul>
				<?php } ?>
			</nav>
			
			<nav class="navbar-default navbar-static-side" role="navigation">
				<div class="sidebar-collapse">
					<ul class="nav">
						<?php if (is_connected()) { ?>
						<li>
							<a href="index.php?page=home"><i class="fa fa-dashboard fa-fw"></i> Home</a>
						</li>
						
						<li>
							<a href="index.php?page=settings"><i class="fa fa-wrench fa-fw"></i> Settings</a>
						</li>
						<?php } ?>
						
						<li>
							<a href="index.php?page=about"><i class="fa fa-bullhorn fa-fw"></i> About</a>
						</li>
						
						<?php if ($need_install) { ?>
						<li>
							<a href="index.php?page=install"><i class="fa fa-rocket fa-fw"></i> Install</a>
						</li>
						<?php } ?>
					</ul>
				</div>
			</nav>
			
			<div id="page-wrapper">
				<div class="row">
					<div class="col-lg-12">
						<h1 class="page-header">About</h1>
					</div>
				</div>
				
				<div class="row">
					<div class="col-lg-2 text-center">
						<img src="https://www.virustotal.com/fr/user/Xylitol/avatar/" alt="Xylitol" class="img-circle">
					</div>
					
					<div class="col-lg-10">
						<div class="panel panel-info">
							<div class="panel-heading text-center">
								About Xylitol
							</div>
							
							<div class="panel-body">
								<p>
									Lorem ipsum dolor sit amet, consectetur adipiscing elit.<br />
									Vestibulum tincidunt est vitae ultrices accumsan.<br />
									Aliquam ornare lacus adipiscing, posuere lectus et, fringilla augue.
								</p>
							</div>
							
							<div class="panel-footer text-center">
								<a href="http://www.xylibox.com/" alt="Xylibox" target="_blank">Xylibox</a> &ndash; <a href="http://cybercrime-tracker.net/" alt="Xylibox" target="_blank">CyberCrime Tracker</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		
		<script src="resources/js/jquery-1.10.2.js"></script>
		<script src="resources/js/bootstrap.min.js"></script>
		<script src="resources/js/main.js"></script>
	</body>
</html>