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
 * Igm
 * 
 * Controller for handling CRUD Requests for the Igm Codetable
 */
class Igm extends AbstractCodeTableController {
	const DPLUSPERMISSION = 'igm';
	const TITLE      = 'Inventory Group';
	const SUMMARY    = 'View / Edit Inventory Group Codes';
	const SHOWONPAGE = 10;
	const USE_EDIT_MODAL  = false;
	const USE_EDIT_PAGE   = true;

	public static function _url() {
		return Menu::igmUrl();
	}

	public static function getCodeFilter() {
		return new Filters\Min\InvGroupCode();
	}

	public static function getCodeTable() {
		$table = Codes\Min\Igm::instance();
		$table->initFieldAttributes();
		return $table;
	}

/* =============================================================
	Indexes
============================================================= */
	protected static function code(WireData $data) {
		$igm = self::getCodeTable();
		$invGroup = $igm->getOrCreate($data->code);

		if ($invGroup->isNew() === false) {
			self::pw('page')->headline = "IGM: Editing $data->code";
			$igm->lockrecord($invGroup);
		}
		self::initHooks();
		self::pw('page')->js .= self::pw('config')->twig->render('code-tables/min/igm/edit/.js.twig', ['igm' => $igm]);
		return self::displayCode($data, $invGroup);
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
		return self::pw('config')->twig->render('code-tables/min/igm/list.twig', ['manager' => $codeTable, 'codes' => $codes]);
	}

	protected static function renderCode(WireData $data, Code $code) {
		$igm = static::getCodeTable();
		return self::pw('config')->twig->render('code-tables/min/igm/edit/display.twig', ['igm' => $igm, 'invgroup' => $code]);
	}
}