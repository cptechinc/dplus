<?php
	$url = !empty($session->loc) ? $session->loc : $pages->get('/')->url;
	$session->remove('loc');

	// Check if user was trying to log in, then handle redirect of login
	if ($session->loggingin) {
		$session->remove('loggingin');

		if (!$user->loggedin) {
			$url = $pages->get('template=login')->url;
		} else {
			$url = $roles->get($user->dplusorole)->homepage;
		}
	}

	header("Location: $url");
	exit;
