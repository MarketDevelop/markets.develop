<?php
/**
 * $Id: Customer.php 96 2014-12-21 14:33:39Z h.ushito@nicosys.jp $
 *
 * @copyright (c) 2014 Nicosys Co. Ltd.
 */

App::uses('AppModel', 'Model');

/**
 * @property Staff $Staff
 * @property Staff $VisitStaff
 */
class Customer extends AppModel {

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
		'Staff',
		'VisitStaff' => ['className' => 'Staff'],
	];

	//----------------------------------------------------------------------
	// 検証ルール
	//----------------------------------------------------------------------

	public $validate = [
	];

	//----------------------------------------------------------------------
	// 選択項目
	//----------------------------------------------------------------------


	public $personality_options = [
		1 => '株式',
		2 => 'その他',
	];

	public $contact_gender_options = [
		1 => '男',
		2 => '女',
	];

	public $responsible_gender_options = [
		1 => '男',
		2 => '女',
	];

	public $carrier_options = [
		1 => 'docomo',
		2 => 'au',
		3 => 'SoftBank',
		4 => 'Y!mobile',
		5 => 'WILLCOM',
		6 => 'O.M.Phone',
		7 => 'その他',
	];

    public $dealer_options = [
        1 => '法人代理店',
        2 => 'キャリアショップ',
        3 => 'その他',
    ];

	public $contract_options = [
		1 => '有',
		2 => '無',
	];

	public $result_options = [
		1  => 'アポイント',
		2  => '見込み',
		3  => '社長に断られた',
		4  => '担当に断られた',
		5  => '他の社員に断られた',
		6  => '不在',
		7  => 'コール',
		8  => '転送',
		9  => '留守電',
		10 => '支店',
		11 => '不通',
		12 => '打電禁止',
		13 => '顧客',
	];

	public $visit_result_options = [
		1 => '受注',
		2 => 'NG',
		3 => 'その他',
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
