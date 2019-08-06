<?php // TODO DATATABLE
	include_once('./ii-include.php');

	use ItemsearchQuery, Itemsearch;

	if ($itemquery->count()) {
		$page->title = "$itemID Activity";

		if ($input->get->date) {
			$date = $input->get->text('date');
			$module_json = $modules->get('JsonDataFiles');
			$json = $module_json->get_file(session_id(), $page->jsoncode);

			if ($module_json->file_exists(session_id(), $page->jsoncode)) {
				$session->activitytry = 0;
				$refreshurl = $page->get_itemcostingURL($itemID);
				$page->body .= $config->twig->render('items/ii/ii-links.twig', ['page' => $page, 'itemID' => $itemID, 'lastmodified' => $module_json->file_modified(session_id(), $page->jsoncode), 'refreshurl' => $refreshurl]);
				$page->body .= $config->twig->render('items/ii/activity/activity-screen.twig', ['page' => $page, 'json' => $json, 'module_json' => $module_json, 'date' => $date, 'itemID' => $itemID]);
			} else {
				if ($session->activitytry > 3) {
					$page->headline = $page->title = "Activity File could not be loaded";
					$page->body = $config->twig->render('util/error-page.twig', ['title' => $page->title, 'msg' => $module_json->get_error()]);
				} else {
					$session->activitytry++;
					$session->redirect($page->get_itemcostingURL($itemID));
				}
			}
		} else {
			$form = $config->twig->render('items/ii/activity/date-form.twig', ['page' => $page, 'itemID' => $itemID]);
			$page->body .= $config->twig->render('items/ii/requirements/links.twig', ['page' => $page, 'itemID' => $itemID]);
			$page->body .= $html->h3('', 'Enter Starting Activity Date');
			$page->body .= $html->div('class=row', $html->div('class=col-sm-6', $form));
		}
	}

	if ($page->print) {
		$page->show_title = true;
		include __DIR__ . "/blank-page.php";
	} else {
		include __DIR__ . "/basic-page.php";
	}
