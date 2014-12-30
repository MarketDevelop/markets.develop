<?php
/**
 * $Id: Base64UrlComponent.php 3 2013-08-30 05:44:51Z h.ushito@nicosys.jp $
 *
 * @copyright (c) 2012 Nicosys Co. Ltd.
 */

App::uses('Component', 'Component');

// ユーティリティ
class Base64UrlComponent extends Component {

	public function encode($value) {
		return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
	}

	public function decode($value) {
		return base64_decode(str_pad(strtr($value, '-_', '+/'), strlen($value) % 4, '=', STR_PAD_RIGHT));
	}

}
