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
	],
	'ajax' => [
		'urls' => [
			'lookup' => $page->searchLookupUrl(''),
			'json'   => $page->jsonApiUrl(''),
		]
	]
]);

$agent = new Jenssegers\Agent\Agent();
$config->js('agent', [
	'browser' => strtolower($agent->browser())
]);

$config->js('user', [
	'dplus' => [
		'whseid' => $user->whseid
	]
]);
