<?php
/**
 * $Id: AuthController.php 6 2014-09-22 08:17:09Z h.ushito@nicosys.jp $
 *
 * @copyright (c) 2014 Nicosys Co. Ltd.
 */

App::uses('AppController', 'Controller');

class AuthController extends AppController {

	public $uses = ['Staff'];

	public $components = [

		// 自動ログインチケット
		'Cookie' => [
			'name' => 'login_ticket', // Cookie 接頭辞
			'time' => '+2 weeks', // チケット有効期限
		],

		'Nicosys.AutoTitle' => [
			'titles' => [
				'login' => 'ログイン',
			],
		],

	];

	//----------------------------------------------------------------------
	// コールバック
	//----------------------------------------------------------------------

	/**
	 * 処理開始前のコールバック
	 */
	public function beforeFilter() {

		// Cookie の有効パス。チケットの垂れ流しを防ぐため、パスを限定
		$this->Cookie->path = Router::url($this->Auth->loginAction);

		// ログアウトは認証不要
		$this->Auth->allow('logout');

		parent::beforeFilter();

	}

	//----------------------------------------------------------------------
	// 公開メソッド
	//----------------------------------------------------------------------

	/**
	 * ログイン
	 */
	public function login() {

		$this->layout = 'unauthenticated';

		$needTicket = false;

// 管理者を作成
//-----------------------------------------------------------------------------------------------
//		$this->request->data['Staff']['code'] = '0000';
// 		$this->request->data['Staff']['name'] = '管理者';
// 		$this->request->data['Staff']['email'] = 'admin@aposys.jp';
// 		$this->request->data['Staff']['password'] = Security::hash('admin', 'sha256', true);
//		$this->request->data['Staff']['password_confirmation'] = Security::hash('admin', 'sha256', true);
//		$this->request->data['Staff']['role'] = 2;
// 		if (!$this->Staff->save($this->request->data)) {
// 			$this->Session->setFlash('管理者を作成できませんでした。', 'alert-error');
// 		} else {
//		    $this->Session->setFlash('管理者を作成しました。', 'alert-success');
//	    }
//		$this->request->data['Staff']['password'] = '';
// 		return;
//-----------------------------------------------------------------------------------------------

		// POST された場合
		if ($this->request->is('post')) {

			// フォーム情報よりログイン
			if (!$this->Auth->login()) {
				$this->Session->setFlash('メールアドレスとパスワードを正しく入力してください。', 'alert-error');
			}

			// 自動ログインチケットの発行が必要なら、ticket チェックボックスが選択されて値が渡される
			$needTicket = isset($this->request->data['ticket']);

		}

		// この時点でログインしてない場合、Cookie による自動ログインを試す
		if (!$this->Auth->loggedIn()) {

			// 自動ログインチケットがあるか？
			$ticket = $this->Cookie->read('ticket');

			if (isset($ticket) && is_array($ticket)) {

				// チケットの有効期限が切れていないか？
				if ($ticket['until'] >= time()) {

					// Auth コンポーネントによるログインを模倣する

					// Form 認証プロバイダの設定を取得
					$settings = $this->_getFormAuthenticateSettings();
					list(, $model) = pluginSplit($settings['userModel']);

					// データベースよりユーザーを得る
					$data = ClassRegistry::init($settings['userModel'])->find('first', [
							'conditions' => [
									[$model . '.' . $settings['fields']['username'] => $ticket['user']],
									$settings['scope'],
							],
							'recursive'  => $settings['recursive'],
							'contain'    => $settings['contain'],
					]);


					// 該当ユーザーが存在した
					if ($data) {

						// Auth コンポーネントに合わせて組み替え
						$user = $data[$model];
						unset($data[$model]);
						unset($user[$settings['fields']['password']]);
						$data = array_merge($user, $data);

						// ログイン成功扱いに
						$this->Auth->login($data);

						// 自動ログインチケットを使ったログインの場合、
						// チケットを更新する必要がある
						$needTicket = true;

					}

				}

			}

		}

		// ログインに成功したか？
		if ($this->Auth->loggedIn()) {

			// 自動ログインチケットの発行が必要か？
			if ($needTicket) {

				// Auth コンポーネントによる動作を模倣する

				// Form 認証プロバイダの設定を取得
				$settings = $this->_getFormAuthenticateSettings();
				list(, $model) = pluginSplit($settings['userModel']);

				// 自動ログインチケット Cookie を発行/更新

				// Cookie の Expires はクライアント管理のため、信頼できない
				// そのため、Cookie の値自体にも有効期限を記録する
				$this->Cookie->write('ticket', [
						'user'  => AuthComponent::user($settings['fields']['username']), // ユーザーと
						'until' => strtotime($this->Cookie->time), // 有効期限を保持
				]);

			}

//			// 最終ログイン日時の更新
//			$this->User->id = AuthComponent::user('id');
//			$this->User->saveField('last_loggedin_date', date('Y-m-d H:i:s'));

			// Auth コンポーネントと同様に、ログイン後の画面にリダイレクト
			$this->redirect($this->Auth->redirectUrl());

			return; // ここには来ない

		}

	}

	// 設定よりフォーム認証プロバイダの情報を得る
	private function _getFormAuthenticateSettings() {
		foreach ($this->Auth->constructAuthenticate() as $authenticate) {
			if ($authenticate instanceof FormAuthenticate) {
				return $authenticate->settings;
			}
		}
		return array();
	}

	/**
	 * ログアウト
	 */
	public function logout() {

		// 自動ログインチケットを無効にする
		$this->Cookie->delete('ticket');

		// Auth コンポーネントを使ってログアウト
		$this->redirect($this->Auth->logout());

	}

}
