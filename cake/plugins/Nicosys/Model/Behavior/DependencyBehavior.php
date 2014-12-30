<?php
/**
 * $Id: DependencyBehavior.php 23 2014-03-27 12:26:22Z h.ushito@nicosys.jp $
 *
 * @copyright (c) 2013 Nicosys Co. Ltd.
 */

App::uses('ModelBehavior', 'Model');

// 他のフィールドに依存しているフィールドの値を、callable を呼び出して計算して格納するビヘイビア。

/*
	# ビヘイビアの設定

		設定は計算対象のフィールド名をキーに持つ連想配列で、各要素は呼び出し方法を表す連想配列である。
		呼び出しを表す連想配列に指定可能な内容は以下の通り。

		'function'	呼び出す callable 疑似型の値を指定する。関数名を表す文字列または配列、クロージャー等。
					通常の名前に加え、'+' のように PHP の各種演算子を指定することもできる。
					演算子を指定する場合、単項演算子は 1 つ、二項演算子は 2 つ、三項演算子 '?:' は 3 つの引数を取るが
					例外的に '+' と '*' に関しては任意の数の引数を渡すことができる。

		'arguments'	function に渡す引数の配列。

		'sources'	引数に依存フィールドの値を渡す際に指定する連想配列。
					キーは依存フィールドの名前、値は arguments 配列内のパスを表す Set::insert 互換の文字列となる。

	# サンプル

		public $actsAs = array(

				'Dependency' => array(

						// phone_for_search を自動計算 。preg_replace('/[^0-9#]/', '', $phone)
						'phone_for_search' => array(
								'function' => 'preg_replace',
								'arguments' => array(
										'/[^0-9#]/',
										'',
								),
								'sources' => array(
									'phone' => '2', // phone フィールドの値を第 3 引数に
								)
						),

						// total を自動計算 。$price * $quantity （演算子を使う場合）
						'total' => array(
								'function' => '*',
								'sources' => array(
									'price'    => '0', // price フィールドの値を第 1 引数に
									'quantity' => '1', // quantity フィールドの値を第 2 引数に
								)
						),

						// total2 を自動計算。 $price * $quantity （array_product を使う場合）
						'total2' => array(
								'function' => 'array_product',
								'sources' => array(
									'price'    => '0.0', // price フィールドの値を第 1 引数の要素 [0] に
									'quantity' => '0.1', // quantity フィールドの値を第 1 引数の要素 [1] に
								)
						),

				),

		);


*/
class DependencyBehavior extends ModelBehavior {

	static private $_OPERATORS = null; // 後で初期化

	// 設定のデフォルト値
	static private $_DEFAULTS = array(
	);

	public function __construct() {

		// 組み込み関数の初期化
		if (self::$_OPERATORS === null) {
			self::$_OPERATORS = array(
					'+'   => function ()       { return array_sum(func_get_args()); },
					'-'   => function ($x, $y) { return $x - $y; },
					'*'   => function ()       { return array_product(func_get_args()); },
					'/'   => function ($x, $y) { return $x / $y; },
					'%'   => function ($x, $y) { return $x % $y; },
					'&'   => function ($x, $y) { return $x & $y; },
					'|'   => function ($x, $y) { return $x | $y; },
					'^'   => function ($x, $y) { return $x ^ $y; },
					'~'   => function ($x)     { return ~$x; },
					'<<'  => function ($x, $y) { return $x << $y; },
					'>>'  => function ($x, $y) { return $x >> $y; },
					'=='  => function ($x, $y) { return $x == $y; },
					'===' => function ($x, $y) { return $x === $y; },
					'!='  => function ($x, $y) { return $x != $y; },
					'<>'  => function ($x, $y) { return $x <> $y; },
					'!==' => function ($x, $y) { return $x !== $y; },
					'<'   => function ($x, $y) { return $x < $y; },
					'>'   => function ($x, $y) { return $x > $y; },
					'<='  => function ($x, $y) { return $x <= $y; },
					'>='  => function ($x, $y) { return $x >= $y; },
					'++'  => function ($x)     { return ++$x; },
					'--'  => function ($x)     { return --$x; },
					'and' => function ($x, $y) { return $x and $y; },
					'or'  => function ($x, $y) { return $x or $y; },
					'xor' => function ($x, $y) { return $x xor $y; },
					'!'   => function ($x)     { return !$x; },
					'&&'  => function ($x, $y) { return $x && $y; },
					'||'  => function ($x, $y) { return $x || $y; },
					'.'   => function ($x, $y) { return $x . $y; },
					'?:'  => function ($x, $y, $z) { return $x ? $y : $z; },
			);
		}

	}

