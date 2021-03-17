<?php namespace Controllers\Min\Itm;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\Itm as ItmModel;
// Validators
use Dplus\CodeValidators\Min as MinValidator;
// Mvc Controllers
use Mvc\Controllers\AbstractController;

class ItmFunction extends AbstractController {
	private static $minvalidator;

	protected static function validateItemid($data) {
		$data = self::sanitizeParametersShort($data, ['itemID|text']);
		$wire = self::pw();
		$validate = self::getMinValidator();

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

		if (static::validateUserPermission() === false) {
			$page   = self::pw('page');
			$config = self::pw('config');
			if (isset($data->action) === false) {
				$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => "You don't have access to this function", 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Permission: ITM $page->name"]);
			}
			return false;
		}
		return true;
	}

	protected static function breadCrumbs() {
		return self::pw('config')->twig->render('items/itm/bread-crumbs.twig');
	}

	/**
	 * Return Itm
	 * @return ItmModel
	 */
	protected static function getItm() {
		return self::pw('modules')->get('Itm');
	}

	/**
	 * Return Min Validator
	 * @return MinValidator
	 */
	protected static function getMinValidator() {
		if (empty(self::$minvalidator)) {
			self::$minvalidator = new MinValidator();
		}
		return self::$minvalidator;
	}
}
