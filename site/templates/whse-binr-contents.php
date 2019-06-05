<?php
	$whsesession = WhsesessionQuery::create()->findOneBySessionid(session_id());
	$warehouse = WarehouseQuery::create()->findOneByWhseid($whsesession->whseid);


	$page->formurl = $page->parent('template=warehouse-menu')->child('template=redir')->url;
	$page->redir_bininquiry = $pages->get('/warehouse/inventory/')->child('template=redir')->url."?action=bin-inquiry";
	$page->url_bininquiry = $pages->get('pw_template=whse-bin-inquiry')->url;

	$page->body    = $config->twig->render('warehouse/binr/contents/move-form.twig', ['page' => $page, 'session' => $session, 'whsesession' => $whsesession]);
	$page->body    .= $config->twig->render('warehouse/binr/contents/bins-modal.twig', ['warehouse' => $warehouse]);
	$page->body    .= $config->twig->render('warehouse/binr/contents/bin-contents-modal.twig', ['page' => $page]);

	// Add JS
	$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));
	$config->scripts->append(hash_templatefile('scripts/warehouse/shared.js'));
	$config->scripts->append(hash_templatefile('scripts/warehouse/binr-contents.js'));

	include __DIR__ . "/basic-page.php";
