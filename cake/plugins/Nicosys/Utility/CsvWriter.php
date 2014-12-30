<?php
/**
 * $Id: CsvWriter.php 3 2013-08-30 05:44:51Z h.ushito@nicosys.jp $
 *
 * @copyright (c) 2012 Nicosys Co. Ltd.
 */

//--+----1----+----2----+----3----+----4----+----5----+----6----+----7----+---

/**
 * エンコーディングを考慮した CSV 書き込みクラス
 * RFC 4180 にエンコーディング拡張を施したもの。
 */
class CsvWriter {

// 	private static $defaults = array(
// 			'encoding'               => 'cp932', // any encoding
// 			'row-delimiter'          => "\r\n", // CR/LF/CRLF
// 			'column-delimiter'       => ',',
// 			'column-enclosure'       => '"',
// 			'escape'                 => '"',
// 			'value-break'            => "\n", // CR/LF/CRLF, empty to strip, null to as-is
// 			'always-enclosed'        => false, // true to force enclosing
// 			'always-enclosed-header' => null,  // can overrides for header
// 	);

	private $filename;
	private $stream;
	private $filter;
	private $row;

	public function __construct($filenameOrHandle, $append = false, $output = 'cp932', $input = 'UTF-8') {
		// parent::__construct();

		if (is_resource($filenameOrHandle)) {
			$filename = null;
			$stream = $filenameOrHandle;
		} else {
			$filename = $filenameOrHandle;
			$stream = fopen($filenameOrHandle, $append ? 'a' : 'w') or die('ファイルを書き込み用に開けませんでした。');
		}

		$filter = null;

		if (strcasecmp($output, $input)) {

			// ストリームに変換フィルタを噛ます
			$filter = stream_filter_prepend($stream, "convert.iconv.$input/$output", STREAM_FILTER_WRITE); // 適用は書き方向のみ
			if (!$filter) {
				if ($filename !== null) {
					fclose($stream);
				}
				die("エンコーディング $input/$output 用のエンコードフィルタが見つかりません。 iconv が利用可能かどうか確認して下さい。");
			}
		}

		$this->filename = $filename;
		$this->stream = $stream;
		$this->filter = $filter;
		$this->row = 0;

	}

	public function __destruct() {
		// parent::__destruct();

		if ($this->stream) {

			// バッファをクリア
			fflush($this->stream);

			// フィルタを外す
			if ($this->filter !== null) {
				stream_filter_remove($this->filter);
				$this->filter = null;
			}

			// 自分で開いた場合、ストリームを閉じる
			if ($this->filename !== null) {
				fclose($this->stream);
			}

			$this->stream = null;

		}

	}

	public function getFilename() {
		return $this->filename;
	}

	public function getRowNumber() {
		return $this->row;
	}

	public function write(/* var_args or array */) {
		$fields = func_get_args();
		if (count($fields) == 1 && is_array($fields[0])) {
			$fields = $fields[0];
		}
		// false to 0
		foreach ($fields as &$_) {
			if ($_ === false) {
				$_ = 0;
			}
		}
		$this->_fputcsv($fields);
		++$this->row;
	}

	private function _fputcsv(array $fields) {
		fputcsv($this->stream, $fields);
	}

}
