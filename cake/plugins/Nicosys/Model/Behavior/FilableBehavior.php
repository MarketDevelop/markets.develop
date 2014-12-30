<?php
/**
 * $Id: FilableBehavior.php 18 2014-03-03 04:10:57Z h.ushito@nicosys.jp $
 *
 * @copyright (c) 2013 Nicosys Co. Ltd.
 */

App::uses('FileValidatableBehavior', 'Nicosys.Model/Behavior');

// v1.1: 既定の保存先ディレクトリを ../files に変更（既定で公開ディレクトリに配置されないように）
// v1.0

/**
 * モデルの仮想フィールドに単一のファイルを関連づけ、一緒に管理するビヘイビア。
 *
 * # 基本的な使い方
 *
 * 	1. webroot ディレクトリ（WWW_ROOT: index.php があるディレクトリ）と同階層に files ディレクトリを作成する。
 * 	2. ファイルを関連づけたいモデルに actsAs でビヘイビアを読み込む。
 * 	3. ビューに multipart/form-data を有効にした form を作成し、その中に 'file' という名前のファイルタイプの input 要素を作成する。
 *
 * 	モデルの save を呼び出すと、files ディレクトリ配下にファイルが保存される。
 *
 * 	- ファイルのサイズや種別に対して制限を行う場合は、'file' に対する $validate ルールを追加する。
 * 	- ファイルを削除するかどうか選択させる場合は、'delete_file' という名前のチェックボックスタイプの input 要素を作成する。
 * 	- ファイル名をデータベースに保存したい場合は、テーブルに 'file_name' フィールドを追加する。
 * 	- ファイルサイズをデータベースに保存したい場合は、テーブルに 'file_size' フィールドを追加する。
 * 	- ファイル受信日時をデータベースに保存したい場合は、テーブルに 'file_mtime' フィールドを追加する。
 *
 * # 動作
 *
 * 	デフォルトでは、'file' という仮想フィールドが 1 つだけ設定されている。
 *
 * 	仮想フィールドの名前でファイルがアップロードされると
 * 	モデル名と ID、仮想フィールド名に基いてパスを生成し、その場所に自動的にファイルを保存する。
 *
 * 	以下の名前の実フィールドがテーブルに存在する場合、その内容も自動的に更新する。
 *
 * 		[仮想フィールド名] _name: 	クライアントがアップロードしたファイル名を保持する。
 * 		[仮想フィールド名] _size: 	ファイルのサイズを保持する。
 * 		[仮想フィールド名] _mtime: ファイルがアップロードされた日時を保持する。
 *
 * 	また、save 時に、 delete_[仮想フィールド名] というフィールドに何かしらの値が送信された場合、ファイルを削除する。
 * 	典型的には、ファイルを削除するためのチェックボックスを配置する際に使う。
 *
 * # 検証ルールの増強
 *
 * 	モデルにおいて以下の検証ルールが使えるようになる。(FileValidatableBehavior より)
 *
 * 		validateFile:			ファイルが必須かどうか検証する。引数は true/false。
 * 		validateFileExtension:	ファイルの拡張子を検証する。引数は受け入れる拡張子の配列。拡張子は小文字で指定する。拡張子にドットは含まない。
 * 		validateFileSize:		ファイルサイズが指定以下かどうか検証する。引数は最大ファイルサイズ。
 *
 * # ビヘイビアの設定
 *
 * 	$actsAs でロードする際に、細かい設定を追加することができる。
 * 	設定は連想配列で指定し、以下のようなキーをとる。
 * 	設定は必要なキーや値だけを定義することが可能で、空でも構わない。
 *
 *		'fields'	ファイルを関連づける仮想フィールド名の配列。
 *					ファイルは仮想フィールドと 1 対 1 で関連付けられる。
 *					このキーに複数の仮想フィールド名を指定することで、複数のファイルをモデルと一緒に扱うことができる。
 *					既定値は array('file')。
 *
 *		'base'		ファイルを格納ベースディレクトリ。 webroot ディレクトリからの相対パス、または絶対パスで指定する。
 *					既定ではベースディレクトリ内にモデル毎のサブディレクトリが作成されるため
 *					複数のモデルで同じ base 設定を使っても問題はない。
 *					既定値は '../files'。
 *
 *		'pattern'	ベースディレクトリ配下にファイルを保存するパスの命名規則を指定する。
 *					値は保存先ファイル名を表す sprintf パターンか、callable で、順に以下の引数が渡される。
 *
 *						1:  モデルのテーブル名
 *						2: 	仮想フィールド名
 *						3: 	レコードの ID
 *						4: 	レコードの ID を 10000 で割った商。
 *
 *					パターンや callable の戻り値は base からの相対パスとして扱われる。 パス区切り記号は / で良い。
 *					基本的にファイルの格納場所を細かく制御する必要がある場合を除き、変更する必要はない。
 *					既定値は '%1$s/%4$04d/%3$08d/%2$s'。
 *
 * # 例
 *
 * 		// file フィールドでアップロードしたファイルを  [WWW_ROOT]/../files/モデルテーブル名/[id]/file に格納し
 * 		// file_name, file_size, file_mtime を自動的に更新（あれば）
 *		public $actsAs = array(
 *				'Filable',
 *		);
 *
 * 		// 上と同じ（既定の設定を明示した場合）
 *		public $actsAs = array(
 *				'Filable' => array(
 *						'fields' => array('file'),
 *						'base' => '../files',
 *				),
 *		);
 *
 * 		// icon, picture の 2 つのフィールドでアップロードしたファイルを
 * 		// /var/files/モデルテーブル名/[id]/icon,
 * 		// /var/files/モデルテーブル名/[id]/picture に格納し
 * 		// icon_name, icon_size, icon_mtime, picture_name, picture_size, picture_mtime を自動的に更新（あれば）
 *		public $actsAs = array(
 *				'Filable' => array(
 *						'fields' => array('icon', 'picture'),
 *						'base' => '/var/files',
 *				),
 *		);
 */
