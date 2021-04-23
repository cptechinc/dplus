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

include_once("./_func.php"); // include our shared functions

$config->maxUrlSegments = 8;

// BUILD AND INSTATIATE CLASSES
$page->fullURL = new Purl\Url($page->httpUrl);
$page->fullURL->path = '';
if (!empty($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'] != '/') {
	$page->fullURL->join($_SERVER['REQUEST_URI']);
}

$input->purl = new Purl\Url($input->url($withQueryString = true));

// CHECK DATABASE CONNECTIONS
if ($page->id != $config->errorpage_dplusdb) {
	if (empty(wire('dplusdata')) || empty(wire('dpluso'))) {
		$modules->get('DplusDatabase')->logError('At least One database is not connected');
		$session->redirect($pages->get($config->errorpage_dplusdb)->url, $http301 = false);
	}

	$db_modules = array(
		'dplusdata' => array(
			'module'   => 'DplusDatabase',
			'default'  => true
		),
		'dpluso' => array(
			'module'          => 'DplusOnlineDatabase',
			'default'  => false
		)
	);

	foreach ($db_modules as $key => $connection) {
		$module = $modules->get($connection['module']);
		$module->connectPropel();

		try {
			$propel_name  = $module->dbConnectionName();
			$$propel_name = $module->getDebugConnection();
		} catch (Exception $e) {
			$module->logError($e->getMessage());
			$session->redirect($pages->get($config->errorpage_dplusdb)->url, $http301 = false);
		}
	}

	$templates_nosignin = array('login', 'redir', 'quote-print');

	if ($input->get->pdf || $input->get->print) {

	} elseif (!in_array($page->template, $templates_nosignin) && LogpermQuery::create()->is_loggedin(session_id()) == false) {
		$session->returnurl = $page->fullURL->getUrl();
		$session->redirect($pages->get('template=login')->url, $http301 = false);
	}
	$user->setup($input->get->sessionID ? $input->get->text('sessionID') : session_id());

	$modules->get('RecordLocker')->remove_locks_olderthan('all', 3);
} else {
	if (!$input->get->retry) {
		$configimporter = $modules->get('Configs');
		if ($configimporter->export_datafile_exists()) {
			$configimporter->import();
			$page->fullURL->query->set('retry', 'true');
			$session->redirect($page->fullURL->getUrl());
		}
	} else {
		try {
			$con    = $modules->get('DplusDatabase')->propelWriteConnection();
			$dpluso = $modules->get('DplusOnlineDatabase')->propelWriteConnection();
		} catch (Exception $e) {
			$page->show_title = true;
		}
		$session->redirect($pages->get('/')->url);
	}
}

$rm = strtolower($input->requestMethod());
$values = $input->$rm;

if (!$values->action || $page->template == 'dplus-screen-formatter') {
	// ADD JS AND CSS
	$config->styles->append(hash_templatefile('styles/bootstrap-grid.min.css'));
	$config->styles->append(hash_templatefile('styles/theme.css'));
	$config->styles->append('//fonts.googleapis.com/css?family=Lusitana:400,700|Quattrocento:400,700');
	$config->styles->append('https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
	$config->styles->append(hash_templatefile('styles/lib/fuelux.css'));
	//$config->styles->append(hash_templatefile('styles/lib/sweetalert.css'));
	$config->styles->append(hash_templatefile('styles/lib/sweetalert2.css'));
	$config->styles->append(hash_templatefile('styles/main.css'));

	$config->scripts->append(hash_templatefile('scripts/lib/jquery.js'));
	$config->scripts->append(hash_templatefile('scripts/popper.js'));
	$config->scripts->append(hash_templatefile('scripts/bootstrap.min.js'));
	$config->scripts->append(hash_templatefile('scripts/lib/fuelux.js'));
	$config->scripts->append(hash_templatefile('scripts/lib/moment.js'));
	$config->scripts->append(hash_templatefile('scripts/lib/bootstrap-notify.js'));
	$config->scripts->append(hash_templatefile('scripts/uri.js'));
	$config->scripts->append(hash_templatefile('scripts/lib/sweetalert2.js'));
	$config->scripts->append(hash_templatefile('scripts/classes.js'));
	$config->scripts->append(hash_templatefile('scripts/main.js'));
}

// SET CONFIG PROPERTIES
if ($input->get->modal) {
	$config->modal = true;
}

if ($input->get->json) {
	$config->json = true;
}

if ($input->get->print) {
	$page->print = true;
}

if ($input->get->pdf) {
	$page->pdf = true;
}

$page->focus = $input->get->text('focus');

$appconfig = $pages->get('/config/app/');
$siteconfig = $pages->get('/config/');
$config->customer = $pages->get('/config/customer/');

$session->sessionid = session_id();

if (!$values->action || $page->template == 'dplus-screen-formatter') {
	$mtwig = $modules->get('Twig');
	$config->twigloader = $mtwig->getLoader();
	$config->twig = $mtwig->getTwig();
	$config->twig->getExtension(\Twig\Extension\CoreExtension::class)->setNumberFormat(3, '.', '');

	if ($page->fullURL->query->__toString() != '') {
		$page->title_previous = $page->title;
	}

	$page->show_breadcrumbs = true;
}
