<?php
/**
 * $Id: CustomersController.php 96 2014-12-21 14:33:39Z h.ushito@nicosys.jp $
 *
 * @copyright (c) 2014 Nicosys Co. Ltd.
 */

App::uses('AppController', 'Controller');
App::uses('Staff', 'Model');

/**
 * @property Customer        $Customer
 * @property SearchComponent $Search
 */
class CustomersController extends AppController {

	public $components = [

		'Nicosys.Search'    => [

			'criteria'     => [

				'id'                => 'Customer.id',

				'contact_date_from' => 'Customer.contact_date >=',
				'contact_date_to'   => 'Customer.contact_date <=',
				'contact_time_from' => 'Customer.contact_time >=',
				'contact_time_to'   => 'Customer.contact_time <=',

				'staff_code'        => ['contains', 'Customer.staff_code'],
				'staff_name'        => ['contains', 'Customer.staff_name'],
				'result'            => 'Customer.result',

                'responsible_title' => ['contains', 'Customer.responsible_title'],

				'name'              => ['contains', 'Customer.name'],
				'business'          => 'Customer.business', // 業種は完全一致
				'personality'       => 'Customer.personality',
				'prefecture'        => ['contains', 'Customer.prefecture'],
				'address'           => ['contains', 'Customer.address'],
				'phone'             => ['contains', 'Customer.phone'],

				'level'             => 'Customer.level',
				'carrier'           => 'Customer.carrier',
				'quantity_min'      => 'Customer.quantity >=',
				'quantity_max'      => 'Customer.quantity <=',
				'contract'          => 'Customer.contract',
				'visit_result'      => 'Customer.visit_result',

				'note'              => ['contains', 'Customer.note'],
				'appointment_note'  => ['contains', 'Customer.appointment_note'],

			],

			//　検索フォームを維持
			'populateData' => 'Search',

		],

		'Paginator'         => [
			// 		'fields' => [
			// 		],
			'order'    => 'Customer.id ASC',
			'maxLimit' => 500,
			'limit'    => 500,
			//		'recursive' => -1
		],

		'Nicosys.AutoTitle' => [
			'labels' => [
				'import' => '顧客インポート',
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

				// find options
				$this->set('personalities', $this->Customer->personality_options);
				$this->set('contactGenders', $this->Customer->contact_gender_options);
				$this->set('responsibleGenders', $this->Customer->responsible_gender_options);
				$this->set('carriers', $this->Customer->carrier_options);
                $this->set('dealers', $this->Customer->dealer_options);
				$this->set('contracts', $this->Customer->contract_options);
				$this->set('results', $this->Customer->result_options);
				$this->set('visitResults', $this->Customer->visit_result_options);

				break;

		}

		parent::beforeRender();

	}

	//----------------------------------------------------------------------
	// アクション
	//----------------------------------------------------------------------

	public function index() {

		// 検索
		$customers = $this->Search->paginate();
		$this->set('customers', $customers);

		if (!empty($customers) && !empty($this->request->query['detail'])) {
			// go
			$this->redirect(am($this->request->named, ['action' => 'edit', $customers[0]['Customer']['id'], 1]));
			return;
		}

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

			$this->Session->setFlash('顧客を追加しました。', 'alert-success');
			$this->redirect(['action' => 'index', '?' => [$model->primaryKey => $model->id]]);

		}

	}

	public function go() {

		$position = $this->request->query('position');

		// 位置は必須で整数
		$position = Expect::int($position);

		$model = $this->{$this->modelClass};

		// conditions
		$query = $this->Search->getQuery();
		$conditions = $this->Search->build($query);

		// order
		$sort = isset($this->request->named['sort']) ? $this->request->named['sort'] : '';
		$direction = isset($this->request->named['direction']) ? $this->request->named['direction'] : '';

		$idExpr = $model->alias . '.' . $model->primaryKey;

		$current = $model->find('first', [
			'conditions' => $conditions,
			'fields'     => [$idExpr],
			'offset'     => $position - 1,
		]);

		$id = Hash::get($current, $idExpr);
		if ($id) {

			// 一覧に戻る際のページ位置を調整
			$page = intval(($position - 1) / $this->Paginator->settings['limit']) + 1;

			$this->redirect(am($this->request->named, ['action' => 'edit', $id, $position, 'page' => $page]));

			return;
		}

		throw new NotFoundException();

	}


	public function edit($id = null, $position = null) {

		// キーは必須で整数
		$id = Expect::int($id);

		// 位置は整数
		$position = Assume::int($position);

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

			$this->Session->setFlash('顧客を更新しました。', 'alert-success');

			// 更新して戻る
			if (!empty($this->request->data['back'])) {
				$this->redirect(am($this->request->named, ['action' => 'index']));
				return;
			}

		}

