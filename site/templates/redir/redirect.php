<?php
	$url = !empty($session->loc) ? $session->loc : $pages->get('/')->url;
	$session->remove('loc');

	// Check if user was trying to log in, then handle redirect of login
	if ($session->loggingin) {

		if (!$user->loggedin) {
			$url = $pages->get('template=login')->url;
		} else {
			$session->remove('loggingin');
			$url = $roles->get($user->dplusrole)->homepage;
		}
	}
	
	header("Location: $url");
	exit;
