<?php // TODO DATATABLE
	include_once('./ii-include.php');
	$config_ii = $modules->get('ConfigsIi');

	if ($lookup_ii->lookup_itm($itemID)) {
		$page->show_breadcrumbs = false;
		$page->body .= $config->twig->render('items/ii/bread-crumbs.twig', ['page' => $page, 'item' => $item]);
		$page->title = "$itemID Components";

		if ($input->get->qty) {
			$module_json = $modules->get('JsonDataFiles');

			if ($config_ii->option_components == 'kit') {
				$page->jsoncode = "$page->jsoncode-$config_ii->option_components";
			} elseif ($config_ii->option_components == 'bom') {
				$bomtype = $input->get->text('bomtype');
				$page->jsoncode = "$page->jsoncode-$config_ii->option_components-$bomtype";
			}

			$json = $module_json->get_file(session_id(), $page->jsoncode);

			if ($module_json->file_exists(session_id(), $page->jsoncode)) {
				if ($json['itemid'] != $itemID) {
					$module_json->remove_file(session_id(), $page->jsoncode);
					$session->redirect($page->get_itemcomponentsURL($itemID));
				}
				$session->activitytry = 0;
				$refreshurl = $page->get_itemcomponentsURL($itemID);
				$page->body .= $config->twig->render('items/ii/ii-links.twig', ['page' => $page, 'itemID' => $itemID, 'lastmodified' => $module_json->file_modified(session_id(), $page->jsoncode), 'refreshurl' => $refreshurl]);

				if ($config_ii->option_components == 'kit') {
					$query_kit = KitQuery::create();
					$query_kit->filterByItemid($itemID);

					if ($query_kit->count()) {
						$kit_items = KitItemsQuery::create()->filterByKititemid($itemID)->find();
						$page->body .= $config->twig->render('items/ii/components/kit-breakdown.twig', ['page' => $page, 'itemID' => $itemID,  'items' => $kit_items]);
					}

					if ($json['error']) {
						$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => $json['errormsg']]);
					} else {
						$page->body .= $config->twig->render('items/ii/components/kit-screen.twig', ['page' => $page, 'json' => $json, 'module_json' => $module_json, 'itemID' => $itemID]);
					}
				} elseif ($config_ii->option_components == 'bom') {
					$bomtype = $input->get->text('bomtype');
					$page->jsoncode = "$page->jsoncode-$config_ii->option_components-$bomtype";

					if ($json['error']) {
						$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => $json['errormsg']]);
					} else {
						$page->body .= $config->twig->render("items/ii/components/bom-$bomtype-screen.twig", ['page' => $page, 'json' => $json, 'module_json' => $module_json, 'itemID' => $itemID]);
					}
				}
			} else {
				if ($session->activitytry > 3) {
					$page->headline = $page->title = "Components File could not be loaded";
					$page->body = $config->twig->render('util/error-page.twig', ['title' => $page->title, 'msg' => $module_json->get_error()]);
				} else {
					$session->activitytry++;
					$session->redirect($page->get_itemcomponentsURL($itemID));
				}
			}
		} else {
			if ($config_ii->option_components == 'kit') {
				$title = "Enter Kit Qty needed";
				$form = $config->twig->render('items/ii/components/kit-form.twig', ['page' => $page, 'itemID' => $itemID]);
			} elseif ($config_ii->option_components == 'bom') {
				$title = "Enter BOM Qty needed";
				$form = $config->twig->render('items/ii/components/bom-form.twig', ['page' => $page, 'itemID' => $itemID]);
			} else {
				$title = '';
				$form = $config_ii->option_components;
			}

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
