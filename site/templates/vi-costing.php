<?php
	include_once('./vi-include.php');

	if ($vendorquery->count()) {
		$page->show_breadcrumbs = false;
		$page->body .= $config->twig->render('vendors/vi/bread-crumbs.twig', ['page' => $page, 'vendor' => $vendor]);
		$page->title = "$vendor->name Costing";

		$module_json = $modules->get('JsonDataFiles');
		$json = $module_json->get_file(session_id(), $page->jsoncode);

		if ($input->get->itemID) {
			$itemID = $input->get->text('itemID');

			if ($module_json->file_exists(session_id(), $page->jsoncode)) {
				if ($json['vendid'] != $vendorID || $json['itemid'] != $itemID) {
					$module_json->remove_file(session_id(), $page->jsoncode);
					$session->redirect($page->get_vicostingURL($vendorID, $itemID));
				}
				$session->costingtry = 0;

				$refreshurl = $page->get_vicostingURL($vendorID);
				$page->body .= $config->twig->render('vendors/vi/vi-links.twig', ['page' => $page, 'lastmodified' => $module_json->file_modified(session_id(), $page->jsoncode), 'refreshurl' => $refreshurl]);

				if ($json['error']) {
					$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => "Error!", 'iconclass' => 'fa fa-warning fa-2x', 'message' => $json['errormsg']]);
				} else {
					$document_management = $modules->get('DocumentManagement');
					$sublink = $page->get_visubURL($vendorID, $itemID);
					$page->body .= $config->twig->render('vendors/vi/costing/costing.twig', ['page' => $page, 'vendorID' => $vendorID, 'json' => $json, 'sublink' => $sublink, 'document_management' => $document_management]);
				}
			} else {
				if ($session->costingtry > 3) {
					$page->headline = $page->title = "Costing File could not be loaded";
					$page->body = $config->twig->render('vendors/vi/vi-links.twig', ['page' => $page, 'refreshurl' => $refreshurl]);
					$page->body .= $config->twig->render('util/error-page.twig', ['title' => $page->title, 'msg' => $module_json->get_error()]);
				} else {
					$session->costingtry++;
					$session->redirect($page->get_vicostingURL($vendorID, $itemID));
				}
			}
		} else {
			$q = $input->get->q ? $input->get->text('q') : '';
			$query = ItemsearchQuery::create();
			$query->filterActive();
			$query->filterByOrigintype([Itemsearch::ORIGINTYPE_VENDOR, Itemsearch::ORIGINTYPE_ITEM]);

			if ($query->filterByItemid($q)->count()) {
				$query->groupby('itemid');
			} else {
				$query->filterByOrigintype([Itemsearch::ORIGINTYPE_VENDOR, Itemsearch::ORIGINTYPE_ITEM]);
				$query->where("MATCH(Itemsearch.itemid, Itemsearch.refitemid, Itemsearch.desc1, Itemsearch.desc2) AGAINST (? IN BOOLEAN MODE)", "*$q*");
				$query->groupby('itemid');
			}

			if ($query->count() == 1) {
				$item = $query->findOne();
				$session->redirect($page->get_vicostingURL($vendorID, $item->itemid));
			} else {
				$items = $query->paginate($input->pageNum, 10);
			}

			$page->body = $config->twig->render('vendors/vi/vi-links.twig', ['page' => $page]);
			$page->searchURL = $page->url;
			$page->body .= $config->twig->render('vendors/vi/costing/item-form.twig', ['page' => $page, 'vendorID' => $vendorID, 'items' => $items]);
		}
	}

	if ($page->print) {
		$page->show_title = true;
		include __DIR__ . "/blank-page.php";
	} else {
		include __DIR__ . "/basic-page.php";
	}
