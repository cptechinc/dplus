<?php
	$whsesession = WhsesessionQuery::create()->findOneBySessionid(session_id());
	$warehouse = WarehouseQuery::create()->findOneByWhseid($whsesession->whseid);
	$config->scripts->append(hash_templatefile('scripts/warehouse/binr.js'));
	$binID = '';

	if ($input->get->scan) {
		$scan = $input->get->text('scan');
		$page->fullURL->query->remove('scan');
		$resultscount = InvsearchQuery::create()->filterBy('Sessionid', session_id())->count();

		if ($resultscount == 0) {
			$items = array();
			$page->body = $config->twig->render('warehouse/binr/inventory-results.twig', ['page' => $page]);
		} elseif ($resultscount == 1) {
			$item = InvsearchQuery::create()->findOneBySessionid(session_id());
			$url = $page->parent('template=warehouse-menu')->child('template=redir')->url."?action=search-item-bins&itemID=$itemID&page=$pageurl";
			$session->redirect($url , $http301 = false);
		} else {
			if (InvsearchQuery::create()->countDistinctItemid(session_id(), $binID) == 1) {
				$item = InvsearchQuery::create()->findOneBySessionidBin(session_id(), $binID);
				$pageurl = $page->fullURL->getUrl();
				$url = $page->parent('template=warehouse-menu,name=binr')->child('template=redir')->url."?action=search-item-bins&itemID=$item->itemid&page=$pageurl";
				$session->redirect($url, $http301 = false);
			} else {
				// TODO TEST
				$items = InvsearchQuery::create()->findByItemidDistinct();
				$page->body = __DIR__."/inventory-results.php";
			}
		}
	} elseif (!empty($input->get->serialnbr) | !empty($input->get->lotnbr) | !empty($input->get->itemID)) {
		if ($input->get->frombin) {
			$binID = $input->get->text('frombin');
		} else {
			$binID = $input->get->text('binID');
		}

		if ($input->get->itemID) {
			$itemID = $input->get->text('itemID');
			$input->get->scan = $itemID;
			$resultscount = InvsearchQuery::create()->countByItemID(session_id(), $itemID, $binID);
			$item = $resultscount == 1 ? InvsearchQuery::create()->findOneByItemid(session_id(), $itemID, $binID) : false;
		}

		if ($resultscount == 1) {
			if (!empty($session->get('binr'))) {
				$nexturl = new Purl\Url($page->fullURL->getUrl());

				if ($input->get->tobin || $input->get->frombin) {
					$nexturl->query->remove('itemID');
				}

				$page->body = $config->twig->render('warehouse/binr/binr-result.twig', ['session' => $session, 'page' => $page, 'whsesession' => $whsesession, 'item' => $item, 'url' => $nexturl]);
				$session->remove('binr');
			} else {
				$inventory = InvsearchQuery::create();
				$currentbins = BininfoQuery::create()->filterByItem(session_id(), $item)->select_bin_qty()->find();

				// 1. Binr form
				$page->formurl = $page->parent('template=warehouse-menu')->child('template=redir')->url;
				$page->body = $config->twig->render('warehouse/binr/binr-form.twig', ['session' => $session, 'page' => $page, 'whsesession' => $whsesession, 'item' => $item, 'inventory' => $inventory]);

				// 2. Choose From Bin Modal
				$page->body .= $config->twig->render('warehouse/binr/from-bins-modal.twig', ['item' => $item, 'bins' => $currentbins]);

				// 3. Choose To Bin Modals
				$page->body .= $config->twig->render('warehouse/binr/to-bins-modal.twig', ['currentbins' => $currentbins, 'warehouse' => $warehouse, 'session' => $session, 'item' => $item, 'inventory' => $inventory]);
				$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));
			}
		} else {
			$items = InvsearchQuery::create()->findBySessionid(session_id());
			$inventory = InvsearchQuery::create();
			$page->body = $config->twig->render('warehouse/binr/inventory-results.twig', ['page' => $page, 'resultscount' => $resultscount, 'items' => $items, 'inventory' => $inventory]);
		}

	} else {
		$page->formurl = $page->parent('template=warehouse-menu')->child('template=redir')->url;
		$page->body    = $config->twig->render('warehouse/item-form.twig', ['page' => $page]);
	}



	include __DIR__ . "/basic-page.php";
