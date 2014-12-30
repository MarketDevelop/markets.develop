<?php
/**
 * $Id: edit.ctp 79 2014-10-15 10:50:00Z h.ushito@nicosys.jp $
 *
 * @copyright (c) 2014 Nicosys Co. Ltd.
 */

App::uses('Staff', 'Model');

$this->extend('_input_form');

?>

<?php if (AuthComponent::user('role') == Staff::ROLE_ADMINISTRATOR) : ?>
<?= $this->element('remove-modal') ?>
<?php endif ?>

<?php $this->start('navigation') //============================================================ ?>

<div class="navigation clearfix">
	<h1 class="pull-left">テレアポ管理</h1>
<?php if (!empty($position)) : ?>
	<form class="form-inline pull-right" role="form" method="get" action="<?= $this->Html->url(am($this->request->named, ['action' => 'go'])) ?>">
		<div class="form-group">
			<label class="sr-only" for="position">位置</label>
			<div class="input-group">
                <span class="input-group-btn">
					<a class="btn btn-default<?= $prev ? '' : ' disabled' ?>" href="<?= $this->Html->url(am($this->request->named, [$prev, $position - 1, 'page' => intval(($position - 2) / $limit) + 1])) ?>">前</a>
                </span>
				<input type="number" class="form-control text-right" id="position" name="position" value="<?= h($position) ?>">
				<span class="input-group-addon">/ <?= h(number_format($count)) ?></span>
				<span class="input-group-btn">
                    <button type="submit" class="btn btn-default">移動</button>
				</span>
				<span class="input-group-btn">
                    <a class="btn btn-default<?= $next ? '' : ' disabled' ?>" href="<?= $this->Html->url(am($this->request->named, [$next, $position + 1, 'page' => intval($position / $limit) + 1])) ?>">次</a>
				</span>
				<span class="input-group-btn">
					<?= $this->Html->link('戻る', am($this->request->named, ['action' => 'index', 'page' => intval(($position - 1) / $limit) + 1, '#' => 'p' . $position]), ['class' => 'btn btn-default']) ?>
				</span>
			</div>
		</div>
	</form>
<?php else : ?>
	<div class="pull-right">
		<?= $this->Html->link('戻る', am($this->request->named, ['action' => 'index']), ['class' => 'btn btn-default']) ?>

	</div>
<?php endif ?>
</div>

<?php $this->end() // navigation ============================================================ ?>

<?php $this->start('form-actions') //============================================================ ?>

	<?= $this->Form->submit('更新', ['class' => 'btn-primary', 'div' => false]) ?>
<?php if (!empty($next)) : ?>
	<?= $this->Form->submit('更新して次へ', ['class' => 'btn-info', 'div' => false, 'name' => 'next']) ?>
<?php endif ?>
	<?= $this->Form->submit('更新して一覧に戻る', ['class' => 'btn-info', 'div' => false, 'name' => 'back']) ?>

<?php if (AuthComponent::user('role') == Staff::ROLE_ADMINISTRATOR) : ?>
	<?= $this->Html->link('削除', am(['action' => 'delete', $this->request->pass[0]], $this->request->named), ['class' => 'btn btn-danger', 'data-confirm-remove' => strval($this->request->data('Customer.name'))]) ?>
<?php endif ?>

<?php if (!empty($position)) : ?>
	<?= $this->Html->link('戻る', am($this->request->named, ['action' => 'index', 'page' => intval(($position - 1) / $limit) + 1, '#' => 'p' . $position]), ['class' => 'btn btn-default']) ?>
<?php else : ?>
	<?= $this->Html->link('戻る', am($this->request->named, ['action' => 'index']), ['class' => 'btn btn-default']) ?>
<?php endif ?>

<?php $this->end() // form-actions ============================================================ ?>
