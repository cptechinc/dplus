<?php namespace Controllers\Ajax\Json;
// Dplus Document Managment
use Dplus\DocManagement\Finders;
use Dplus\DocManagement\Folders;
use Dplus\DocManagement\Copier;
// Mvc Controllers
use Mvc\Controllers\AbstractController;

class Mdm extends AbstractController {
	public static function test() {
		return 'test';
	}

	public static function copyFile($data) {
		$fields = ['folder|text', 'file|text'];
		self::sanitizeParametersShort($data, $fields);

		$docm = new Finders\Finder();
		if ($docm->exists($data->folder, $data->file) === false) {
			return false;
		}
		$file = $docm->getDocumentByFilename($data->folder, $data->file);
		$folder = Folders::getInstance()->folder($file->folder);
		$copier = Copier::getInstance();
		$copier->useDocVwrDirectory();

		if ($copier->isInDirectory($file->filename)) {
			return true;
		}
		return $copier->copyFile($folder->directory, $file->filename);
	}
}
