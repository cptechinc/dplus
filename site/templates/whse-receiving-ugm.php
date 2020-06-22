<?php
	$whsesession = WhsesessionQuery::create()->findOneBySessionid(session_id());
	$warehouse   = WarehouseQuery::create()->findOneByWhseid($whsesession->whseid);
	$warehouse_receiving = $modules->get('ReceivingUgm');
	$warehouse_receiving->set_sessionID(session_id());

	$html = $modules->get('HtmlWriter');
	$page->bin = '';

	if ($input->get->ponbr) {
		$ponbr = PurchaseOrder::get_paddedponumber($input->get->text('ponbr'));
		$page->ponbr = $ponbr;
		$page->title = "Receiving PO # $ponbr";
		$warehouse_receiving->set_ponbr($ponbr);
		$config->inventory = $modules->get('ConfigsWarehouseInventory');
		$page->bin = $config->inventory->physicalcount_savebin ? $session->receiving_bin : '';
		$page->force_bin_itemlookup = $config->inventory->receive_force_bin_itemlookup;

		if ($config->inventory->physicalcount_savebin) {
			$page->bin = $session->receiving_bin ? $session->receiving_bin : '';
		}

		if ($warehouse_receiving->purchaseorder_exists()) {
			$purchaseorder = $warehouse_receiving->get_purchaseorder();
			$page->body .= $config->twig->render('warehouse/inventory/receiving/po-header.twig', ['page' => $page, 'purchaseorder' => $purchaseorder]);

			if ($input->get->scan) {
				$scan = $input->get->text('scan');
				$page->scan = $scan;
				$page->formurl = $pages->get('template=redir, redir_file=inventory')->url;
				$query_phys = WhseitemphysicalcountQuery::create();
				$query_phys->filterBySessionid(session_id());
				$query_phys->filterByScan($scan);

				if ($query_phys->count() == 1) {
					$physicalitem = $query_phys->findOne();
					$page->body .= $html->div('class=mb-3');
					if ($physicalitem->has_error()) {
						if (!$physicalitem->is_on_po()) {
							$physicalitem->setItemid('');
							$physicalitem->setLotserial('');
							$physicalitem->setLotserialref('');
						}
						$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => $physicalitem->get_error()]);
						$page->body .= $html->div('class=mb-3');
					} else {
						if ($session->receiving_bin) {
							if ($session->receiving_itemid == $physicalitem->itemid) {
								$physicalitem->setBin($session->receiving_bin);
							}
						}
						if ($input->get->binID) {
							$physicalitem->setBin($input->get->text('binID'));
						}
						$bins = WarehouseBinQuery::create()->get_warehousebins($whsesession->whseid)->toArray();
						$jsconfig = array('warehouse' => array('id' => $whsesession->whseid, 'binarrangement' => $warehouse->get_binarrangementdescription(), 'bins' => $bins));
						$page->body .= $config->twig->render('util/js-variables.twig', ['variables' => array('warehouse' => $jsconfig)]);
					}

					if (trim($physicalitem->get_error()) == 'invalid item id') {
						$page->searchitemsURL = $pages->get('pw_template=itm-search')->httpUrl;
						$page->body .= $config->twig->render('warehouse/inventory/receiving/ugm/create-ilookup-item-form.twig', ['page' => $page, 'item' => $physicalitem, 'm_receiving' => $warehouse_receiving]);
						$page->js   .= $config->twig->render('warehouse/inventory/receiving/ugm/ilookup.js.twig', ['page' => $page]);
					} else {
						$page->body .= $config->twig->render('warehouse/inventory/receiving/po-item-receive-form.twig', ['page' => $page, 'item' => $physicalitem, 'm_receiving' => $warehouse_receiving, 'config' => $config]);
					}
				}
			} else {
				$page->formurl = $pages->get('template=redir, redir_file=inventory')->url;
				$page->body .= $html->div('class=mb-3');
				$page->body .= $html->h3('', 'Scan item to add');
				$page->body .= $config->twig->render('warehouse/inventory/receiving/po-item-form.twig', ['page' => $page, 'ponbr' => $ponbr, 'config' => $config]);
			}

			if (file_exists($config->paths->templates."twig/warehouse/inventory/receiving/$config->company/po-items.twig")) {
				$page->body .= $config->twig->render("warehouse/inventory/receiving/$config->company/po-items.twig", ['page' => $page, 'ponbr' => $ponbr, 'items' => $purchaseorder->get_receivingitems()]);
			} else {
				$page->body .= $config->twig->render('warehouse/inventory/receiving/po-items.twig', ['page' => $page, 'ponbr' => $ponbr, 'items' => $purchaseorder->get_receivingitems()]);
			}

			$page->body .= $config->twig->render('warehouse/inventory/receiving/item-edit-modal.twig', ['page' => $page, 'config' => $config]);

			if (!$input->get->scan) {
				$page->body .= $config->twig->render('warehouse/inventory/receiving/po-actions.twig', ['page' => $page, 'ponbr' => $ponbr]);
			}

			$page->body .= $config->twig->render('warehouse/inventory/bins-modal.twig', ['warehouse' => $warehouse]);
			$bins = WarehouseBinQuery::create()->get_warehousebins($whsesession->whseid)->toArray();
			$jsconfig = array('warehouse' => array('id' => $whsesession->whseid, 'binarrangement' => $warehouse->get_binarrangementdescription(), 'bins' => $bins), 'items' => $warehouse_receiving->get_purchaseorder_recevingdetails_js(), 'config_receive' => $warehouse_receiving->get_jsconfig());
			$page->body .= $config->twig->render('util/js-variables.twig', ['variables' => $jsconfig]);
			$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));
			$page->js .= $config->twig->render('warehouse/inventory/receiving/js.twig', ['page' => $page, 'config' => $config]);

			if ($session->removefromline) {
				$page->js .= $config->twig->render('warehouse/inventory/receiving/remove-line.js.twig', ['ponbr' => $ponbr, 'linenbr' => $session->removefromline]);
				$session->remove('removefromline');
			}
		} else {
			$page->title = "PO # $ponbr Does Not Exist";
			$page->body = $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => $page->title, 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Check the Purchase Order Number and try again"]);
			$page->body .= $html->div('class=mb-3');
			$page->formurl = $pages->get('template=redir, redir_file=inventory')->url;
			$page->body .= $config->twig->render('warehouse/inventory/receiving/po-form.twig', ['page' => $page]);
			$page->body .= $html->a("class=btn btn-secondary|href=$page->url?create=new", "Create PO");
		}
	} elseif ($input->get->create) {
		$page->searchURL = $page->fullURL->getUrl();
		$page->body .= $html->h4('', 'Select a Vendor to create PO');
		$page->body .= $config->twig->render('warehouse/inventory/receiving/ugm/vendors/search-form.twig', ['page' => $page]);

		if ($input->get->q) {
			$filter_vendors = $modules->get('FilterVendors');
			$filter_vendors->init_query($user);

			if ($input->get->q) {
				$q = strtoupper($input->get->text('q'));
				$page->headline = "Receving: Searching Vendors for '$q'";
				$filter_vendors->filter_search($q);
			}

			$filter_vendors->apply_sortby($page);
			$query = $filter_vendors->get_query();
			$vendors = $query->paginate($input->pageNum, 10);

			$page->body = $config->twig->render('warehouse/inventory/receiving/ugm/vendors/vendors-search.twig', ['page' => $page, 'vendors' => $vendors]);
			$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'resultscount'=> $vendors->getNbResults()]);
		}
	} else {
		$page->formurl = $pages->get('template=redir, redir_file=inventory')->url;
		$page->body .= $config->twig->render('warehouse/inventory/receiving/po-form.twig', ['page' => $page]);
		$page->body .= $html->a("class=btn btn-secondary|href=$page->url?create=new", "Create PO");
	}

	include __DIR__ . "/basic-page.php";
