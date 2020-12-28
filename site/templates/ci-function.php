<?php
	$cio = $modules->get('Cio');

	if ($user->has_function('ci') && $cio->is_user_allowed_template($user, $page->pw_template)) {
		include("./dplus-function.php");
	} else {
		$page->body = $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => "You don't have access to this function", 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Permission: Ci $page->name"]);
		include __DIR__ . "/basic-page.php";
	}
