<?php
/**
 * $Id: environment.php 4 2014-09-16 09:51:46Z h.ushito@nicosys.jp $
 *
 * @copyright (c) 2013 Nicosys Co. Ltd.
 */

//----------------------------------------------------------------------
// 環境の自動切り替え
//----------------------------------------------------------------------

// 環境変数 CAKE_ENV が定義されていればそれを使う
$environment = env('CAKE_ENV');

if (empty($environment)) {

	// なければ環境の検出

	// 開発: リクエストホスト名が localhost, 127.0.0.1 または devel という語を含む
	// テスト: リクエストホスト名が test という語を含む
	// 本番: 上記以外全て

	if (PHP_SAPI === 'cli') {

		// コンソールの場合、機械のホスト名を取得
		$serverName  = php_uname('n');

		// ドットを含まないホスト名の場合、
		// Windows 開発機によるローカル開発環境とみなす
		if (strpos($serverName, '.') === false) {
			$serverName = 'localhost';
		}

	} else {

		// 通常のリクエストの場合、環境変数からリクエストホスト名を得る
		$serverName = env('SERVER_NAME');

	}

	if ($serverName === 'localhost' || $serverName === '127.0.0.1' || strpos($serverName, 'devel') !== false) {

		// 開発
		$environment = 'development';

	} else if (strpos($serverName, 'test') !== false) {

		// テスト
		$environment = 'test';

	} else {

		// 本番
		$environment = 'production';

	}

	unset ($serverName);

}

Configure::write('environment', $environment);

// 環境に応じて、デバッグレベルを切り替える

if ($environment == 'development') {
	Configure::write('debug', 2);
} else if ($environment == 'test') {
	Configure::write('debug', 0);
} else {
	Configure::write('debug', 0);
}

unset ($environment);
