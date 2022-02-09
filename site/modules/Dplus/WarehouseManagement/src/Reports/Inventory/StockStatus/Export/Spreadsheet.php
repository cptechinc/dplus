<?php namespace Dplus\Wm\Reports\Inventory\StockStatus\Export;
// PhpSpreadsheet Library
use PhpOffice\PhpSpreadsheet\Spreadsheet as PhpSpreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style as SpreadsheetStyles;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
// ProcessWire
use ProcessWire\WireData;
// Dplus Spreadsheets
use Dplus\Spreadsheets as DplusSS;

/**
 * Spreadsheet
 * Exports Stock Status Report into Spreadsheet
 *
 * @property array          $reportData   Data from Report
 * @property array          $columns      Columns ['header' => [], 'lot' => []]
 * @property PhpSpreadsheet $spreadsheet
 */
class Spreadsheet extends WireData {
	protected $reportData;
	protected $columns;
	protected $spreadsheet;

	const STYLES_HEADER = [
		'font' => [
			'bold' => true,
			'size' => 14
		],
		'borders' => [
			'bottom' => [
				'borderStyle' => SpreadsheetStyles\Border::BORDER_THICK,
			],
		],
	];

	const STYLES_ITEM = [
		'font' => [
			'bold' => true,
			'size' => 12
		],
		'borders' => [
			'top' => [
				'borderStyle' => SpreadsheetStyles\Border::BORDER_THIN,
			],
		],
		'fill' => [
			'fillType' => SpreadsheetStyles\Fill::FILL_SOLID,
			'startColor' => [
				'rgb' => 'E6E6EA',
			],
			'endColor' => [
				'rgb' => 'E6E6EA',
			],
		],
	];

	public function __construct() {
		$this->spreadsheet = new PhpSpreadsheet();
	}

	/**
	 * Set Report Data
	 * @param array $data
	 */
	public function setReportData(array $data) {
		$this->reportData = $data;
	}

	/**
	 * Set Columns
	 * @param array $columns ['header' => [], 'lot' => []]
	 */
	public function setColumns(array $columns) {
		$this->columns = $columns;
	}

	/**
	 * Export Spreadsheet
	 * @return bool
	 */
	public function export() {
		$this->writeHeader();
		$this->writeBody();
		$writer = new DplusSS\Writers\Xlsx();
		$writer->filename = 'stock-status';
		return $writer->write($this->spreadsheet);
	}

	/**
	 * Return Filepath for file
	 * @return string
	 */
	public function getExportFilePath() {
		$writer = new DplusSS\Writers\Xlsx();
		$writer->filename = 'stock-status';
		return $writer->getFilepath();
	}

	/**
	 * Return Index for the next available blank row
	 * @param  Worksheet $sheet
	 * @return int
	 */
	private function getNewRowIndex(Worksheet $sheet = null) {
		$sheet = $sheet ? $sheet : $this->spreadsheet->getActiveSheet();
		return $sheet->getHighestRow() + 1;
	}

	/**
	 * Write Header Row to spreadsheet
	 * @return void
	 */
	private function writeHeader() {
		$sheet = $this->spreadsheet->getActiveSheet();
		$colCount = count(array_merge($this->columns['item'], $this->columns['lot']));
		DplusSS\Writer::setColumnsAutowidth($sheet, $colCount);

		$row = 1;
		$i = 0;
		foreach ($this->columns['item'] as $col => $colData) {
			$cell = $sheet->getCellByColumnAndRow($i + 1, $row);
			$cell->getStyle()->applyFromArray(static::STYLES_HEADER);
			$cell->getStyle()->getAlignment()->setHorizontal(DplusSS\Writer::getAlignmentCode($colData['justify']));
			$cell->setValue($colData['label']);
			$i++;
		}

		$i = sizeof($this->columns['item']);

		foreach ($this->columns['lot'] as $col => $colData) {
			$cell = $sheet->getCellByColumnAndRow($i + 1, $row);
			$cell->getStyle()->applyFromArray(static::STYLES_HEADER);
			$cell->getStyle()->getAlignment()->setHorizontal(DplusSS\Writer::getAlignmentCode($colData['justify']));
			$cell->setValue($colData['label']);
			$i++;
		}
	}

