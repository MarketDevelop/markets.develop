<?php
/**
 * $Id: HabtmFieldsWorkaroundBehavior.php 12 2014-02-23 16:41:00Z h.ushito@nicosys.jp $
 *
 * @copyright (c) 2013 Nicosys Co. Ltd.
 */

App::uses('ModelBehavior', 'Model');

/*
 * 通常、Form ヘルパー等の機能により HABTM のデータはキーの値だけの簡易構造として送信される。
 *     [Tag] => Array(
 *         [Tag] => Array(
 *             [0] => <tag_id>
 *             [1] => <tag_id>
 *             [2] => <tag_id>
 *         )
 *     )
 *
 * この場合、CakePHP は HTBTM 関連モデルによる検証をバイパスし
 * 直接 INSERT で挿入する特別なショートカット処理を行う。
 * これは HABTM の関連テーブルにも追加情報のフィールドがあると具合が悪いので
 * 簡易構造（$id） から通常構造（array('tag_id' => $id)） に組み替える。
 *
 *     [Tag] => Array(
 *         [Tag] => Array(
 *             [0] => Array(
 *                 tag_id => <tag_id>
 *             )
 *             [1] => Array(
 *                 tag_id => <tag_id>
 *             )
 *             [2] => Array(
 *                 tag_id => <tag_id>
 *             )
 *         )
 *     )
 *
 * これによりショートカットではなく HABTM 関連モデルを使用した通常通りの保存処理が行われるようになる。
 * また、この場合 HABTM 関連モデルでも beforeSave が呼び出されるようになる。
 */
class HabtmFieldsWorkaroundBehavior extends ModelBehavior {

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

	// データを保存する直前のコールバック
	public function beforeSave(Model $model, $options = array()) {

		foreach ($model->data as $name => &$data) {

			// HABTM モデルのデータ？
			if (isset($model->hasAndBelongsToMany[$name]) && isset($data[$name]) && is_array($data[$name])) {

				// 組み替え
				$associationForeignKey = $model->hasAndBelongsToMany[$name]['associationForeignKey'];
				foreach ($data[$name] as $index => &$id) {
					if (!is_array($id)) {
						$id = array($associationForeignKey => $id);
					}
				}

			}

		}

		return true;

	}

}
