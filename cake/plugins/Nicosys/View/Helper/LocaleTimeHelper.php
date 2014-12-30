<?php
/**
 * $Id: LocaleTimeHelper.php 36 2014-09-26 08:18:33Z h.ushito@nicosys.jp $
 *
 * @copyright (c) 2013 Nicosys Co. Ltd.
 */

App::uses('TimeHelper', 'View/Helper');

/**
 * アクセスユーザーのロケールに基づいて書式を行う TimeHelper。
 */
class LocaleTimeHelper extends TimeHelper {

	/**
	 * 短い時間表記を返す。
	 * @param unknown $date
	 * @param string $default
	 * @param string $timezone
	 */
	public function shortTime($date, $default = false, $timezone = null) {
		return $this->formatDate($date, __d('time', 'g:i A'), $default, $timezone);
	}

	/**
	 * 長い時間表記を返す。
	 * @param unknown $date
	 * @param string $default
	 * @param string $timezone
	 * @return string
	 */
	public function longTime($date, $default = false, $timezone = null) {
		return $this->formatDate($date, __d('time', 'g:i:s A'), $default, $timezone);
	}

	/**
	 * 短い日付表記を返す。
	 * @param unknown $date
	 * @param string $default
	 * @param string $timezone
	 * @return string
	 */
	public function shortDate($date, $default = false, $timezone = null) {
		return $this->formatDate($date, __d('time', 'n/j/Y'), $default, $timezone);
	}

	/**
	 * 長い日付表記を返す。
	 * @param unknown $date
	 * @param string $default
	 * @param string $timezone
	 * @return string
	 */
	public function longDate($date, $default = false, $timezone = null) {
		return $this->formatDate($date, __d('time', 'l, M d, Y'), $default, $timezone);
	}

	/**
	 * 短い日時表記を返す。
	 * @param unknown $date
	 * @param string $default
	 * @param string $timezone
	 * @return string
	 */
	public function shortDateTime($date, $default = false, $timezone = null) {
		return $this->formatDate($date, __d('time', 'n/j/Y g:i:s A'), $default, $timezone);
	}

	/**
	 * 長い日時表記を返す。
	 * @param unknown $date
	 * @param string $default
	 * @param string $timezone
	 * @return string
	 */
	public function longDateTime($date, $default = false, $timezone = null) {
		return $this->formatDate($date, __d('time', 'l, M d, Y, g:i:s A'), $default, $timezone);
	}

	/**
	 * date 関数で書式化する。
	 * @param unknown $date
	 * @param string $default
	 * @param string $timezone
	 * @return string
	 */
	public function formatDate($date, $format, $default = false, $timezone = null) {
		$timestamp = $this->fromString($date, $timezone);
		if ($timestamp === false && $default !== false) {
			return $default;
		}
		return date($format, $timestamp);
	}

}
