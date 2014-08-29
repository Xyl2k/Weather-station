<?php
    if (!defined('SECURE_INCLUDE')) {
        header('HTTP/1.1 403 Forbidden');
        exit;
    }

    $vars   = array('api_key', 'temperature', 'humidity', 'pressure', 'altitude', 'luminosity', 'cycle', 'angle');
    $ints   = array('cycle', 'luminosity');
    $floats = array('humidity', 'temperature', 'pressure', 'altitude', 'angle');

    foreach ($vars as $var) {
        if (!isset($_GET[$var]) || !is_string($_GET[$var]) || strlen($_GET[$var]) <= 0) {
            response('BAD_PARAM');
        }

        $data = $_GET[$var];

        if (in_array($var, $ints)) {
            if (!ctype_digit($data)) {
                return false;
            }
        }
        else if (in_array($var, $floats)) {
            $data  = number_format($data, 2, '.', '');
            $check = number_format((float)$data, 2, '.', '');
            
            if ($data !== $check) {
                response('BAD_FLOAT');
            }
            else {
                $_GET[$var] = $data;
            }
        }
    }

    $api_key     = $_GET['api_key'];
    $temperature = $_GET['temperature'];
    $humidity    = $_GET['humidity'];
    $pressure    = $_GET['pressure'];
    $altitude    = $_GET['altitude'];
    $luminosity  = $_GET['luminosity'];
    $cycle       = $_GET['cycle'];
	$angle       = $_GET['angle'];

    if ($api_key === ARDUINO_KEY) {
        if ($humidity >= 0 && $humidity <= 100) {
			if ($angle >= 0 && $angle <= 360) {
				if ($cycle >= 0) {
					$mysql->query("INSERT INTO `informations` (`id`, `date`, `temperature`, `humidity`, `pressure`, `altitude`, `luminosity`, `cycle`, `angle`) VALUES (NULL, " . time() . ", '" . $temperature . "', '" . $humidity . "', '" . $pressure . "', '" . $altitude . "', '" . $luminosity . "', '" . $cycle . "', '" . $angle . "');");
					
					cycle_done();
					
					response('OK');
				}
				else {
					response('BAD_CYCLE');
				}
			}
			else {
				response('BAD_ANGLE');
			}
        }
        else {
            response('BAD_HUMIDITY');
        }
    }
    else {
        response('BAD_KEY');
    }
?>