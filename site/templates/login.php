<?php
	use Purl\Url as Purl;
	$rm = strtolower($input->requestMethod());
	$values = $input->$rm;
	$loginm = $modules->get('DplusUser');

	if ($values->action) {
		$loginm->process_input($input);
		$session->redirect($page->url, $http301 = false);
	}

	if ($user->loggedin) {
		$session->remove('loggingin');

		$url = $pages->get('/')->url;

		if ($session->returnurl) {
			$url = $session->returnurl;
			$purl = new Purl($url);
			if (in_array('site', $purl->path->getData())) {
				$url = $pages->get('/')->url;
			}
			$session->remove('returnurl');
		}

		$session->redirect($url, $http301 = false);
	}

	$page->show_title = false;
	$page->formurl = $page->url;
	$page->body = $config->twig->render('user/login-form.twig', ['page' => $page, 'appconfig' => $appconfig, 'config_customer' => $config->customer, 'login_error' => ($session->loggingin && !$user->isloggedin)]);
	include __DIR__ . "/blank-page.php";
