<?php namespace Controllers\Min\Itm;

use Mvc\Controllers\AbstractController;

use ProcessWire\Page, ProcessWire\Itm as ItmModel;

use Dplus\CodeValidators\Min as MinValidator;

class ItmFunction extends AbstractController {
	protected static function validateItemid($data) {
		$data = self::sanitizeParametersShort($data, ['itemID|text']);
		$wire = self::pw();
		$validate = new MinValidator();

		if ($validate->itemid($data->itemID) === false) {
			$wire->wire('session')->redirect($wire->wire('page')->itmURL($data->itemID), $http301 = false);
		}
		return true;
	}

	protected static function validateUserPermission() {
		$wire = self::pw();
		$user = $wire->wire('user');
		$itmp = $wire->wire('modules')->get('Itmp');
		$page = $wire->wire('page');
		if ($user->has_function('itm') === false) {
			return false;
		}
		return $itmp->is_user_allowed_template($user, $page->pw_template);
	}

	protected static function validateItemidAndPermission($data) {
		self::validateItemid($data);

		if (self::validateUserPermission() === false) {
			$page = self::pw('page');
			$config = $page->wire('config');
			$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => "You don't have access to this function", 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Permission: ITM $page->name"]);
			return false;
		}
		return true;
	}

	/**
	 * Return Itm
	 * @return ItmModel
	 */
	protected static function getItm() {
		return self::pw('modules')->get('Itm');
	}
}
