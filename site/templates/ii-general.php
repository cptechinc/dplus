<?php
	include_once('./ii-include.php');

	if ($lookup_ii->lookup_itm($itemID)) {
		$page->show_breadcrumbs = false;
		$page->body .= $config->twig->render('items/ii/bread-crumbs.twig', ['page' => $page, 'item' => $item]);
		$page->title = "$itemID General";

		$module_json = $modules->get('JsonDataFiles');

		$partial_exist = $module_json->file_exists(session_id(), 'ii-misc') || $module_json->file_exists(session_id(), 'ii-notes') || $module_json->file_exists(session_id(), 'ii-usage') ;
		if ($partial_exist) {
			$config->styles->append('//cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.css');
			$config->scripts->append('//cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js');
			$config->scripts->append('//cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.min.js');
			$config->scripts->append(hash_templatefile('scripts/lib/moment.js'));

			$returntop = $html->div('class=text-right', $html->a('href=#general-nav|class=link h5', "Back to the top ".$html->icon('fa fa-arrow-circle-o-up', '')));;

			$session->generaltry = 0;
			$refreshurl = $page->get_itemgeneralURL($itemID);
			$page->body .= $config->twig->render('items/ii/ii-links.twig', ['page' => $page, 'itemID' => $itemID, 'lastmodified' => $module_json->file_modified(session_id(), 'ii-usage'), 'refreshurl' => $refreshurl]);
			$page->body .= $html->div('class=row mb-3|id=general-nav',
				$html->div('class=col',
					$html->a('href=#usage|class=btn btn-secondary', 'Usage') . '&nbsp' .
					$html->a('href=#notes|class=btn btn-secondary', 'Notes') . '&nbsp' .
					$html->a('href=#misc|class=btn btn-secondary', 'Misc')
				)
			);

			if ($module_json->file_exists(session_id(), 'ii-usage')) {
				$page->body .= $html->h3('id=usage|class=info-heading', 'Sales Usage');
				$module_usage = $modules->get('IiUsage');
				$json_usage = $module_json->get_file(session_id(), 'ii-usage');

				if ($json_usage['itemid'] != $itemID) {
					$module_json->remove_file(session_id(), $page->jsoncode);
					$session->redirect($page->get_itemgeneralURL($itemID));
				}

				if ($json_usage['error']) {
					$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => $json_usage['errormsg']]);
				} else {
					$page->body .= $config->twig->render('items/ii/usage/sales-usage.twig', ['page' => $page, 'json' => $json_usage, 'module_json' => $module_json]);
					$page->body .= $config->twig->render('items/ii/usage/warehouses.twig', ['page' => $page, 'json' => $json_usage, 'module_json' => $module_json, 'module_usage' => $module_usage]);
					$page->js    = $config->twig->render('items/ii/usage/warehouses.js.twig', ['page' => $page, 'json' => $json_usage, 'module_json' => $module_json, 'module_usage' => $module_usage]);
				}
			} else {
				$page->body .= $config->twig->render('util/error-page.twig', ['title' => $page->title, 'msg' => 'II Usage could not be loaded']);
			}
			$page->body .= $returntop;

			if ($module_json->file_exists(session_id(), 'ii-notes')) {
				$page->body .= $html->h3('id=notes|class=info-heading', 'Notes');
				$json_notes = $module_json->get_file(session_id(), 'ii-notes');

				if ($json_notes['itemid'] != $itemID) {
					$module_json->remove_file(session_id(), $page->jsoncode);
					$session->redirect($page->get_itemgeneralURL($itemID));
				}

				if ($json_notes['error']) {
					$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => $json_notes['errormsg']]);
				} else {
					$page->body .= $config->twig->render('items/ii/general/notes.twig', ['page' => $page, 'json' => $json_notes, 'module_json' => $module_json]);
				}
			} else {
				$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => 'II Notes could not be loaded']);
			}

			$page->body .= $returntop;

			if ($module_json->file_exists(session_id(), 'ii-misc')) {
				$page->body .= $html->h3('id=misc|class=info-heading', 'Misc');
				$json_misc = $module_json->get_file(session_id(), 'ii-misc');

				if ($json_misc['itemid'] != $itemID) {
					$module_json->remove_file(session_id(), $page->jsoncode);
					$session->redirect($page->get_itemgeneralURL($itemID));
				}

				if ($json_misc['error']) {
					$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => $json_misc['errormsg']]);
				} else {
					$page->body .= $config->twig->render('items/ii/general/misc.twig', ['page' => $page, 'json' => $json_misc, 'module_json' => $module_json]);
				}
			} else {
				$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => 'II Misc could not be loaded']);
			}
			$page->body .= $returntop;

		} else {
			if ($session->generaltry > 3) {
				$page->headline = $page->title = "General File could not be loaded";
				$refreshurl = $page->get_itemgeneralURL($itemID);
				$page->body .= $config->twig->render('items/ii/ii-links.twig', ['page' => $page, 'itemID' => $itemID, 'lastmodified' => $module_json->file_modified(session_id(), 'ii-misc'), 'refreshurl' => $refreshurl]);
				$page->body .= $config->twig->render('util/error-page.twig', ['title' => $page->title, 'msg' => 'II Usage, Misc, Notes could not be loaded']);
			} else {
				$session->generaltry++;
				$session->redirect($page->get_itemgeneralURL($itemID));
			}
		}
	}

	if ($page->print) {
		$page->show_title = true;
		include __DIR__ . "/blank-page.php";
	} else {
		include __DIR__ . "/basic-page.php";
	}
