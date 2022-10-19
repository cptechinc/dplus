<?php namespace Controllers\Ajax\Lookup;
// Dplus Filters
use Dplus\Filters;

class Msa extends Lookup {
	/**
	 * Search Login Groups
	 * @param  object $data
	 *                     q   Search Term
	 * @return void
	 */
	public static function loginGroups($data) {
		self::sanitizeParametersShort($data, self::FIELDS_LOOKUP);
		self::pw('page')->headline = "Login Groups";;
		$filter = new Filters\Msa\SysLoginGroup();
		$filter->init();
		return self::filterResults($filter, $data);
	}

	/**
	 * Search Login Roles
	 * @param  object $data
	 *                     q   Search Term
	 * @return void
	 */
	public static function loginRoles($data) {
		self::sanitizeParametersShort($data, self::FIELDS_LOOKUP);
		self::pw('page')->headline = "Login Roles";;
		$filter = new Filters\Msa\SysLoginRole();
		$filter->init();
		return self::filterResults($filter, $data);
	}

	/**
	 * Search Printers
	 * @param  object $data
	 *                     q   Search Term
	 * @return void
	 */
	public static function printers($data) {
		self::sanitizeParametersShort($data, self::FIELDS_LOOKUP);
		self::pw('page')->headline = "Printers";;
		$filter = new Filters\Misc\Printer();
		$filter->init();
		return self::filterResults($filter, $data);
	}

	/**
	 * Search Sysop Codes
	 * @param  object $data
	 *                     q      Search Term
	 *                     system Sysop System
	 * @return void
	 */
	public static function sysopCodes($data) {
		self::sanitizeParametersShort($data, self::FIELDS_LOOKUP);
		self::sanitizeParametersShort($data, ['system|text']);
		$page = self::pw('page');
		$filter = new Filters\Msa\MsaSysopCode();
		$page->headline = "System Optional Codes";
		if ($data->system) {
			$filter->system($data->system);
		}
		if ($data->q) {
			$filter->search($data->q);
			$page->headline = "Searching for $data->q";
		}
		return self::filterResults($filter, $data);
	}

	/**
	 * Search Sysop Options
	 * @param  object $data
	 *                     q      Search Term
	 *                     system Sysop System
	 *                     sysop  Sysop Optional Code
	 * @return void
	 */
	public static function sysopOptions($data) {
		self::sanitizeParametersShort($data, self::FIELDS_LOOKUP);
		self::sanitizeParametersShort($data, ['system|text', 'sysop|text']);

		$filter = new Filters\Msa\SysopOptionalCode();
		$page = self::pw('page');
		$page->headline = "Optional Code ($data->sysop) Options";
		if ($data->system) {
			$filter->system($data->system);
		}
		if ($data->sysop) {
			$filter->query->filterBySysop($data->sysop);
		}
		if ($data->q) {
			$filter->search($data->q);
			$page->headline = "Searching for $data->q";
		}
		return self::filterResults($filter, $data);
	}

	/**
	 * Search Users
	 * @param  object $data
	 *                     q   Search Term
	 * @return void
	 */
	public static function users($data) {
		self::sanitizeParametersShort($data, self::FIELDS_LOOKUP);
		self::pw('page')->headline = "Users";
		$filter = $filter = new Filters\Msa\DplusUser();
		return self::filterResults($filter, $data);
	}
}
