<?php
	if (empty($page->pw_template)) {
		$page->headline = $page->title = "Cannot Render Page";
		$page->body = $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => $page->title, 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Template is not defined"]);
		include __DIR__ . "/basic-page.php";
	} else {
		$template = str_replace('.php', '', $page->pw_template) . '.php';

		if (file_exists("./$template")) {
			include("./$template");
		} else {
			switch ($page->ajaxtype->value) {
				case 'lookup':
					include("./api-ajax-lookup.php");
					break;
				case 'json':
					break;
			}
		}
	}
