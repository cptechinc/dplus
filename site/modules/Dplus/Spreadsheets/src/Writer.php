<?php namespace Dplus\Spreadsheets;
// PhpSpreadsheet Library
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as WriterXlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
// ProcessWire
use ProcessWire\WireData;

/**
 * Writer
 * Base Class for Writing Spreadsheets
 *
 * @property string $directory  Directory to Write file to
 * @property string $filename   File Name
 * @property string $fileprefix Prefix to Filename
 */
abstract class Writer extends WireData {
	const EXTENSION = 'xlsx';

	public function __construct() {
		$this->directory = $this->wire('config')->directory_webdocs;

		$this->filename   = 'spreadsheet';
		$this->fileprefix = session_id();
	}

	/**
	 * Return Spreadsheet File Writer
	 * @return BaseWriter
	 */
	abstract protected function getWriter(Spreadsheet $spreadsheet);

	/**
	 * Return Filepath for file
	 * @return string
	 */
	public function getFilepath() {
		return $this->directory.$this->fileprefix.'-'.$this->filename.'.'.static::EXTENSION;
	}

	/**
	 * Writes Spreadsheet to File
	 * @param  Spreadsheet $spreadsheet Spreadsheet
	 * @return bool
	 */
	public function write(Spreadsheet $spreadsheet) {
		$writer  = $this->getWriter($spreadsheet);
		$success = $writer->save($this->getFilepath());
		return true;
	}

	/**
	 * Returns Spreadsheet Alignment Code
	 * @param  string $justify  Code given e.g. r, right
	 * @return string
	 */
	public static function getAlignmentCode($justify) {
		switch (substr($justify, 0, 1)) {
			case 'r':
				return Alignment::HORIZONTAL_RIGHT;
				break;
			case 'c':
				return Alignment::HORIZONTAL_CENTER;
				break;
			default:
				return Alignment::HORIZONTAL_LEFT;
				break;
		}
	}

	/**
	 * Set Columns to be autowidth
	 * @param Worksheet $sheet       Sheet
	 * @param int       $columncount Number of Columns to iterate
	 */
	public static function setColumnsAutowidth(Worksheet $sheet, int $columncount) {
		for ($i = 0; $i < ($columncount); $i++) {
			$index = Coordinate::stringFromColumnIndex($i);
			$sheet->getColumnDimension($index)->setAutoSize(true);
		}
	}
}
