<?php
/**
 * $Id: AssocComponent.php 5 2013-08-30 09:01:35Z h.ushito@nicosys.jp $
 *
 * @copyright (c) 2013 Nicosys Co. Ltd.
 */

App::uses('Component', 'Component');

/**
 * 関連（アソシエーション）とタイトルに関するユーティリティ
*/
class AssocComponent extends Component {

	/**
	 * 指定したパスが示すモデルを取得
	 *
	 * @param string $assocPath 関連モデルのパス。主モデルに対する直接関連モデルの場合はその名前、関連の関連の場合はモデルをドットで区切って指定。null だと主モデル。
	 */
	public function getModel($assocPath = null) {

		$controller = $this->_Collection->getController();

		// 関連を辿ってモデルを特定

		// 主モデルを取得
		$model = $controller->{$controller->modelClass};

		if (!empty($assocPath)) {

			// パスを辿る
			foreach (explode('.', $assocPath) as $_) {

				// hasMany の場合、インデックスが含まれるので除外
				if (!is_numeric($_)) {
					$model = $model->{$_};
				}

			}

		}

		return $model;
	}

	/**
	 * 関連モデルを取得する絞り込み条件を $options の contain に追加する。
	 * 関連モデル配下の条件は置換されるので、関連モデルのサブ関連モデルの設定が必要であれば、このメソッドの後で設定すること。
	 * @param array $options 条件を格納するオプション配列。find に渡す形式と同じ。
	 * @param string $assocPath 関連モデルのパス。主モデルに対する直接関連モデルの場合はその名前、関連の関連の場合はドットで区切ったパスを指定。
	 * @param array $fields 省略可能。関連モデルから取得するフィールドを指定する。モデル名の修飾は自動的に行われる。省略した場合は全てのフィールド。
	 * @return AssocComponent
	 */
	public function contain(array &$options, $assocPath, array $fields = null) {

		$modelPath = 'contain.' . $assocPath;
		$fieldsPath = 'contain.' . $assocPath . '.fields';

		// 主モデル
		if (empty($assocPath)) {
			$modelPath = '';
			$fieldsPath = 'fields';
		}

		// 関連を辿ってモデルを特定
		$model = $this->getModel($assocPath);

		if (empty($fields)) {
			if (!empty($modelPath)) {
				$options = Hash::insert($options, $modelPath, array());
			}
		} else {
			$options = Hash::insert($options, $fieldsPath,
					array_map(function ($_) use ($model) {
						return $model->alias . '.' . $_;
					}, $fields)
			);
		}

		return $this;

	}

	/**
	 * 関連モデルからキーとタイトルだけ取得する絞り込み条件を $options の contain に追加する。
	 * 主に参照系画面で関連モデルのタイトルを画面表示するために使用する。
	 * 関連モデル配下の条件は置換されるので、関連モデルのサブ関連モデルの設定が必要であれば、このメソッドの後で設定すること。
	 * @param array $options 条件を格納するオプション配列。find に渡す形式と同じ。
	 * @param string $assocPath 関連モデルのパス。主モデルに対する直接関連モデルの場合はその名前、関連の関連の場合はドットで区切ったパスを指定。
	 * @return AssocComponent
	 */
	public function containTitle(array &$options, $assocPath) {

		$path = 'contain.' . $assocPath . '.fields';

		// 主モデル
		if (empty($assocPath)) {
			$path = 'fields';
		}

		// 関連を辿ってモデルを特定
		$model = $this->getModel($assocPath);

		// キーとタイトルの条件
		$options = Hash::insert($options, $path, array(
				$model->alias . '.' . $model->primaryKey,
				$model->alias . '.' . $model->displayField,
		));

		return $this;

	}

	/**
	 * $this->data に含まれる関連モデルの id を取得する
	 *
	 * @param string $assocPath 関連モデルのパス。主モデルに対する直接関連モデルの場合はその名前、関連の関連の場合はドットで区切ったパスを指定。null だと主モデル。
	 */
	public function getId($assocPath = null) {
		$controller = $this->_Collection->getController();
		$model = $this->getModel($assocPath);
		return Hash::get($controller->request->data, (empty($assocPath) ? $model->alias : $assocPath) . '.' . $model->primaryKey);
	}

