<?php
/**
 * $Id: SearchComponent.php 32 2014-09-22 08:18:29Z h.ushito@nicosys.jp $
 *
 * @copyright (c) 2013 Nicosys Co. Ltd.
 */

App::uses('Component', 'Component');

/**
 * 検索フォームから検索条件を自動生成するコンポーネント。
 *
 * @property Base64UrlComponent $Base64Url
 * @property SQLComponent       $SQL
 */
class SearchComponent extends Component {

	/**
	 * 検索条件を保持するための名前つきパラメータの名前。
	 * @var string
	 */
	public $named = 'q';

	/**
	 * 検索ルール。
	 *
	 * 項目名をキー、条件を値とする連想配列。
	 * 条件は文字列か、演算子とパラメータの 2 要素の配列。
	 * 条件が文字列の場合はそのまま $conditions に渡される
	 * 条件が配列の場合、以下の演算子を利用可能。
	 *
	 * contains 演算子
	 * パラメータとしてフィールド名を受け取る。指定したフィールドに部分一致検索を行う LIKE 条件を $conditions 配列に追加する。
	 *
	 * expressions 演算子
	 * パラメータとして、入力候補値をキー、条件式を値する連想配列を受け取る。
	 * パラメータに入力値と一致するキーがあれば、その条件式を $conditions 配列に追加する。
	 *
	 * 例:
	 *    'Nicosys.Search' => array(
	 *            'criteria' => array(
	 *
	 *                    // 完全一致
	 *                    'supplier_contact_id' => 'Purchase.supplier_contact_id',
	 *                    'employee_id'         => 'Purchase.employee_id',
	 *                    'status'              => 'Purchase.status',
	 *
	 *                    // 比較
	 *                    'ordered_date_from'   => 'Purchase.ordered_date >=',
	 *                    'ordered_date_to'     => 'Purchase.ordered_date <=',
	 *                    'delivery_date_from'  => 'Purchase.delivery_date >=',
	 *                    'delivery_date_to'    => 'Purchase.delivery_date <=',
	 *                    'payment_date_from'   => 'Purchase.payment_date >=',
	 *                    'payment_date_to'     => 'Purchase.payment_date <=',
	 *
	 *                    // 部分一致
	 *                    'project'             => array('contains', 'Project.project'),
	 *                    'title'               => array('contains', 'Purchase.title'),
	 *                    'memo'                => array('contains', 'Purchase.memo'),
	 *                    'supplier'            => array('contains', 'Supplier.company'),
	 *
	 *                    // 独自条件（チェックを入れた場合のみ）
	 *                    'not_delivered' => array('expression', array(
	 *                            1 => array('Purchase.delivered' => false)
	 *                    )),
	 *                    'not_paid' => array('expression', array(
	 *                            1 => array('Purchase.paid' => false)
	 *                    )),
	 *
	 *                    // 独自条件（チェックが入らなかった場合のみ）
	 *                    'include_archive' => array('expression', array(
	 *                            0 => array('Purchase.archive' => false),
	 *                    )),
	 *
	 *                    // 独自条件（選択式）
	 *                    'contract' => array('expression', array(
	 *                            1 => 'Purchase.contract_start_date > CURRENT_DATE', // 契約前
	 *                            2 => 'COALESCE(Purchase.contract_start_date <= CURRENT_DATE, TRUE) AND COALESCE(Purchase.contract_end_date >= CURRENT_DATE, TRUE)', // 契約中
	 *                            3 => 'Purchase.contract_end_date < CURRENT_DATE', // 契約満了
	 *                            4 => array('Purchase.contract_start_date' => null, 'Purchase.contract_end_date' => null), // 定めなし
	 *                    )),
	 *
	 *            ),
	 *    );
	 *
	 * @var array
	 */
	public $criteria = array();

	/**
	 * 検索のデフォルト値。
	 *
	 * 検索項目の値が渡されなかった場合は、ここで指定された値が渡されたものとして振舞う。
	 *
	 * 例:
	 *    'Nicosys.Search' => array(
	 *
	 *            'criteria' => array(
	 *                    // 独自条件（チェックが入らなかった場合のみ）
	 *                    'include_archive' => array('expression', array(
	 *                            0 => array('Purchase.archive' => false),
	 *                    )),
	 *            ),
	 *
	 *            'defaults' => array(
	 *                    // 権限によってチェックボックスが検索画面に表示されない場合や、チェックされない場合でも、値 0　が送信されたと見なす
	 *                    'include_archive' => 0,
	 *            ),
	 *
	 *    );
	 *
	 * @var array
	 */
	public $defaults = array();

	/**
	 * 検索に使ったパラメータをリクエストの query 配列に残すかどうか。
	 *
	 * 既定ではパラメータは削除される。
	 *
	 * @var boolean
	 */
	public $stayQuery = false;

	/**
	 * 検索に使ったパラメータからリクエストの data 配列を生成するかどうか。
	 *
	 * 既定の false では何も保存されない。
	 * true ではパラメータはコントローラの既定のモデル名をキーとして data 配列に格納される。
	 * 文字列を指定すると既定のモデル名の代わりに指定したキーを使って data 配列に格納される。
	 *
	 * @var boolean|string
	 */
	public $populateData = false;

