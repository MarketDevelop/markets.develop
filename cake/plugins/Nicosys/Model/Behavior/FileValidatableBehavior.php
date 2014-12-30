<?php
/**
 * $Id: FileValidatableBehavior.php 18 2014-03-03 04:10:57Z h.ushito@nicosys.jp $
 *
 * @copyright (c) 2013 Nicosys Co. Ltd.
 */

/**
 * ファイルアップロード用の検証ルールが使えるようになる。
 *
 * 		validateFile:			ファイルが必須かどうか検証する。引数は true/false。
 * 		validateFileExtension:	ファイルの拡張子を検証する。引数は受け入れる拡張子の配列。拡張子は小文字で指定する。拡張子にドットは含まない。
 * 		validateFileSize:		ファイルサイズが指定以下かどうか検証する。引数は最大ファイルサイズ。
 *
 */
class FileValidatableBehavior extends ModelBehavior {

	//----------------------------------------------------------------------
	// 拡張バリデーター
	//----------------------------------------------------------------------

	// アップロード自体が成功しているかどうか
	public function validateFile(Model $model, array $data, $required) {
		$upload = current($data);

		// $required = false の場合、ファイルがアップロードされていなくても OK とする
		if (!$required && (isset($upload['error']) && $upload['error'] == UPLOAD_ERR_NO_FILE)) {
			return true;
		}

		if ((isset($upload['error']) && $upload['error'] == UPLOAD_ERR_OK)
				|| (!empty($upload['tmp_name']) && $upload['tmp_name'] != 'none')) {
			return is_uploaded_file($upload['tmp_name']);
		} else {
			return false;
		}

	}

	// アップロード項目の拡張子がマッチするか
	public function validateFileExtension(Model $model, array $data, $extensions) {
		$upload = current($data);

		// ファイルがアップロードされていなくても OK とする
		if ((isset($upload['error']) && $upload['error'] == UPLOAD_ERR_NO_FILE) ) {
			return true;
		}

		$filename = $upload['name'];
		$extension = pathinfo($filename, PATHINFO_EXTENSION);

		// 拡張子確認
		return in_array(strtolower($extension), (array)$extensions);

	}

	// アップロード項目のサイズが一定値以内か
	public function validateFileSize(Model $model, array $data, $maxSize) {
		$upload = current($data);

		// ファイルがアップロードされていなくても OK とする
		if ((isset($upload['error']) && $upload['error'] == UPLOAD_ERR_NO_FILE) ) {
			return true;
		}

		$fileSize = $upload['size'];

		// ファイルサイズチェック
		return (int)$fileSize <= $maxSize;
	}

}