class FilableBehavior extends FileValidatableBehavior {

	// 設定のデフォルト値
	static private $_DEFAULTS = array(

			// ファイルを関連づける仮想フィールド名
			'fields' => array('file'),

			// 格納ベースディレクトリ。 webroot ディレクトリからの相対パス、または絶対パス
			'base' => '../files',

			// 保存先ファイル名を表す sprintf パターン
			// 1: モデルのテーブル名
			// 2: 仮想フィールド名
			// 3: レコードの ID
			// 4: レコードの ID を 10000 で割った商
			'pattern' => '%1$s/%4$04d/%3$08d/%2$s',

	);

	//----------------------------------------------------------------------
	// コールバック
	//----------------------------------------------------------------------

	// ビヘイビアにモデルを関連づけ/設定の変更
	public function setup(Model $model, $config = array()) {

		// 初期値をマージ
		$config += self::$_DEFAULTS;

		if (!is_array($config['fields'])) {
			$config['fields'] = array($config['fields']);
		}
		if (empty($config['fields'])) {
			throw new InvalidArgumentException('No fields specified.');
		}

		// パスを正規化
		if (empty($config['base'])) {
			$config['base'] = '.';
		}
		if ($config['base'][0] !== '/' // Linux
				&& !preg_match('/^[a-zA-Z]:\\\\/', $config['base'])	// Windows Drive
				&& substr($config['base'], 0, 2) !== '\\\\'			// Windows UNC
		) {
			$path = WWW_ROOT . str_replace('/', DS, $config['base']);
			$config['_base_absolute'] = realpath($path);
		} else {
			$path = $config['base'];
			$config['_base_absolute'] = $path;
		}

		if (!file_exists($config['_base_absolute'])) {
			throw new InvalidArgumentException("Directory '$path' not found.");
		}

		// 設定を保存
		$this->settings[$model->alias] = $config;

	}

