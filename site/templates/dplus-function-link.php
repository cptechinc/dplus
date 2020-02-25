<?php
	$path = '';

	if ($page->function_url) {
		$path = $page->function_url;
	} elseif ($page->dplus_function) {
		$path = $pages->get("template!=redir|dplus-json,dplus_function=$page->dplus_function")->url;
	} elseif ($page->pw_template) {
		$path = $pages->get("template!=redir|dplus-json,pw_template=$page->pw_template")->url;
	}

	if ($path) {
		$url = new Purl\Url($path);
		$url->query->set('redirect_from', $page->parent->url);
		$session->redirect($url->getUrl(), $http301 = false);
	} else {
		$page->body = $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => "Error Finding Function", 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Cannot find function this page links to"]);
		include __DIR__ . "/basic-page.php";
	}
