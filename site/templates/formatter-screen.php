<?php
	$page->pagetitle = $page->title;
	$module_formatter = $modules->get($page->formatter);
	$module_formatter->init_formatter();
	$http = new WireHttp();
	$html = $modules->get('HtmlWriter');

	if ($input->post) {

	}

	if ($input->get->text('action') == 'preview') {
		$module_json = $modules->get('JsonExampleDataFiles');
		$json = $module_json->get_file(session_id(), $page->jsoncode);
		//$module_formatter->generate_formatterfrominput($input);

		$preview = $config->twig->render('items/ii/sales-orders/sales-orders.twig', ['json' => $json, 'module_formatter' => $module_formatter, 'blueprint' => $module_formatter->get_tableblueprint()]);
		$button = $html->button('class=btn btn-outline-secondary|type=button|data-toggle=collapse|data-target=#preview-screen|aria-expanded=true|aria-controls=preview-screen', 'Show / Hide');
		$header = $html->div('class=row mb-4', $html->div('class=col-sm-6', $html->h3('class=text-secondary', 'Preview:')) . $html->div('class=col-sm-6', $button));

		$page->body .= $html->div('class=border border-secondary p-2 mb-3',  $header . $html->div('class=collapse show|id=preview-screen', $preview));
	}

	$page->body .= $config->twig->render('screen-formatters/formatter-form.twig', ['page' => $page, 'module_formatter' => $module_formatter]);
	include __DIR__ . "/basic-page.php";
