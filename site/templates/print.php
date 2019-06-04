<?php
	if (empty($page->parent->pw_template)) {
		$page->parent->headline = $page->parent->title = "Cannot Render Page";
		$page->parent->body = $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => $page->parent->title, 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Template is not defined"]);
		include __DIR__ . "/basic-page.php";
	} else {
		$template = str_replace('.php', '', $page->parent->pw_template) . '.php';

		if (file_exists("./$template")) {
			include("./$template");
		} else {
			$page->parent->headline = $page->parent->title = "Cannot Render Page";
			$page->parent->body = $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => $page->parent->title, 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Template can not be found"]);
			include __DIR__ . "/basic-page.php";
		}
	}
