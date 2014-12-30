<?php
/**
 * $Id: OfficesController.php 65 2014-10-07 06:44:57Z h.ushito@nicosys.jp $
 *
 * @copyright (c) 2014 Nicosys Co. Ltd.
 */

App::uses('AppController', 'Controller');
App::uses('Office', 'Model');

/**
 * @property Office $Office
 */
class OfficesController extends AppController {

	public $components = [

		'Paginator' => [
			'order' => 'Office.id ASC',
			'limit' => 10,
		],

	];

	//----------------------------------------------------------------------
	// コールバック
	//----------------------------------------------------------------------

	public function beforeRender() {

		$this->set('LABEL', $this->AutoTitle->getLabel());

		return parent::beforeRender();
	}

	//----------------------------------------------------------------------
	// アクション
	//----------------------------------------------------------------------

	public function index() {

		// 検索
		$this->set('items', $this->Paginator->paginate());

	}


	public function add() {

		$model = $this->{$this->modelClass};

		if (!$this->request->is('post') || empty($this->request->data)) {

			// 既定値を用意
			$this->request->data = $model->create();

		} else {

			unset($this->request->data[$model->alias][$model->primaryKey]);

			// 保存
			if (!$model->save($this->request->data)) {
				$this->Session->setFlash('入力内容に問題があります。正しく入力して下さい。', 'alert');
				return;
			}

			$this->Session->setFlash($this->AutoTitle->getLabel() . 'を追加しました。', 'alert-success');
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

			$this->Session->setFlash($this->AutoTitle->getLabel() . 'を更新しました。', 'alert-success');
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
			$this->Session->setFlash($this->AutoTitle->getLabel() . 'を削除できませんでした。', 'alert-error');
			$this->redirect(am($this->request->named, ['action' => 'index']));
		}

		$this->Session->setFlash($this->AutoTitle->getLabel() . 'を削除しました。', 'alert-success');

		// コミット
		$transaction->commit();

		$this->redirect(am($this->request->named, ['action' => 'index']));

	}

	public function isAuthorized($user = null) {
		return isset($user['role']) && $user['role'] == Office::ROLE_ADMINISTRATOR;
	}

}
