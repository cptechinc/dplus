<?php
/**
 * Initialization file for template files
 *
 * This file is automatically included as a result of $config->prependTemplateFile
 * option specified in your /site/config.php.
 *
 * You can initialize anything you want to here. In the case of this beginner profile,
 * we are using it just to include another file with shared functions.
 *
 */
$connected = Dplus\Databases\Connectors\Dplus::instance()->connect();
Dplus\Databases\Connectors\Dpluso::instance()->connect();

if ($connected === false) {
	if ($input->urlSegmentLast() != 'db-error') {
		$session->redirect($pages->get($config->errorpage_dplusdb)->url, $http301 = false);
	}
}

// BUILD URLs
$page->fullURL = new Purl\Url($page->httpUrl);
$page->fullURL->path = '';
if (!empty($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'] != '/') {
	$page->fullURL->join($_SERVER['REQUEST_URI']);
}

$input->purl = new Purl\Url($input->url($withQueryString = true));


// Check if Needs Redirect based on Template, Query Values
$noSigninRequiredTemplates = ['login', 'redir', 'quote-print'];

if ($input->get->pdf || $input->get->print || $input->lastSegment() == 'print') {

} elseif (!in_array($page->template, $noSigninRequiredTemplates) && LogpermQuery::create()->is_loggedin(session_id()) == false) {
	$session->returnurl = $page->fullURL->getUrl();
	$session->redirect($pages->get('template=login')->url, $http301 = false);
}

$modules->get('RecordLocker')->remove_locks_olderthan('all', 3);

$user->setup($input->get->sessionID ? $input->get->text('sessionID') : session_id());

$config->maxUrlSegments = 10;
$config->maxPageNum = 10000;


$rm = strtolower($input->requestMethod());
$values = $input->$rm;

// SET CONFIG PROPERTIES
if ($input->get->modal) {
	$config->modal = true;
}

if ($input->get->json) {
	$config->json = true;
}

$page->print = false;

if ($input->get->print || $input->lastSegment() == 'print') {
	$page->print = true;
}

if ($input->get->pdf) {
	$page->pdf = true;
}

$page->focus = $values->text('focus');

$appconfig = $pages->get('/config/app/');
$siteconfig = $pages->get('/config/');
$config->customer = $pages->get('/config/customer/');

$session->sessionid = session_id();

// Initialze CSS / JS / Twig
if ($values->offsetExists('action') === false|| $page->template == 'dplus-screen-formatter') {
	$hasher = $modules->get('FileHasher');;

	// ADD JS AND CSS
	$config->styles->append($hasher->getHashUrl('styles/bootstrap-grid.min.css'));
	$config->styles->append($hasher->getHashUrl('styles/theme.css'));
	$config->styles->append('//fonts.googleapis.com/css?family=Lusitana:400,700|Quattrocento:400,700');
	$config->styles->append('https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
	$config->styles->append('https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css');
	$config->styles->append($hasher->getHashUrl('styles/lib/fuelux.css'));
	$config->styles->append($hasher->getHashUrl('styles/lib/sweetalert2.css'));
	$config->styles->append($hasher->getHashUrl('styles/main.css'));
	$config->styles->append('https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/css/bootstrap-select.min.css');

	$config->scripts->append($hasher->getHashUrl('scripts/lib/jquery.js'));
	$config->scripts->append($hasher->getHashUrl('scripts/popper.js'));
	$config->scripts->append($hasher->getHashUrl('scripts/bootstrap.min.js'));
	$config->scripts->append($hasher->getHashUrl('scripts/lib/fuelux.js'));
	$config->scripts->append($hasher->getHashUrl('scripts/lib/moment.js'));
	$config->scripts->append($hasher->getHashUrl('scripts/lib/bootstrap-notify.js'));
	$config->scripts->append($hasher->getHashUrl('scripts/lib/bs-file-input.min.js'));
	$config->scripts->append('https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/js/bootstrap-select.min.js');
	$config->scripts->append($hasher->getHashUrl('scripts/uri.js'));
	$config->scripts->append($hasher->getHashUrl('scripts/lib/sweetalert2.js'));
	$config->scripts->append($hasher->getHashUrl('scripts/classes.js'));
	$config->scripts->append($hasher->getHashUrl('scripts/ajax-modal.js'));
	$config->scripts->append($hasher->getHashUrl('scripts/main.js'));

	$mtwig = $modules->get('Twig');
	$config->twigloader = $mtwig->getLoader();
	$config->twig = $mtwig->getTwig();
	$config->twig->getExtension(\Twig\Extension\CoreExtension::class)->setNumberFormat(3, '.', '');

	if ($page->fullURL->query->__toString() != '') {
		$page->title_previous = $page->title;
	}

	$page->show_breadcrumbs = true;

	$agent = new Jenssegers\Agent\Agent();
	$config->js('agent', [
		'browser' => strtolower($agent->browser())
	]);

	$page->js .= $config->twig->render('util/js/variables.js.twig', ['variables' => ['agent' => $config->js('agent')]]);
}
include_once('./_func.php');
include ('./_init.js.php');
