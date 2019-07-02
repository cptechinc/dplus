<?php
	include_once('./ii-include.php');

	use ItemsearchQuery, Itemsearch;
	use WarehouseQuery, Warehouse;

	$jsoncode = 'ii-pricing';
	$module_ii = $modules->get('MiiPages');
	$module_ii->init_iipage();

	if ($itemquery->count()) {
		$page->title = "$itemID Pricing";

		if (!$input->get->custID) {
			$query = CustomerQuery::create();

			if ($input->get->q) {
				$q = $input->get->text('q');
				$page->title = "II Pricing: Searching for '$q'";
				$col_custid = Customer::get_aliasproperty('custid');
				$col_name = Customer::get_aliasproperty('name');
				$columns = array($col_custid, $col_name);
				$query->search_filter($columns, strtoupper($q));
			}
			$customers = $query->paginate($input->pageNum, 10);

			$page->searchURL = $page->url;
			$page->body = $config->twig->render('items/ii/pricing/customer/customer-search.twig', ['page' => $page, 'customers' => $customers, 'itemID' => $itemID]);
			$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'resultscount'=> $customers->getNbResults()]);
		} else {
			$module_json = $modules->get('JsonDataFiles');
			$json = $module_json->get_file(session_id(), $page->jsoncode);

			if ($module_json->file_exists(session_id(), $page->jsoncode)) {
				$session->pricingtry = 0;
				$page->body =  $config->twig->render('items/ii/requirements/requirements-form.twig', ['page' => $page, 'itemID' => $itemID, 'warehouses' => $warehouses, 'module_ii' => $module_ii, 'view' => $json['reqavl'], 'whse' => $json['whse']]);
				$page->body .= $config->twig->render('items/ii/requirements/requirements.twig', ['page' => $page, 'view' => $module_ii->get_requirementsoptions()[$json['reqavl']], 'json' => $json, 'module_json' => $module_json]);
			} else {
				if ($session->pricingtry > 3) {
					$page->headline = $page->title = "Pricing File could not be loaded";
					$page->body = $config->twig->render('util/error-page.twig', ['title' => $page->title, 'msg' => $module_json->get_error()]);
				} else {
					$session->pricingtry++;
					$session->redirect($page->get_itempricingURL($itemID));
				}
			}
		}
	}

	if ($page->print) {
		$page->show_title = true;
		include __DIR__ . "/blank-page.php";
	} else {
		include __DIR__ . "/basic-page.php";
	}