	// ビヘイビアからモデルを解除
	public function cleanup(Model $model) {
		unset($this->settings[$model->alias]);
	}

	// 保存前のコールバック
	public function beforeSave(Model $model, $options = array()) {

		// ビヘイビアが有効か？
		if (!isset($this->settings[$model->alias])) {
			return true;
		}
		$config = $this->settings[$model->alias];

		// 情報整理

		// アップロードされたファイル
		$uploads = $this->_detectUploadedFiles($model);

		// 削除指示があるフィールド
		$deletes = $this->_detectDeletedFields($model);

		// アップロード反映の準備
		foreach ($uploads as $field => $file) {

			// 関連フィールドの準備
			$model->data[$model->alias] = array_merge($model->data[$model->alias], array(
					$field . '_name'  => $file['name'],
					$field . '_size'  => $file['size'],
					$field . '_mtime' => date('Y-m-d H:i:s', filemtime($file['tmp_name'])),
			));

			// 自動的に追加するフィールドをホワイトリストに追加
			$this->_addToWhitelist($model, $field . '_name');
			$this->_addToWhitelist($model, $field . '_size');
			$this->_addToWhitelist($model, $field . '_mtime');

			// 削除とアップロードが被った場合、アップロード優先（置換)
			unset($deletes[$field]);

		}

		// 削除反映の準備
		foreach ($deletes as $field => $dummy) {

			// 関連フィールドの消去
			$model->data[$model->alias] = array_merge($model->data[$model->alias], array(
					$field . '_name'  => null,
					$field . '_size'  => null,
					$field . '_mtime' => null,
			));

			// 自動的に追加するフィールドをホワイトリストに追加
			$this->_addToWhitelist($model, $field . '_name');
			$this->_addToWhitelist($model, $field . '_size');
			$this->_addToWhitelist($model, $field . '_mtime');

		}

		return true;

	}

	// 保存後のコールバック
	public function afterSave(Model $model, $created, $options = array()) {

		// ビヘイビアが有効か？
		if (!isset($this->settings[$model->alias])) {
			return true;
		}
		$config = $this->settings[$model->alias];

		// アップロードされたファイル
		$uploads = $this->_detectUploadedFiles($model);

		// 削除指示があるフィールド
		$deletes = $this->_detectDeletedFields($model);

		// アップロード反映
		foreach ($uploads as $field => $file) {

			// 格納ファイル名を用意
			$path = $this->getFilePath($model, null, $field);

			// なければディレクトリ作成
			clearstatcache();
			if (!file_exists(dirname($path))) {
				mkdir(dirname($path), 0777, true);
			}

			// 既存ファイルがあれば削除
			if (file_exists($path)) {
				if (!unlink($path)) {
					// XXX しょうがない
					$this->log("Cannot delete '$path' on FilableBehavior::afterSave.");
				}
			}

			// 保存
			if (!move_uploaded_file($file['tmp_name'], $path)) {
				if (!copy($file['tmp_name'], $path)) {
					// XXX しょうがない
					$this->log("Cannot move uploaded file '$file[tmp_name]' to '$path'.");
				}
			}

			// 削除とアップロードが被った場合、アップロード優先（置換)
			unset($deletes[$field]);

		}

		// 削除反映
		foreach ($deletes as $field => $dummy) {

			// 格納ファイル名を用意
			$path = $this->getFilePath($model, null, $field);

			clearstatcache();

			// ファイルがあれば削除
			if (file_exists($path)) {
				if (!unlink($path)) {
					// XXX しょうがない
					$this->log("Cannot delete '$path' on FilableBehavior::afterSave.");
				}
			}

		}

		return true;

	}

	// 削除後コールバック
	public function afterDelete(Model $model) {

		// ビヘイビアが有効か？
		if (!isset($this->settings[$model->alias])) {
			return true;
		}
		$config = $this->settings[$model->alias];

		foreach ($config['fields'] as $field) {

			// 格納ファイル名を用意
			$path = $this->getFilePath($model, null, $field);

			clearstatcache();

			// ファイルがあれば削除
			if (file_exists($path)) {
				if (!unlink($path)) {
					// XXX しょうがない
					$this->log("Cannot delete '$path' on FilableBehavior::afterDelete.");
				}
			}

		}

		return true;

	}

