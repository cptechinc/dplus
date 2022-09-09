<?php namespace Controllers\Mar\Armain;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Code;
// ProcessWire
use ProcessWire\WireData;
// Dplus Filters
use Dplus\Filters;
// Dplus CRUD
use Dplus\Codes;


class Ctm extends AbstractCodeTableController {
	const DPLUSPERMISSION = 'ctm';
	const TITLE      = 'Customer Type Code';
	const SUMMARY    = 'View / Edit Customer Type Codes';
	const SHOWONPAGE = 10;
	const USE_EDIT_MODAL  = false;
	const USE_EDIT_PAGE   = true;

	public static function _url() {
		return Menu::ctmUrl();
	}

	public static function getCodeFilter() {
		return new Filters\Mar\ArCustTypeCode();
	}

	public static function getCodeTable() {
		return Codes\Mar\Ctm::instance();
	}

/* =============================================================
	Indexes
============================================================= */
	public static function handleCRUD(WireData $data) {
		if (self::validateUserPermission() === false) {
			return self::pw('session')->redirect(self::url(), $http301 = false);
		}
		$fields = ['code|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		$url  = self::url();
		$ctm = self::getCodeTable();

		if ($data->action) {
			switch ($data->action) {
				case 'update-notes':
				case 'delete-notes':
					$ctm->qnotes->processInput(self::pw('input'));
					$url = self::codeEditUrl($data->whseID);
					break;
				default:
					$ctm->processInput(self::pw('input'));
					if ($data->action != 'delete') {
						$url = self::codeEditUrl($data->code);
					}
					break;
			}
		}
		self::pw('session')->redirect($url, $http301 = false);
	}

	protected static function code(WireData $data) {
		$ctm = self::getCodeTable();
		$code = $ctm->getOrCreate($data->code);

		if ($code->isNew() === false) {
			self::pw('page')->headline = "CTM: Editing $data->code";
			$ctm->lockrecord($code);
		}
		self::initHooks();
		self::pw('page')->js .= self::pw('config')->twig->render('code-tables/mar/ctm/edit/.js.twig', ['ctm' => $ctm]);

		if ($code->isNew() === false) {
			self::pw('page')->js .= self::pw('config')->twig->render('msa/noce/ajax/js.twig');
			// self::pw('page')->js .= self::pw('config')->twig->render('code-tables/min/iwhm/edit/qnotes/js.twig', ['iwhm' => $ctm]);
		}
		$html = self::displayCode($data, $code);
		self::getCodeTable()->deleteResponse();
		return $html;
	}

	protected static function list(WireData $data) {
		$ctm = self::getCodeTable();
		$ctm->recordlocker->deleteLock();
		$html =  parent::list($data);
		return $html;
	}

/* =============================================================
	Displays
============================================================= */
	protected static function displayCode(WireData $data, Code $code) {
		$html  = '';
		$html .= static::renderBreadcrumbs($data);
		$html .= static::renderResponse($data);
		$html .= static::renderLockedAlert($data);
		$html .= static::renderCode($data, $code);
		return $html;
	}

/* =============================================================
	Render HTML / JS
============================================================= */
	protected static function renderList(WireData $data, PropelModelPager $codes) {
		$codeTable = static::getCodeTable();
		return self::pw('config')->twig->render('code-tables/mar/ctm/list.twig', ['manager' => $codeTable, 'codes' => $codes]);
	}

	protected static function renderModal(WireData $data) {
		return '';
	}

	protected static function renderCode(WireData $data, Code $code) {
		$codeTable = static::getCodeTable();
		return self::pw('config')->twig->render('code-tables/mar/ctm/edit/display.twig', ['manager' => $codeTable, 'code' => $code]);
	}
}
