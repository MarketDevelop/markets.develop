<?php
/**
 * $Id: AliasableBehavior.php 3 2013-08-30 05:44:51Z h.ushito@nicosys.jp $
 *
 * @copyright (c) 2013 Nicosys Co. Ltd.
 */

App::uses('ModelBehavior', 'Model');

/**
 * order や virtualFields 等に自動的にモデルを参照しているエイリアスを補う。
 * 複雑な式を指定する場合は、式中に主モデル名を記述するとその部分を alias に展開する。
 */
class AliasableBehavior extends ModelBehavior {

	// 設定のデフォルト値
	static private $_DEFAULTS = array(
	);

	// ビヘイビアにモデルを関連づけ
	public function setup(Model $model, $config = array()) {

		// 設定を保存
		$this->settings[$model->alias] = $config + self::$_DEFAULTS;

		// $order
		if (isset($model->order)) {

			if (!is_array($model->order)) {

				// 単一式

				// $order = 'field DESC';
				$model->order = $this->_expand($model, $model->order);

			} else {

				// 配列

				foreach ($model->order as $key => $value) {

					if (is_numeric($key)) {

						// $order = array(
						// 	'field DESC',
						// 	'field2 DESC',
						// );
						$model->order[$key] = $this->_expand($model, $model->order[$key]);

					} else {

						// $order = array(
						// 	'field' => 'DESC',
						// 	'field2 DESC',
						// );
						unset($model->order[$key]);
						$model->order[$this->_expand($model, $key)] = $value;

					}

				}

			}

		}

		if (!empty($model->virtualFields)) {
			foreach ($model->virtualFields as $field => $expression) {
				$model->virtualFields[$field] = $this->_expand($model, $expression);
			}
		}

		// 保持
		$this->settings[$model->alias] = $config;

	}

	// ビヘイビアからモデルを解除
	public function cleanup(Model $model) {
		throw new Exception('Cannot detach Aliasable behavior.');
		// unset($this->settings[$model->alias]);
	}

	private function _expand(Model $model, $expression) {

		// 小文字から始まるフィールドだけの単純式と思われる場合、エイリアスを修飾子として補う
		if (preg_match('/^[a-z][a-z0-9_]*(?: |$)/', $expression)) {
			return "$model->alias.$expression";
		}

		// それ以外の場合
		if ($model->name === $model->alias) {
			return $expression;
		}

		// 主モデル名をエイリアスに置換する
		return preg_replace("/\\b\\Q$model->name\\E\b/", $model->alias, $expression);

	}

}