	//----------------------------------------------------------------------
	// 拡張メソッド
	//----------------------------------------------------------------------

	/**
	 * ファイルの保存パスを取得 。実在確認はしない
	 *
	 * @param Model $model モデルへの参照が自動的に渡される。
	 * @param integer $id レコードの ID。省略時は現在の ID。
	 * @param string $field ファイルフィールド名。省略時は最初のファイルフィールド名。（通常は 'file'）
	 * @throws InvalidArgumentException
	 * @return NULL|string ファイル名。失敗時は null。
	 */
	public function getFilePath(Model $model, $id = null, $field = null) {

		// ビヘイビアが有効か？
		if (!isset($this->settings[$model->alias])) {
			return null;
		}
		$config = $this->settings[$model->alias];

		if ($id === null) {
			$id = $model->id;
		}
		if ($field === null) {
			$field = $config['fields'][0];
		}

		if (!is_numeric($id)) {
			throw new InvalidArgumentException();
		}
		if (empty($field)) {
			throw new InvalidArgumentException();
		}

		// パスを決定
		if (is_string($config['pattern'])) {

			$path = str_replace('/', DS, sprintf($config['pattern'],
					$model->table,
					$field,
					$id,
					floor($id / 10000)
			));

		} else {

			$path = str_replace('/', DS, call_user_func($config['pattern'],
					$model->table,
					$field,
					$id,
					floor($id / 10000)
			));

		}

		return rtrim($config['_base_absolute'], DS) . DS . $path;

	}

