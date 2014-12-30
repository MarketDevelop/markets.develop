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
	<h1 class="pull-left">スタッフ管理</h1>
	<div class="pull-right">
		<?= $this->Paginator->link('新規登録', ['action' => 'add'], ['class' => 'btn btn-default']) ?>

	</div>
</div>
<?php $this->end() // navigation ============================================================ ?>

<div class="panel panel-info">
	<div class="panel-heading">
		<h4 class="panel-title">
			<a data-toggle="collapse" href="#search">
				<span class="glyphicon glyphicon-filter"></span> 絞り込み
			</a>
		</h4>
	</div>
	<div id="search" class="panel-collapse collapse in">
		<div class="panel-body">

			<?= $this->Form->create('Search', ['type' => 'get', 'url' => 'index']) ?>

				<div class="row">
					<div class="col-xs-6 col-sm-4 col-md-3 col-lg-2">
						<?= $this->Form->input('code', ['label' => 'コード']) ?>

					</div>
					<div class="col-xs-6 col-sm-4 col-md-3 col-lg-2">
						<?= $this->Form->input('name', ['label' => '名前']) ?>

					</div>
					<div class="col-xs-6 col-sm-4 col-md-3 col-lg-2">
						<?= $this->Form->input('office_id', ['label' => 'オフィス']) ?>

					</div>
				</div>

				<?= $this->Form->submit('検索', ['class' => 'btn-primary']) ?>

			<?= $this->Form->end() ?>

		</div>
	</div>
</div>

<div class="panel panel-default">
	<div class="panel-heading">
		<h2 class="panel-title">一覧</h2>
	</div>

<?php if (empty($staffs)) : ?>

	<div class="alert">
		検索条件に該当するスタッフは見つかりませんでした。
	</div>

<?php else : ?>

	<div class="panel-body">
		<?= $this->element('pager') ?>

	</div>

	<div style="overflow: auto; overflow-y: hidden">
		<table class="table table-condensed table-hover" style="margin-bottom: 0">
			<thead>
				<tr>
					<th><?= $this->Paginator->sort('code', 'コード') ?></th>
					<th><?= $this->Paginator->sort('name', '名前') ?></th>
					<th><?= $this->Paginator->sort('role', '権限') ?></th>
					<th><?= $this->Paginator->sort('office_id', 'オフィス') ?></th>
				</tr>
			</thead>
			<tbody>
<?php foreach ($staffs as $_) : ?>
				<tr>
					<td><span class="glyphicon glyphicon-<?= Hash::get($_, 'Staff.locked') ? 'lock' : 'user' ?>"></span> <?= $this->Paginator->link(Hash::get($_, 'Staff.code'), ['action' => 'edit', Hash::get($_, 'Staff.id')]) ?></td>
					<td><?= h(Hash::get($_, 'Staff.name')) ?></td>
					<td><?= h(Set::enum(Hash::get($_, 'Staff.role'), $roles)) ?></td>
					<td><?= h(Set::enum(Hash::get($_, 'Staff.office_id'), $offices)) ?></td>
				</tr>
<?php endforeach ?>
			</tbody>
		</table>
	</div>

<?php endif ?>

</div>
