<?php
/**
 * $Id: add.ctp 75 2014-10-15 08:27:22Z h.ushito@nicosys.jp $
 *
 * @copyright (c) 2014 Nicosys Co. Ltd.
 */

$this->extend('_input_form');

?>

<?php $this->start('navigation') //============================================================ ?>
<div class="navigation clearfix">
	<h1 class="pull-left">スタッフ登録</h1>
	<div class="pull-right">
		<?= $this->Html->link('戻る', am($this->request->named, ['action' => 'index']), ['class' => 'btn btn-default']) ?>

	</div>
</div>
<?php $this->end() // navigation ============================================================ ?>


<?php $this->start('form-actions') //============================================================ ?>

	<?= $this->Form->submit('登録', ['class' => 'btn-danger', 'div' => false]) ?>

<?php $this->end() // form-actions ============================================================ ?>

