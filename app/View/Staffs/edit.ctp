<?php
/**
 * $Id: edit.ctp 75 2014-10-15 08:27:22Z h.ushito@nicosys.jp $
 *
 * @copyright (c) 2014 Nicosys Co. Ltd.
 */

$this->extend('_input_form');

?>

<?= $this->element('remove-modal') ?>

<?php $this->start('navigation') //============================================================ ?>
<div class="navigation clearfix">
	<h1 class="pull-left">スタッフ編集</h1>
	<div class="pull-right">
		<?= $this->Html->link('戻る', 'index', ['class' => 'btn btn-default']) ?>

	</div>
</div>
<?php $this->end() // navigation ============================================================ ?>


<?php $this->start('form-actions') //============================================================ ?>

	<?= $this->Form->submit('変更', ['class' => 'btn-danger', 'div' => false]) ?>

<?php if ($this->request->data('Staff.id') != AuthComponent::user('id')) : ?>
	<?= $this->Html->link('削除', am($this->request->named, ['action' => 'delete', $this->request->pass[0]]), ['class' => 'btn btn-danger', 'data-confirm-remove' => strval($this->request->data('Staff.name'))]) ?>

<?php endif ?>

<?php $this->end() // form-actions ============================================================ ?>
