<?php namespace Controllers\Mpo\Poadmn;
// Purl Library
use Purl\Url as Purl;

class Menu extends AbstractController  {
	const DPLUSPERMISSION = 'poadmn';
	const TITLE = 'Administration';
	const SUBFUNCTIONS = [
		'cnfm' => [
			'name'       => 'cnfm',
			'permission' => Cnfm::DPLUSPERMISSION,
			'title'      => Cnfm::TITLE,
			'summary'    => Cnfm::SUMMARY
		]
	];

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		self::sanitizeParametersShort($data, []);
		if (self::validateUserPermission() === false) {
			return self::renderUserNotPermittedAlert();
		}
		return self::menu($data);
	}

	private static function menu($data) {
		$functions = [];
		foreach (self::SUBFUNCTIONS as $key => $function) {
			if (empty($function['permission']) || self::pw('user')->hasPermissionCode($function['permission'])) {
				$functions[$key] = $function;
			}
		}
		return self::renderMenu($data, $functions);
	}

/* =============================================================
	URLs
============================================================= */
	public static function url() {
		return self::pw('pages')->get('pw_template=poadmn')->url;
	}

	public static function menuUrl() {
		return self::url();
	}
	
	public static function subfunctionUrl($key) {
		$url = new Purl(self::url());
		if (array_key_exists($key, self::SUBFUNCTIONS)) {
			$url->path->add($key);
		}
		return $url->getUrl();
	}

	public static function cnfmUrl() {
		$url = new Purl(self::url());
		$url->path->add('cnfm');
		return $url->getUrl();
	}

/* =============================================================
	Render HTML
============================================================= */
	private static function renderMenu($data, array $functions) {
		$html = '';
		$html .= self::pw('config')->twig->render('dplus-menu/bread-crumbs.twig');
		$html .= self::pw('config')->twig->render('dplus-menu/function-menu.twig', ['functions' => $functions]);
		return $html;
	}

/* =============================================================
	Init
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('DpagesMpo');

		$m->addHook('Page(pw_template=poadmn)::subfunctionUrl', function($event) {
			$event->return = self::subfunctionUrl($event->arguments(0));
		});
	}
}