		if ($position) {

			// conditions
			$query = $this->Search->getQuery();
			$conditions = $this->Search->build($query);

			// order
			$sort = isset($this->request->named['sort']) ? $this->request->named['sort'] : '';
			$direction = isset($this->request->named['direction']) ? $this->request->named['direction'] : '';

			$idExpr = $model->alias . '.' . $model->primaryKey;

			$next = $model->find('first', [
				'conditions' => $conditions,
				'fields'     => [$idExpr],
				'offset'     => $position,
			]);

			// 更新して次へ
			if ($next && !empty($this->request->data['next'])) {
				$this->redirect(am($this->request->named, [Hash::get($next, $idExpr), $position + 1]));
				return;
			}

			$prev = $model->find('first', [
				'conditions' => $conditions,
				'fields'     => [$idExpr],
				'offset'     => $position - 2,
			]);

			$count = $model->find('count', [
				'conditions' => $conditions,
			]);

			$this->set('position', $position);
			$this->set('count', $count);
			$this->set('limit', $this->Paginator->settings['limit']);
			$this->set('next', Hash::get($next, $idExpr));
			$this->set('prev', Hash::get($prev, $idExpr));


		}

	}

	public function delete($id = null) {

		// キーは整数
		$id = Expect::int($id);

		$model = $this->{$this->modelClass};

		// トランザクション開始
		$transaction = $this->Transaction->begin();

		// 存在するか？
		if (!$model->hasAnyOfId($id)) {
			throw new NotFoundException();
		}

		// 削除
		if (!$model->delete($id, true)) {
			$this->Session->setFlash('顧客を削除できませんでした。', 'alert-error');
			$this->redirect(am($this->request->named, ['action' => 'index']));
		}

		$this->Session->setFlash('顧客を削除しました。', 'alert-success');

		// コミット
		$transaction->commit();

		$this->redirect(am($this->request->named, ['action' => 'index']));

	}

	public function import() {

		if (empty($this->request->data)) {
			return;
		}

		$import = ClassRegistry::init('CustomerImport');
		$customer = $this->Customer;

        // 一時的にメールアドレスにユニーク・必須条件をつける

        // 先頭に追加するため、旧式の addValidationRules を使う
        $customer->addValidationRules('phone', [['rule' => 'notEmpty', 'required' => true, 'allowEmpty' => false, 'message' => '電話番号は必須です。']], true);
        $customer->validator()->add('phone', [['rule' => 'isUnique', 'message' => 'この電話番号は既に登録されています。']]);

		// 検証
		$import->set($this->request->data);
		if (!$import->validates()) {
			return;
		}

		$file = $this->request->data('CustomerImport.file');

		if (is_uploaded_file($file['tmp_name'])) {

			App::uses('CsvReader', 'Nicosys.Utility');

			$csv = new CsvReader($file['tmp_name']);

			// 見出し
			$columns = $csv->read();
			if ($columns === false) {
				$this->Session->setFlash('指定されたファイルには見出し行がありません。', 'alert-error');
				return;
			}
			$mapping = $import->mapping;

			// 見出しからフィールドへマッピング
			$fields = array_map(function ($_) use ($mapping) {
				return isset($mapping[$_]) ? $mapping[$_] : null;
			}, $columns);

			// 対応しないフィールドは無視
			$fields = array_filter($fields);

			if (empty($fields)) {
				$this->Session->setFlash('指定されたファイルには有効な列がありません。', 'alert-error');
				return;
			}

			// トランザクション開始
			$transaction = $this->Transaction->begin();

			$current = date('Y-m-d H:i:s');
			$succeeded = 0;

			// タイムアウト時間を 1h に設定
			set_time_limit(3600);

			$errors = [];

			while (($values = $csv->read()) !== false) {

				// 項目数が見出しと一致してるか？
				if (count($values) !== count($columns)) {
					$errors[$csv->getRowNumber()]['_error'] = '行の項目数が見出しと一致していません。';
					continue;
				}

				// データの用意

				// 無関係のカラムを除外し
				$values = array_intersect_key($values, $fields);

				// data 配列を作る
				$data = array_combine(array_values($fields), array_values($values));

                // 住所の検出
                if (!empty($data['address']) && preg_match('/^...?[都道府県]/u', $data['address'], $m)) {
                    $data['prefecture'] = $m[0];
                }

				// 保存
				$customer->clear();
				if ($customer->save($data)) {
					++$succeeded;
				} else {
					$errors[$csv->getRowNumber()] = $customer->validationErrors;
				}

			}

			if ($csv->getRowNumber() <= 1) {
				$this->Session->setFlash('指定されたファイルには有効なデータがありません。', 'alert-error');
				return;
			}

			// コミット
			$transaction->commit();

			if ($succeeded > 0 && empty($errors)) {
				$this->Session->setFlash('インポートが完了しました。', 'alert-success');
			} else if ($succeeded == 0 && !empty($errors)) {
				$this->Session->setFlash('インポートに失敗しました。', 'alert-error');
			} else {
				$this->Session->setFlash('一部のデータをインポートしました。', 'alert');
			}

			$this->set('succeeded', $succeeded);
			$this->set('errors', $errors);

		}

	}

	public function isAuthorized($user = null) {

		if ($this->request->action == 'delete' || $this->request->action == 'import') {
			return $user['role'] == Staff::ROLE_ADMINISTRATOR;
		}

		return parent::isAuthorized($user);

	}

}