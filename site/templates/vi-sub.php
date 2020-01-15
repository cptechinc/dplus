<?php
	include_once('./vi-include.php');

	if ($vendorquery->count()) {
		$page->show_breadcrumbs = false;
		$page->body .= $config->twig->render('vendors/vi/bread-crumbs.twig', ['page' => $page, 'vendor' => $vendor]);
		$page->title = "$vendor->name Substitutions";

		$module_json = $modules->get('JsonDataFiles');
		$json = $module_json->get_file(session_id(), $page->jsoncode);

		if ($input->get->itemID) {
			$itemID = $input->get->text('itemID');

			if ($module_json->file_exists(session_id(), $page->jsoncode)) {
				if ($json['itemid'] != $itemID) {
					$module_json->remove_file(session_id(), $page->jsoncode);
					$session->redirect($page->get_visubURL($vendorID, $itemID));
				}
				$session->subtry = 0;

				$document_management = $modules->get('DocumentManagement');
				$refreshurl = $page->get_visubURL($vendorID);
				$page->body .= $config->twig->render('vendors/vi/vi-links.twig', ['page' => $page, 'lastmodified' => $module_json->file_modified(session_id(), $page->jsoncode), 'refreshurl' => $refreshurl]);

				if ($json['error']) {
					$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => "Error!", 'iconclass' => 'fa fa-warning fa-2x', 'message' => $json['errormsg']]);
				} else {
					$page->body .= $config->twig->render('vendors/vi/sub/sub.twig', ['page' => $page, 'vendorID' => $vendorID, 'json' => $json, 'document_management' => $document_management]);
				}
			} else {
				if ($session->subtry > 3) {
					$page->headline = $page->title = "Substitutions File could not be loaded";
					$page->body = $config->twig->render('vendors/vi/vi-links.twig', ['page' => $page, 'refreshurl' => $refreshurl]);
					$page->body .= $config->twig->render('util/error-page.twig', ['title' => $page->title, 'msg' => $module_json->get_error()]);
				} else {
					$session->subtry++;
					$session->redirect($page->get_visubURL($vendorID, $itemID));
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
				$session->redirect($page->get_visubURL($vendorID, $item->itemid));
			} else {
				$items = $query->paginate($input->pageNum, 10);
			}

			$page->body = $config->twig->render('vendors/vi/vi-links.twig', ['page' => $page]);
			$page->searchURL = $page->url;
			$page->body .= $config->twig->render('vendors/vi/sub/item-form.twig', ['page' => $page, 'vendorID' => $vendorID, 'items' => $items]);
		}
	}

	if ($page->print) {
		$page->show_title = true;
		include __DIR__ . "/blank-page.php";
	} else {
		include __DIR__ . "/basic-page.php";
	}
