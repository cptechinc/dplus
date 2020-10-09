<?php
	if (WhsesessionQuery::create()->sessionExists(session_id())) {
		include('./dplus-menu.php');
	} else {
		$loginm = $modules->get('DplusUser');
		$loginm->request_login_whse($user->loginid);
		$session->redirect($page->url, $http301 = false);
	}
