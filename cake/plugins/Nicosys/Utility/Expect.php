<?php
/**
 * $Id: Expect.php 3 2013-08-30 05:44:51Z h.ushito@nicosys.jp $
 *
 * @copyright (c) 2013 Nicosys Co. Ltd.
 */

App::uses('Assume', 'Nicosys.Utility');

/**
 * ユーザー入力に対する、形式確認を行うためのユーティリティクラス。
 */
class Expect {

	/**
	 * 引数が整数表現でなければ BadRequestException を投げる。
	 * @param any $value 整数値または整数表現の文字列。
	 * @throws BadRequestException
	 * @return int 整数値。
	 */
	static public function int($value) {
		if (($value = Assume::int($value)) === false) {
			throw new BadRequestException();
		}
		return $value;
	}

}
