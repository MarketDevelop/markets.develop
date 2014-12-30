<?php
/**
 * $Id: AutoTitleComponent.php 39 2014-10-07 06:44:59Z h.ushito@nicosys.jp $
 *
 * @copyright (c) 2013 Nicosys Co. Ltd.
 */

App::uses('Component', 'Component');

/**
 * ビュー名とコントローラーのモデルのテーブルコメントを使って、自動的に title_for_layout を生成する。
 */
class AutoTitleComponent extends Component {

	/**
	 * ビュー毎のタイトル
	 *
	 * ビュー名をキー、タイトルを値とする連想配列。
	 * これが定義されていれば優先して使用する。
	 *
	 * @var array
	 */
	public $titles = array();

	/**
	 * ビュー別の、パターンに沿ったタイトル
	 *
	 * ビュー名をキー、一つの %s を含む printf 書式を値とする連想配列。
	 * %s は $label またはテーブルコメントに置換される。
	 *
	 * @var array
	 */
	public $formats = array();

	/**
	 * モデルの自然な名前を定義する。
	 * 定義されていなければテーブルコメントを利用する。
	 *
	 * @var array
	 */
	public $label = null;

	// エラーコントローラー
	public $error = array();

	// 既定の書式
	static private $DEFAULT_FORMATS = array(
		'index' => '%s一覧',
		'add'   => '%s登録',
		'edit'  => '%s編集',
		'show'  => '%s詳細',
		'view'  => '%s詳細',
	);

	public function initialize(Controller $controller) {
		$this->formats += self::$DEFAULT_FORMATS;
		parent::initialize($controller);
	}

	public function beforeRender(Controller $controller) {

		// タイトルが未設定の場合
		if (isset($controller->viewVars['title_for_layout'])) {
			return;
		}

		// ビュー名を取得
		$view = $controller->view;

		// ビュー毎のタイトルの設定があれば
		if (isset($this->titles[$view])) {

			// それを使う
			$controller->set('title_for_layout', $this->titles[$view]);
			return;

		}

		// ビューに対する書式が存在し
		if (isset($this->formats[$view])) {

			// ラベルを取得（推測なし）
			$label = $this->getLabel(false);

			// あれば
			if (!empty($label)) {

				// ビュー別命名規則に沿って名前をつける
				$title = sprintf($this->formats[$view], $label);
				$controller->set('title_for_layout', $title);
				return;

			}

		}

	}

	public function getLabel($guess = true) {

		// 自然な名前が定義されている場合
		if (!empty($this->label)) {
			return $this->label;
		}

		$controller = $this->_Collection->getController();

		// ビュー名を取得
		$view = $controller->view;

		// コントローラーに対応する既定のモデルがあり
		if (!empty($controller->modelClass) && !empty($controller->uses)) {

			// モデルがテーブル参照している場合
			$model = $controller->{$controller->modelClass};
			if ($model->useTable) {

				// 主モデルの名前とビューのパターンからタイトルを合成

				// テーブルのコメントを取得
				$dataSource = $model->getDataSource();
				if ($dataSource) {

					$comment = $dataSource->query('SELECT TABLE_COMMENT FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?',
						array($dataSource->getSchemaName(), $model->useTable));
					$comment = Hash::get($comment, '0.TABLES.TABLE_COMMENT');

					// コメントをラベルとする
					if (!empty($comment)) {

						// キャッシュ
						$this->label = $comment;

						return $comment;
					}

				}

				// テーブル名を読みやすく変換してローカライズしたものを返す
				return $guess ? __(Inflector::humanize($model->useTable)) : null;

			}

		}

		// どうしようもない
		return null;

	}

}
