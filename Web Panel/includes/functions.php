<?php
    if (!defined('SECURE_INCLUDE')) {
        header('HTTP/1.1 403 Forbidden');
        exit;
    }

    $need_install = !file_exists('includes/config.php');

    if (!$need_install) {
        require_once('includes/config.php');
        require_once('includes/kcaptcha.php');

        session_start();

        try {
            $mysql = new PDO('mysql:host=' . MYSQL_HOST . ';dbname=' . MYSQL_DB, MYSQL_USER, MYSQL_PASS);
        }
        catch (Exception $e) {
            die('Cannot connect to MySQL database, please check MySQL credentials.');
        }
    }
	
	function response($str) {
		die('<arduino>' . $str . '</arduino>');
	}
	
    function reset_database() {
		global $mysql;
		
        $mysql->query("TRUNCATE `informations`");
    }

    function do_cycle() {
        set_cycle('1');
    }

    function has_cycle() {
        return ((file_get_contents('resources/.CYCLE') === '1') ? 'YES' : 'NO');
    }
    
    function cycle_done() {
        set_cycle('0');
    }
    
    function set_cycle($value) {
        file_put_contents('resources/.CYCLE', $value);
    }

    function get_position($angle) {
    $position = 'Unknown';
    $angle    = floatval($angle);

        if (($angle < 22.5) || ($angle > 337.5 ))
            $position = 'South';

        if (($angle > 22.5) && ($angle < 67.5 ))
            $position = 'South-West';

        if (($angle > 67.5) && ($angle < 112.5 ))
            $position = 'West';

        if (($angle > 112.5) && ($angle < 157.5 ))
            $position = 'North-West';

        if (($angle > 157.5) && ($angle < 202.5 ))
            $position = 'North';

        if (($angle > 202.5) && ($angle < 247.5 ))
            $position = 'North-East';

        if (($angle > 247.5) && ($angle < 292.5 ))
            $position = 'East';

        if (($angle > 292.5) && ($angle < 337.5 ))
            $position = 'South-East';
        return $position;
    }

    function redirect($page) {
        header('Location: index.php?page=' . $page);
        exit;
    }

    function is_connected() {
        if (isset($_SESSION['username']) && isset($_SESSION['password'])) {
            if ($_SESSION['username'] === USERNAME) {
                if ($_SESSION['password'] === PASSWORD) {
                    return true;
                }
            }
        }

        return false;
    }

    function logout() {
        unset($_SESSION['username']);
        unset($_SESSION['password']);
    }

    function parse_informations($informations) {
        $return = array();
        $first  = null;
        $last   = null;
        $lasts  = $informations;
		$facing = '';

        $lasts = array_slice($lasts, -24, 24, true);

        if (count($informations) >= 1) {
            $first  = $informations[0];
            $last   = $informations[count($informations) - 1];
			$facing = get_position($last['angle']);
        }

        $high_temp  = null;
        $low_temp   = null;
        $high_hum   = null;
        $low_hum    = null;
        $high_lum   = null;
        $low_lum    = null;
        $max_cycles = 0;
        $cycles     = 0;
        $current    = '';

        foreach ($informations as $idx => $information) {
            if ($idx === 0) {
                $high_temp = $low_temp = $high_hum = $low_hum = $high_lum = $low_lum = $information;
                $cycles++;
            }
            else {
                if ($information['temperature'] > $high_temp['temperature']) {
                    $high_temp['temperature'] = $information['temperature'];
                }

                if ($information['temperature'] < $low_temp['temperature']) {
                    $low_temp['temperature'] = $information['temperature'];
                }

                if ($information['humidity'] > $high_hum['humidity']) {
                    $high_hum['humidity'] = $information['humidity'];
                }

                if ($information['humidity'] < $low_hum['humidity']) {
                    $low_hum['humidity'] = $information['humidity'];
                }

                if ($information['luminosity'] > $high_lum['luminosity']) {
                    $high_lum['luminosity'] = $information['luminosity'];
                }

                if ($information['luminosity'] < $low_lum['luminosity']) {
                    $low_lum['luminosity'] = $information['luminosity'];
                }

                if ($current === ($cycles + 1)) {
                    $cycles++;
                }
                else {
                    $cycles = 1;
                }
            }

            if ($cycles > $max_cycles) {
                $max_cycles = $cycles;
            }

            $current = $information['cycle'];
        }

        if (strlen($current) === 0) {
            $current = 'N/A';
        }

        if ($max_cycles === 0) {
            $max_cycles = 'N/A';
        }

        return array(
            'first'         => $first,
            'last'          => $last,
			'facing'        => $facing,
            'lasts'         => $lasts,
            'high_temp'     => $high_temp,
            'low_temp'      => $low_temp,
            'high_hum'      => $high_hum,
            'low_hum'       => $low_hum,
            'high_lum'      => $high_hum,
            'low_lum'       => $low_lum,
            'max_cycles'    => $max_cycles,
            'current_cycle' => $current
        );
    }

    function get_date($timestamp) {
        return date('d/m/Y H:m', $timestamp);
    }

    function display($str, $quotes = false) {
        if ($quotes) {
            echo(htmlentities($str, ENT_QUOTES));
        }
        else {
            echo(htmlentities($str));
        }
    }

    function create_config($mysql_host, $mysql_user, $mysql_pass, $mysql_db, $username, $password, $arduino_key, $encode = true) {
        $file  = '<?php' . PHP_EOL;
        $file .= "if (!defined('SECURE_INCLUDE')) {" . PHP_EOL;
        $file .= "header('HTTP/1.1 403 Forbidden');" . PHP_EOL;
        $file .= "exit;" . PHP_EOL;
        $file .= "}" . PHP_EOL;
        $file .= "define('MYSQL_HOST',  '" . addslashes($mysql_host) . "');" . PHP_EOL;
        $file .= "define('MYSQL_USER',  '" . addslashes($mysql_user) . "');" . PHP_EOL;
        $file .= "define('MYSQL_PASS',  '" . addslashes($mysql_pass) . "');" . PHP_EOL;
        $file .= "define('MYSQL_DB',    '" . addslashes($mysql_db) . "');" . PHP_EOL;
        $file .= "define('USERNAME',    '" . addslashes($username) . "');" . PHP_EOL;

        if ($encode) {
            $file .= "define('PASSWORD',    '" . hash('whirlpool', $password) . "');" . PHP_EOL;
        }
        else {
            $file .= "define('PASSWORD',    '" . $password . "');" . PHP_EOL;
        }

        $file .= "define('ARDUINO_KEY', '" . addslashes($arduino_key) . "');";

        file_put_contents('includes/config.php', $file);
    }

    function install_table() {
        return 'CREATE TABLE IF NOT EXISTS `informations` (`id` int(11) NOT NULL AUTO_INCREMENT, `date` int(11) NOT NULL, `temperature` int(11) NOT NULL, `humidity` int(11) NOT NULL, `pressure` int(11) NOT NULL, `altitude` int(11) NOT NULL, `luminosity` int(11) NOT NULL, `cycle` int(11) NOT NULL, `angle` int(11) NOT NULL, PRIMARY KEY (`id`), UNIQUE KEY `id` (`id`)) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;';
    }