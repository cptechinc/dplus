<?php
	$whsesession = WhsesessionQuery::create()->findOneBySessionid(session_id());
	$warehouse   = WarehouseQuery::create()->findOneByWhseid($whsesession->whseid);
	$warehouse_receiving = $modules->get('WarehouseReceiving');
	$warehouse_receiving->set_sessionID(session_id());

	$html = $modules->get('HtmlWriter');

	if ($input->get->ponbr) {
		$ponbr = PurchaseOrder::get_paddedponumber($input->get->text('ponbr'));
		$page->title = "Receiving PO # $ponbr";
		$warehouse_receiving->set_ponbr($ponbr);

		if ($warehouse_receiving->purchaseorder_exists()) {
			$purchaseorder = $warehouse_receiving->get_purchaseorder();

			if (!$purchaseorder->count_receivingitems()) {
				$warehouse_receiving->request_purchaseorder_init();
			}

			$page->body .= $config->twig->render('warehouse/inventory/receiving/po-header.twig', ['page' => $page, 'purchaseorder' => $purchaseorder]);

			if ($input->get->scan) {
				$scan = $input->get->text('scan');
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
						$bins = WarehouseBinQuery::create()->get_warehousebins($whsesession->whseid)->toArray();
						$jsconfig = array('warehouse' => array('id' => $whsesession->whseid, 'binarrangement' => $warehouse->get_binarrangementdescription(), 'bins' => $bins));
						$page->body .= $config->twig->render('util/js-variables.twig', ['variables' => array('warehouse' => $jsconfig)]);
					}
					$page->body .= $config->twig->render('warehouse/inventory/receiving/po-item-receive-form.twig', ['page' => $page, 'item' => $physicalitem]);
				} elseif ($query_phys->count() > 1) {
					if ($input->get->recno) {
						$recno = $input->get->int('recno');
						$query_phys->filterByRecno($recno, Criteria::ALT_NOT_EQUAL);
						$query_phys->delete();
						$page->fullURL->query->remove('recno');
						$session->redirect($page->fullURL->getUrl());
					} else {
						$physicalitems = $query_phys->find();
						$page->body = $config->twig->render('warehouse/inventory/physical-count/item-list.twig', ['page' => $page, 'items' => $physicalitems]);
					}
				} else {
					if ($input->get->q) {
						$q = $input->get->text('q');
						$item_master = ItemMasterItemQuery::create();
						$item_master->filterByItemid($q);
						$item_master->count();

						if ($item_master->count()) {
							$itemID = $q;
							$physicalitem = new Whseitemphysicalcount();
							$physicalitem->setSessionid(session_id());
							$physicalitem->setRecno(0);
							$physicalitem->setScan($scan);
							$physicalitem->setitemid($itemID);
							$physicalitem->setType($item_master->get_itemtype($itemID));
							$physicalitem->save();
							$session->redirect($page->url."?scan=$scan");
						} else {
							$page->title = "No results found for ''$q'";
							$page->formurl = $page->url;
							$page->body .= $html->div('class=mb-3');
							$page->body .= $config->twig->render('warehouse/inventory/receiving/po-item-form.twig', ['page' => $page, 'ponbr' => $ponbr]);
						}
					} else {
						$page->title = "No results found for ''$scan'";
						$page->formurl = $page->url;
						$page->body .= $html->div('class=mb-3');
						$page->body .= $config->twig->render('warehouse/inventory/receiving/po-item-form.twig', ['page' => $page, 'ponbr' => $ponbr]);
					}
				}
			} else {
				$page->formurl = $pages->get('template=redir, redir_file=inventory')->url;
				$page->body .= $html->div('class=mb-3');
				$page->body .= $html->h3('', 'Scan item to add');
				$page->body .= $config->twig->render('warehouse/inventory/receiving/po-item-form.twig', ['page' => $page, 'ponbr' => $ponbr]);
			}

			$page->body .= $html->h3('', 'PO Items');

			if (file_exists($config->paths->templates."twig/warehouse/inventory/receiving/$config->company/po-items.twig")) {
				$page->body .= $config->twig->render("warehouse/inventory/receiving/$config->company/po-items.twig", ['page' => $page, 'ponbr' => $ponbr, 'items' => $purchaseorder->get_receivingitems()]);
			} else {
				$page->body .= $config->twig->render('warehouse/inventory/receiving/po-items.twig', ['page' => $page, 'ponbr' => $ponbr, 'items' => $purchaseorder->get_receivingitems()]);
			}
			$bins = WarehouseBinQuery::create()->get_warehousebins($whsesession->whseid)->toArray();
			$jsconfig = array('warehouse' => array('id' => $whsesession->whseid, 'binarrangement' => $warehouse->get_binarrangementdescription(), 'bins' => $bins), 'items' => $warehouse_receiving->get_purchaseorder_recevingdetails_js(), 'config_receive' => $warehouse_receiving->get_jsconfig());
			$page->body .= $config->twig->render('util/js-variables.twig', ['variables' => $jsconfig]);
			$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));
			$config->scripts->append(hash_templatefile('scripts/warehouse/receiving.js'));

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
		}
	} else {
		$page->formurl = $pages->get('template=redir, redir_file=inventory')->url;
		$page->body .= $config->twig->render('warehouse/inventory/receiving/po-form.twig', ['page' => $page]);
	}

	include __DIR__ . "/basic-page.php";
