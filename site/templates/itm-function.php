<?php
	if ($user->has_function('itm') && $user->permitted_template($page->pw_template)) {
		include("./dplus-function.php");
	} else {
		$page->body = $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => "You don't have access to this function", 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Permission: ITM $page->name"]);
		include __DIR__ . "/basic-page.php";
	}
