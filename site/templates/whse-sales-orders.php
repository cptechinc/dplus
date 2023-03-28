<?php
	// Dplus Classes
	use Dplus\Session\UserMenuPermissions;
	$pageMenuPerm = $page->dplus_function ? $page->dplus_function : $page->dplus_permission;
	if (empty($pageMenuPerm)) {
		$pageMenuPerm = 'wm';
	}

	foreach ($page->parents('template=dplus-menu|warehouse-menu') as $parent) {
		$code = $parent->dplus_function ? $parent->dplus_function : $parent->dplus_permission;

		if (empty($code) === false && UserMenuPermissions::instance()->canAccess($code) === false) {
			$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => "You don't have access to this function", 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Permission: $pageMenuPerm"]);
			include('./basic-page.php');
			return false;
		}
	}

	if (empty($pageMenuPerm) === false && UserMenuPermissions::instance()->canAccess($pageMenuPerm ) === false) {
		$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => "You don't have access to this function", 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Permission: $pageMenuPerm"]);
		include('./basic-page.php');
		return false;
	}

	$rm = strtolower($input->requestMethod());
	$values = $input->$rm;
	$m_json = $modules->get('JsonDataFiles');
	$xls = $modules->get('XlsSalesOrderOpen');

	if (!$m_json->file_exists(session_id(), $page->pw_template) || $values->refresh) {
		$xls->request_json();
	}

	$json = $m_json->get_file(session_id(), $page->pw_template);

	if ($m_json->file_exists(session_id(), $page->pw_template)) {
		$session->stockstatus = 0;

		if ($json['error'] || empty($json)) {
			$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => $json['errormsg']]);
		} else {

			$xls->write($json);
			$page->body .= $config->twig->render('util/jdf/table.twig', ['page' => $page, 'json' => $json, 'jsondatafiles' => $m_json]);
		}
	} else {
		if ($session->stockstatus > 3) {
			$page->headline = "Stock Status File could not be loaded";
			$page->body .= $config->twig->render('util/error-page.twig', ['title' => $page->headline, 'msg' => $m_json->get_error()]);
		} else {
			$session->stockstatus++;

		}
	}

	if ($values->download) {
		$file = $xls->writer->get_filepath();
		header('Content-Description: File Transfer');
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename='.basename($file));
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . filesize($file));
		ob_clean();
		flush();
		readfile($file);
	}

	if ($page->print) {
		$page->show_title = true;
		include __DIR__ . "/blank-page.php";
	} else {
		include __DIR__ . "/basic-page.php";
	}
