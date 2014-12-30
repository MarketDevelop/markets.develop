<?php
/**
 * $Id: Bootstrap3FormHelper.php 37 2014-09-26 08:56:57Z h.ushito@nicosys.jp $
 *
 * @copyright (c) 2013 Nicosys Co. Ltd.
 */

App::uses('NamedFieldFormHelper', 'Nicosys.View/Helper');

/**
 * Bootstrap 3 用にカスタマイズされた Form ヘルパー。
 */
class Bootstrap3FormHelper extends NamedFieldFormHelper {

	public $enableDateTimeMagics = false;
	public $enableDateTimeType = false;

	public function create($model = null, $options = array()) {

		if (!isset($options['role'])) {
			$options['role'] = 'form';
		}

		return parent::create($model, $options);
	}


	/**
	 * Bootstrap に合わせた形式で INPUT 出力する
	 *
	 * 追加オプション:
	 *    prepend:    input の前に連結する add-on テキストやボタンの HTML を指定。コンテナの div には input-group-addon クラスが自動的に追加される。
	 *    append:    input の後に連結する add-on テキストやボタンの HTML を指定。コンテナの div には input-group-addon クラスが自動的に追加される。
	 *    controls:    false を指定すると controls クラスを持つ div を出力しない。
	 *    group:        false を指定すると、最外郭の div に form-group クラスを追加しない。
	 *
	 * <pre>
	 * &lt;div class="form-group"&gt;
	 * &#x20;   &lt;!-- before --&gt;
	 * &#x20;   &lt;label&gt;ラベル&lt;/label&gt;
	 * &#x20;   &lt;div class="controls"&gt;
	 * &#x20;       &lt;!-- between --&gt;
	 * &#x20;       &lt;input type="text" id="HogeHoge" name="data[Hoge][hoge]" value="hoge" /&gt;
	 * &#x20;       &lt;!-- after --&gt;
	 * &#x20;       &lt;div class="help-block"&gt;エラー&lt;/div&gt;
	 * &#x20;    &lt;!-- afterError--&gt;
	 * &#x20;   &lt;/div&gt;
	 * &lt;/div&gt;
	 * </pre>
	 * <pre>
	 * &lt;div class="form-group"&gt;
	 * &#x20;   &lt;!-- before --&gt;
	 * &#x20;   &lt;div class="controls"&gt;
	 * &#x20;    &lt;label class="checkbox"&gt;
	 * &#x20;           &lt;input type="hidden" id="HogeHoge_" name="data[Hoge][hoge]" value="0" /&gt;
	 * &#x20;           &lt;input type="checkbox" id="HogeHoge" name="data[Hoge][hoge]" value="1" checked="checked" /&gt;
	 * &#x20;           &lt;!-- between --&gt;
	 * &#x20;           ラベル
	 * &#x20;       &lt;/label&gt;
	 * &#x20;       &lt;!-- after --&gt;
	 * &#x20;       &lt;div class="help-block"&gt;エラー&lt;/div&gt;
	 * &#x20;    &lt;!-- afterError--&gt;
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

		$bootstrap_prepend = $this->_extractOption('prepend', $options, null);
		$bootstrap_append = $this->_extractOption('append', $options, null);
		$bootstrap_controls = $this->_extractOption('controls', $options, false);
		$bootstrap_group = $this->_extractOption('group', $options, true);

		unset($options['prepend'], $options['append'], $options['controls'], $options['group']);

		if ($options['type'] === 'checkbox') {
			$boostrapLabel = $this->_getInputLabelText($fieldName, $options);;
		}

//		if (!isset($options['type']) || ($options['type'] != 'readonly' && $options['type'] != 'static')) {
		$options = $this->addClass($options, 'form-control');
//		}

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
				$divOptions = $this->addClass($divOptions, 'has-error'); // Bootstrap
				if ($errorMessage) {
					$out['error'] = $errMsg;
				}
			}
		}

		if ($type === 'radio' && isset($out['between'])) {
			$options['between'] = $out['between'];
			$out['between'] = null;
		}
		$out['input'] = $this->_getInput(compact('type', 'fieldName', 'options', 'radioOptions', 'selected',
				'dateFormat', 'timeFormat'));

// Bootstrap

		$out['bootstrap-controls-begin'] = '';
		$out['bootstrap-controls-end'] = '';

		// input-group-addon
		$attachedClasses = '';
		if (!empty($bootstrap_prepend)) {
			$attachedClasses = ' input-group-addon';
			$out['input'] = $bootstrap_prepend . $out['input'];
		}
		if (!empty($bootstrap_append)) {
			$attachedClasses = ' input-group-addon';
			$out['input'] .= $bootstrap_append;
		}
		if (!empty($attachedClasses)) {
			// $out['input'] = $this->Html->tag('span', $out['input'], ['class' => ltrim($attachedClasses)]);
//			$out['bootstrap-controls-begin'] = '<div class="input-group">';
//			$out['bootstrap-controls-end'] = '</div>';
			$bootstrap_controls = 'input-group';
		}

		if ($type === 'checkbox') {
			$out['bootstrap-checkbox-label-begin'] = '<label class="checkbox" for="' . (isset($options['id']) ? $options['id'] : $this->domId($fieldName)) . '">';
			$out['bootstrap-checkbox-label'] = $boostrapLabel;
			$out['bootstrap-checkbox-label-end'] = '</label>';
		}

		if ($bootstrap_controls === false) {
			$out['bootstrap-controls-begin'] = null;
			$out['bootstrap-controls-end'] = null;
		} else if (is_string($bootstrap_controls)) {
			$out['bootstrap-controls-begin'] = '<div class="' . h($bootstrap_controls) . '">';
			$out['bootstrap-controls-end'] = '</div>';
		}

		if ($bootstrap_group === true) {
			$divOptions = $this->addClass($divOptions, 'form-group');
		} else if (is_string($bootstrap_group)) {
			$divOptions = $this->addClass($divOptions, $bootstrap_group);
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

//	protected function _inputLabel($fieldName, $label, $options) {
//		if (!is_array($label)) {
//			$label = array('text' => $label);
//		}
//		$label = $this->addClass($label, 'form-control');
//		return parent::_inputLabel($fieldName, $label, $options);
//	}

	protected function _parseOptions($options) {
		$defaultEmpty = !isset($options['empty']);
		$options = parent::_parseOptions($options);
		if ($defaultEmpty && isset($options['empty'])) {
			// empty
			unset($options['empty']);
		}
		return $options;
	}

	protected function _getInput($args) {

		extract($args);

		if (!$this->enableDateTimeType) {

			if (isset($type)) {
				if ($type == 'date' || $type == 'time' || $type == 'datetime' || $type == 'datetime-local') {
					$options = $this->addClass($options, $type);
					return $this->text($fieldName, $options);
				}
			}

		}

		if (!$this->enableDateTimeMagics) {

			if (isset($type)) {
				if ($type == 'date' || $type == 'time' || $type == 'datetime' || $type == 'datetime-local') {
					return $this->text($fieldName, ['type' => $type] + $options);
				}
			}

		}


//		if (isset($type) && $type == 'static') {
//			return $this->_static($fieldName, $options);
//		}

		return parent::_getInput($args);

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
		$hook = new Property3Hook($this, 'Html', new Bootstrap3HtmlHelperProxy($this->Html));

		return parent::radio($fieldName, $options, $attributes);

	}

//	public function _static($fieldName, $options = array()) {
//
//		$options += array('class' => 'form-control-static');
//		$text = $this->value($fieldName);
//
//		if (array_key_exists('value', $options)) {
//			$text = $options['value'];
//			if (!array_key_exists('escape', $options) || $options['escape'] !== false) {
//				$text = h($text);
//			}
//		}
//
//		return $this->Html->useTag('para', ' class="' . h($options['class']) . '"', $text);
//
//	}
//
//	public function readonly($fieldName, $options = array()) {
//
//		$options = $this->_initInputField($fieldName, $options);
//		$options = $this->addClass($options, 'form-control-static');
//
//		$text = null;
//		$output = '';
//
//		if (array_key_exists('value', $options)) {
//			$text = $options['value'];
//			if (!array_key_exists('escape', $options) || $options['escape'] !== false) {
//				$text = h($text);
//			}
//		}
//
//		$output = $this->hidden($fieldName, array(
//			'id' => $options['id'],
//			'name' => $options['name'],
//			'value' => $options['value'],
//			'secure' => false
//		));
//
//		return $output . $this->Html->useTag('para', ' class="' . h($options['class']) . '"', $text);
//
//	}


	/**
	 * エラーブロックを出力。
	 * 既定で help-block クラスを指定。
	 *
	 * @see FormHelper::error()
	 */
	public function error($field, $text = null, $options = array('class' => 'help-block')) {
		return parent::error($field, $text, $options);
	}

	public function submit($caption = null, $options = array()) {
		$options = $this->addClass($options, 'btn');
		return parent::submit($caption, $options);
	}

	/**
	 * アイコンの指定とリンクの生成が可能、
	 *
	 * 追加オプション:
	 *    bootstrap-icon: Bootstrap のアイコンクラスを指定
	 *  bootstrap-href: リンク先の URL。これを指定した場合ボタンの代わりにリンクを生成。
	 *
	 * @see FormHelper::button()
	 */
	public function button($title, $options = array()) {

		$options = $this->addClass($options, 'btn');

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

final class Property3Hook {
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

class Bootstrap3HtmlHelperProxy {

	private $helper;

	public function useTag($tag) {
		$result = call_user_func_array(array($this->helper, 'useTag'), func_get_args());

		if ($tag === 'radio') {
			$result = '<div class="radio">' . $result . '</div>';
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