	public $components = array(
		'Nicosys.Base64Url',
		'Nicosys.SQL',
	);

	// 条件ビルダーのキャッシュ
	private $_builders = null;

	/**
	 * 入力に基づく条件を作成、または現在の条件を引き継いで paginate 検索を実行する。
	 */
	public function paginate($name = null) {

		$controller = $this->_Collection->getController();

		// 検索条件の取得
		$query = $this->populate();

		// 条件作成
		$conditions = $this->build($query);

		// PaginatorComponent が無ければ 1.x 方式の記法としてロードする
		if (!isset($controller->Paginator)) {
			$controller->Paginator = $controller->Components->load('Paginator', $controller->paginate);
		}

		try {

			// 検索
			return $controller->Paginator->paginate($name, $conditions);

		} catch (NotFoundException $e) {

			// 削除後元の位置に戻った場合、ページが 1 つ超える可能性があるので対処する
			$page = $controller->request->params['named']['page'];
			if ($page <= 1) {
				throw $e;
			}

			--$controller->request->params['named']['page'];
			return $controller->Paginator->paginate($name, $conditions);


		}
	}

	/**
	 * 現在の条件式を返す。
	 */
	public function getQuery() {

		$controller = $this->_Collection->getController();

		// 名前付きパラメータがあれば
		if (!empty($controller->request->named[$this->named])) {

			// 名前付きパラメータから検索条件を復元
			parse_str($this->Base64Url->decode($controller->request->named[$this->named]), $query);

			return $query;

		}

		return array();

	}

	/**
	 * 新しい検索条件があれば保存し、名前つきパラメータに保持した条件を復元して返す。
	 */
	public function populate() {

		$controller = $this->_Collection->getController();

		$query = array();

		// 検索条件があれば
		if (!empty($controller->request->query)) {

			// 新規検索: パラメータを名前付きパラメータに保持
			$query = array_intersect_key((array)$controller->request->query, $this->criteria);
			if (!$this->stayQuery) {
				$controller->request->query = array_diff_key((array)$controller->request->query, $query);
			}
			$controller->request->params['named'][$this->named] = $this->Base64Url->encode(http_build_query($query));

		} // 名前付きパラメータがあれば
		else if (!empty($controller->request->named[$this->named])) {

			// 名前付きパラメータから検索条件を復元
			parse_str($this->Base64Url->decode($controller->request->named[$this->named]), $query);

		}

		// data を生成
		if ($this->populateData) {
			$key = $this->populateData === true ? $controller->{$controller->modelClass}->alias : $this->populateData;
			$controller->request->data[$key] = $query;
		}

		return $query;

	}


	/**
	 * 入力データより検索条件を生成
	 *
	 * @param array $query    検索フォームの入力値
	 * @param array $criteria フィールド毎の条件配列
	 * @param array $query    入力されない場合の既定値
	 *
	 * @return mixed $conditions 配列
	 */
	public function build($query = array(), $criteria = array(), $defaults = array()) {

		if (!empty($this->criteria)) {
			$criteria += $this->criteria;
		}
		if (!empty($this->defaults)) {
			$defaults += $this->defaults;
		}

		// 条件ビルダー
		if ($this->_builders === null) {

			$sql = $this->SQL;

			$this->_builders = array(

				// 部分一致
				'contains'   => function ($field, $value) use ($sql) {
						return array("$field LIKE ? COLLATE utf8_unicode_ci" => '%' . $sql->quoteLike($value) . '%');
					},

				// 値によって異なる条件式
				'expression' => function ($options, $value) {
						if (!isset($options[$value])) {
							return null;
						}
						return $options[$value];
					},

			);

		}

		// 入力値を用意

		// 既定値をマージ
		$query = array_merge($defaults, $query);

		// 検索条件を作成
		$conditions = array();

		// 用意された条件を順に探索
		foreach ($criteria as $entry => $criterion) {

			// 値の指定がある？
			if (!isset($query[$entry])) {
				continue;
			}

			// 条件を作成
			$condition = $this->_build($criterion, $query[$entry]);
			if (!empty($condition)) {
				$conditions[] = $condition;
			}

		}

		return $conditions;

	}

	private function _build($criterion, $value) {

		// 値が配列か？
		if (is_array($value)) {

			// 空？
			if (empty($value)) {
				return array();
			}

			// 複数値の場合、各値について条件式を作成し、OR で結合する

			$or = array();

			// 個別に条件作成
			foreach ($value as $_) {
				$c = $this->_build($criterion, $_);
				if (!empty($c)) {
					$or[] = $c;
				}
			}

			return array('OR' => $or);

		}

		// 単一値

		// 値が空ではない？
		$value = trim($value);
		if ($value === '') {
			return array();
		}

		if (is_array($criterion)) {

			// 複雑条件
			return $this->_builders[$criterion[0]]($criterion[1], $value);

		}

		// 単純条件
		return array($criterion => $value);

	}

}
