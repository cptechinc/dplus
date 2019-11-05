<?php // TODO DATATABLE
	include_once('./ii-include.php');
	$config_ii = $modules->get('ConfigsIi');

	use ItemsearchQuery, Itemsearch;

	if ($itemquery->count()) {
		$page->title = "$itemID BOM";
		$page->show_breadcrumbs = false;
		$page->body .= $config->twig->render('items/ii/bread-crumbs.twig', ['page' => $page, 'item' => $item]);

		if ($input->get->qty) {
			$module_json = $modules->get('JsonDataFiles');

			$bomtype = $input->get->text('bomtype');
			$page->jsoncode = "$page->jsoncode-$config_ii->option_components-$bomtype";

			$json = $module_json->get_file(session_id(), $page->jsoncode);

			if ($module_json->file_exists(session_id(), $page->jsoncode)) {
				if ($json['itemid'] != $itemID) {
					$module_json->remove_file(session_id(), $page->jsoncode);
					$session->redirect($page->get_itembomURL($itemID));
				}
				$session->activitytry = 0;
				$refreshurl = $page->get_itembomURL($itemID);
				$page->body .= $config->twig->render('items/ii/ii-links.twig', ['page' => $page, 'itemID' => $itemID, 'lastmodified' => $module_json->file_modified(session_id(), $page->jsoncode), 'refreshurl' => $refreshurl]);

				$bomtype = $input->get->text('bomtype');
				$page->jsoncode = "$page->jsoncode-$config_ii->option_components-$bomtype";
				$page->body .= $config->twig->render("items/ii/components/bom-$bomtype-screen.twig", ['page' => $page, 'json' => $json, 'module_json' => $module_json, 'itemID' => $itemID]);
			} else {
				if ($session->activitytry > 3) {
					$page->headline = $page->title = "BOM File could not be loaded";
					$page->body = $config->twig->render('util/error-page.twig', ['title' => $page->title, 'msg' => $module_json->get_error()]);
				} else {
					$session->activitytry++;
					$session->redirect($page->get_itembomURL($itemID));
				}
			}
		} else {
			$title = "Enter BOM Qty needed";
			$form = $config->twig->render('items/ii/components/bom-form.twig', ['page' => $page, 'itemID' => $itemID]);

			$page->body .= $config->twig->render('items/ii/ii-links.twig', ['page' => $page, 'itemID' => $itemID]);
			$page->body .= $html->h3('', $title);
			$page->body .= $html->div('class=row', $html->div('class=col-sm-6', $form));
		}
	}

	if ($page->print) {
		$page->show_title = true;
		include __DIR__ . "/blank-page.php";
	} else {
		include __DIR__ . "/basic-page.php";
	}
