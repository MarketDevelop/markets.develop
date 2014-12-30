<?php
/**
 * $Id: Staff.php 65 2014-10-07 06:44:57Z h.ushito@nicosys.jp $
 *
 * @copyright (c) 2014 Nicosys Co. Ltd.
 */

App::uses('AppModel', 'Model');

/**
 * @property Office $Office
 */
class Staff extends AppModel {

	const ROLE_USER = 1;
	const ROLE_ADMINISTRATOR = 2;

	//----------------------------------------------------------------------
	// 動作設定
	//----------------------------------------------------------------------

	public $order = [
		'id' => 'ASC'
// 		'modified' => 'DESC',
// 		'created' => 'DESC',
	];

	//----------------------------------------------------------------------
	// 依存関係
	//----------------------------------------------------------------------

	public $belongsTo = [
		'Office',
	];

	//----------------------------------------------------------------------
	// 検証ルール
	//----------------------------------------------------------------------

	public $validate = [

		'code'                  => [
			'rule'     => 'isUnique',
			'required' => true,
			'message'  => 'このコードは既に登録されています。',
			'last'     => true
		],

		'email'                 => [
			'rule'     => 'isUnique',
			'required' => true,
			'message'  => 'このメールアドレスは既に登録されています。',
			'last'     => true
		],

		'password_confirmation' => [

			'Required' => [
				'rule'     => 'validateNone',
				'required' => true,
				'message'  => 'パスワードの確認を入力してください。',
				'on'       => 'create',
				'last'     => true
			],

			'NotEmpty' => [
				'rule'       => 'notEmpty',
				'message'    => 'パスワードの確認を入力してください。',
				'on'         => 'create',
				'allowEmpty' => false,
				'last'       => true
			],

			'Match'    => [
				'rule'    => ['passwordMatch', 'password'],
				'message' => 'パスワードと確認入力が一致しません。',
				'last'    => true
			],

		],

	];

	//----------------------------------------------------------------------
	// 選択項目
	//----------------------------------------------------------------------

	public $role_options = [
		self::ROLE_USER          => '一般',
		self::ROLE_ADMINISTRATOR => '管理者',
	];

	public $office_options = [
		1 => '本町',
		2 => '新大阪',
		3 => '東京',
	];

	//----------------------------------------------------------------------
	// ロジック
	//----------------------------------------------------------------------

	public function passwordMatch(array $data, $name) {

		$field = key($data);
		$value = current($data);

		if (!isset($this->data[$this->alias][$name])) {
			return true;
		}

		return $value === $this->data[$this->alias][$name];

	}

}
