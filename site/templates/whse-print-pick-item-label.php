<?php
	$whsesession = WhsesessionQuery::create()->findOneBySessionid(session_id());
	$warehouse   = WarehouseQuery::create()->findOneByWhseid($whsesession->whseid);
	$m_print = $modules->get('PrintLabelItem');

	$pickingsession = $modules->get('DplusoWarehousePicking');
	$pickingsession->set_sessionID(session_id());


	$page->addHook('Page::print_labelredirURL', function($event) {
		$p = $event->object;
		$ordn   = $event->arguments(0);
		$itemID = $event->arguments(1);
		$url = new Purl\Url($p->parent('template=warehouse-menu')->child('template=redir')->url);
		$url->query->set('action', 'init-pick-item-label-print');
		$url->query->set('ordn', $ordn);
		$url->query->set('itemID', $itemID);
		$url->query->set('sessionID', session_id());
		$event->return = $url->getUrl();
	});


	if ($input->get->ordn) {
		$ordn = $input->get->text('ordn');
		$pickingsession->set_ordn($ordn);

		if (SalesOrderQuery::create()->filterByOrdernumber($ordn)->count()) {
			if ($input->get->linenbr) {
				$linenbr = $input->get->text('linenbr') == 'ALL' ? $input->get->text('linenbr') : $input->get->int('linenbr');
				$exists = $input->get->text('linenbr') == 'ALL';
				$page->returnpage = $input->get->text('returnpage');

				if (is_numeric($linenbr)) {
					$sublinenbr = $input->get->int('sublinenbr');
					$pickingsession->set_linenbr($linenbr);
					$pickingsession->set_sublinenbr($sublinenbr);
					$exists = boolval(SalesOrderDetailQuery::create()->filterByOrderNumberLinenbr($ordn, $linenbr)->count());
					$pickitem = PickSalesOrderDetailQuery::create()->filterBySessionidOrder(session_id(), $ordn)->filterByLinenbrSublinenbr($linenbr, $sublinenbr)->findOne();
					$itemID = $pickitem->itemid;
					$page->title = "Printing label(s) for $pickitem->itemid";
				} else {
					$itemID = 'ALL';
					$page->title = "Printing labels for all picked items on Order $ordn";
				}

				if ($exists) {
					$nbr_labels = $input->get->labels ? $input->get->int('labels') : 1;
					$modules->get('DplusRequest')->self_request($page->print_labelredirURL($ordn, $itemID));

					if (LabelPrintSessionQuery::create()->filterBySessionid(session_id())->count()) {
						$labelsession = LabelPrintSessionQuery::create()->findOneBySessionid(session_id());
					} else {
						$labelsession = new LabelPrintSession();
						$labelsession->setSessionid(session_id());
						$labelsession->setItemid($itemID);
						$labelsession->setWhse($whsesession->whseid);
					}

					$qty = $itemID == 'ALL' ? 0 : $pickingsession->get_userpickedtotal();
					$page->formurl = $page->parent('template=warehouse-menu')->child('template=redir')->url;
					$page->body = $config->twig->render('warehouse/inventory/print-pick-item-label/label-form.twig', ['page' => $page, 'labelsession' => $labelsession, 'm_print' => $m_print, = 'ordn' => $ordn, 'qty' => $qty]);
					$page->body .= $config->twig->render('warehouse/inventory/print-item-label/labels-modal.twig', ['formats' => $m_print->get_labelformats()]);
					$page->body .= $config->twig->render('warehouse/inventory/print-item-label/printers-modal.twig', ['printers' => $m_print->get_printers()]);
				} else {
					$page->headline = "Sales Order #$ordn Line $linenbr could not be found";
					$page->body = $config->twig->render('util/error-page.twig', ['msg' => "Check if the Order Number / Line Number is correct"]);
				}
			}
		} else {
			$page->headline = "Sales Order #$ordn could not be found";
			$page->body = $config->twig->render('util/error-page.twig', ['msg' => "Check if the Order Number is correct"]);
		}
	} else {

	}
	// Add JS
	$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));
	$config->scripts->append(hash_templatefile('scripts/warehouse/print-item-label.js'));


	include __DIR__ . "/basic-page.php";
