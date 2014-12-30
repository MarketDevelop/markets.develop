<?php
/**
 * Application model for CakePHP.
 *
 * This file is application-wide model file. You can put all
 * application-wide model-related methods here.
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Model
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

App::uses('Model', 'Model');

/**
 * Application model for Cake.
 *
 * Add your application-wide methods in the class below, your models
 * will inherit them.
 *
 * @package       app.Model
 */
class AppModel extends Model {

	//----------------------------------------------------------------------
	// 動作設定
	//----------------------------------------------------------------------

	// recursive が必要な場合は明示させる
	public $recursive = -1;

	// 全モデルで必須の性質
	public $actsAs = [

		// 柔軟にデータ範囲を限定できるように
		'Containable',

		// listBy や hasAnyOf などの拡張
		'Nicosys.Magics',

		// 自動バリデータを有効に
		'Nicosys.AutoValidation', // => array('extract' => ':'),

		// エイリアスを使用している場合でも、order や virtualField の指定を正しく
		'Nicosys.Aliasable',

		// JOIN とそうでない find の結果の違いを吸収
		'Nicosys.JoinResultWorkaround',

		// HABTM モデルに追加フィールドがある場合の解決策
		'Nicosys.HabtmFieldsWorkaround',

		// メタデータの自動更新
		'AutoMeta',

	];

	//----------------------------------------------------------------------
	// コールバック
	//----------------------------------------------------------------------

//	// 検索後のコールバック
//	public function afterFind($results, $primary = false) {
//
//		$model = $this;
//
//		// 念のため配列かどうか調べる
//		if (!is_array($results)) {
//			return $results;
//		}
//
//		// afterFind に渡される結果は常に配列
//		foreach ($results as $index => $data) {
//
//			// 主モデル
//			if (isset($data[$model->alias]) && is_array($data[$model->alias])) {
//				$results[$index][$model->alias] = $this->_formatFields($model, $data[$model->alias], false);
//			}
//
//			// 関連モデル（ビヘイビアの afterFind は、関連モデルに対しては呼ばれないためここで処理）
//
//			foreach (array_merge($model->belongsTo, $model->hasOne) as $key => $assoc) {
//				if (isset($data[$key]) && is_array($data[$key])) {
//					$results[$index][$key] = $this->_formatFields($model->{$key}, $data[$key], true);
//				}
//			}
//			foreach ($model->hasMany as $key => $assoc) {
//				if (isset($data[$key]) && is_array($data[$key])) {
//					foreach ($data[$key] as $i => $d) {
//						$results[$index][$key][$i] = $this->_formatFields($model->{$key}, $d, true);
//					}
//				}
//			}
//
//		}
//
//		return $results;
//
//	}
//
//	// float 型である文字列を真の数値に変換する
//	// date, time, datetime 型である文字列を SQL 書式から日本語書式にフォーマットする
//	private function _formatFields(Model $model, $fields, $processAssoc) {
//
//		// ビヘイビアが有効か？
//		if (!isset($this->settings[$model->alias])) {
//			return $fields;
//		}
//
//		// 念のため配列かどうか調べる
//		if (!is_array($fields)) {
//			return $fields;
//		}
//
//		// 主モデル
//		foreach ($model->schema() as $name => $info) {
//
//			// float 型で管理されてるカラムの値を、文字列でなく float 値にする
//			if ($info['type'] === 'float') {
//				if (!empty($fields[$name])) {
//					$fields[$name] = (float) $fields[$name];
//				}
//			}
//
//			// date 系の型で管理されているカラムの値は、SQL から日本語形式にフォーマットする
//			if ($info['type'] === 'datetime' || $info['type'] === 'timestamp' || $info['type'] === 'date') {
//				if (!empty($fields[$name])) {
//					$fields[$name] = str_replace('-', '/', $fields[$name]);
//				}
//			}
//
//		}
//
////		// 関連モデル
////		if ($processAssoc) {
////			foreach (array_merge($model->belongsTo, $model->hasOne) as $key => $assoc) {
////				if (isset($fields[$key]) && is_array($fields[$key])) {
////					$fields[$key] = $this->_formatFields($model->{$key}, $fields[$key], $processAssoc);
////				}
////			}
////			foreach ($model->hasMany as $key => $assoc) {
////				if (isset($fields[$key]) && is_array($fields[$key])) {
////					foreach ($fields[$key] as $i => $d) {
////						$fields[$key][$i] = $this->_formatFields($model->{$key}, $d, $processAssoc);
////					}
////				}
////			}
////		}
//
//		return $fields;
//
//	}

}
