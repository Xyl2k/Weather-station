<?php
	if (!defined('SECURE_INCLUDE')) {
		header('HTTP/1.1 403 Forbidden');
		exit;
	}
	
	logout();
	redirect('login');