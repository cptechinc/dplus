<?php
	if (empty($page->pw_template)) {
		$page->headline = $page->title = "Cannot Render Page";
		$page->body = $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => $page->title, 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Template is not defined"]);
		include __DIR__ . "/basic-page.php";
	} else {
		$template = str_replace('.php', '', $page->pw_template) . '.php';

		if (file_exists("./$template")) {
			$permission = empty($page->dplus_function) ? $page->dplus_permission : $page->dplus_function;

			if ($user->has_function($permission) || empty($permission)) {
				include("./$template");
			} else {
				$page->body = $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => "You don't have access to this function", 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Permission: $permission"]);
				include __DIR__ . "/basic-page.php";
			}
		} else {
			$page->headline = $page->title = "Cannot Render Page";
			$page->body = $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => $page->title, 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Template can not be found"]);
			include __DIR__ . "/basic-page.php";
		}
	}
