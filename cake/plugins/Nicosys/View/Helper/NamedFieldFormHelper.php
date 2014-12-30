<?php
/**
 * $Id: NamedFieldFormHelper.php 33 2014-09-22 08:20:01Z h.ushito@nicosys.jp $
 *
 * @copyright (c) 2013 Nicosys Co. Ltd.
 */

App::uses('FormHelper', 'View/Helper');

/**
 * フィールドコメントに対応した Form ヘルパー。
*/
class NamedFieldFormHelper extends FormHelper {

	/**
	 * label に対し、コメントを参照してラベル名を自動生成する。
	 * @see FormHelper::label()
	 */
	public function label($fieldName = null, $text = null, $options = array()) {

		if ($fieldName === null) {
			$fieldName = implode('.', $this->entity());
		} else if ($text === null) {
			$this->setEntity($fieldName); // TODO
		}

		if ($text === null) {

			if (strpos($fieldName, '.') !== false) {
				$c = explode('.', $fieldName);
				$f = array_pop($c);
			} else {
				$f = $fieldName;
			}

			$modelKey = $this->model();
			$fieldKey = $this->field();

			if ($modelKey && $fieldKey === $f) {

				$model = $this->_getModel($modelKey);
				if ($model && ($model->Behaviors->enabled('AutoValidation') || $model->Behaviors->enabled('NamedField'))) {
					$text = $model->getFieldName($fieldKey);
				}

			}
			//$text = __(Inflector::humanize(Inflector::underscore($text)));
		}

		return parent::label($fieldName, $text, $options);

	}

}
