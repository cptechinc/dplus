<?php
	include_once('./vi-include.php');

	if ($vendorquery->count()) {
		$page->show_breadcrumbs = false;
		$page->body .= $config->twig->render('vendors/vi/bread-crumbs.twig', ['page' => $page, 'vendor' => $vendor]);
		$page->title = "$vendor->name 24-month Summary";

		$module_json = $modules->get('JsonDataFiles');
		$json = $module_json->get_file(session_id(), $page->jsoncode);

		if ($module_json->file_exists(session_id(), $page->jsoncode)) {
			if ($json['vendid'] != $vendorID) {
				$module_json->remove_file(session_id(), $page->jsoncode);
				$session->redirect($page->get_vi24monthsummaryURL($vendorID, $date));
			}

			$session->monthsummarytry = 0;
			$refreshurl = $page->get_vi24monthsummaryURL($vendorID, $date);
			$page->body .= $config->twig->render('vendors/vi/vi-links.twig', ['page' => $page, 'lastmodified' => $module_json->file_modified(session_id(), $page->jsoncode), 'refreshurl' => $refreshurl]);

			if ($json['error']) {
				$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => "Error!", 'iconclass' => 'fa fa-warning fa-2x', 'message' => $json['errormsg']]);
			} else {
				$page->body .= $config->twig->render('vendors/vi/24-monthsummary/24-monthsummary.twig', ['page' => $page, 'vendorID' => $vendorID, 'json' => $json]);
				$page->js   .= $config->twig->render('vendors/vi/24-monthsummary/summary.js.twig', [ 'json' => $json]);

				$config->styles->append(hash_templatefile('styles/lib/morris.css'));
				$config->scripts->append(hash_templatefile('scripts/lib/raphael.js'));
				$config->scripts->append(hash_templatefile('scripts/lib/morris.js'));
			}
		} else {
			if ($session->monthsummarytry > 3) {
				$page->headline = $page->title = "24-month Summary File could not be loaded";
				$refreshurl = $page->get_vi24monthsummaryURL($vendorID, $date);
				$page->body = $config->twig->render('vendors/vi/vi-links.twig', ['page' => $page, 'refreshurl' => $refreshurl]);
				$page->body .= $config->twig->render('util/error-page.twig', ['title' => $page->title, 'msg' => $module_json->get_error()]);
			} else {
				$session->monthsummarytry++;
				$session->redirect($page->get_vi24monthsummaryURL($vendorID, $date));
			}
		}
	}

	if ($page->print) {
		$page->show_title = true;
		include __DIR__ . "/blank-page.php";
	} else {
		include __DIR__ . "/basic-page.php";
	}
