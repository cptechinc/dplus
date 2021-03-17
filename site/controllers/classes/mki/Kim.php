<?php namespace Controllers\Mki;
// Dplus Model
use Invkit;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\Kim as KimModel;
// Mvc Controllers
use Mvc\Controllers\AbstractController;

class Kim extends AbstractController {
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
		$fields = ['action' => ['sanitizer' => 'text'], 'kitID' => ['sanitizer' => 'text']];
		$data = self::sanitizeParameters($data, $fields);

		if ($data->action) {
			$kim = self::pw('modules')->get('Kim');
			$kim->process_input(self::pw('input'));
		}
		self::pw('session')->redirect(self::pw('page')->kitURL($data->kitID), $http301);
	}

	public static function kit($data) {
		$wire = self::pw();
		$config = self::pw('config');
		$page = self::pw('page');
		$kim = $wire->modules->get('Kim');
		$kim->init_configs();
		$kit = $kim->new_get_kit($data->kitID);

		$page->body .= self::lockKit($page, $kim, $kit);
		$page->body .= $config->twig->render('mki/kim/kit/page.twig', ['kim' => $kim, 'kit' => $kit]);
		$page->js   .= $config->twig->render('mki/kim/kit/js.twig', ['kim' => $kim]);
		return $page->body;
	}

	public static function kitComponent($data) {
		$wire = self::pw();
		$config = self::pw('config');
		$page = self::pw('page');
		$kim = $wire->modules->get('Kim');
		$kim->init_configs();
		$kit = $kim->new_get_kit($data->kitID);

		$page->body .= self::lockKit($page, $kim, $kit);
		$component = $kim->component->new_get_component($data->kitID, $data->component);
		$page->headline = $data->component == 'new' ? "Kit Master: $data->kitID" : "Kit Master: $data->kitID - $data->component";
		$page->body .= $config->twig->render('mki/kim/kit/component/page.twig', ['kim' => $kim, 'kit' => $kit, 'component' => $component]);
		$page->js   .= $config->twig->render('mki/kim/kit/component/js.twig', ['kim' => $kim,]);
		return $page->body;
	}

	private static function lockKit(Page $page, KimModel $kim, InvKit $kit) {
		$config = $page->wire('config');

		if (!$kit->isNew()) {
			$page->headline = "Kit Master: $kit->itemid";
			if (!$kim->lockrecord($kit->itemid)) {
				$msg = "Kit $kit->itemid is being locked by " . $kim->recordlocker->get_locked_user($kit->itemid);
				$page->body .= $config->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "Kit $kit->itemid is locked", 'iconclass' => 'fa fa-lock fa-2x', 'message' => $msg]);
			}
		}

		if ($kit->isNew()) {
			$page->body .= $config->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "Kit $kit->itemid does not exist", 'iconclass' => 'fa fa-warning fa-2x', 'message' => "You will be able to create this kit"]);
		}
		return $page->body;
	}

	public static function listKits($data) {
		$fields = ['q' => ['sanitizer' => 'text']];
		$data = self::sanitizeParameters($data, $fields);
		$wire = self::pw();
		$config = $wire->wire('config');
		$page = $wire->wire('page');
		$kim = $wire->modules->get('Kim');
		$filter = $wire->wire('modules')->get('FilterKim');
		$filter->init_query();
		if ($data->q) {
			$page->headline = "KIM: Searching for '$data->q'";
			$filter->search($data->q);
		}
		$kits = $filter->query->paginate(self::pw('input')->pageNum, $wire->wire('session')->display);
		$page->body .= $config->twig->render('mki/kim/search-form.twig', ['q' => $data->q]);
		$page->body .= $config->twig->render('mki/kim/page.twig', ['kim' => $kim, 'kits' => $kits]);
		$page->js   .= $config->twig->render('mki/kim/list.js.twig', ['kim' => $kim]);
		return $page->body;
	}
}
