<?php
/**
 * $Id: BootstrapFormHelper.php 16 2014-02-25 08:57:18Z h.ushito@nicosys.jp $
 *
 * @copyright (c) 2013 Nicosys Co. Ltd.
 */

App::uses('NamedFieldFormHelper', 'Nicosys.View/Helper');

/**
 * Bootstrap 用にカスタマイズされた Form ヘルパー。
 */
class BootstrapFormHelper extends NamedFieldFormHelper {

	/**
	 * Bootstrap に合わせた形式で INPUT 出力する
	 *
	 * 追加オプション:
	 * 	bootstrap: Bootstrap の出力制御を行う配列
	 *  	prepend: 	input の前に連結する add-on テキストやボタンの HTML を指定。コンテナの div には input-prepend クラスが自動的に追加される。
	 *  	append: 	input の後に連結する add-on テキストやボタンの HTML を指定。コンテナの div には input-append クラスが自動的に追加される。
	 *		controls:	false を指定すると controls クラスを持つ div を出力しない。
	 *		group: 		false を指定すると、最外郭の div に control-group クラスを追加しない。
	 *
	 * <pre>
	 * &lt;div class="control-group"&gt;
	 * &#x20;   &lt;!-- before --&gt;
	 * &#x20;   &lt;label class="control-label"&gt;ラベル&lt;/label&gt;
	 * &#x20;   &lt;div class="controls"&gt;
	 * &#x20;       &lt;!-- between --&gt;
	 * &#x20;       &lt;input type="text" id="HogeHoge" name="data[Hoge][hoge]" value="hoge" /&gt;
	 * &#x20;       &lt;!-- after --&gt;
	 * &#x20;       &lt;div class="help-block"&gt;エラー&lt;/div&gt;
	 * &#x20;   	&lt;!-- afterError--&gt;
	 * &#x20;   &lt;/div&gt;
	 * &lt;/div&gt;
	 * </pre>
	 * <pre>
	 * &lt;div class="control-group"&gt;
	 * &#x20;   &lt;!-- before --&gt;
	 * &#x20;   &lt;div class="controls"&gt;
	 * &#x20;   	&lt;label class="checkbox"&gt;
	 * &#x20;           &lt;input type="hidden" id="HogeHoge_" name="data[Hoge][hoge]" value="0" /&gt;
	 * &#x20;           &lt;input type="checkbox" id="HogeHoge" name="data[Hoge][hoge]" value="1" checked="checked" /&gt;
	 * &#x20;           &lt;!-- between --&gt;
	 * &#x20;           ラベル
	 * &#x20;       &lt;/label&gt;
	 * &#x20;       &lt;!-- after --&gt;
	 * &#x20;       &lt;div class="help-block"&gt;エラー&lt;/div&gt;
	 * &#x20;   	&lt;!-- afterError--&gt;
	 * &#x20;   &lt;/div&gt;
	 * &lt;/div&gt;
	 * </pre>
	 *
	 * @see FormHelper::input
	 */
	public function input($fieldName, $options = array()) {

		$this->setEntity($fieldName);
		$options = $this->_parseOptions($options);

// Bootstrap
		$bootstrap = $this->_extractOption('bootstrap', $options, array());
		unset($options['bootstrap']);
		if ($options['type'] === 'checkbox') {
			$boostrapLabel = $this->_getInputLabelText($fieldName, $options);;
		}
// /Bootstrap

		$divOptions = $this->_divOptions($options);
		unset($options['div']);

		if ($options['type'] === 'radio' && isset($options['options'])) {
			$radioOptions = (array)$options['options'];
			unset($options['options']);
		}

		$label = $this->_getLabel($fieldName, $options);
		if ($options['type'] !== 'radio') {
			unset($options['label']);
		}

		$error = $this->_extractOption('error', $options, null);
		unset($options['error']);

		$errorMessage = $this->_extractOption('errorMessage', $options, true);
		unset($options['errorMessage']);

		$selected = $this->_extractOption('selected', $options, null);
		unset($options['selected']);

		if ($options['type'] === 'datetime' || $options['type'] === 'date' || $options['type'] === 'time') {
			$dateFormat = $this->_extractOption('dateFormat', $options, 'MDY');
			$timeFormat = $this->_extractOption('timeFormat', $options, 12);
			unset($options['dateFormat'], $options['timeFormat']);
		}

		$type = $options['type'];
		$out = array('before' => $options['before'], 'label' => $label, 'between' => $options['between'], 'after' => $options['after']);
		$format = $this->_getFormat($options);

		unset($options['type'], $options['before'], $options['between'], $options['after'], $options['format']);

		$out['error'] = null;
		if ($type !== 'hidden' && $error !== false) {
			$errMsg = $this->error($fieldName, $error);
			if ($errMsg) {
				$divOptions = $this->addClass($divOptions, 'error');
				if ($errorMessage) {
					$out['error'] = $errMsg;
				}
			}
		}

		if ($type === 'radio' && isset($out['between'])) {
			$options['between'] = $out['between'];
			$out['between'] = null;
		}
		$out['input'] = $this->_getInput(compact('type', 'fieldName', 'options', 'radioOptions', 'selected', 'dateFormat', 'timeFormat'));

// Bootstrap

		// input-append, input-prepend
		$attachedClasses = '';
		if (!empty($bootstrap['prepend'])) {
			$attachedClasses .= ' input-prepend';
			$out['input'] = $bootstrap['prepend'] . $out['input'];
		}
		if (!empty($bootstrap['append'])) {
			$attachedClasses .= ' input-append';
			$out['input'] .= $bootstrap['append'];
		}
		if (!empty($attachedClasses)) {
			$out['input'] = $this->Html->div(ltrim($attachedClasses), $out['input']);
		}

		$out['bootstrap-controls-begin'] = '<div class="controls">';
		$out['bootstrap-controls-end'] = '</div>';
		if ($type === 'checkbox') {
			$out['bootstrap-checkbox-label-begin'] = '<label class="checkbox" for="' . (isset($options['id']) ? $options['id'] : $this->domId($fieldName)). '">';
			$out['bootstrap-checkbox-label'] = $boostrapLabel;
			$out['bootstrap-checkbox-label-end'] = '</label>';
		}

		$controls = Hash::get($bootstrap, 'controls');
		if ($controls === false) {
			$out['bootstrap-controls-begin'] = null;
			$out['bootstrap-controls-end'] = null;
		}
		$group = Hash::get($bootstrap, 'group');
		if ($group !== false) {
			$divOptions = $this->addClass($divOptions, 'control-group');
		}

// /Bootstrap

		$output = '';
		foreach ($format as $element) {
			$output .= $out[$element];
		}

		if (!empty($divOptions['tag'])) {
			$tag = $divOptions['tag'];
			unset($divOptions['tag']);
			$output = $this->Html->tag($tag, $output, $divOptions);
		}
		return $output;
	}

