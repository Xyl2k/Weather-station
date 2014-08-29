<?php
    if (!defined('SECURE_INCLUDE')) {
        header('HTTP/1.1 403 Forbidden');
        exit;
    }

    if (is_connected()) {
        redirect('home');
    }

    $captcha = new KCAPTCHA();

    $_SESSION['captcha'] = $captcha->getKeyString();

    $captcha->show();