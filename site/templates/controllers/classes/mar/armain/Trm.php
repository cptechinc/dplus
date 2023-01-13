<?php namespace Controllers\Mar\Armain;
// Purl URI manipulation Library
use Purl\Url as Purl;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Code;
// ProcessWire
use ProcessWire\WireData;
// Dplus Filters
use Dplus\Filters;
// Dplus CRUD
use Dplus\Codes;


class Trm extends AbstractCodeTableController {
	const DPLUSPERMISSION = 'trm';
	const TITLE      = 'Customer Terms Code';
	const SUMMARY    = 'View / Edit Customer Terms Codes';
	const SHOWONPAGE = 10;
	const USE_EDIT_MODAL  = false;
	const USE_EDIT_PAGE   = true;

	public static function _url() {
		return Menu::trmUrl();
	}

	public static function getCodeFilter() {
		return new Filters\Mar\ArTermsCode();
	}

	public static function getCodeTable() {
		return Codes\Mar\Trm::instance();
	}

/* =============================================================
	Indexes
============================================================= */
	public static function handleCRUD(WireData $data) {
		if (self::validateUserPermission() === false) {
			return self::pw('session')->redirect(self::url(), $http301 = false);
		}
		$fields = ['code|string', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		$url  = self::url();
		$trm = self::getCodeTable();

		if ($data->action) {
			$trm->processInput(self::pw('input'));
					
			if ($data->action != 'delete') {
				// $url = self::codeEditUrl($data->code);
				$url  = self::url($data->code);
			}
		}
		self::pw('session')->redirect($url, $http301 = false);
	}

	protected static function code(WireData $data) {
		$trm = self::getCodeTable();
		$code = $trm->getOrCreate($data->code);
		self::pw('page')->show_breadcrumbs = false;

		if ($code->isNew() === false) {
			self::pw('page')->headline = "CTM: Editing $data->code";
			$trm->lockrecord($code);
		}
		self::initHooks();
		self::addVarsToJsVars($data);
		self::pw('config')->scripts->append(self::getFileHasher()->getHashUrl('scripts/code-tables/ajax-modal.js'));
		self::appendJs($data);
		$html = self::displayCode($data, $code);
		self::getCodeTable()->deleteResponse();
		return $html;
	}

	protected static function list(WireData $data) {
		self::pw('page')->show_breadcrumbs = false;
		$trm = self::getCodeTable();
		$trm->recordlocker->deleteLock();
		$html = parent::list($data);
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
	URLS
============================================================= */


/* =============================================================
	Render HTML / JS
============================================================= */
	protected static function renderList(WireData $data, PropelModelPager $codes) {
		$codeTable = static::getCodeTable();
		return self::pw('config')->twig->render('code-tables/mar/trm/list.twig', ['manager' => $codeTable, 'codes' => $codes]);
	}

	protected static function renderListForPrinting(WireData $data, PropelModelPager $codes) {
		$codeTable = static::getCodeTable();
		return self::pw('config')->twig->render('code-tables/mar/trm/list-print.twig', ['manager' => $codeTable, 'codes' => $codes]);
	}

	protected static function renderModal(WireData $data) {
		return '';
	}

	protected static function renderBreadcrumbs(WireData $data) {
		return self::pw('config')->twig->render('code-tables/mar/trm/bread-crumbs.twig');
	}

	protected static function renderCode(WireData $data, Code $code) {
		$codeTable = static::getCodeTable();
		return self::pw('config')->twig->render('code-tables/mar/trm/edit/display.twig', ['manager' => $codeTable, 'code' => $code]);
	}

/* =============================================================
	Supplemental
============================================================= */
	/**
	 * Return CodeTable field Config Data
	 * NOTE: Keep public for classes that are a copy of another, in a different menu
	 * @param  WireData $data
	 * @return array
	 */
	public static function getCodeTableFieldConfigData(WireData $data) {
		$config = parent::getCodeTableFieldConfigData($data);
		$table = static::getCodeTable();
		$config['type'] = ['default' => $table->fieldAttribute('type', 'default')];
		$config['method'] = ['default' => $table->fieldAttribute('method', 'default'), 'std' => 'S', 'eom' => 'E'];
		$config['std_disc_date'] = ['regex' => $table->fieldAttribute('std_disc_date', 'regex')];
		$config['std_due_date']  = ['regex' => $table->fieldAttribute('std_due_date', 'regex')];
		$config['eom_disc_percent']  = ['precision' => $table->fieldAttribute('eom_disc_percent', 'precision')];
		$config['eom_thru_day']  = ['max' => $table->fieldAttribute('eom_thru_day', 'max')];
		return $config;
	}

	protected static function codeTableJsVarsArray(WireData $data) {
		/** @var  Codes\Mar\Trm */
		$table = static::getCodeTable();
		$js = parent::codeTableJsVarsArray($data);
		$js['config']['methods'] = [
			'std' => [
				'value' => $table::METHOD_STD,
				'splitCount' => $table::NBR_SPLITS_METHOD_S,
				'typeInputName' => 'typeS'
			],
			'eom' => [
				'value' => $table::METHOD_EOM,
				'splitCount' => $table::NBR_SPLITS_METHOD_E,
				'typeInputName' => 'typeE'
			],
		];
		$js['config']['types'] = [
			'creditcard' => [
				'value' => $table::TYPE_CREDITCARD,
			],
		];
		return $js;
	}

/* =============================================================
	Hooks
============================================================= */
	public static function initHooks() {
		parent::initHooks();

		$m = self::pw('modules')->get('Dpages');

		// $m->addHook('Page(pw_template=armain)::trmNotesDeleteUrl', function($event) {
		// 	$event->return = self::trmNotesDeleteUrl($event->arguments(0));
		// });
	}
}
