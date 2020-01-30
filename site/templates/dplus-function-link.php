<?php
	$url = '';

	if ($page->function_url) {
		$url = (new Purl\Url($page->function_url))->getUrl();
	} elseif ($page->dplus_function) {
		$url = $pages->get("template!=redir|dplus-json,dplus_function=$page->dplus_function")->url;
	} elseif ($page->pw_template) {
		$url = $pages->get("template!=redir|dplus-json,pw_template=$page->pw_template")->url;
	}

	if ($url) {
		$session->redirect($url, $http301 = false);
	} else {
		$page->body = $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => "Error Finding Function", 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Cannot find function this page links to"]);
		include __DIR__ . "/basic-page.php";
	}
