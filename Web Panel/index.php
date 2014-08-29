<?php
    define('SECURE_INCLUDE', 1337);
    
    ob_start();
    
    header('Content-Type: text/html; charset=utf-8');

    require_once('includes/functions.php');

    $pages = array('index', 'login', 'logout', 'home', 'settings', 'about', 'install', 'gate', 'captcha', 'cycle');

    if (isset($_GET['page']) && in_array($_GET['page'], $pages)) {
        $page = $_GET['page'];
    }
    else {
        $page = 'index';
    }

    if ($need_install) {
        if ($page === 'gate') {
            die('GATE_DOWN');
        }
        else if (($page !== 'install' && $page !== 'about')) {
            redirect('install');
        }
    }

    require_once('pages/' . $page . '.php');
    
    ob_end_flush();