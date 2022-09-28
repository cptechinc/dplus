<?php
use Dplus\DocManagement\Viewer as DocViewer;

$docView = DocViewer::getInstance();

$config->js('api', [
	'urls' => [
		'mdm' => [
			'docvwr' => [
				'copy' => $page->jsonApiUrl('mdm/docvwr/copy')
			]
		]
	]
]);

$config->js('config', [
	'urls' => [
		'docvwr' => $docView->url('')
	]
]);

$agent = new Jenssegers\Agent\Agent();
$config->js('agent', [
	'browser' => strtolower($agent->browser())
]);
