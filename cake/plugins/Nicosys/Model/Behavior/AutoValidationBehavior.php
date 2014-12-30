<?php
/**
 * $Id: AutoValidationBehavior.php 40 2014-11-21 01:13:59Z h.ushito@nicosys.jp $
 *
 * @copyright (c) 2012 Nicosys Co. Ltd.
 */

App::uses('NamedFieldBehavior', 'Nicosys.Model/Behavior');


// Version: 2.2: フィールドコメントを利用したラベルの検出機能を別クラスに切り離す。
// Version: 2.1: CakePHP 2 系に対応

// データベース型情報とモデルのプロパティより自動的に入力値の検証を追加するビヘイビア

/*

	$actsAs で読み込むことで、フィールドに対してスキーマによる自動的な検証項目を追加する。
	また、フィールドの名前や特定のプロパティの存在によっても、型を類推して検証項目を追加する。

	# 自動的に追加されるルール

		自動的に追加される検証ルールのキーは、以下の 6 種類存在する

			AutoValidationRequired:		フィールドの指定が必須であることを表す検証ルール。
										NOT NULL でかつデフォルト値が設定されていないフィールドに対し、
										'on' => 'create' の条件で自動的に追加される。

			AutoValidationNotEmpty:		フィールドが空ではいけないことを表す検証ルール。
										NOT NULL のフィールドに自動的に追加される。

			AutoValidationType:			フィールド型または名前から類推した型に応じた検証ルール。
										対応しているのは CakePHP スキーマ型のうち、
										integer, float, datetime, timestamp, time, date, boolean の型。
										フィールド名が email という単語で終わる場合 email 用検証が適用される。
										フィールド名が url という単語で終わる場合 url 用検証が適用される。
										フィールド名が ip という単語で終わる場合 ip 用検証が適用される。
										フィールド名が phone, fax という単語で終わる場合 phone 用検証が適用される。
										フィールド名が zip_code, zipcode という単語で終わる場合 zipcode 用検証が適用される。

			AutoValidationSelection:	一覧に該当値が含まれるかどうかを表す検証ルール。
										フィールド名から '_id' を除き、後ろに '_options' をつけた名前の配列がモデルにある場合
										そのキーに含まれる範囲の値が有効となる。

			AutoValidationMaxLength:	文字数の制限を表す検証ルール。
										文字列型でカラム最大長が指定されている場合に自動的に追加される。

			AutoValidationAllowEmpty:	allowEmpty のためのダミールール。
										NOT NULL ではないフィールドで、上記の自動ルールが何もない場合に限り、
										'allowEmpty' => true であるルールが追加される。（後述）

		上記のルールは、既存のルールの「前」に追加されるため、既存ルールに先立って検証が行われる。

		AutoValidationAllowEmpty 以外のルールは 'last' => true を持つので、
		自動ルールに引っかかった場合は、既存のルールの検証には到達しない。

		また、NOT NULL ではないフィールドの自動ルールには、
		最低 1 つの 'allowEmpty' = true ルールが追加されることが保証される。
		つまり、フィールドの値が空の場合には、既存のルールの検証には到達しない。
		この仕組みにより、モデルで既存のルールを定義する際は 'allowEmpty' => true の記述は省略することができる。

	# 特別なフィールド

		データベースのカラム型が不明な場合は、ルールは追加されない。
		また、主キーにはルールが追加されない。

		created, updated, modified は CakePHP による自動更新が行われるため、
		AutoValidationRequired ルールは追加されない。（レコード追加時に値が必須とはされない）

	# 検証ルールの増強

		モデルにおいて以下の整形・検証ルールが使えるようになる。
		これらの検証は、入力値に対して寛容であり、解釈可能であれば全角半角変換などデータの整形も行ってモデルを更新する。

			validateInteger:			整数かどうか検証する
			validateNonNegativeInteger:	0 以上の整数かどうか検証する
			validateNumeric:			数値かどうか検証する。指数表記は受け入れない。
			validateDate:				日付かどうか検証する
			validateTime:				時刻かどうか検証する
			validateDateTime:			日付と時刻を空白で区切った値かどうか検証する
			validateEmail:				メールアドレスかどうか検証する
			validatePhone:				電話番号かどうか検証する
			validateZipCode:			郵便番号かどうか検証する

	# モデルへのメソッドの追加

		モデルにおいて以下のメソッドが使えるようになる

			addValidationRules:			モデルの $validate 変数にルールを追加する
			removeValidationRule:		モデルの $validate 変数からルールを削除する

			applyAutoValidationRules:	ビヘイビアによる自動ルールをその場で $validate に反映する。
										'delayed' => true の場合、処理効率を考慮して beforeValidate コールバックのタイミングで反映されるが、
										自動ルールを参照・変更する必要が生じた場合は、任意のタイミングで呼び出すことが可能。
										自動ルールを反映したことは内部で記録しているため、複数回呼び出しても問題は起こらない。

	# エラーメッセージ

		エラーメッセージは、内部に持つ英語のメッセージが利用される。
		メッセージは __d 関数によって国際化されるため、以下のキーのリソースを定義することで多言語化することが可能である。
		また、設定で独自の指定をすることも可能である。

				msgid "%s is required."
				msgid "%s cannot be empty."  （通常の場合）
				msgid "%s is not selected."  （_options が存在して選択式と自動認識した場合、または $belongsTo の外部キーフィールドの場合）
				msgid "%s must be an integer number."
				msgid "%s must be a number."
				msgid "%s is not a date."
				msgid "%s is not a time."
				msgid "%s is not a date and time."
				msgid "%s is invalid."
				msgid "%s cannot be greater than %d characters."

		メッセージは国際化の後 sprintf によって書式化され、最初の引数には項目名が渡される。

		項目名は通常、フィールド名を Inflector::humanize によって自然表記したものだが、
		フィールドにコメントがある場合は、コメントの先頭から最初のタブ文字までの間を項目名として利用する。
		フィールドのコメントに画面表示用の項目名を入れると、この機能をスムーズに利用できる。
		項目名も __d 関数による国際化処理の後、sprintf に渡される。

	# ビヘイビアの設定

		$actsAs でロードする際に、細かい設定を追加することができる。
		設定は連想配列で指定し、以下のようなキーをとる。
		設定は必要なキーや値だけを定義することが可能で、空でも構わない。

		'delayed'   この値が true の場合、ルールの追加は直ぐに行われず、 beforeValidate の時点で追加される。
					性能を重視する場合は 'delayed' => true で構わないが、
					その場合、FormHelper のフィールド自動生成処理において、検証ルールがまだ生成されていないため、
					FormHelper によってフィールドが必須とみなされず、自動生成要素に required クラスが追加されないことがある。
					（FormHelper は validate ルールを参照して　HTML を生成するため）

		'excludes'	検証ルールを自動的に追加しないフィールド名を配列で指定する。単一の場合は配列でなく文字列を指定しても構わない。

		'messages'	追加されるルールのメッセージを独自のものに変更する際に指定する連想配列。
					以下をキーとして、それぞれに対するメッセージを値で指定する。
					ここで指定した文字列も __ 関数を通るので、英語で定義しておけば多言語化が可能。
						'required'	NULL 不可で既定値のないフィールドに対するメッセージ。%s はフィールド名に置換される。
						'notEmpty'	値が必要なフィールドに対するメッセージ。%s はフィールド名に置換される。
						'domain'	値が範囲外のフィールドに対するメッセージ。%s はフィールド名に置換される。
						'maxLength'	文字列でカラム長を超える入力に対するメッセージ。%s はフィールド名、%d は最大文字数に置換される。
						'integer'	整数型に対する値が不正なメッセージ。%s はフィールド名に置換される。
						'float'		数値型に対する値が不正なメッセージ。%s はフィールド名に置換される。
						'datetime'	日時型に対する値が不正なメッセージ。%s はフィールド名に置換される。
						'timestamp'	タイムスタンプ型に対する値が不正なメッセージ。%s はフィールド名に置換される。
						'time'		時刻型に対する値が不正なメッセージ。%s はフィールド名に置換される。
						'date'		日付型に対する値が不正なメッセージ。%s はフィールド名に置換される。
						'boolean'	論理型に対する値が不正なメッセージ。%s はフィールド名に置換される。
						'email'		メール型に対する値が不正なメッセージ。%s はフィールド名に置換される。
						'url'		URL 型に対する値が不正なメッセージ。%s はフィールド名に置換される。
						'ip'		IP 型に対する値が不正なメッセージ。%s はフィールド名に置換される。
						'phone'		URL 型に対する値が不正なメッセージ。%s はフィールド名に置換される。
						'zipcode'	IP 型に対する値が不正なメッセージ。%s はフィールド名に置換される。

		'messageDomain'	この値を指定すると、メッセージを国際化する際に、指定したドメインが使用される。
						デフォルトは null で標準のドメイン（default.po） となる。

		'fields'	フィールド毎に個別に設定を上書きする場合に指定する連想配列。
					フィールド名をキーとし、以下の連想配列を値として持つ。
						'name'		項目名の自動判別を使わず、エラーメッセージに使う項目名を直接指定する。国際化は行われる。（NamedFieldBehavior）
						'notNull'	フィールド定義上は NULL 可だが、仕組み上 NOT NULL と同等に扱う場合は true を指定する。
						'messages'	自動的に追加されるルールのメッセージをフィールド単位で上書きする際に指定する連想配列。
									設定方法は、上の 'messages' と同じ。
						'selectable'	true を指定すると値が空の時のメッセージが常に選択式のものになる。
										false を指定すると値が空の時のメッセージが常に入力式のものになる。
										通常はモデルに *_options が存在するか、$belongsTo の外部キーフィールドの場合に選択式と認識する。

		'extract'	フィールドコメントから項目名を抽出する際、この値に指定した文字列がコメントにあれば、一致以降を除外する。
					コメントにフィールド名だけでなく追加情報がある場合に使用する。デフォルトは : 文字。
					null か空文字列を指定すると、コメント全体が使用される。
					false を指定すると、コメントからの項目名抽出を行わない。
					（NamedFieldBehavior）

		'nameDomain'	この値を指定すると、フィールド名を国際化する際に、指定したドメインが使用される。
						デフォルトは null で標準のドメイン（default.po） となる。
						（NamedFieldBehavior）

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

class AutoValidationBehavior extends NamedFieldBehavior {

	// 型に対するルール
	static private $_RULES = array(
			'integer'   => 'validateInteger',
			'float'     => 'validateNumeric',
			'datetime'  => 'validateDateTime',
			'timestamp' => 'validateDateTime',
			'time'      => 'validateTime',
			'date'      => 'validateDate',
			'boolean'   => 'boolean',
			'email'     => 'validateEmail', // 類推型
			'url'       => 'url', // 類推型
			'ip'        => 'ip', // 類推型
			'phone'     => 'validatePhone', // 類推型
			'zipcode'   => 'validateZipCode', // 類推型
	);

	// エラーメッセージ
	static private $_MESSAGES = array(
			'required'    => '%s is required.',
			'notEmpty'    => '%s cannot be empty.',
			'notSelected' => '%s is not selected.',
			'selection'   => '%s is invalid.',
			'maxLength'   => '%s cannot be greater than %d characters.',

			// types
			'integer'     => '%s must be an integer number.',
			'float'       => '%s must be a number.',
			'datetime'    => '%s is not a date and time.',
			'timestamp'   => '%s is not a date and time.',
			'time'        => '%s is not a time.',
			'date'        => '%s is not a date.',
			'boolean'     => '%s is invalid.',
			'email'       => '%s is invalid.',
			'url'         => '%s is invalid.',
			'ip'          => '%s is invalid.',
			'phone'       => '%s is invalid.',
			'zipcode'     => '%s is invalid.',
	);

	// 文字列型
	static private $_STRING_TYPES = array(
			'string',
			'text',
			'email',
			'url',
			'ip',
			'phone',
			'zipcode',
	);

	// CakePHP による自動処理フィールド
	static private $_AUTO_FIELDS = array(
			'created',
			'updated',
			'modified',
	);


	// 設定のデフォルト値
	static private $_DEFAULTS = array(
			'delayed' => false,
			'excludes' => null,
			'messages' => array(),
			'messageDomain' => null,

			// parent
			'fields' => array(),
			'extract' => ':',
			'nameDomain' => null,
	);

	// ビヘイビアにモデルを関連づけ
	public function setup(Model $model, $config = array()) {

		// 設定に既定値をマージ
		$config += self::$_DEFAULTS;

		// 除外指定が文字列なら単項目の配列に
		$config['excludes'] = (array)$config['excludes'];

		// 保存
		parent::setup($model, $config);

		// delayed でなければ即反映
		if (!$config['delayed']) {
			$this->applyAutoValidationRules($model);
		}

	}

	// 検証前のコールバック
	public function beforeValidate(Model $model, $options = array()) {
		$this->applyAutoValidationRules($model);
		return true;
	}

	/**
	 * $validate に自動検査項目を直ちに追加する
	 *
	 * オプションに delayed を指定した場合、通常は validate の直前（beforeValidate） に追加されるが、
	 * このメソッドを呼び出すと、即時に反映することができる。
	 * @param AppModel $model
	 */
	public function applyAutoValidationRules(Model $model) {

		// モデル用の設定をロード
		$config = &$this->settings[$model->alias];

		// 既に適用されている場合はなにもしない
		if (isset($config['_applied'])) {
			return true;
		}

		// モデルのスキーマを参照
		$schema = $model->schema();

		// 無ければなにもしない
		if (empty($schema)) {
			return;
		}

		// 外部キーを収集
		$foreignKeys = Hash::extract($model->belongsTo, '{s}.foreignKey');

		// メッセージの準備
		$baseMessages = array_merge(self::$_MESSAGES, $config['messages']);

		foreach ($schema as $fieldName => $info) {

			// 型を検出
			$type = $this->_detectType($fieldName, isset($info['type']) ? $info['type'] : null);

			// 型を判別できないフィールドは処理しない
			if ($type === null) {
				continue;
			}

			// 除外されているフィールドは処理しない
			if (in_array($fieldName, $config['excludes'])) {
				continue;
			}

			// フィールドの明示指定があれば読み込む
			$overrides = array();
			if (isset($config['fields'][$fieldName])) {
				$overrides = $config['fields'][$fieldName];
				if (is_string($overrides)) {
					$overrides = array('name' => $overrides);
				}
			}

			//
			// メッセージを用意
			//

			$messages = $baseMessages;
			// フィールド固有のメッセージ指定があれば上書きする
			if (isset($overrides['messages'])) {
				$messages = array_merge($messages, $overrides['messages']);
			}

			// フィールドが文字列型かどうか
			$isString = in_array($type, self::$_STRING_TYPES);

			// 項目名を用意
			$name = $this->getFieldName($model, $fieldName);

			//
			// ルールを構築
			//

			$rules = array();

			//
			// 必須ルール（required と  allowEmpty）の制御
			//

			// 自動フィールド、キー、外部キーではない場合、
			if (!in_array($fieldName, self::$_AUTO_FIELDS) && $fieldName !== $model->primaryKey && !in_array($fieldName, $foreignKeys)) {

				// notNull の明示がある、または NOT NULL でデフォルト値の指定がない場合、作成時にフィールドは指定が必須となる
				if (!empty($overrides['notNull']) || (empty($info['null']) && !isset($info['default']))) {

					// 必須用のルールを追加
					$rules['AutoValidationRequired'] = array(
							'rule'     => 'validateNone', // required によって検証されるため、ルール自体はダミーでよい
							'required' => true,
							'message'  => sprintf(__d($config['messageDomain'], $messages['required']), $name),
							'on'       => 'create', // 作成時のみ
							'last'     => true,
					);

				}

			}

			// 直接指定がある、または NOT NULL 制約がある場合、空を認めない
			if (!empty($overrides['notNull']) || empty($info['null'])) {

				// 選択式かどうか
				$selectable = false;

				if (isset($overrides['selectable']) && is_bool($overrides['selectable'])) {
					// 明示
					$selectable = $overrides['selectable'];
				} else {
					// 自動判別
					$selectable = $this->_isSelectable($fieldName, $model);
				}

				// 必須用のルールを追加
				$rules['AutoValidationNotEmpty'] = array(
						'rule'       => 'notEmpty',
						'message'    => sprintf(__d($config['messageDomain'], $messages[$selectable ? 'notSelected' : 'notEmpty']), $name),
						'allowEmpty' => false,
						'last'       => true,
				);

			}

			// 空に関する検証は既に終わっているので、以降のルールではルール上空の値を許容する

			//
			// 型に対する検証
			//

			if (isset(self::$_RULES[$type])) {

				// ルールを追加
				$rules['AutoValidationType'] = array(
						'rule'       => self::$_RULES[$type],
						'message'    => sprintf(__d($config['messageDomain'], $messages[$type]), $name),
						'allowEmpty' => true, // 空を許容し、その場合これ以降のルールによる検証を行わない
						'last'       => true,
				);

			}

			//
			// フィールドに選択肢がある場合、その中から選択
			//

			$options = $this->_getOptions($fieldName, $model);
			if ($options !== null) {

				// ルールを追加
				$rules['AutoValidationDomain'] = array(
						'rule'       => array('validateDomain', $options),
						'message'    => sprintf(__d($config['messageDomain'], $messages['selection']), $name),
						'allowEmpty' => true, // 空を許容し、その場合これ以降のルールによる検証を行わない
						'last'       => true,
				);

			}

			//
			// カラム長の指定がある文字列の場合、長さを制限
			//

			if ($isString && !empty($info['length'])) {

				// ルールを追加
				$rules['AutoValidationMaxLength'] = array(
						'rule'    => array('maxLength', $info['length']),
						'message' => sprintf(__d($config['messageDomain'], $messages['maxLength']), $name, $info['length']),
						'allowEmpty' => true, // 空を許容し、その場合これ以降のルールによる検証を行わない
						'last'    => true,
				);

			}

			//
			// NOT NULL ではなく、自動ルールが何もない場合、空を許容するルールを追加する
			// 最低 1 つの allowEmpty = true ルールを追加しておけば、
			// モデルでの細かい制約のルールを定義する最にいちいち allowEmpty を追加する必要がなくなる。
			//
			if (empty($overrides['notEmpty']) && !empty($info['null']) && empty($rules)) {

				// allowEmpty のためのルールを追加する。
				// このルールが false になることはないので、メッセージは不要
				$rules['AutoValidationAllowEmpty'] = array(
						'rule'       => 'validateNone', // allowEmpty のためなので、ルール自体はダミーでよい
						'allowEmpty' => true, // 空を許容し、その場合これ以降のルールによる検証を行わない
				);

			}

			// 既存のルールの先頭に生成したルールを追加
			$this->addValidationRules($model, $fieldName, $rules, true);

		}

		$config['_applied'] = true;

		return true;

	}

	/**
	 * $validate にルールを追加する。
	 *
	 * @param Model $model
	 * @param string $fieldName フィールド名
	 * @param mixed $rules 検証ルール名、ルール設定配列、またはルール設定配列の配列
	 * @param bool $top 最初に追加するかどうか
	 */
	public function addValidationRules(Model $model, $fieldName, $rules = array(), $top = false) {

		if (empty($rules)) {
			return;
		}

		// 単純ルールや単一ルールの場合は、複数定義に拡張する
		if (!is_array($rules) || isset($rules['rule'])) {
			$rules = array($rules);
		}

		$validate = $model->validate;
		if (!is_array($validate)) {
			$validate = array();
		}

		if (!isset($validate[$fieldName])) {
			// フィールドにルールがない

			// 追加
			$validate[$fieldName] = $rules;

		} else {
			// ルールがある

			$ruleSet = $validate[$fieldName];

			// 単純ルールや単一ルールの場合は、 複数のルールを定義できるように拡張する
			if (!is_array($ruleSet) || (is_array($ruleSet) && isset($ruleSet['rule']))) {
				$ruleSet = array($ruleSet);
			}

			//　追加
			if ($top) {
				$ruleSet = array_merge($rules, $ruleSet);
			} else {
				$ruleSet = array_merge($ruleSet, $rules);
			}

			$validate[$fieldName] = $ruleSet;

		}

		$model->validate = $validate;

	}

	/**
	 * $validate からルールを削除して返す。
	 *
	 * @param Model $model
	 * @param string $fieldName フィールド名
	 * @param string $ruleName ルール名。単一ルールを削除する場合は 0 を指定。
	 * @return mixed ルール。存在しない場合は false。
	 */
	public function removeValidationRule(Model $model, $fieldName, $ruleName) {

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
		if (!isset($validate[$fieldName])) {
			return false;
		}

		// 単純ルールの場合
		if (!is_array($validate[$fieldName])) {

			if ($validate[$fieldName] !== $ruleName) {
				return false;
			}

			// 削除
			$rule = $model->validate[$fieldName];
			unset($model->validate[$fieldName]);
			return $rule;

		}

		// 単一ルールの場合
		if (isset($validate[$fieldName]['rule'])) {

			// 名前がないので、$ruleName が 0 の場合に削除
			if ($ruleName != 0) {
				return false;
			}

			// 削除
			$rule = $model->validate[$fieldName];
			unset($model->validate[$fieldName]);
			return $rule;
		}

		// 複合ルールの場合

		// 指定した名前をキーに持つルールがあれば削除
		if (isset($validate[$fieldName][$ruleName])) {

			$rule = array($ruleName => $validate[$fieldName][$ruleName]);
			unset($model->validate[$fieldName][$ruleName]);
			return $rule;

		}

		// 無ければ、キーの指定のない単純ルールがあれば削除
		$key = array_search($ruleName, $validate[$fieldName]);
		if ($key !== false) {

			$rule = array($key => $validate[$fieldName][$key]);
			unset($model->validate[$fieldName][$key]);
			return $rule;

		}

		// 見つからない
		return false;

	}

	// options 用のルール。false は '0' の文字列として比較する
	public function validateDomain(Model $model, array $data, array $options) {

		$field = key($data);
		$value = current($data);

		// 緩い比較だと、'php' == 0 になってしまうので文字列における一致を取る
		// 但し、false は '0' とする
		$value = $value === false ? '0' : (string)$value;

		foreach ($options as $_) {
			if ($_ === false) {
				if ($value === '0') {
					return true;
				}
			} else {
				if ($value === (string)$_) {
					return true;
				}
			}
		}

		return false;
	}

	// required 等で使うダミールール
	public function validateNone(Model $model, array $data) {
		return true;
	}

	// integer
	public function validateInteger(Model $model, array $data) {
		return $this->_validateGeneric($model, $data, '/^[-+]?\\d+$/', array(',' => '')); // カンマ除去
	}

	// 0 以上の整数
	public function validateNonNegativeInteger(Model $model, array $data) {
		return $this->_validateGeneric($model, $data, '/^+?\\d+$/', array(',' => '')); // カンマ除去
	}

	// float
	public function validateNumeric(Model $model, array $data) {
		return $this->_validateGeneric($model, $data, '/^[-+]?\\d*(?:\\d\\.?|\.\\d+)$/', array(',' => '')); // カンマ除去
	}

	// date
	public function validateDate(Model $model, array $data) {

		$field = key($data);
		$value = current($data);

		// 整形
		$value = $this->_formatHalfwidth($value, ' ./', '---'); // 区切り記号を - に

		// 検証
		$result = Validation::date($value);

		if ($result) {

			// 値が有効なら、書式化した値をフィールドに書き戻す
			$model->data[$model->alias][$field] = $value;

		}

		return $result;

	}

	// time
	public function validateTime(Model $model, array $data) {
		return $this->_validateGeneric($model, $data, '/^(?:[01]?\d|2[0-3])(?::[0-5]?\d){0,2}$/');
	}

	// datetime
	public function validateDateTime(Model $model, array $data) {

		$field = key($data);
		$value = current($data);

		// 整形
		$value = $this->_formatHalfwidth($value, './', '--'); // 区切り記号を - に

		// 検証
		@list($date, $time) = explode(' ', $value, 2);
		$result = Validation::date($date) && preg_match('/^(?:[01]\d|2[0-3])(?::[0-5]?\d){0,2}$/', $time);

		if ($result) {

			// 値が有効なら、書式化した値をフィールドに書き戻す
			$model->data[$model->alias][$field] = $value;

		}

		return $result;
	}

	// メールアドレス
	public function validateEmail(Model $model, array $data) {

		$field = key($data);
		$value = current($data);

		// 整形
		$value = $this->_formatHalfwidth($value);

		// 検証
		$result = Validation::email($value);

		if ($result) {

			// 値が有効なら、書式化した値をフィールドに書き戻す
			$model->data[$model->alias][$field] = $value;

		}

		return $result;
	}

	// 電話番号
	public function validatePhone(Model $model, array $data) {
		return $this->_validateGeneric($model, $data, '/^[-+ .()\\d]+$/', 'ABCDEFGHIJKLMNOPRSTUVWXY', '222333444555666777888999'); // 米式表記
	}

	// 郵便番号
	public function validateZipCode(Model $model, array $data) {
		return $this->_validateGeneric($model, $data, '/^\\d{3}(?:-?\\d{2,4})?$/');
	}

	/**
	 * 半角化して前後の空白を除去して返す。必要であれば指定した追加ルールで strtr も行う。
	 * @param string $input 入力値
	 * @param array $fromOrRules strtr に渡す追加ルール
	 * @param array $to strtr に渡す追加ルール
	 * @return boolean
	 */
	private function _formatHalfwidth($input, $fromOrRules = null, $to = null) {

		// 半角化 & 空白除去 & 大文字化
		$input = trim($this->_halfizeAsciiWithSpace($input));

		// その他マッピング
		if (is_array($fromOrRules)) {
			$input = strtr($input, $fromOrRules);
		} else if (is_scalar($fromOrRules) && is_scalar($to)) {
			$input = strtr($input, $fromOrRules, $to);
		}

		return $input;

	}

	/**
	 * 半角文字ベースの汎用検証
	 * @param Model $model 検査モデル
	 * @param array $data 検査フィールド => 値の配列
	 * @param string $pattern 検証パターン
	 * @param array $fromOrRules strtr に渡す追加ルール
	 * @param array $to strtr に渡す追加ルール
	 * @return boolean
	 */
	private function _validateGeneric(Model $model, array $data, $pattern, $fromOrRules = null, $to = null) {

		$field = key($data);
		$value = current($data);

		// 整形
		$value = $this->_formatHalfwidth($value, $fromOrRules, $to);

		// 検証
		$result = preg_match($pattern, $value);

		if ($result) {

			// 値が有効なら、書式化した値をフィールドに書き戻す
			$model->data[$model->alias][$field] = $value;

		}

		return $result;

	}

	/**
	 * ASCII 印字可能文字に対する Full-width や一部約物を対応する Half-width 文字に変換する。
	 * @param string $value
	 * @return string
	 */
	private function _halfizeAsciiWithSpace($value) {

		// 　: U+3000 ->  : U+0020
		// ！: U+FF01 -> !: U+0021
		// ～: U+FF5E -> ~: U+007E
		$from = $this->_range_utf8('！', '～');
		$to   = $this->_range_utf8('!', '~');

		// No-break, 間隔、音引き（全・半）、ハイフン、ダッシュ（ニ分・全）
		array_push($from, ' ', '　', 'ー', 'ｰ', '‐', '–', '—');
		array_push($to, ' ', ' ', '-', '-', '-', '-', '-');

		return $this->_tr_fast($value, array_combine($from, $to));
	}

	/**
	 * UTF-8 の文字範囲の配列を作成する。
	 * @param mixed $from UTF-8 文字又は Unicode コードポイント
	 * @param mixed $to UTF-8 文字又は Unicode コードポイント
	 * @return array 文字配列。
	 */
	private function _range_utf8($from, $to) {

		// UTF-8 文字を指定した場合、Unicode コードポイントに変換
		if (!is_integer($from)) {
			$from = current(unpack('v', mb_convert_encoding($from, 'UTF-16LE', 'UTF-8')));
		}
		if (!is_integer($to)) {
			$to = current(unpack('v', mb_convert_encoding($to, 'UTF-16LE', 'UTF-8')));
		}

		// Unicode コードポイントの配列を作り
		$packArgs = range($from, $to);

		// 先頭に、リトルエンディアンの 16 ビット数値にエンコードするフォーマットを追加し
		array_unshift($packArgs, 'v*');

		// それを引数として pack を呼び出して　UTF-16LE バイナリ文字列を作成
		$utf16le = call_user_func_array('pack', $packArgs);

		// UTF-8 に変換
		$utf8 = mb_convert_encoding($utf16le, 'UTF-8', 'UTF-16LE');

		// 文字単位に切り出して配列に
		return preg_split('//u', $utf8, -1, PREG_SPLIT_NO_EMPTY);

	}

	// strtr のパターンマッチ版。項目数が多い場合はこちらの方が速い
	private function _tr_fast($value, array $mappings) {

		$pattern = array();
		foreach (array_keys($mappings) as $_) {
			$pattern[] = '(?:' . preg_quote($_, '/') . ')';
		}
		$pattern = '/' . implode('|', $pattern) . '/u';

		return preg_replace_callback($pattern, function (array $_) use ($mappings) {
			return $mappings[$_[0]];
		}, $value);

	}

	// 型を類推
	private function _detectType($name, $databaseType) {

		// email, url, ip, phone という単語で終端する場合
		foreach (array('email', 'url', 'ip', 'phone') as $t) {
			if (preg_match('/(?:^|_)' . $t . '$/', $name)) {
				return $t;
			}
		}

		// fax
		if (preg_match('/(?:^|_)fax$/', $name)) {
			return 'phone';
		}
		// zip_code, zipcode
		if (preg_match('/(?:^|_)zip_?code$/', $name)) {
			return 'zipcode';
		}

		if ($databaseType !== null) {
			return $databaseType;
		}

		return null;
	}

	// 選択式かどうか
	private function _isSelectable($name, Model $model) {

		// $belongsTo
		if (isset($model->belongsTo) && is_array($model->belongsTo)) {
			foreach ($model->belongsTo as $relation) {
				if (is_array($relation) && isset($relation['foreignKey']) && $relation['foreignKey'] === $name) {
					return true;
				}
			}
		}

		$name = preg_replace('/_id$/', '', $name);
		$name .= '_options';

		if (isset($model->{$name})) {
			return true;
		}

		return false;

	}

	// 選択肢を検出
	private function _getOptions($name, Model $model) {

		$name = preg_replace('/_id$/', '', $name);
		$name .= '_options';

		if (!isset($model->{$name})) { // property_exists($model, $name)
			return null;
		}

		$options = $model->{$name};

		if (!is_array($options)) {
			return null;
		}

		return array_keys($options);

	}

}
