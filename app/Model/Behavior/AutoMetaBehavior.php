<?php
/**
 * $Id: AutoMetaBehavior.php 8 2014-09-24 10:40:04Z h.ushito@nicosys.jp $
 *
 * @copyright (c) 2014 Nicosys Co. Ltd.
 */

App::uses('AuthComponent', 'Controller/Component');

class AutoMetaBehavior extends ModelBehavior {

	// 設定のデフォルト値
	static private $_DEFAULTS = [];

	// ビヘイビアにモデルを関連づけ
	public function setup(Model $model, $config = []) {
		// 設定を保存
		$this->settings[$model->alias] = $config + self::$_DEFAULTS;
	}

	// 保存前のコールバック
	public function beforeSave(Model $model, $options = []) {

		// 1. 主モデルの作成者、更新者フィールドを自動で埋める

		foreach ($model->data as $name => &$data) {

			// このモデルのデータ
			if ($name === $model->alias) {

				// save 処理が追加か更新かを調べる
				$create = !$model->exists();

				// ログイン中か？
				$isLoggedIn = AuthComponent::user('id') !== null;

				// ログインユーザー名を用意
				$loginUser = $isLoggedIn ? AuthComponent::user('email') : 'system';

				// フィールド追加
				$data['modifier'] = $loginUser;
				if ($create) {
					$data['creator'] = $loginUser;
				}

				// ホワイトリスト（保存対象）に追加
				$this->_addToWhitelist($model, 'modifier');
				if ($create) {
					$this->_addToWhitelist($model, 'creator');
				}

			}

		}

		return true;

	}

}
