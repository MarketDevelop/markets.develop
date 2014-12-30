<?php
/**
 * $Id: tags.php 6 2014-09-22 08:17:09Z h.ushito@nicosys.jp $
 *
 * @copyright (c) 2013 Nicosys Co. Ltd.
 */

//----------------------------------------------------------------------
// TEXTAREA 要素のパッチ
//----------------------------------------------------------------------

$config['tags'] = array(
		'textarea' => '<textarea name="%s"%s>' . "\n" . '%s</textarea>',
);
