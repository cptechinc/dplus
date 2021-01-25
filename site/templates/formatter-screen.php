<?php
	$requestmethod = $input->requestMethod('POST') ? 'post' : 'get';
	$formatters = $modules->get('ScreenFormatters');
	$module_formatter = $formatters->formatter($page->formatter);
	$module_formatter->set_userID('default');
	$module_formatter->init_formatter();
	$html = $modules->get('HtmlWriter');

	if ($input->post) {
		$action = $input->$requestmethod->text('action');

		if ($action == 'preview') {
			$module_json = $modules->get('JsonExampleDataFiles');
			$json = $module_json->get_file(session_id(), $page->jsoncode);
			$module_formatter->generate_formatterfrominput($input);
			$preview = $config->twig->render($page->twig_include, ['json' => $json, 'module_formatter' => $module_formatter, 'blueprint' => $module_formatter->get_tableblueprint()]);
			$button = $html->button('class=btn btn-outline-secondary|type=button|data-toggle=collapse|data-target=#preview-screen|aria-expanded=true|aria-controls=preview-screen', 'Show / Hide');
			$header = $html->div('class=row mb-4', $html->div('class=col-sm-6', $html->h3('class=text-secondary', 'Preview:')) . $html->div('class=col-sm-6', $button));

			$page->body .= $html->div('class=border border-secondary p-2 mb-3',  $header . $html->div('class=collapse show|id=preview-screen', $preview));
		} elseif ($action == 'save') {
			$module_formatter->generate_formatterfrominput($input);
			$result = $module_formatter->save();

			if ($result) {
				$page->body .= $config->twig->render('util/alert.twig', ['type' => 'success', 'title' => 'Success!', 'iconclass' => 'fa fa-floppy-o fa-2x', 'message' => "$page->title formatter was able to be saved"]);
			} else {
				$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => "$page->title formatter was not able to be saved"]);
			}
			$page->body .= $html->div('class=mb-3');
		}
	}

	$page->body .= $config->twig->render('screen-formatters/formatter-form.twig', ['page' => $page, 'module_formatter' => $module_formatter]);
	include __DIR__ . "/basic-page.php";
