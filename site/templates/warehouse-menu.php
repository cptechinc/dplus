<?php
	if (WhsesessionQuery::create()->sessionExists(session_id())) {
		include('./dplus-menu.php');
	} else {
		$url = $page->get_loginURL();
		$modules->get('DplusRequest')->self_request($url);
	}
