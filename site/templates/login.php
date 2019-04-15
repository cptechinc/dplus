<?php
	if ($user->loggedin) {
		$session->redirect($pages->get('/')->url, $http301 = false);
	}

	$page->body = $config->twig->render('user/login-form.twig', ['page' => $page, 'appconfig' => $appconfig,'login_error' => ($session->loggingin && !$user->isloggedin)]);
	include __DIR__ . "/blank-page.php";