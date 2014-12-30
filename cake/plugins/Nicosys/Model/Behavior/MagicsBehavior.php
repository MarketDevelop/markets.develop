<?php
/**
 * $Id: MagicsBehavior.php 3 2013-08-30 05:44:51Z h.ushito@nicosys.jp $
 *
 * @copyright (c) 2013 Nicosys Co. Ltd.
 */

App::uses('ModelBehavior', 'Model');

/**
 * findBy, findAllBy, listBy, countBy, hasAnyOf マジックメソッドを利用可能にする。
 *
 * findBy と findAllBy は CakePHP コアが本来持つマジック機能に加え、引数リストの最後尾に options 追加引数を渡すことが可能。
 * options を渡した場合は、options の conditions にマジック条件がマージされる。
 * listBy と countBy は findBy と findAllBy と同様、find('list') と find('count') に対応し、追加引数も渡すことができる。
 *
 * hasAnyOf は hasAny のマジック版で
 * 引数リストの最後尾に conditions 追加引数を渡すことが可能。
 * conditions を渡した場合、マジック条件とマージされる。
 */
class MagicsBehavior extends ModelBehavior {

	public $mapMethods = array(
			'/^findBy/' => 'findBy', // may not be called
			'/^findAllBy/' => 'findAllBy', // may not be called
			'/^listBy/' => 'listBy',
			'/^countBy/' => 'countBy',
			'/^hasAnyOf/' => 'hasAnyOf',
	);

	public function findBy(Model $model, $method) {
		$arguments = func_get_args();
		array_splice($arguments, 0, 2); // $model, $method
		$options = $this->_parseFindArguments($model, $method, 6, $arguments);
		return $model->find('first', $options);
	}

	public function findAllBy(Model $model, $method) {
		$arguments = func_get_args();
		array_splice($arguments, 0, 2); // $model, $method
		$options = $this->_parseFindArguments($model, $method, 9, $arguments);
		return $model->find('all', $options);
	}

	public function listBy(Model $model, $method) {
		$arguments = func_get_args();
		array_splice($arguments, 0, 2); // $model, $method
		$options = $this->_parseFindArguments($model, $method, 6, $arguments);
		return $model->find('list', $options);
	}

	public function countBy(Model $model, $method) {
		$arguments = func_get_args();
		array_splice($arguments, 0, 2); // $model, $method
		$options = $this->_parseFindArguments($model, $method, 7, $arguments);
		return $model->find('count', $options);
	}


	public function hasAnyOf(Model $model, $method) {

		$arguments = func_get_args();
		array_splice($arguments, 0, 2); // $model, $method

		$conditions = $this->_parseMagicConditions($model, $method, 8, $arguments);

		// Accept extra 'conditions'
		if (!empty($arguments)) {

			if (!(count($arguments) == 1 && is_array($arguments[0]))) {
				trigger_error("Magic parameter mismatch: $method accepts only one extra 'conditions' argument.", E_USER_ERROR);
			}

			if (!empty($arguments[0])) {
				$conditions[] = $arguments[0];
			}

		}

		return $model->hasAny($conditions);

	}

// 	public function _magic_fieldBy(Model $model, $method) {

// 		$arguments = func_get_args();
// 		array_splice($arguments, 0, 2); // $model, $method

// 		$conditions = $this->_parseMagicConditions($method, 8, $arguments);

// 		// Accept extra $name, $conditions = null, $order = null
// 		if (!empty($arguments)) {

// 			if (!(count($arguments) == 1 && is_array($arguments[0]))) {
// 				trigger_error("Magic parameter mismatch: $method accepts only one extra 'conditions' argument.", E_USER_ERROR);
// 			}

// 			if (!empty($arguments[0])) {
// 				$conditions[] = $arguments[0];
// 			}

// 		}

// 		return $this->hasAny($conditions);


// 	}

	private function _parseFindArguments(Model $model, $method, $offset, array $arguments) {

		$conditions = $this->_parseMagicConditions($model, $method, $offset, $arguments);

		if (empty($arguments)) {
			return array('conditions' => $conditions);
		}

		// Accept extra 'options'
		if (!(count($arguments) == 1 && is_array($arguments[0]))) {
			trigger_error("Magic parameter mismatch: $method accepts only one extra 'options' argument.", E_USER_ERROR);
		}

		$options = $arguments[0];
		$options['conditions'][] = $conditions;

		return $options;

	}

	/**
	 * メソッド名から条件配列を作成
	 * @param Model $model モデル
	 * @param string $method メソッド名
	 * @param int $offset メソッド名の接頭辞（共通部分）の文字数
	 * @param array $arguments メソッドに渡された引数への参照。戻る際にマジック条件値として使われた要素は削除される。
	 * @return array 条件配列
	 */
	private function _parseMagicConditions(Model $model, $method, $offset, array &$arguments) {

		$expression = Inflector::underscore(substr($method, $offset));

		$or = (strpos($expression, '_or_') !== false);
		if ($or) {
			$fields = explode('_or_', $expression);
		} else {
			$fields = explode('_and_', $expression);
		}

		$assumed = count($fields);
		$actual = count($arguments);

		if ($actual < $assumed) {
			trigger_error("Magic parameter mismatch: $method requires at least $assumed parameter(s).", E_USER_ERROR);
		}

		// Build conditions
		$conditions = array();
		foreach ($fields as $field) {
			$conditions[$model->alias . '.' . $field] = array_shift($arguments);
		}

		if ($or) {
			$conditions = array(array('OR' => $conditions));
		} else {
			$conditions = array($conditions);
		}

		return $conditions;
	}


}
