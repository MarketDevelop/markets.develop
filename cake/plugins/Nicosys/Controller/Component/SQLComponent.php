<?php
/**
 * $Id: SQLComponent.php 3 2013-08-30 05:44:51Z h.ushito@nicosys.jp $
 *
 * @copyright (c) 2013 Nicosys Co. Ltd.
 */

App::uses('Component', 'Component');

class SQLComponent extends Component {

	/**
	 * SQL の LIKE 演算のメタ文字をクォートする。
	 * @param unknown_type $value
	 */
	public function quotelike($value) {

		// \ => \\, % => \%, _ => \_
		return str_replace(
				array(  '\\',   '_',   '%'),
				array('\\\\', '\\_', '\\%'), $value);

	}

	/**
	 * 日付データを SQL 形式にする。
	 * @param unknown_type $expression
	 */
	public function toDate($expression = null) {
		if ($expression instanceof DateTime) {
			return $expression->format('Y-m-d');
		}
		if ($expression === null) {
			$expression = time();
		}
		if (is_int($expression) || is_numeric($expression)) {
			$expression = (int)($expression);
		} else {
			$expression = strtotime($expression);
		}
		return date('Y-m-d', $expression);
	}

	/**
	 * 日時データを SQL 形式にする。
	 * @param unknown_type $expression
	 */
	public function toDateTime($expression = null) {
		if ($expression instanceof DateTime) {
			return $expression->format('Y-m-d H:i:s');
		}
		if ($expression === null) {
			$expression = time();
		}
		if (is_int($expression) || is_numeric($expression)) {
			$expression = (int)($expression);
		} else {
			$expression = strtotime($expression);
		}
		return date('Y-m-d H:i:s', $expression);
	}

	/**
	 * 時刻データを SQL 形式にする。
	 * @param unknown_type $expression
	 */
	public function toTime($expression = null) {
		if ($expression instanceof DateTime) {
			return $expression->format('H:i:s');
		}
		if ($expression === null) {
			$expression = time();
		}
		if (is_int($expression) || is_numeric($expression)) {
			$expression = (int)($expression);
		} else {
			$expression = strtotime($expression);
		}
		return date('H:i:s', $expression);
	}

}
