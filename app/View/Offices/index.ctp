<?php
/**
 * $Id: index.ctp 75 2014-10-15 08:27:22Z h.ushito@nicosys.jp $
 *
 * @copyright (c) 2014 Nicosys Co. Ltd.
 */
?>

<?php $this->Html->scriptStart(['inline' => false]) ?>
<?php ob_start() ?><script><?php ob_end_clean() // for IDE support  ?>

	$(function () {
		$('.table-hover > tbody > tr')
			.css('cursor', 'pointer')
			.on('click', function () {
				// $('a', this).trigger('click')
				location.href = $('a', this).attr('href')
				return false
			})
	})

<?php ob_start() ?></script><?php ob_end_clean() // for IDE support  ?>
<?php $this->Html->scriptEnd() ?>

<?php $this->start('navigation') //============================================================ ?>
<div class="navigation clearfix">
	<h1 class="pull-left"><?= $LABEL ?>管理</h1>
	<div class="pull-right">
		<?= $this->Paginator->link('新規登録', ['action' => 'add'], ['class' => 'btn btn-default']) ?>

	</div>
</div>
<?php $this->end() // navigation ============================================================ ?>


<div class="panel panel-default">
	<div class="panel-heading">
		<h2 class="panel-title">一覧</h2>
	</div>

<?php if (empty($items)) : ?>

	<div class="alert">
		営業所がありません
	</div>

<?php else : ?>

	<div class="panel-body">
		<?= $this->element('pager') ?>

	</div>

	<div style="overflow: auto; overflow-y: hidden">
		<table class="table table-condensed table-hover" style="margin-bottom: 0">
			<thead>
				<tr>
					<th><?= $this->Paginator->sort('name', '名前') ?></th>
				</tr>
			</thead>
			<tbody>
<?php foreach ($items as $_) : ?>
				<tr>
					<td><?= $this->Paginator->link(Hash::get($_, 'Office.name'), ['action' => 'edit', Hash::get($_, 'Office.id')]) ?></td>
				</tr>
<?php endforeach ?>
			</tbody>
		</table>
	</div>

<?php endif ?>

</div>