	// ビヘイビアにモデルを関連づけ
	public function setup(Model $model, $config = array()) {

		// 設定を保存
		$this->settings[$model->alias] = $config + self::$_DEFAULTS;

	}

	// ビヘイビアからモデルを解除
	public function cleanup(Model $model) {
		unset($this->settings[$model->alias]);
	}

	// 検証前のコールバック
	public function beforeValidate(Model $model, $options = array()) {

		if (!isset($model->data[$model->alias])) {
			return true;
		}

		$settings = $this->settings[$model->alias];
		$values = &$model->data[$model->alias];

		// 計算可能な状態であれば、検証から除外する
		foreach ($settings as $field => $options) {
			if ($this->_isComputable($values, $options)) {
				$this->_suppressRequired($model, $field);
			}
		}

		return true;
	}

	// 保存前のコールバック
	public function beforeSave(Model $model, $options = array()) {

		if (!isset($model->data[$model->alias])) {
			return true;
		}

		$settings = $this->settings[$model->alias];
		$values = &$model->data[$model->alias];

		// 各フィールドの値を計算する
		foreach ($settings as $field => $options) {
			$this->_compute($model, $values, $field, $options);
		}

		return true;

	}

	private function _isComputable(array &$values, $options) {

		// 参照値が全て揃っているか
		$sources = $options['sources'];

		foreach (array_keys($sources) as $source) {

			// 参照値がなければ計算できない
			if (!array_key_exists($source, $values)) {
				return false;
			}

		}

		return true;

	}

	private function _compute(Model $model, array &$values, $field, $options) {

		// 引数を用意
		$arguments = array();
		if (isset($options['arguments'])) {
			$arguments = $options['arguments'];
		}

		// 参照値を集める
		$sources = $options['sources'];

		foreach ($sources as $source => $path) {

			// 参照値がなければ計算できない
			if (!array_key_exists($source, $values)) {
				return;
			}

			$arguments = Set::insert($arguments, $path, $values[$source]);

		}

		// 関数への参照を用意
		$callable = $options['function'];
		if (is_string($callable) && isset(self::$_OPERATORS[$callable])) {
			$callable = self::$_OPERATORS[$callable];
		}

		// 計算！
		$values[$field] = call_user_func_array($callable, $arguments);

		// フィールドの値を保存対象に
		$this->_addToWhitelist($model, $field);

	}

	// 指定したフィールドに対して必須（$data にキーが存在する必要がある）ルールを無効にする
	private function _suppressRequired(Model $model, $field) {

		// $validate がない
		if (!isset($model->validate)) {
			return false;
		}

		$validate = $model->validate;

		// $validate が空
		if (!is_array($validate) || empty($validate)) {
			return false;
		}

		// フィールドにルールがない
		if (!isset($validate[$field])) {
			return false;
		}

		// 単純ルールの場合は必須ルールもない
		if (!is_array($validate[$field])) {
			return false;
		}

		// 単一ルールの場合
		if (isset($validate[$field]['rule'])) {

			// 必須なら
			if (isset($validate[$field]['required']) && $validate[$field]['required'] === true) {
				// 緩く
				$model->validate[$field]['required'] = false;
				return true;
			}

			return false;
		}

		// 複合ルールの場合

		$suppressed = false;
		foreach ($validate[$field] as $name => $rule) {

			// 必須なら
			if (isset($rule['required']) && $rule['required'] === true) {
				// 緩く
				$model->validate[$field][$name]['required'] = false;
				$suppressed = true;
			}

		}

		return $suppressed;

	}

}