	protected function _getFormat($options) {
		if ($options['type'] === 'hidden') {
			return array('input');
		}
		if (is_array($options['format']) && in_array('input', $options['format'])) {
			return $options['format'];
		}
		if ($options['type'] === 'checkbox') {
			return array('before', 'bootstrap-controls-begin', 'bootstrap-checkbox-label-begin', 'input', 'between', 'bootstrap-checkbox-label', 'bootstrap-checkbox-label-end', 'after', 'error', 'bootstrap-controls-end');
		}
		return array('before', 'label', 'bootstrap-controls-begin', 'between', 'input', 'after', 'error', 'bootstrap-controls-end');
	}

	protected function _inputLabel($fieldName, $label, $options) {
		if (!is_array($label)) {
			$label = array('text' => $label);
		}
		$label = $this->addClass($label, 'control-label');
		return parent::_inputLabel($fieldName, $label, $options);
	}

	protected function _getInputLabelText($fieldName = null, $options = null) {

		if ($fieldName === null) {
			$fieldName = implode('.', $this->entity());
		}

		$labelText = null;
		if (isset($options['label'])) {
			$label = $options['label'];
			if (is_array($label)) {
				$labelText = null;
				if (isset($label['text'])) {
					$labelText = $label['text'];
					unset($label['text']);
				}
			} else {
				$labelText = $label;
			}
		}

		if ($labelText === null) {
			if (strpos($fieldName, '.') !== false) {
				$fieldElements = explode('.', $fieldName);
				$labelText = array_pop($fieldElements);
			} else {
				$labelText = $fieldName;
			}
			if (substr($labelText, -3) === '_id') {
				$labelText = substr($labelText, 0, -3);
			}
			$labelText = __(Inflector::humanize(Inflector::underscore($labelText)));
		}

		return $labelText;

	}

	public function radio($fieldName, $options = array(), $attributes = array()) {

		// override $this->Html->useTag
		$hook = new PropertyHook($this, 'Html', new BootstrapHtmlHelperProxy($this->Html));

		return parent::radio($fieldName, $options, $attributes);

	}


	/**
	 * エラーブロックを出力。
	 * 既定で help-block クラスを指定。
	 *
	 * @see FormHelper::error()
	 */
	public function error($field, $text = null, $options = array('class' => 'help-block')) {
		return parent::error($field, $text, $options);
	}

	/**
	 * アイコンの指定とリンクの生成が可能、
	 *
	 * 追加オプション:
	 * 	bootstrap-icon: Bootstrap のアイコンクラスを指定
	 *  bootstrap-href: リンク先の URL。これを指定した場合ボタンの代わりにリンクを生成。
	 *
	 * @see FormHelper::button()
	 */
	public function button($title, $options = array()) {

		if (!empty($options['bootstrap-icon'])) {
			if (!isset($options['escape']) || $options['escape']) {
				$title = h($title);
				$options['escape'] = false;
			}
			if (!empty($title)) {
				$title = ' ' . $title;
			}
			$title = $this->Html->tag('i', '', array('class' => $options['bootstrap-icon'])) . $title;
			unset($options['bootstrap-icon']);
		}
		if (!empty($options['bootstrap-href'])) {
			// link instead of button
			$url = $options['bootstrap-href'];
			unset($options['bootstrap-href']);
			return $this->Html->link($title, $url, $options);
		}
		return parent::button($title, $options);

	}

}

/* プロパティを一時的に置換するスコープを作る */
final class PropertyHook {
	private $object;
	private $property;
	private $original;

	public function __construct($object, $property, $value) {
		$this->object = $object;
		$this->property = $property;
		$this->original = $object->$property;
		$object->$property = $value;
	}
	public function __destruct() {
		$this->object->{$this->property} = $this->original;
	}
}

/* useTag のみをフックし、それ以外を元のオブジェクトにバイパスするプロキシ */
class BootstrapHtmlHelperProxy {

	private $helper;

	public function useTag($tag) {
		$result = call_user_func_array(array($this->helper, 'useTag'), func_get_args());

		if ($tag === 'radio') {
			$result = '<div class="radio">'. $result . '</div>';
		}
		return $result;
	}

	public function __construct(HtmlHelper $helper) {
		$this->helper = $helper;
	}

	public function __call($name, array $arguments) {
		return call_user_func_array(array($this->helper, $name), $arguments);
	}

	public function __set($name, $value) {
		$this->helper->$name = $value;
	}

	public function __get($name) {
		return $this->helper->$name;
	}

	public function __isset($name) {
		return isset($this->helper->$name);
	}

	public function __unset($name) {
		unset($this->helper->$name);
	}

}
