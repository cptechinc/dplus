<?php
	$whsesession = WhsesessionQuery::create()->findOneBySessionid(session_id());
	$warehouse = WarehouseQuery::create()->findOneByWhseid($whsesession->whseid);
	$config->binr = $modules->get('ConfigsBinr');

	$page->frombin = '';
	$page->tobin = '';

	if ($input->get->frombin) {
		$binID = $input->get->text('frombin');
		$page->frombin = $binID;
	} else {
		$binID = $input->get->text('binID');
	}

	if ($input->get->tobin) {
		$page->tobin = $input->get->text('tobin');
	}

	$page->addHook('Page::binr_itemURL', function($event) {
		$p = $event->object;
		$item = $event->arguments(0);
		$url = new Purl\Url($p->parent('template=warehouse-menu')->child('template=redir')->url);
		$url->query->set('action','search-item-bins');
		$url->query->set('itemID', $item->itemid);
		$url->query->set($item->get_itemtypeproperty(), $item->get_itemidentifier());
		$url->query->set('binID', $item->bin);
		$url->query->set('page', $p->fullURL->getUrl());
		$event->return = $url->getUrl();
	});

	if ($input->get->scan) {
		$scan = $input->get->text('scan');
		$page->scan = $scan;
		$page->fullURL->query->remove('scan');
		$resultscount = InvsearchQuery::create()->filterBy('Sessionid', session_id())->count();

		$page->addHookProperty('Page::scan', function($event) {
			$p = $event->object;
			$event->return = !empty($p->scan) ? $p->scan : false;
		});

		// If no items are found
		if ($resultscount == 0) {
			$items = array();
			$page->body = $config->twig->render('warehouse/binr/inventory-results.twig', ['page' => $page]);
		} elseif ($resultscount == 1) { // If one item is found
			$item = InvsearchQuery::create()->findOneBySessionid(session_id());
			$url = $page->binr_itemURL($item);
			$session->redirect($url , $http301 = false);
		} else {
			// Multiple Items - count the number Distinct Item IDs
			$resultscount = InvsearchQuery::create()->countDistinctItemid(session_id(), $binID);

			if ($resultscount == 1) {
				$item = InvsearchQuery::create()->findOneBySessionidBin(session_id(), $binID);

				// If Item is Lotted / Serialized show results to choose which lot or serial to move
				if ($item->is_lotted() || $item->is_serialized()) {
					$resultscount = InvsearchQuery::create()->countByItemid(session_id(), $item->itemid, $binID);
					$items = InvsearchQuery::create()->findDistinctItems(session_id(), $binID);
					$inventory = InvsearchQuery::create();

					if ($config->twigloader->exists("warehouse/binr/$config->company/inventory-results.twig")) {
						$page->body = $config->twig->render("warehouse/binr/$config->company/inventory-results.twig", ['page' => $page, 'config' => $config->binr, 'resultscount' => $resultscount, 'items' => $items, 'warehouse' => $warehouse, 'inventory' => $inventory]);
					} else {
						$page->body = $config->twig->render('warehouse/binr/inventory-results.twig', ['page' => $page, 'config' => $config->binr, 'resultscount' => $resultscount, 'items' => $items, 'warehouse' => $warehouse, 'inventory' => $inventory]);
					}

				} else { // Make Inventory Request for item
					$pageurl = $page->fullURL->getUrl();
					$url = $page->parent('template=warehouse-menu,name=binr')->child('template=redir')->url."?action=search-item-bins&itemID=$item->itemid&page=$pageurl";
					$session->redirect($url, $http301 = false);
				}
			} else {
				$items = InvsearchQuery::create()->findDistinctItems(session_id(), $item->itemid);
				$page->body = $config->twig->render('warehouse/binr/inventory-results.twig', ['page' => $page, 'config' => $config->binr,  'resultscount' => $resultscount, 'items' => $items]);
			}
		}
	} elseif (!empty($input->get->serialnbr) | !empty($input->get->lotnbr) | !empty($input->get->itemID)) {
		if ($input->get->lotnbr) {
			$lotnbr = $input->get->text('lotnbr');
			$input->get->scan = $page->scan = $lotnbr;
			$resultscount = InvsearchQuery::create()->countByLotserial(session_id(), $lotnbr, $binID);
			$item = $resultscount == 1 ? InvsearchQuery::create()->get_lotserial(session_id(), $lotnbr, $binID) : false;
		} if ($input->get->serialnbr) {
			$serialnbr = $input->get->text('serialnbr');
			$input->get->scan = $page->scan = $serialnbr;
			$resultscount = InvsearchQuery::create()->countByLotserial(session_id(), $serialnbr, $binID);
			$item = $resultscount == 1 ? InvsearchQuery::create()->get_lotserial(session_id(), $serialnbr, $binID) : false;
		} elseif ($input->get->itemID) {
			$itemID = $input->get->text('itemID');
			$input->get->scan = $page->scan = $itemID;
			$resultscount = InvsearchQuery::create()->countByItemid(session_id(), $itemID, $binID);
			$item = $resultscount == 1 ? InvsearchQuery::create()->findOneByItemid(session_id(), $itemID, $binID) : false;
		}

		if ($resultscount == 1) {
			if (!empty($session->get('binr'))) { // Show result of BinR
				$nexturl = new Purl\Url($page->fullURL->getUrl());
				$nexturl->query->remove('itemID');
				$nexturl->query->remove('lotnbr');
				$nexturl->query->remove('serialnbr');

				if ($input->get->tobin || $input->get->frombin) {
					$nexturl->query->remove('binID');
				}

				$page->body = $config->twig->render('warehouse/binr/binr-result.twig', ['session' => $session, 'page' => $page, 'whsesession' => $whsesession, 'item' => $item, 'nexturl' => $nexturl]);
				$session->remove('binr');
			} else { // Prepare Binr Form
				$inventory = InvsearchQuery::create();
				$currentbins = BininfoQuery::create()->filterByItem(session_id(), $item)->select_bin_qty()->find();

				// 1. Binr form
				$page->formurl = $page->parent('template=warehouse-menu')->child('template=redir')->url;
				$page->body = $config->twig->render('warehouse/binr/binr-form.twig', ['session' => $session, 'config' => $config->binr,  'page' => $page, 'whsesession' => $whsesession, 'item' => $item, 'inventory' => $inventory, 'config' => $config->binr]);

				// 2. Choose From Bin Modal
				$page->body .= $config->twig->render('warehouse/binr/from-bins-modal.twig', ['config' => $config->binr, 'item' => $item, 'bins' => $currentbins]);

				// 3. Choose To Bin Modals
				$page->body .= $config->twig->render('warehouse/binr/to-bins-modal.twig', ['config' => $config->binr, 'currentbins' => $currentbins, 'warehouse' => $warehouse, 'session' => $session, 'item' => $item, 'inventory' => $inventory]);

				// 4. Warehouse Config JS
				$bins = $warehouse->get_bins();
				$validbins = BininfoQuery::create()->filterBySessionItemid(session_id(), $item->itemID)->find()->toArray('Bin');
				$jsconfig = array('warehouse' => array('id' => $whsesession->whseid, 'binarrangement' => $warehouse->get_binarrangementdescription(), 'bins' => $bins));
				$page->body .= $config->twig->render('util/js-variables.twig', ['variables' => array('warehouse' => $jsconfig, 'validfrombins' => $validbins)]);
			}
		} else { // Show Inventory Search Results
			$items = InvsearchQuery::create()->findBySessionid(session_id());
			$inventory = InvsearchQuery::create();
			$page->body = $config->twig->render('warehouse/binr/inventory-results.twig', ['page' => $page, 'resultscount' => $resultscount, 'items' => $items, 'inventory' => $inventory]);
		}
	} else { // Show Item Form
		$page->formurl = $page->parent('template=warehouse-menu')->child('template=redir')->url;
		$page->body    = $config->twig->render('warehouse/item-form.twig', ['page' => $page]);
	}

	// Add JS
	$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));
	$config->scripts->append(hash_templatefile('scripts/warehouse/shared.js'));
	$config->scripts->append(hash_templatefile('scripts/warehouse/binr.js'));

	include __DIR__ . "/basic-page.php";
