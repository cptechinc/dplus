<?php
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
