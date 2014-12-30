<?php
/**
 * $Id: BoolToIntegerBehavior.php 25 2014-04-24 10:15:49Z h.ushito@nicosys.jp $
 *
 * @copyright (c) 2013 Nicosys Co. Ltd.
 */

App::uses('ModelBehavior', 'Model');

/**
 * CakePHP のヘルパーには boolean に対する考慮が甘いものが多く、false と null を区別できなかったり、false が notEmpty で引っかかったりする。
 * このビヘイビアを有効にすると、$data に boolean 型があれば数値 1, 0 に変換する。
 *
 * find, validate という設定に true を指定するとそれぞれ検索時・検証時に有効になる。
 * 過去の互換性を考慮して、find の既定値は true、validate の既定値は false となっている。
 */
class BoolToIntegerBehavior extends ModelBehavior {

	private static $DEFAULTS = array(
		'find'     => true,
		'validate' => false,
	);

	// 初期化
	public function setup(Model $model, $config = array()) {
		$this->settings[$model->alias] = $config + self::$DEFAULTS;
	}

	// 検索後のコールバック
	public function afterFind(Model $model, $results, $primary = false) {

		if (!isset($this->settings[$model->alias])) {
			return $results;
		}
		$settings = $this->settings[$model->alias];

		if ($settings['find'] !== false) {

			// 念のため配列かどうか調べる
			if (!is_array($results)) {
				return $results;
			}

			// bool -> int
			// アソシエーションも辿る
			array_walk_recursive($results, function (&$_) {
				if (is_bool($_)) {
					$_ = (int)$_;
				}
			});

		}

		return $results;

	}

	public function beforeValidate(Model $model, $options = array()) {

		if (!isset($this->settings[$model->alias])) {
			return true;
		}
		$settings = $this->settings[$model->alias];

		if ($settings['validate'] !== false) {

			if (!isset($model->data[$model->alias])) {
				return true;
			}

			// bool -> int
			// 自身のみ
			array_walk($model->data[$model->alias], function (&$_) {
				if (is_bool($_)) {
					$_ = (int)$_;
				}
			});

		}

		return true;

	}

}
