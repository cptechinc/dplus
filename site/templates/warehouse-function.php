<?php
	if (WhsesessionQuery::create()->sessionExists(session_id())) {
		include('./dplus-function.php');
	} else {
		$http = new WireHttp();
		$url = $pages->get('template=warehouse-menu, dplus_function=wm')->child('template=redir')->url."?action=login&sessionID=".session_id();
		$http->get("127.0.0.1$url");
		$session->redirect($page->url);
	}
