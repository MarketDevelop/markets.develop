<?php
/**
 * $Id: Office.php 65 2014-10-07 06:44:57Z h.ushito@nicosys.jp $
 *
 * @copyright (c) 2014 Nicosys Co. Ltd.
 */

App::uses('AppModel', 'Model');

/**
 */
class Office extends AppModel {

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

	//----------------------------------------------------------------------
	// 検証ルール
	//----------------------------------------------------------------------

	public $validate = [

		'name' => [
			'rule'     => 'isUnique',
			'required' => true,
			'message'  => 'この名前は既に登録されています。',
			'last'     => true
		],

	];

	//----------------------------------------------------------------------
	// 選択項目
	//----------------------------------------------------------------------

	public $role_options = [
		self::ROLE_USER          => '一般',
		self::ROLE_ADMINISTRATOR => '管理者',
	];

	//----------------------------------------------------------------------
	// ロジック
	//----------------------------------------------------------------------

}
