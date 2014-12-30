<?php
/**
 * $Id: JoinResultWorkaroundBehavior.php 12 2014-02-23 16:41:00Z h.ushito@nicosys.jp $
 *
 * @copyright (c) 2013 Nicosys Co. Ltd.
 */

App::uses('ModelBehavior', 'Model');

/*
 * CakePHP の効率化により関連モデルが JOIN されて一気に取得される場合、
 * belongsTo の外部キーが NULL 可であっても、外部モデルが含まれてしまうため、これを除外する
 *
 * 例: A belongsTo B で A.b_id が NULL 可の場合
 * A.b_id が NULL のレコードを取得すると以下のような結果が返る。
 *
 * array(
 *     'A' => array(
 *         'id' => 1,
 *         'name' => 'an A',
 *         'b_id' => NULL,
 *     ),
 *     'B' => array(
 *         'id' => NULL,
 *         'name' => NULL,
 *     ),
 * )
 *
 * これを以下のように修正する
 *
 * array(
 *     'A' => array(
 *         'id' => 1,
 *         'name' => 'an A',
 *         'b_id' => NULL,
 *     ),
 * )
 *
 */
class JoinResultWorkaroundBehavior extends ModelBehavior {

	// 設定のデフォルト値
	static private $_DEFAULTS = array(
	);

	// ビヘイビアにモデルを関連づけ
	public function setup(Model $model, $config = array()) {

		// 設定を保存
		$this->settings[$model->alias] = $config + self::$_DEFAULTS;

	}

	// ビヘイビアからモデルを解除
	public function cleanup(Model $model) {
		unset($this->settings[$model->alias]);
	}

	// 検索後のコールバック
	public function afterFind(Model $model, $results, $primary = false) {

		// 念のため配列かどうか調べる
		if (!is_array($results)) {
			return $results;
		}

		// afterFind に渡される結果は常に配列
		foreach ($results as $index => $data) {

			// 各結果に対して
			foreach ($model->belongsTo as $key => $assoc) {

				// 外部参照モデルのデータが存在し
				if (isset($data[$key])) {

					$pk = $model->{$key}->primaryKey;

					// モデルのキーが結果に含まれているが NULL の場合
					if (array_key_exists($pk, $data[$key]) && !isset($data[$key][$pk])) {

						// お呼びでない
						unset($results[$index][$key]);
						continue;

					}

					// 参照側の外部キーが結果に含まれているが NULL の場合
					if (isset($data[$model->alias])
							&& array_key_exists($assoc['foreignKey'], $data[$model->alias])
							&& !isset($data[$model->alias][$assoc['foreignKey']])
					) {

						// お呼びでない
						unset($results[$index][$key]);
						continue;

					}

				}

			}

		}

		return $results;
	}

}