	/**
	 * ファイルをレンダリング用に送信。
	 * 公開ディレクトリにファイルを配置していない場合、このメソッドでファイルを送り返す。
	 *
	 * @param Model $model モデルへの参照が自動的に渡される。
	 * @param integer $id レコードの ID。省略時は現在の ID。
	 * @param string $field ファイルフィールド名。省略時は最初のファイルフィールド名。（通常は 'file'）
	 * @param string $mimeType 送信するメディアタイプ。省略時はファイルから自動的に判別する。
	 * @return boolean|number 送信したファイルのサイズ。失敗時は false。
	 */
	public function renderFile(Model $model, $id = null, $field = null, $mimeType = null) {

		// ビヘイビアが有効か？
		if (!isset($this->settings[$model->alias])) {
			return false;
		}
		$config = $this->settings[$model->alias];

		// 格納ファイル名を用意
		$path = $this->getFilePath($model, $id, $field);

		clearstatcache();
		if (!file_exists($path)) {
			header('HTTP/1.0 404 Not Found');
			return false;
		}

		if ($field === null) {
			$field = $config['fields'][0];
		}

		$modified = filemtime($path);

		// メディアタイプの検出
		if (empty($mimeType)) {
			$finfo = new finfo(FILEINFO_MIME_TYPE);
			$mimeType = $finfo->file($path);
		}

		header('Content-Type: ' . $mimeType);
		header('Content-Length: ' . filesize($path));
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $modified) . ' GMT'); // RFC1123 with GMT

		return readfile($path);

	}

	/**
	 * ファイルをダウンロード用に送信。
	 * 公開ディレクトリにファイルを配置していない場合、このメソッドでファイルをダウンロード用に送り返す。
	 *
	 * @param Model $model モデルへの参照が自動的に渡される。
	 * @param integer $id レコードの ID。省略時は現在の ID。
	 * @param string $field ファイルフィールド名。省略時は最初のファイルフィールド名。（通常は 'file'）
	 * @param string $name ファイル名。省略時は フィールド名 '_name' という名前のフィールドがテーブルにあればそれを使う。
	 * @param string $mimeType 送信するメディアタイプ。null を指定するとファイルから自動判別。省略時は 'application/octet-stream'
	 * @return boolean|number 送信したファイルのサイズ。失敗時は false。
	 */
	public function downloadFile(Model $model, $id = null, $field = null, $name = null, $mimeType = 'application/octet-stream') {

		// ビヘイビアが有効か？
		if (!isset($this->settings[$model->alias])) {
			return false;
		}
		$config = $this->settings[$model->alias];

		// 格納ファイル名を用意
		$path = $this->getFilePath($model, $id, $field);

		clearstatcache();
		if (!file_exists($path)) {
			header('HTTP/1.0 404 Not Found');
			return false;
		}

		if ($field === null) {
			$field = $config['fields'][0];
		}

		// 名前がテーブルにあるなら参照する
		if ($name === null && $model->hasField($field . '_name')) {
			$name = $model->field($field . '_name', $id);
			if ($name === false) {
				header('HTTP/1.0 404 Not Found');
				return false;
			}
		}

		$modified = filemtime($path);

		// メディアタイプの検出
		if (empty($mimeType)) {
			$finfo = new finfo(FILEINFO_MIME_TYPE);
			$mimeType = $finfo->file($path);
		}

		if (empty($name)) {
			$name = $field;
		}

		header('Content-Type: ' . $mimeType);
		header('Content-Length: ' . filesize($path));
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $modified) . ' GMT'); // RFC1123 with GMT

		// header('Cache-Control: private, max-age=0, must-revalidate');
		// header('Pragma: public');

		if (preg_match('/[^\\x20-\\x7f]/', $name)) {
			// UTF-8
			header('Content-Disposition: attachment; filename*=UTF-8\'\'' . rawurlencode($name));
		} else {
			header('Content-Disposition: attachment; filename="' . preg_replace('/["\\\\]/', '\\\\1', $name) . '"');
		}

		return readfile($path);

	}

	/**
	 * ファイルを差し替え、または削除する。
	 * アップロード画面ではなく、プログラムでファイルの差し替えや削除が必要な場合に使用する。
	 *
	 * @param Model $model モデルへの参照が自動的に渡される。
	 * @param integer $id レコードの ID。省略時は現在の ID。
	 * @param string $field ファイルフィールド名。省略時は最初のファイルフィールド名。（通常は 'file'）
	 * @param string $sourcePath 差し替えるファイルのパス。ファイルはコピーされる。省略するとファイルを削除する。
	 * @param boolean $keepName true を指定すると、テーブルにファイル名が保存されていればそれを変更しない。
	 * @throws InvalidArgumentException
	 * @return boolean 成否。
	 */
	public function updateFile(Model $model, $id = null, $field = null, $sourcePath = null, $keepName = false) {

		if ($sourcePath === null) {
			return $this->deleteFile($model, $id);
		}

		// ビヘイビアが有効か？
		if (!isset($this->settings[$model->alias])) {
			return false;
		}
		$config = $this->settings[$model->alias];

		if (!file_exists($sourcePath)) {
			throw new InvalidArgumentException('Source file is not found.');
		}

		// 格納ファイル名を用意
		$path = $this->getFilePath($model, $id, $field);

		clearstatcache();

		// なければディレクトリ作成
		if (!file_exists(dirname($path))) {
			mkdir(dirname($path), 0777, true);
		}

		if (!copy($sourcePath, $path)) {
			return false;
		}
		touch($path);

		if ($id === null) {
			$id = $model->id;
		}
		if ($field === null) {
			$field = $config['fields'][0];
		}

		$data = array(
				$model->primaryKey => $id,
				$field . '_size' => filesize($path),
				$field . '_mtime' => date('Y-m-d H:i:s', filemtime($path)),
		);
		if (!$keepName) {
			$data[$field . '_name'] = basename($path);
		}

		if (!$model->save($data)) {
			return false;
		}

		return true;

	}

	/**
	 * ファイルを削除する。メタフィールドがあれば NULL に更新する。
	 * アップロード画面ではなく、プログラムでファイルの削除が必要な場合に使用する。
	 *
	 * @param Model $model モデルへの参照が自動的に渡される。
	 * @param integer $id レコードの ID。省略時は現在の ID。
	 * @param string $field ファイルフィールド名。省略時は最初のファイルフィールド名。（通常は 'file'）
	 * @return boolean
	 */
	public function deleteFile(Model $model, $id = null, $field = null) {

		// ビヘイビアが有効か？
		if (!isset($this->settings[$model->alias])) {
			return false;
		}
		$config = $this->settings[$model->alias];

		// 格納ファイル名を用意
		$path = $this->getFilePath($model, $id, $field);

		if ($field === null) {
			$field = $config['fields'][0];
		}

		if (!$model->save(array(
				$model->primaryKey => $id === null ? $model->id : $id,
				$field . '_name' => null,
				$field . '_size' => null,
				$field . '_mtime' => null,
		))) {
			return false;
		}

		clearstatcache();

		// ファイルがあれば削除
		if (file_exists($path)) {
			if (!unlink($path)) {
				// XXX しょうがない
				$this->log("Cannot delete '$path' on FilableBehavior::deleteFile.");
			}
		}

		return true;

	}

	// アップロードされたファイルのうち、有効なものだけを返す
	private function _detectUploadedFiles(Model $model) {

		if (!isset($this->settings[$model->alias])) {
			return null;
		}
		$config = $this->settings[$model->alias];

		$uploads = array();
		foreach ($config['fields'] as $field) {
			if (isset($model->data[$model->alias][$field])) {
				$file = $model->data[$model->alias][$field];
				if (isset($file['error']) && $file['error'] == UPLOAD_ERR_OK && is_uploaded_file($file['tmp_name'])) {
					$uploads[$field] = $file;
				}
			}
		}

		return $uploads;

	}

	// 削除指示があったフィールドを返す
	private function _detectDeletedFields(Model $model) {

		if (!isset($this->settings[$model->alias])) {
			return null;
		}
		$config = $this->settings[$model->alias];

		$deletes = array();
		foreach ($config['fields'] as $field) {
			if (!empty($model->data[$model->alias]['delete_' . $field])) {
				$deletes[$field] = true;
			}
		}

		return $deletes;
	}

	//----------------------------------------------------------------------
	// 拡張バリデーター
	//----------------------------------------------------------------------

	// アップロード自体が成功しているかどうか
	public function validateFile(Model $model, array $data, $required) {
		$upload = current($data);

		// $required = false の場合、ファイルがアップロードされていなくても OK とする
		if (!$required && (isset($upload['error']) && $upload['error'] == UPLOAD_ERR_NO_FILE)) {
			return true;
		}

		if ((isset($upload['error']) && $upload['error'] == UPLOAD_ERR_OK)
				|| (!empty($upload['tmp_name']) && $upload['tmp_name'] != 'none')) {
			return is_uploaded_file($upload['tmp_name']);
		} else {
			return false;
		}

	}

	// アップロード項目の拡張子がマッチするか
	public function validateFileExtension(Model $model, array $data, $extensions) {
		$upload = current($data);

		// ファイルがアップロードされていなくても OK とする
		if ((isset($upload['error']) && $upload['error'] == UPLOAD_ERR_NO_FILE) ) {
			return true;
		}

		$filename = $upload['name'];
		$extension = pathinfo($filename, PATHINFO_EXTENSION);

		// 拡張子確認
		return in_array(strtolower($extension), (array)$extensions);

	}

	// アップロード項目のサイズが一定値以内か
	public function validateFileSize(Model $model, array $data, $maxSize) {
		$upload = current($data);

		// ファイルがアップロードされていなくても OK とする
		if ((isset($upload['error']) && $upload['error'] == UPLOAD_ERR_NO_FILE) ) {
			return true;
		}

		$fileSize = $upload['size'];

		// ファイルサイズチェック
		return (int)$fileSize <= $maxSize;
	}

}