	/**
	 * $this->data に含まれる関連モデルのタイトルを取得する
	 *
	 * @param string $assocPath 関連モデルのパス。主モデルに対する直接関連モデルの場合はその名前、関連の関連の場合はドットで区切ったパスを指定。null だと主モデル。
	 */
	public function getTitle($assocPath = null) {
		$controller = $this->_Collection->getController();
		$model = $this->getModel($assocPath);
		return Hash::get($controller->request->data, (empty($assocPath) ? $model->alias : $assocPath) . '.' . $model->displayField);
	}

	/**
	 * belongsTo で従属参照している関連モデルのフィールドを取得して $this->request->data に放り込む。
	 * 主に編集系画面で参照モデルを画面表示するために使用する。
	 *
	 * @param string $modelName
	 * @param array $fields 省略可能。関連モデルから取得するフィールドを指定する。モデル名の修飾は自動的に行われる。省略した場合は全てのフィールド。
	 * @return AssocComponent
	 */
	public function fill($modelName, array $fields = null) {

		if (empty($modelName)) {
			throw new InvalidArgumentException();
		}

		$controller = $this->_Collection->getController();

		// 主モデルを取得
		$primaryModel = $controller->{$controller->modelClass};

		// 依存関係を取得
		$belongsTo = $primaryModel->belongsTo[$modelName];

		// 参照モデルの ID があり
		if (isset($controller->request->data[$primaryModel->alias][$belongsTo['foreignKey']])) {

			$id = $controller->request->data[$primaryModel->alias][$belongsTo['foreignKey']];

			// 値が空でないなら
			if (!empty($id)) {

				// 参照モデルを取得
				$referencedModel = $controller->{$primaryModel->alias}->{$modelName};

				// 参照先の名前を取得
				$data = $this->_fetch($referencedModel, $id, $fields);
				if (!empty($data)) {

					// 格納
					$controller->request->data = Hash::merge($controller->request->data, $data);

				}

			}

		}

		return $this;

	}

	/**
	 * belongsTo で従属参照している関連モデルのタイトルを取得して $this->request->data に放り込む。
	 * 主に編集系画面で参照モデルのタイトルを画面表示するために使用する。
	 *
	 * @param string $modelName
	 * @return AssocComponent
	 */
	public function fillTitle($modelName) {

		if (empty($modelName)) {
			throw new InvalidArgumentException();
		}

		$controller = $this->_Collection->getController();

		// 主モデルを取得
		$primaryModel = $controller->{$controller->modelClass};

		// 依存関係を取得
		$belongsTo = $primaryModel->belongsTo[$modelName];

		// 参照モデルの ID があり
		if (isset($controller->request->data[$primaryModel->alias][$belongsTo['foreignKey']])) {

			$id = $controller->request->data[$primaryModel->alias][$belongsTo['foreignKey']];

			// 値が空でないなら
			if (!empty($id)) {

				// 参照モデルを取得
				$referencedModel = $controller->{$primaryModel->alias}->{$modelName};

				// 参照先の名前を取得
				$title = $this->_fetchTitle($referencedModel, $id);
				if ($title !== false) {

					// 格納
					$controller->request->data[$modelName][$referencedModel->displayField] = $title;

				}

			}

		}

		return $this;

	}

	// 指定したモデルのフィールドを取得する。
	private function _fetch(Model $model, $id = null, array $fields = null) {

		if ($id === null) {
			$id = $model->id;
		}
		if (empty($id)) {
			throw new InvalidArgumentException('ID is not valid.');
		}
		return $model->find('first', array(
				'conditions' => array($model->alias . '.' . $model->primaryKey => $id),
				'fields' => $fields,
				'recursive' => -1,
		));

	}

	// 指定したモデルのタイトルを取得する。
	private function _fetchTitle(Model $model, $id = null) {

		if ($id === null) {
			$id = $model->id;
		}
		if (empty($id)) {
			throw new InvalidArgumentException('ID is not valid.');
		}
		return $model->field($model->displayField, array($model->alias . '.' . $model->primaryKey => $id));

	}

}
