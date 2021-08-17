<?php
	$path = '';
	$url = new Purl\Url($pages->get('/')->url);

	if ($page->function_url) {
		$path = ltrim($page->function_url, '/');
		$url->path->add($path);
	} elseif ($page->dplus_function) {
		$path = $pages->get("template!=redir|dplus-json,dplus_function=$page->dplus_function")->url;
		$url->path = $path;
		$url->query->set('redirect_from', $page->parent->url);
	} elseif ($page->pw_template) {
		$path = $pages->get("template!=redir|dplus-json,pw_template=$page->pw_template")->url;
		$url->path = $path;
		$url->query->set('redirect_from', $page->parent->url);
	}

	if ($path) {
		$session->redirect($url->getUrl(), $http301 = false);
	} else {
		$page->body = $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => "Error Finding Function", 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Cannot find function this page links to"]);
		include __DIR__ . "/basic-page.php";
	}
