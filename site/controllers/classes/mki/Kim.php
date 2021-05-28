<?php namespace Controllers\Mki;
// Purl URI Library
use Purl\Url as Purl;
// Propel ORM Ljbrary
use Propel\Runtime\Util\PropelModelPager;
// Dplus Model
use Invkit;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\Kim as KimCRUD;
// Dplus Filters
use Dplus\Filters\Mki\Kim as FilterKim;
// Mvc Controllers
use Mvc\Controllers\AbstractController;

class Kim extends AbstractController {
	private static $kim;

	public static function index($data) {
		$fields = [
			'kitID'     => ['sanitizer' => 'text'],
			'component' => ['sanitizer' => 'text'],
			'q'         => ['sanitizer' => 'text'],
			'action'    => ['sanitizer' => 'text']
		];
		$data = self::sanitizeParameters($data, $fields);
		$page = self::pw('page');
		$page->show_breadcrumbs = false;

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}

		if (empty($data->kitID) === false) {
			if (empty($data->component) === false) {
				return self::kitComponent($data);
			}
			return self::kit($data);
		}
		return self::listKits($data);
	}

	public static function handleCRUD($data) {
		self::sanitizeParametersShort($data, ['action|text', 'kitID|text', 'component|text']);

		if ($data->action) {
			$kim = self::pw('modules')->get('Kim');
			$kim->process_input(self::pw('input'));
		}
		self::pw('session')->redirect(self::pw('page')->kitURL($data->kitID), $http301);
	}

	public static function kit($data) {
		$kim    = self::getKim();
		$kim->init_configs();
		$kit = $kim->getCreateKit($data->kitID);
		$page           = self::pw('page');
		$page->headline = "Kit Master: $kit->itemid";
		$page->js       .= self::pw('config')->twig->render('mki/kim/kit/js.twig', ['kim' => $kim]);
		$html = self::kitDisplay($data, $kit);
		return $html;
	}

	private static function kitDisplay($data, Invkit $kit) {
		$config = self::pw('config');
		$kim    = self::getKim();
		$kim->init_configs();

		$html = '';
		$html .= self::kimHeader();
		$html .= self::lockKit($kit);
		if ($kit->isNew()) {
			$html .= $config->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "Kit $kit->itemid does not exist", 'iconclass' => 'fa fa-warning fa-2x', 'message' => "You will be able to create this kit"]);
		}
		$html .= $config->twig->render('mki/kim/kit/page.twig', ['kim' => $kim, 'kit' => $kit]);
		return $html;
	}

	public static function kitComponent($data) {
		self::sanitizeParametersShort($data, ['action|text', 'kitID|text', 'component|text']);
		$kim    = self::getKim();
		$kim->init_configs();
		$page = self::pw('page');
		$page->headline = $data->component == 'new' ? "Kit Master: $data->kitID" : "Kit Master: $data->kitID - $data->component";
		$page->js       .= self::pw('config')->twig->render('mki/kim/kit/component/js.twig', ['kim' => $kim]);
		$html = self::kitComponentDisplay($data);
		return $html;
	}

	private static function kitComponentDisplay($data) {
		$kim    = self::getKim();
		$kim->init_configs();
		$kit = $kim->kit($data->kitID);
		$component = $kim->component->getCreateComponent($data->kitID, $data->component);

		$html = '';
		$html .= self::kimHeader();
		$html .= self::lockKit($kit);
		$html .= self::pw('config')->twig->render('mki/kim/kit/component/page.twig', ['kim' => $kim, 'kit' => $kit, 'component' => $component]);
		return $html;
	}

	public static function lockKit(InvKit $kit) {
		$config = self::pw('config');
		$kim    = self::getKim();
		$html = '';

		if (!$kit->isNew()) {
			if (!$kim->lockrecord($kit->itemid)) {
				$msg = "Kit $kit->itemid is being locked by " . $kim->recordlocker->get_locked_user($kit->itemid);
				$html .= $config->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "Kit $kit->itemid is locked", 'iconclass' => 'fa fa-lock fa-2x', 'message' => $msg]);
			}
		}
		return $html;
	}

	public static function listKits($data) {
		self::sanitizeParametersShort($data, ['q|text']);
		$kim    = self::getKim();
		$filter = new FilterKim();
		$filter->init();
		if ($data->q) {
			$page->headline = "KIM: Searching for '$data->q'";
			$filter->search($data->q);
		}
		$kits = $filter->query->paginate(self::pw('input')->pageNum, self::pw('session')->display);
		self::pw('page')->js   .= self::pw('config')->twig->render('mki/kim/list.js.twig', ['kim' => $kim]);
		$html = self::listKitsDisplay($data, $kits);
		return $html;
	}

	private static function listKitsDisplay($data, $kits) {
		$config = self::pw('config');
		$kim    = self::getKim();

		$html = '';
		$html .= self::kimHeader();
		$html .= $config->twig->render('mki/kim/search-form.twig', ['q' => $data->q]);
		$html .= $config->twig->render('mki/kim/page.twig', ['kim' => $kim, 'kits' => $kits]);
		return $html;
	}

	private static function kimHeader() {
		$session = self::pw('session');
		$config  = self::pw('config');
		$html = '';

		$html .= $config->twig->render('mki/kim/bread-crumbs.twig', ['input' => self::pw('input')]);

		if ($session->getFor('response','kim')) {
			$html .= $config->twig->render('items/itm/response-alert.twig', ['response' => $session->getFor('response','kim')]);
		}
		return $html;
	}

	public static function getKim() {
		if (empty(self::$kim)) {
			self::$kim = self::pw('modules')->get('Kim');
		}
		return self::$kim;
	}
}
