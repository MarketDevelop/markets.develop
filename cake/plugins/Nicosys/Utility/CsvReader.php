<?php
/**
 * $Id: CsvReader.php 19 2014-03-03 09:38:56Z h.ushito@nicosys.jp $
 *
 * @copyright (c) 2012 Nicosys Co. Ltd.
 */

//--+----1----+----2----+----3----+----4----+----5----+----6----+----7----+---

// エンコーディングを考慮した CSV 読み込みクラス。 foreach 対応（論理行番号をキーとする）
class CsvReader implements Iterator {

	private $filename;
	private $handle;
	private $row;

	private $current; // Iterator 用

	public function __construct($filename, $encoding = 'cp932') {
		// parent::__construct();

		$handle = fopen($filename, 'r') or die('ファイルを読み込み用に開けませんでした。');

		// UTF-8 でない場合
		if (strcasecmp($encoding, 'UTF-8')) {

			// ストリームに cp932 -> UTF-8 の変換フィルタを噛ます
			$filter = stream_filter_prepend($handle, 'convert.iconv.' . $encoding . '/utf-8//IGNORE', STREAM_FILTER_READ);
			if (!$filter) {
				fclose($handle);
				die("エンコーディング $encoding 用のデコードフィルタが見つかりません。 iconv が利用可能かどうか確認して下さい。");
			}
		}

		$this->filename = $filename;
		$this->handle = $handle;
		$this->row = 0;

	}

	public function __destruct() {
		// parent::__destruct();

		if ($this->handle) {
			fclose($this->handle);
			$this->handle = null;
		}

	}

	public function getFilename() {
		return $this->filename;
	}

	public function getRowNumber() {
		return $this->row;
	}

	public function read() {
		$this->current = $fields = fgetcsv($this->handle, 0, ',', '"', '"');
		if ($fields !== false) {
			++$this->row;
		}
		return $fields;
	}


	// Iterator 実装

	public function current() {
		return $this->current;
	}

	public function key() {
		return $this->row; // 論理行番号
	}

	public function next() {
		// advance
		$this->read();
	}

	public function rewind() {
		if ($this->row > 0) {
			fseek($this->handle, 0, SEEK_SET);
			$this->row = 0;
			$this->current = null;
		}
		// advance
		$this->read();
	}

	public function valid() {
		return $this->current !== false;
	}

}

//--+----1----+----2----+----3----+----4----+----5----+----6----+----7----+---