	/**
	 * Write Body to Spreadsheet
	 * NOTE: loops through items, and their lots
	 * @return void
	 */
	protected function writeBody() {
		$sheet = $this->spreadsheet->getActiveSheet();
		foreach ($this->reportData as $item) {
			$this->writeItemRow($item);
			$this->writeItemLotsRows($item['lots']);
		}
	}

	/**
	 * Write Item Row to Sheet
	 * @param  array  $item
	 * @return void
	 */
	private function writeItemRow(array $item) {
		$sheet = $this->spreadsheet->getActiveSheet();
		$row   = $this->getNewRowIndex($sheet);
		$i = 0;
		$sanitizer = $this->wire('sanitizer');

		foreach ($this->columns['item'] as $col => $colData) {
			$cell = $sheet->getCellByColumnAndRow($i + 1, $row);
			$cell->getStyle()->applyFromArray(static::STYLES_ITEM);
			$cell->getStyle()->getAlignment()->setHorizontal(DplusSS\Writer::getAlignmentCode($colData['justify']));
			$cell->setValueExplicit($sanitizer->text($item[$col]), DataType::TYPE_STRING);
			$i++;
		}

		$cols = [
			'lotcount' => [
				'justify' => 'right',
				'value'   => $sanitizer->text($item['totals']['lotcount']) . ' Lots',
			],
			'lotref' => ['justify' => 'left', 'value'   => ''],
		];

		if (array_key_exists('expiredate', $this->columns['lot'])) {
			$cols['expiredate'] = ['justify' => 'left', 'value' => ''];
		}

		if (array_key_exists('age', $this->columns['lot'])) {
			$cols['age'] = ['justify' => 'right', 'value' => $sanitizer->text($item['totals']['avgage'])];
		}

		$cols['qty'] = [
			'justify' => 'right',
			'value'   => $sanitizer->text($item['totals']['qty']),
		];

		$index = $i;

		foreach ($cols as $col) {
			$cell = $sheet->getCellByColumnAndRow($index + 1, $row);
			$cell->getStyle()->applyFromArray(static::STYLES_ITEM);
			$cell->getStyle()->getAlignment()->setHorizontal(DplusSS\Writer::getAlignmentCode($col['justify']));
			$cell->setValueExplicit($col['value'], DataType::TYPE_STRING);
			$index++;
		}
	}

	/**
	 * Write Item's Lots Rows to spreadsheet
	 * @param  array $lots
	 * @param  int   $row   Row Number to start
	 * @return void
	 */
	private function writeItemLotsRows($lots) {
		$sanitizer = $this->wire('sanitizer');
		$sheet = $this->spreadsheet->getActiveSheet();
		$row   = $this->getNewRowIndex($sheet);

		foreach ($lots as $lot) {
			$i = 0;

			// Set Item Columns as blanks
			foreach ($this->columns['item'] as $col => $colData) {
				$cell = $sheet->getCellByColumnAndRow($i + 1, $row);
				$cell->getStyle()->getAlignment()->setHorizontal(DplusSS\Writer::getAlignmentCode($colData['justify']));
				$cell->setValue('');
				$i++;
			}

			$i = sizeof($this->columns['item']);

			foreach ($this->columns['lot'] as $col => $colData) {
				$cell = $sheet->getCellByColumnAndRow($i + 1, $row);
				$cell->getStyle()->getAlignment()->setHorizontal(DplusSS\Writer::getAlignmentCode($colData['justify']));
				$value = $col == 'expiredate' ? date('m/d/Y', strtotime($lot[$col])) : $sanitizer->text($lot[$col]);
				$cell->setValueExplicit($value, DataType::TYPE_STRING);
				$i++;
			}
			$row++;
		}
	}
}
