<?php
/**
 * $Id: StaffsController.php 65 2014-10-07 06:44:57Z h.ushito@nicosys.jp $
 *
 * @copyright (c) 2014 Nicosys Co. Ltd.
 */

App::uses('AppController', 'Controller');
App::uses('Staff', 'Model');

/**
 * @property Staff $Staff
 */
class StaffsController extends AppController {

	public $components = [

		'Nicosys.Search' => [

			'criteria'     => [
				// 部分一致
				'code'   => ['contains', 'Staff.code'],
				'name'   => ['contains', 'Staff.name'],

				'office_id' => 'Staff.office_id',
			],

			//　検索フォームを維持
			'populateData' => 'Search',

		],

		'Paginator'      => [
			// 		'fields' => [
			// 		],
			'order' => 'Staff.code ASC',
			'limit' => 10,
			//		'recursive' => -1
		],

		'Nicosys.AutoTitle' => [
			'labels' => [
				'profile' => 'プロフィール',
			]
		],

	];

	//----------------------------------------------------------------------
	// コールバック
	//----------------------------------------------------------------------

	/**
	 * 表示直前のコールバック
	 */
	public function beforeRender() {

		// 選択項目を用意
		switch ($this->request->action) {

			case 'index':
			case 'add':
			case 'edit':
			case 'profile':

				// find options
				$this->set('roles', $this->Staff->role_options);
				$this->set('offices', $this->Staff->Office->find('list'));
				break;

		}

		parent::beforeRender();

	}

	//----------------------------------------------------------------------
	// アクション
	//----------------------------------------------------------------------

	/*
	 * Typeahead 用。スタッフ一覧の取得
	 */
	public function json() {

		$term = $this->request->query('term');

		$staffs = $this->Staff->find('all', [
			'fields'     => [
				'Staff.id',
				'Staff.code',
				'Staff.name',
				'Staff.locked',
			],
			'conditions' => [
				'Staff.code LIKE ? COLLATE utf8_unicode_ci' => '%' . $this->SQL->quotelike($term) . '%',
			],
			'order' => 'Staff.code ASC',
		]);

		$this->set('staffs', Hash::extract($staffs, '{n}.Staff'));

		$this->viewClass = 'Json';
		$this->set('_serialize', 'staffs');

	}

	public function index() {

		// 検索
		$this->set('staffs', $this->Search->paginate());

	}


	public function add() {

		$model = $this->{$this->modelClass};

		if (!$this->request->is('post') || empty($this->request->data)) {

			// 既定値を用意
			$this->request->data = $model->create();

		} else {

			unset($this->request->data[$model->alias][$model->primaryKey]);

			// パスワードをハッシュ化する
			if (isset($this->request->data[$model->alias]['password']) && $this->request->data[$model->alias]['password'] !== '') {
				$this->request->data[$model->alias]['password'] = Security::hash($this->request->data[$model->alias]['password'],
					'sha256', true);
			}
			if (isset($this->request->data[$model->alias]['password_confirmation']) && $this->request->data[$model->alias]['password_confirmation'] !== '') {
				$this->request->data[$model->alias]['password_confirmation'] = Security::hash($this->request->data[$model->alias]['password_confirmation'],
					'sha256', true);
			}

			// 保存
			if (!$model->save($this->request->data)) {
				$this->Session->setFlash('入力内容に問題があります。正しく入力して下さい。', 'alert');
				return;
			}

			$this->Session->setFlash('スタッフを追加しました。', 'alert-success');
			$this->redirect(am($this->request->named, ['action' => 'index']));

		}

	}


