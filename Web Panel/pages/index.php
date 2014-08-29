<?php
	if (!defined('SECURE_INCLUDE')) {
		header('HTTP/1.1 403 Forbidden');
		exit;
	}
	
	if (is_connected()) {
		redirect('home');
	}
	else {
		redirect('login');
	}
?>