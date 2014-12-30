<?php
/**
 * $Id: NamedFieldBehavior.php 16 2014-02-25 08:57:18Z h.ushito@nicosys.jp $
 *
 * @copyright (c) 2012 Nicosys Co. Ltd.
 */

App::uses('ModelBehavior', 'Model');

// Version: 2.2: AutoValidation ビヘイビアから独立。

// フィールドのコメントよりフィールド名を生成するビヘイビア

/*

	$actsAs で読み込むことで、フィールドに設定したコメントからフィールドの項目名を抽出する。

	# ビヘイビアの設定

		$actsAs でロードする際に、細かい設定を追加することができる。
		設定は連想配列で指定し、以下のようなキーをとる。
		設定は必要なキーや値だけを定義することが可能で、空でも構わない。

		'fields'	フィールド毎に個別に設定を上書きする場合に指定する連想配列。
					フィールド名をキーとし、以下の連想配列を値として持つ。
						'name'		項目名の自動判別を使わず、項目名を直接指定する。国際化は行われる。

		'extract'	フィールドコメントから項目名を抽出する際、この値に指定した文字列がコメントにあれば、一致以降を除外する。
					コメントにフィールド名だけでなく追加情報がある場合に使用する。デフォルトは : 文字。
					null か空文字列を指定すると、コメント全体が使用される。
					false を指定すると、コメントからの項目名抽出を行わない。

		'nameDomain'	この値を指定すると、フィールド名を国際化する際に、指定したドメインが使用される。
						デフォルトは null で標準のドメイン（default.po） となる。

	# サンプル

		// 無設定でロード
		public $actsAs = array(
				'AutoValidation',
		);

		// 細かく設定してロード
		public $actsAs = array(
				'AutoValidation' = array(
						'delayed'  => true, // 直前に反映
						'excludes' => 'leave_me_alone', // 検証しないフィールドを指定
						'messages' => array( // メッセージをオーバーライド
								'required'  => '%sは必須です。',
								'notEmpty'  => '%sに入力してください。',
								'maxLength' => '%sは %d 文字以内で入力してください。',
								'integer'   => '%sは整数を入力してください。',
						),
						'fields' => array( // フィールドの個別設定
								'description' => array(
										'notNull' => true, // 必須扱い
								),
								'code' => array(
										'name' => 'Application Code',
								),
						),
						'extract' => '\t', // コメントのタブ文字以降を無視
				),
		);

*/

class NamedFieldBehavior extends ModelBehavior {

	// 設定のデフォルト値
	static private $_DEFAULTS = array(
			'fields' => array(),
			'extract' => ':',
			'nameDomain' => null,
	);

	// ビヘイビアにモデルを関連づけ
	public function setup(Model $model, $config = array()) {

		// 設定に既定値をマージ
		$config += self::$_DEFAULTS;

		// 保存
		$this->settings[$model->alias] = $config;

	}

	/**
	 * フィールドの項目名を取得
	 * @param unknown $fieldName
	 */
	public function getFieldName(Model $model, $fieldName) {

		// モデル用の設定をロード
		$config = &$this->settings[$model->alias];

		// フィールドの明示指定があれば読み込む
		$overrides = array();
		if (isset($config['fields'][$fieldName])) {
			$overrides = $config['fields'][$fieldName];
			if (is_string($overrides)) {
				$overrides = array('name' => $overrides);
			}
		}

		// 項目名として、カラム名を読みやすくした物を既定として用意（field_name => Field Name）
		// 但し、末尾が _id の場合はそれを取り除いてから名前を生成する
		if (substr($fieldName, -3) === '_id') {
			$name = Inflector::humanize(substr($fieldName, 0, -3));
		} else {
			$name = Inflector::humanize($fieldName);
		}

		if (isset($overrides['name'])) {

			// 明示指定があればそれを使う
			$name = $overrides['name'];

		} else {

			// 抽出機能がオンなら
			if ($config['extract'] !== false) {

				// モデルのスキーマを参照
				$schema = $model->schema();

				// フィールド定義があり
				if (isset($schema[$fieldName])) {

					// カラムにコメントがある場合
					if (isset($schema[$fieldName]['comment'])) {

						if (empty($config['extract'])) {
							// 区切り文字が空か null ならコメント全体を使う
							$name = $schema[$fieldName]['comment'];
						} else {
							// 指定があれば、先頭から指定文字までの間のテキストをフィールド名として使う
							list($name) = explode($config['extract'], $schema[$fieldName]['comment'], 2);
						}

					}

				}

			}

		}

		// 最後に国際化
		return __d($config['nameDomain'], $name);

	}

}
