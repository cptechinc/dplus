<?php namespace Controllers\Min\Inmain;
// Purl URI Manipulation Library
use Purl\Url as Purl;
// ProcessWire 
use ProcessWire\WireData;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Code;
// Dplus
use Dplus\Filters;
use Dplus\Codes;

/**
 * Iwhm
 * 
 * Controller for handling HTTP Requests for the Iwhm Codetable
 */
class Iwhm extends AbstractCodeTableController {
	const DPLUSPERMISSION = 'iwhm';
	const TITLE      = 'Warehouse';
	const SUMMARY    = 'View / Edit Warehouses';
	const SHOWONPAGE = 10;
	const USE_EDIT_MODAL  = false;
	const USE_EDIT_PAGE   = true;

	public static function _url() {
		return Menu::iwhmUrl();
	}

	public static function getCodeFilter() {
		return new Filters\Min\Warehouse();
	}

	public static function getCodeTable() {
		$table = Codes\Min\Iwhm::instance();
		$table->initFieldAttributes();
		return $table;
	}

/* =============================================================
	Indexes
============================================================= */
	public static function handleCRUD(WireData $data) {
		if (self::validateUserPermission() === false) {
			return self::pw('session')->redirect(self::url(), $http301 = false);
		}
		$fields = ['code|string', 'whseID|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		$url  = self::url();
		$iwhm = self::getCodeTable();

		if ($data->action) {
			switch ($data->action) {
				case 'update-notes':
				case 'delete-notes':
					$iwhm->qnotes->processInput(self::pw('input'));
					$url = self::codeEditUrl($data->whseID);
					break;
				default:
					$iwhm->processInput(self::pw('input'));
					if ($data->action != 'delete') {
						$url = self::codeEditUrl($data->code);
					}
					break;
			}
		}
		self::pw('session')->redirect($url, $http301 = false);
	}

	protected static function code(WireData $data) {
		$iwhm = self::getCodeTable();
		$warehouse = $iwhm->getOrCreate($data->code);

		if ($warehouse->isNew() === false) {
			self::pw('page')->headline = "IWHM: Editing $data->code";
			$iwhm->lockrecord($warehouse);
		}
		self::initHooks();
		self::pw('config')->scripts->append(self::getFileHasher()->getHashUrl('scripts/code-tables/ajax-modal.js'));
		self::pw('page')->js .= self::pw('config')->twig->render('code-tables/min/iwhm/edit/.js.twig', ['iwhm' => $iwhm]);

		if ($warehouse->isNew() === false) {
			self::pw('page')->js .= self::pw('config')->twig->render('msa/noce/ajax/js.twig');
			self::pw('page')->js .= self::pw('config')->twig->render('code-tables/min/iwhm/edit/qnotes/js.twig', ['iwhm' => $iwhm]);
		}
		$html = self::displayCode($data, $warehouse);
		self::getCodeTable()->deleteResponse();
		self::getCodeTable()->qnotes->iwhs->deleteResponse();
		return $html;
	}

	protected static function list(WireData $data) {
		$iwhm = self::getCodeTable();
		$iwhm->recordlocker->deleteLock();
		$html =  parent::list($data);
		self::getCodeTable()->qnotes->iwhs->deleteResponse();
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
		return self::pw('config')->twig->render('code-tables/min/iwhm/list.twig', ['manager' => $codeTable, 'codes' => $codes]);
	}

	protected static function renderListForPrinting(WireData $data, PropelModelPager $codes) {
		$codeTable = static::getCodeTable();
		return self::pw('config')->twig->render('code-tables/min/iwhm/list-print.twig', ['manager' => $codeTable, 'codes' => $codes]);
	}

	protected static function renderCode(WireData $data, Code $code) {
		$iwhm = static::getCodeTable();
		return self::pw('config')->twig->render('code-tables/min/iwhm/edit/display.twig', ['iwhm' => $iwhm, 'warehouse' => $code]);
	}
}