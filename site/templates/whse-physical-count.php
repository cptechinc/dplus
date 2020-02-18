<?php
	use Propel\Runtime\ActiveQuery\Criteria;

	$whsesession = WhsesessionQuery::create()->findOneBySessionid(session_id());
	$warehouse   = WarehouseQuery::create()->findOneByWhseid($whsesession->whseid);
	$modules->get('DpagesMwm')->init_physicalcount();
	$config->inventory = $modules->get('ConfigsWarehouseInventory');

	$html = $modules->get('HtmlWriter');

	$page->formurl = $page->parent('template=warehouse-menu')->child('template=redir')->url;

	if ($input->get->scan) {
		$scan = $input->get->text('scan');
		$page->scan = $scan;
		$page->title = "Find Item Inquiry for $scan";

		$query_phys = WhseitemphysicalcountQuery::create();
		$query_phys->filterBySessionid(session_id());
		$query_phys->filterByScan($scan);

		if ($query_phys->count() == 1) {
			$physicalitem = $query_phys->findOne();
			$page->title = "Physical Count for $physicalitem->itemid";

			if ($physicalitem->is_complete()) {
				$page->fullURL->query->remove('scan');
				$session->redirect($page->fullURL->getUrl());
			}

			if ($physicalitem->has_error()) {
				$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => $physicalitem->get_error()]);
				$page->body .= $html->div('class=mb-3');
				$page->body .= $config->twig->render('warehouse/inventory/physical-count/item-search-form.twig', ['page' => $page]);
			} else {
				if ($session->bin && $config->inventory->physicalcount_savebin) {
					$physicalitem->setBin($session->bin);
				}
				$page->body = $config->twig->render('warehouse/inventory/physical-count/physical-count-form.twig', ['page' => $page, 'item' => $physicalitem]);
				$page->body .= $config->twig->render('warehouse/inventory/bins-modal.twig', ['warehouse' => $warehouse]);
				$config->scripts->append(hash_templatefile('scripts/warehouse/physical-count.js'));
			}
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
					$page->title = "No results found for '$q'";
					$page->body .= $html->h3('class=secondary-text', $page->title);
					$page->body .= $config->twig->render('warehouse/inventory/physical-count/item-search-form.twig', ['page' => $page]);
				}
			} else {
				$page->title = "No results found for '$scan'";
				$page->body .= $html->h3('class=secondary-text', $page->title);
				$page->body .= $config->twig->render('warehouse/inventory/physical-count/item-search-form.twig', ['page' => $page]);
			}
		}
	} else {
		$page->body = $config->twig->render('warehouse/inventory/physical-count/item-search-form.twig', ['page' => $page]);
	}

	$bins = WarehouseBinQuery::create()->get_warehousebins($whsesession->whseid)->toArray();
	$jsconfig = array('warehouse' => array('id' => $whsesession->whseid, 'binarrangement' => $warehouse->get_binarrangementdescription(), 'bins' => $bins));
	$page->body .= $config->twig->render('util/js-variables.twig', ['variables' => array('warehouse' => $jsconfig)]);
	$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));

	include __DIR__ . "/basic-page.php";
