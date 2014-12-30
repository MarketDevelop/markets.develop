<?php
/**
 * $Id: import.ctp 71 2014-10-14 12:12:28Z h.ushito@nicosys.jp $
 *
 * @copyright (c) 2014 Nicosys Co. Ltd.
 */
?>

<!-- navi -->
<div class="pull-right">
	<?= $this->Html->link('戻る', am($this->request->named, ['action' => 'index']), ['class' => 'btn btn-default']) ?>

</div>

<div class="page-header">
	<h1>顧客インポート</h1>
</div>

<div class="panel panel-info">
	<div class="panel-heading">
		<h3 class="panel-title"></h3>
	</div>
	<div class="panel-body">

		<?= $this->Form->create('CustomerImport', ['class' => 'form-horizontal', 'type' => 'file', 'inputDefaults' => [
			'controls' => 'col-sm-10',
			'label' => ['class' => 'col-sm-2 control-label'],
		]]) ?>

		<?= $this->Form->input('file', ['type' => 'file', 'label' => ['text' => 'CSV ファイル', 'class' => 'col-sm-2 control-label']]) ?>

		<div class="form-group form-actions">
			<div class="col-sm-offset-2 col-sm-10">
				<?= $this->Form->submit('インポート', ['class' => 'btn-danger', 'div' => false]) ?>
			</div>
		</div>

		<?= $this->Form->end() ?>

	</div>

</div>

<?php if (isset($errors)) : ?>

<?php if (!empty($succeeded)) : ?>
	<div class="alert alert-success" role="alert"><?= number_format($succeeded) ?> 件のレコードをインポートしました。</div>
<?php endif ?>

<?php if (!empty($errors)) : ?>
	<div class="alert alert-danger" role="alert"><?= number_format(count($errors)) ?> 件のレコードでエラーが発生しました。</div>

	<div class="panel panel-default">
		<div class="panel-heading">
			<h2 class="panel-title">エラー詳細</h2>
		</div>

		<div class="panel-body">
			<?= $this->element('pager') ?>
		</div>

		<div>
			<table class="table table-condensed table-hover" style="margin-bottom: 0">
				<thead>
				<tr>
					<th>行</th>
					<th>内容</th>
				</tr>
				</thead>
				<tbody>
<?php foreach ($errors as $number => $messages) : ?>
					<tr>
						<td class="text-right"><?= number_format($number) ?></td>
						<td><?= nl2br(h(implode("\n", Hash::extract($messages, '{s}.{n}')))) ?></td>
					</tr>
<?php endforeach ?>
				</tbody>
			</table>
		</div>

	</div>

<?php endif ?>

<?php endif ?>
