<?php
    if (!defined('SECURE_INCLUDE')) {
        header('HTTP/1.1 403 Forbidden');
        exit;
    }
    
    if (isset($_GET['api_key'])) {
        $api_key = $_GET['api_key'];
        
        if ($api_key === ARDUINO_KEY) {
            if (isset($_GET['check'])) {
                response(has_cycle());
            }
        }
        else {
            response('BAD_KEY');
        }
    }
    else {
        response('BAD_PARAM');
    }