	public function edit($id = null) {

		// キーは整数
		$id = Expect::int($id);

		$model = $this->{$this->modelClass};

		if (!$this->request->is('put') || empty($this->request->data)) {

			// レコード読み込み
			$this->request->data = $model->findById($id);
			if (empty($this->request->data)) {
				throw new NotFoundException();
			}

		} else {

			$model->create(false);
			$this->request->data[$model->alias][$model->primaryKey] = $id;

			// パスワード
			$hasPassword = isset($this->request->data[$model->alias]['password']) && $this->request->data[$model->alias]['password'] !== '';
			$hasConfirm = isset($this->request->data[$model->alias]['password_confirmation']) && $this->request->data[$model->alias]['password_confirmation'] !== '';

			if ($hasPassword) {
				$this->request->data[$model->alias]['password'] = Security::hash($this->request->data[$model->alias]['password'],
					'sha256', true);
			}
			if ($hasConfirm) {
				$this->request->data[$model->alias]['password_confirmation'] = Security::hash($this->request->data[$model->alias]['password_confirmation'],
					'sha256', true);
			}

			// パスワードは省略可能
			if (!$hasPassword && !$hasConfirm) {
				unset($this->request->data[$model->alias]['password']);
				unset($this->request->data[$model->alias]['password_confirmation']);
			}

			// トランザクション開始
			$transaction = $this->Transaction->begin();

			// 存在を確認
			if (!$model->hasAnyOfId($id)) {
				throw new ForbiddenException();
			}

			// 保存
			if (!$model->save($this->request->data)) {
				$this->Session->setFlash('入力内容に問題があります。正しく入力して下さい。', 'alert');
				return;
			}

			// コミット
			$transaction->commit();

			$this->Session->setFlash('スタッフを更新しました。', 'alert-success');
			$this->redirect(am($this->request->named, ['action' => 'index']));

		}

	}

	public function profile() {

		$id = AuthComponent::user('id');

		$model = $this->{$this->modelClass};

		if (!$this->request->is('put') || empty($this->request->data)) {

			// レコード読み込み
			$this->request->data = $model->findById($id);
			if (empty($this->request->data)) {
				throw new NotFoundException();
			}

		} else {

			$model->create(false);
			$this->request->data[$model->alias][$model->primaryKey] = $id;

			// パスワード
			$hasPassword = isset($this->request->data[$model->alias]['password']) && $this->request->data[$model->alias]['password'] !== '';
			$hasConfirm = isset($this->request->data[$model->alias]['password_confirmation']) && $this->request->data[$model->alias]['password_confirmation'] !== '';

			if ($hasPassword) {
				$this->request->data[$model->alias]['password'] = Security::hash($this->request->data[$model->alias]['password'],
					'sha256', true);
			}
			if ($hasConfirm) {
				$this->request->data[$model->alias]['password_confirmation'] = Security::hash($this->request->data[$model->alias]['password_confirmation'],
					'sha256', true);
			}

			// パスワードは省略可能
			if (!$hasPassword && !$hasConfirm) {
				unset($this->request->data[$model->alias]['password']);
				unset($this->request->data[$model->alias]['password_confirmation']);
			}

			// トランザクション開始
			$transaction = $this->Transaction->begin();

			// 存在を確認
			if (!$model->hasAnyOfId($id)) {
				throw new ForbiddenException();
			}

			// 保存
			if (!$model->save($this->request->data, true, ['name', 'email', 'password', 'password_confirmation'])) {
				$this->Session->setFlash('入力内容に問題があります。正しく入力して下さい。', 'alert');
				return;
			}

			// コミット
			$transaction->commit();

			$this->Session->setFlash('個人情報を更新しました。', 'alert-success');
			$this->redirect(am($this->request->named, ['action' => 'index']));

		}

	}



	public function delete($id = null) {

		// キーは整数
		$id = Expect::int($id);

		$model = $this->{$this->modelClass};

		// 自分は消せない
		if ($id == AuthComponent::user('id')) {
			throw new ForbiddenException();
		}

		// トランザクション開始
		$transaction = $this->Transaction->begin();

		// 存在するか？
		if (!$model->hasAnyOfId($id)) {
			throw new NotFoundException();
		}

		// 削除
		if (!$model->delete($id, true)) {
			$this->Session->setFlash('スタッフを削除できませんでした。', 'alert-error');
			$this->redirect(am($this->request->named, ['action' => 'index']));
		}

		$this->Session->setFlash('スタッフを削除しました。', 'alert-success');

		// コミット
		$transaction->commit();

		$this->redirect(am($this->request->named, ['action' => 'index']));

	}

	public function isAuthorized($user = null) {

		if ($this->request->action != 'profile' && $this->request->action != 'json') {
			return $user['role'] == Staff::ROLE_ADMINISTRATOR;
		}

		return parent::isAuthorized($user);

	}

}
