<?php
	$rm = strtolower($input->requestMethod());
	$values = $input->$rm;
	$loginm = $modules->get('DplusUser');

	if ($values->action) {
		$loginm->process_input($input);
		$session->redirect($page->url, $http301 = false);
	}

	if ($user->loggedin) {
		$session->remove('loggingin');

		if ($session->returnurl) {
			$url = $session->returnurl;
			$session->remove('returnurl');
		} else {
			$url = $pages->get('/')->url;
		}
		$session->redirect($url, $http301 = false);
	}

	$page->show_title = false;
	$page->formurl = $page->url;
	$page->body = $config->twig->render('user/login-form.twig', ['page' => $page, 'appconfig' => $appconfig, 'config_customer' => $config->customer, 'login_error' => ($session->loggingin && !$user->isloggedin)]);
	include __DIR__ . "/blank-page.php";
