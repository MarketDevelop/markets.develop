<?php
/**
 * $Id: TransactionComponent.php 3 2013-08-30 05:44:51Z h.ushito@nicosys.jp $
 *
 * @copyright (c) 2013 Nicosys Co. Ltd.
 */

App::uses('Component', 'Component');

/**
 * トランザクションのユーティリティコンポーネント。
*/
class TransactionComponent extends Component  {

	/**
	 * トランザクションを開始する。
	 *
	 * @param Model $model データソースを取得するモデルを指定。省略した場合はコントローラーのデフォルトモデル。
	 * @return TransactionComponentTransaction
	 */
	public function begin(Model $model = null) {

		$controller = $this->_Collection->getController();

		// モデルの指定が無ければ、コントローラーの既定のモデルを使う
		if ($model === null) {
			$model = $controller->{$controller->modelClass};
		}
		return new TransactionComponentTransaction($model);
	}

}

/**
 * トランザクションのラッパー。
 */
class TransactionComponentTransaction {

	private $model;
	private $dataSource;

	/**
	 * モデルを指定してトランザクションを開始
	 */
	public function __construct(Model $model) {
		$this->model = $model;
		$this->dataSource = $model->getDataSource();
		$this->dataSource->begin($model);
	}

	/**
	 * トランザクションを確定する
	 */
	public function commit() {
		if (isset($this->dataSource)) {
			$this->dataSource->commit($this->model);
			$this->dataSource = null;
			$this->model = null;
		}
	}

	/**
	 * トランザクションをロールバックする
	 */
	public function rollback() {
		if (isset($this->dataSource)) {
			$this->dataSource->rollback($this->model);
			$this->dataSource = null;
			$this->model = null;
		}
	}

	/**
	 * トランザクションが閉じていない場合ロールバックする
	 */
	public function __destruct() {
		$this->rollback();
	}

}
