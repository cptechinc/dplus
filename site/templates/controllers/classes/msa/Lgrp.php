<?php namespace Controllers\Msa;
// External Libraries, classes
use Purl\Url as Purl;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager;
// Dplus Models
use ProspectSource;
// Dplus Filters
use Dplus\Filters;
// Dplus CRUD
use Dplus\Codes\Msa\Lgrp as LgrpManager;

class Lgrp extends Base {
	const DPLUSPERMISSION = 'msa';
	const SHOWONPAGE = 10;

	private static $lgrp;

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['code|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		self::pw('page')->show_breadcrumbs = false;

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}
		return self::list($data);
	}

	public static function handleCRUD($data) {
		$fields = ['code|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		$url  = self::lgrpUrl();
		$lgrp  = self::getLgrp();

		if ($data->action) {
			$lgrp->processInput(self::pw('input'));
			$url  = self::lgrpUrl($data->code);
		}
		self::pw('session')->redirect($url, $http301 = false);
	}

	private static function list($data) {
		$fields = ['q|text'];
		self::sanitizeParametersShort($data, $fields);
		$page   = self::pw('page');
		$filter = new Filters\Msa\SysLoginGroup();

		$page->headline = "Login Group";

		if (empty($data->q) === false) {
			$filter->search($data->q);
			$page->headline = "LGRP: Searching for '$data->q'";
		}

		$filter->sortby($page);
		$codes = $filter->query->paginate(self::pw('input')->pageNum, self::SHOWONPAGE);
		self::initHooks();

		$page->js .= self::pw('config')->twig->render('code-tables/msa/lgrp/.js.twig', ['src' => self::getLgrp()]);
		$html = self::displayList($data, $codes);
		self::getLgrp()->deleteResponse();
		return $html;
	}

/* =============================================================
	URLs
============================================================= */
	public static function lgrpUrl($code = '') {
		if (empty($code)) {
			return Menu::lgrpUrl();
		}
		return self::srcFocusUrl($code);
	}

	public static function srcFocusUrl($focus) {
		$filter = new Filters\Msa\SysLoginGroup();
		if ($filter->exists($focus) === false) {
			return Menu::lgrpUrl();
		}
		$position = $filter->positionQuick($focus);
		$pagenbr = self::getPagenbrFromOffset($position, self::SHOWONPAGE);

		$url = new Purl(Menu::lgrpUrl());
		$url->query->set('focus', $focus);
		$url = self::pw('modules')->get('Dpurl')->paginate($url, 'src', $pagenbr);
		return $url->getUrl();
	}

	public static function codeDeleteUrl($code) {
		$url = new Purl(Menu::lgrpUrl());
		$url->query->set('code', $code);
		$url->query->set('action', 'delete-code');
		return $url->getUrl();
	}

/* =============================================================
	Displays
============================================================= */
	private static function displayList($data, PropelModelPager $codes) {
		$config = self::pw('config');
		$lgrp = self::getLgrp();

		$html  = '';
		// $html .= $config->twig->render('code-tables/msa/src/bread-crumbs.twig');
		$html .= self::displayResponse($data);
		$html .= $config->twig->render('code-tables/list.twig', ['manager' => $lgrp, 'codes' => $codes]);
		$html .= $config->twig->render('util/paginator/propel.twig', ['pager'=> $codes]);
		$html .= $config->twig->render('code-tables/edit-modal.twig', ['manager' => $lgrp]);
		return $html;
	}

	public static function displayResponse($data) {
		$lgrp = self::getLgrp();
		$response = $lgrp->getResponse();
		if (empty($response)) {
			return '';
		}
		return self::pw('config')->twig->render('code-tables/response.twig', ['response' => $response]);
	}

/* =============================================================
	Hooks
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('Dpages');

		$m->addHook('Page(template=test)::menuUrl', function($event) {
			$event->return = Menu::menuUrl();
		});

		$m->addHook('Page(template=test)::codeDeleteUrl', function($event) {
			$event->return = self::codeDeleteUrl($event->arguments(0));
		});
	}

/* =============================================================
	Supplemental
============================================================= */
	public static function getLgrp() {
		if (empty(self::$lgrp)) {
			self::$lgrp = new LgrpManager();
		}
		return self::$lgrp;
	}
}
