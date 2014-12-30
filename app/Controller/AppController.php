<?php
/**
 * Application level Controller
 *
 * This file is application-wide controller file. You can put all
 * application-wide controller-related methods here.
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

App::uses('Controller', 'Controller');

App::uses('Expect', 'Nicosys.Utility');

/**
 * @property TransactionComponent $Transaction
 * @property AutoTitleComponent   $AutoTitle
 * @property Base64UrlComponent   $Base64Url
 * @property SQLComponent         $SQL
 */
class AppController extends Controller {

	public $components = [

		// デバッグ用
		'DebugKit.Toolbar',

		// 旧リクエストハンドラ
		'RequestHandler',

		// セッション
		'Session',

		'Nicosys.Transaction',
		'Nicosys.AutoTitle',
		'Nicosys.Base64Url',
		'Nicosys.SQL',

		// 認証と認可
		'Auth' => [

			// ログイン URL
			'loginAction'          => [
				'controller' => 'auth',
				'action'     => 'login',
			],

			// 認証エラーメッセージ
			'authError'            => 'ログイン可能時間を超過しました。再度ログインしてください。',

			// アクセス不可の際の遷移先
			'unauthorizedRedirect' => '/',

			// 認証設定
			'authenticate'         => [

				// フォーム認証
				'Form' => [

					// 使用モデル
					'userModel'      => 'Staff',

					// フィールドのマッピング
					'fields'         => [
						'username' => 'email',
						'password' => 'password'
					],

					// 絞込み条件
					'scope' => [
						'locked' => false,
					],

					// セッションに持つ情報の絞込み
					'recursive'      => -1,
					//'contain' => array(),

					'passwordHasher' => [
						'className' => 'Simple',
						'hashType'  => 'sha256'
					]
				],

			],


			// 認可設定
			'authorize'            => [

				// コントローラーで行う
				'Controller',

			],

		],

	];

	public $helpers = [
		'Html' => [
			'configFile' => 'tags.php', // TEXTAREA パッチ
		],
		'Form' => [
			'className' => 'Nicosys.Bootstrap3Form', // Bootstrap 用に差し替え
		],
		'Session',
		'Time' => [
			'className' => 'Nicosys.LocaleTime', // Locale 対応版に差し替え
		],
		'Number',
	];

	//----------------------------------------------------------------------
	// コールバック
	//----------------------------------------------------------------------

	// ビューの直前のコールバック
	public function beforeRender() {

		// エラーコントローラーの場合
		if ($this instanceof CakeErrorController) {
			$this->set('title_for_layout', 'エラー');
		}

		parent::beforeRender();

	}

	// コントローラー単位の認可（Auth コンポーネントより）
	public function isAuthorized($user) {

		// 認可制御が必要
		return true;

	}

}
