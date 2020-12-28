<?php
	$iio = $modules->get('Iio');

	if ($user->has_function('ii') && $iio->is_user_allowed_template($user, $page->pw_template)) {
		include("./dplus-function.php");
	} else {
		$page->body = $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => "You don't have access to this function", 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Permission: II $page->name"]);
		include __DIR__ . "/basic-page.php";
	}
