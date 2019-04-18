<?php
	if (WhsesessionQuery::create()->sessionExists(session_id())) {
		include('./dplus-function.php');
	} else {
		$http = new WireHttp();
		$url = $pages->get('template=warehouse-menu, dplus_function=wm')->child('template=redir')->httpUrl."?action=login&sessionID=".session_id();
		$http->get($url);
		$session->redirect($page->url);
	}
