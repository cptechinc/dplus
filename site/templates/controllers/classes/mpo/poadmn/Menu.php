<?php namespace Controllers\Mpo\Poadmn;
// Purl Library
use Purl\Url as Purl;
// Controllers
use Controllers\Templates\AbstractMenuController;

/**
 * Poadmn\Menu
 * 
 * Class for Rendering the Poadmn Menu
 */
class Menu extends AbstractMenuController {
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
	URLs
============================================================= */
	public static function _url() {
		return self::pw('pages')->get('pw_template=poadmn')->url;
	}

	public static function cnfmUrl() {
		$url = new Purl(self::url());
		$url->path->add('cnfm');
		return $url->getUrl();
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
