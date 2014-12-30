<?php
/**
 * $Id: Assume.php 3 2013-08-30 05:44:51Z h.ushito@nicosys.jp $
 *
 * @copyright (c) 2013 Nicosys Co. Ltd.
 */

/**
 * ユーザー入力に対する、形式確認を行うためのユーティリティクラス。
 */
class Assume {

	/**
	 * 引数が整数表現であればその値を返し、違えば false を返す。
	 * @param any $value 整数値または整数表現の文字列。
	 * @return int|bool 整数値、または false。
	 */
	static public function int($value) {
		return filter_var($value, FILTER_VALIDATE_INT);
	}

}
