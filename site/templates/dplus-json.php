<?php
	header('Content-Type: application/json');

	if (empty($page->pw_template)) {
		$response = array(
			'error' => true,
			'message' => "Cannot Render Page, Template is not defined"
		);
		$page->body = json_encode($response);
	} else {
		$template = str_replace('.php', '', $page->pw_template) . '.php';

		if (file_exists("./$template")) {
			include("./$template");
		} else {
			$page->headline = $page->title = "Cannot Render Page";
			$response = array(
				'error' => true,
				'message' => "Cannot Render Page, Template can not be found"
			);
			$page->body = json_encode($response);
		}
	}
	echo $page->body;
