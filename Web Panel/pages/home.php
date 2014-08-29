<?php
    if (!defined('SECURE_INCLUDE')) {
        header('HTTP/1.1 403 Forbidden');
        exit;
    }

    if (!is_connected()) {
        redirect('login');
    }
    
    if (isset($_GET['cycle'])) {
        do_cycle();
        
        $success = true;
    }
    
    $query = $mysql->query('SELECT * FROM `informations`;');

    $informations = $query->fetchAll();
    $informations = parse_informations($informations)
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		
		<title>Weather &ndash; Home</title>
		
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
							<i class="fa fa-user fa-fw"></i>  <i class="fa fa-caret-down"></i>
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
						<h1 class="page-header">Home</h1>
					</div>
				</div>
				
				<div class="row">
					<div class="col-lg-12">
                        <?php if (isset($success)) { ?>
							<div class="alert alert-success alert-dismissable">
								<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
								
								<b>Success:</b> Your device will cycle shortly!
							</div>
						<?php } ?>
                                                
						<?php if (count($informations['lasts']) === 0) { ?>
						<div class="alert alert-info">
							<b>Information:</b> There is no record into the database.
						</div>
						<?php } else { ?>
						<div class="alert alert-warning">
							<b>Warning:</b> Pressure and Altitude should not be taken seriously.
						</div>
						<?php } ?>
						
						<?php if ($facing !== '') { ?>
						<div class="alert alert-info">
							<b>Information:</b> You're facing <?php display($informations['facing']); ?>.
						</div>
						<?php } ?>
                                                
                        <a class="btn btn-lg btn-info btn-block" href="?page=home&cycle">Cycle</a>
                                            
						<div class="table-responsive">
							<table class="table table-striped">
								<tbody>
									<tr>
										<th>First update of the charts</th>
										<td><?php echo(($informations['first'] === null) ? 'N/A' : get_date($informations['first']['date'])); ?></th>
									</tr>
									
									<tr>
										<th>Lastest update of the charts</th>
										<td><?php echo(($informations['last'] === null) ? 'N/A' : get_date($informations['last']['date'])); ?></th>
									</tr>
									
									<tr>
										<th>Current cycle</th>
										<td><?php echo($informations['current_cycle']); ?></th>
									</tr>
									
									<tr>
										<th>Maximum cycles</th>
										<td><?php echo($informations['max_cycles']); ?></th>
									</tr>
									
									<tr>
										<th>Hightest temperature recorded</th>
										<td><?php echo(($informations['high_temp'] === null) ? 'N/A' : (get_date($informations['high_temp']['date']) . ' : ' . $informations['high_temp']['temperature'] . '°C')); ?></th>
									</tr>
									
									<tr>
										<th>Lowest temperature recorded</th>
										<td><?php echo(($informations['low_temp'] === null) ? 'N/A' : (get_date($informations['low_temp']['date']) . ' : ' . $informations['low_temp']['temperature'] . '°C')); ?></th>
									</tr>
									
									<tr>
										<th>Hightest Humidity recorded</th>
										<td><?php echo(($informations['high_hum'] === null) ? 'N/A' : (get_date($informations['high_hum']['date']) . ' : ' . $informations['high_hum']['humidity'] . '%')); ?></th>
									</tr>
									
									<tr>
										<th>Lowest Humidity recorded</th>
										<td><?php echo(($informations['low_hum'] === null) ? 'N/A' : (get_date($informations['low_hum']['date']) . ' : ' . $informations['low_hum']['humidity'] . '%')); ?></th>
									</tr>
									
									<tr>
										<th>Hightest Luminosity recorded</th>
										<td><?php echo(($informations['high_lum'] === null) ? 'N/A' : (get_date($informations['high_lum']['date']) . ' : ' . $informations['high_lum']['luminosity'])); ?></th>
									</tr>
									
									<tr>
										<th>Lowest Luminosity recorded</th>
										<td><?php echo(($informations['low_lum'] === null) ? 'N/A' : (get_date($informations['low_lum']['date']) . ' : ' . $informations['low_lum']['luminosity'])); ?></th>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>

				<?php if (count($informations['lasts']) > 0) { ?>
				<div class="row">
					<div class="col-lg-12">
						<div class="panel panel-default">
							<div class="panel-heading">
								General informations
							</div>
							
							<div class="panel-body">
								<div id="visualization" style="width: 90%"></div>
							</div>
						</div>
					</div>
				</div>
				
				<div class="row">
					<div class="col-lg-12">
						<div class="panel panel-default">
							<div class="panel-heading">
								Average pressure
							</div>
							
							<div class="panel-body">
								<div id="pressure" style="width: 90%"></div>
							</div>
						</div>
					</div>
				</div>
				
				<div class="row">
					<div class="col-lg-12">
						<div class="panel panel-default">
							<div class="panel-heading">
								Average altitude
							</div>
							
							<div class="panel-body">
								<div id="altitude" style="width: 90%"></div>
							</div>
						</div>
					</div>
				</div>
				<?php } ?>
			</div>
		</div>
		
		<script src="resources/js/jquery-1.10.2.js"></script>
		<script src="resources/js/bootstrap.min.js"></script>
		<script src="resources/js/main.js"></script>
		
		<?php if (count($informations['lasts']) > 0) { ?>
		<script type="text/javascript" src="https://www.google.com/jsapi"></script>
		<script type="text/javascript">
			google.load('visualization', '1', {packages: ['corechart']});
			function draw() {
				var data = google.visualization.arrayToDataTable([
					['Days', 'Temperature (°C)', 'Humidity (%)', 'Luminosity'],
<?php foreach ($informations['lasts'] as $information) { ?>
					['<?php echo(get_date($information['date'])); ?>', <?php echo($information['temperature']); ?>, <?php echo($information['humidity']); ?>, <?php echo($information['luminosity']); ?>],
<?php } ?>
				]);
				new google.visualization.LineChart(document.getElementById('visualization')).
				draw(data, {
					curveType: 'function',
					height:    400,
					vAxis: {
						maxValue: 24
					}
				});
			}
			google.setOnLoadCallback(draw);
		</script>
		<script type="text/javascript">
			function draw() {
				var data = google.visualization.arrayToDataTable([
					['days', 'Pressure (Pa)'],
<?php foreach ($informations['lasts'] as $information) { ?>
					['<?php echo(get_date($information['date'])); ?>', <?php echo($information['pressure']); ?>],
<?php } ?>
				]);
				var chart = new google.visualization.LineChart(document.getElementById('pressure'));
				chart.draw(data, {
					height: 400
				});
			}
			google.setOnLoadCallback(draw);
		</script>
		<script type="text/javascript">
			function draw() {
				var data = google.visualization.arrayToDataTable([
					['days', 'Altitude (M)'],
<?php foreach ($informations['lasts'] as $information) { ?>
					['<?php echo(get_date($information['date'])); ?>', <?php echo($information['altitude']); ?>],
<?php } ?>
				]);
				var chart = new google.visualization.LineChart(document.getElementById('altitude'));
				chart.draw(data, {
					height: 400
				});
			}
			google.setOnLoadCallback(draw);
		</script>
		<?php } ?>
	</body>
</html>