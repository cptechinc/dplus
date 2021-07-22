<?php namespace Controllers\Mqo\Quote;
// Purl URI Library
use Purl\Url as Purl;
// Dplus Configs
use Dplus\Configs;
// Alias Document Finders
use Dplus\DocManagement\Finders as DocFinders;
// Dplus Validators
use Dplus\CodeValidators\Mqo as MqoValidator;
// Dplus Filters
use Dplus\Filters\Mqo\Quote as FilterQuotes;
// Mvc Controllers
use Mvc\Controllers\AbstractController;
use Controllers\Mii\Ii;

abstract class Base extends AbstractController {
	private static $validate;
	private static $docm;
	private static $configQt;
	private static $filehasher;

/* =============================================================
	Displays
============================================================= */
	protected static function invalidQt($data) {
		$page   = self::pw('page');
		$config = self::pw('config');
		$page->headline = "Quote #$data->qnbr not found";
		$html = $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Quote Not Found', 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Quote # $data->qnbr can not be found"]);
		$html .= '<div class="mb-3"></div>';
		$html .= self::lookupForm();
		return $html;
	}

	protected static function qtAccessDenied($data) {
		$page   = self::pw('page');
		$config = self::pw('config');
		$page->headline = "Access Denied";
		$html = $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Quote Access Denied', 'iconclass' => 'fa fa-warning fa-2x', 'message' => "You don't have access to Quote # $data->qnbr"]);
		$html .= '<div class="mb-3"></div>';
		$html .= self::lookupForm();
		return $html;
	}

	protected static function lookupForm() {
		$config = self::pw('config');
		$html = $config->twig->render('quotes/quote/lookup-form.twig');
		return $html;
	}

	protected static function breadCrumbs() {
		return self::pw('config')->twig->render('quotes/bread-crumbs.twig');
	}

	protected static function lookupScreen($data) {
		$html  = self::breadCrumbs();
		$html .= self::lookupForm($data);
		return $html;
	}

/* =============================================================
	URLs
============================================================= */
	public static function quoteUrl($qnbr = '') {
		$url = new Purl(self::pw('pages')->get('pw_template=quote-view')->url);
		if ($qnbr) {
			$url->query->set('qnbr', $qnbr);
		}
		return $url->getUrl();
	}

	public static function quoteListUrl($qnbr = '') {
		$url = new Purl(self::pw('pages')->get('pw_template=quotes')->url);
		if ($qnbr) {
			$filter = new FilterQuotes();

			if ($filter->exists($qnbr)) {
				$url->query->set('focus', $qnbr);
				$offset = $filter->positionQuick($qnbr);
				$pagenbr = self::getPagenbrFromOffset($offset);
				$url = self::pw('modules')->get('Dpurl')->paginate($url, self::pw('pages')->get('pw_template=quotes')->name, $pagenbr);
			}
		}
		return $url->getUrl();
	}

	public static function quotePrintUrl($qnbr) {
		$url = new Purl(self::quoteUrl($qnbr));
		$url->path->add('print');
		return $url->getUrl();
	}

	public static function quoteEditUrl($qnbr = '') {
		$url = new Purl(self::quoteUrl($qnbr));
		$url->path->add('edit');
		return $url->getUrl();
	}

	public static function quoteEditNewUrl() {
		$url = new Purl(self::quoteEditUrl($qnbr));
		$url->query->set('action', 'edit-new-quote');
		return $url->getUrl();
	}

	public static function quoteEditUnlockUrl($qnbr) {
		$url = new Purl(self::quoteEditUrl($qnbr));
		$url->query->set('action', 'unlock-quote');
		return $url->getUrl();
	}

	public static function quoteNotesUrl($qnbr, $linenbr = '') {
		$url = new Purl(self::quoteUrl($qnbr));
		$url->path->add('notes');
		$hash = $linenbr > 0 ? "#line-$linenbr" : '';
		return $url->getUrl().$hash;
	}

	public static function orderQuoteUrl($qnbr) {
		$url = new Purl(self::quoteUrl($qnbr));
		$url->path->add('order');
		return $url->getUrl();
	}

	public static function documentsUrl($qnbr) {
		$url = new Purl(self::quoteUrl($qnbr));
		$url->path->add('documents');
		return $url->getUrl();
	}

	public static function documentUrl($qnbr, $folder, $document = '') {
		$url = new Purl(self::quoteUrl($qnbr));
		$url->path->add('documents');
		$url->query->set('folder', $folder);
		$url->query->set('document', $document);
		return $url->getUrl();
	}

/* =============================================================
	Supplemental
============================================================= */
	/**
	 * Return Mqo Validator
	 * @return MqoValidator
	 */
	protected static function validator() {
		if (empty(self::$validate)) {
			self::$validate = new MqoValidator();
		}
		return self::$validate;
	}

	/**
	 * Return Document Management
	 * @return DocFinders\Qt
	 */
	public static function docm() {
		if (empty(self::$docm)) {
			self::$docm = new DocFinders\Qt();
		}
		return self::$docm;
	}

	/**
	 * Return Sales Order Config
	 * @return ConfigQt
	 */
	protected static function configQt() {
		if (empty(self::$configQt)) {
			self::$configQt = Configs\Qt::config();
		}
		return self::$configQt;
	}

	/**
	 * Return Sales Order Config
	 * @return ProcessWire\FileHasher
	 */
	public static function getFileHasher() {
		if (empty(self::$filehasher)) {
			self::$filehasher = self::pw('modules')->get('FileHasher');
		}
		return self::$filehasher;
	}
}
