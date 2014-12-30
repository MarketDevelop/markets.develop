<?php
/**
 * $Id: index.ctp 96 2014-12-21 14:33:39Z h.ushito@nicosys.jp $
 *
 * @copyright (c) 2014 Nicosys Co. Ltd.
 */
?>

<?php $this->Html->scriptStart(['inline' => false]) ?>
<?php ob_start() ?><script><?php ob_end_clean() // for IDE support  ?>

	$(function () {
//		$('.table-hover > tbody > tr')
//			.css('cursor', 'pointer')
//			.on('click', function () {
//				// $('a', this).trigger('click')
//				location.href = $('a', this).attr('href')
//				return false
//			})
		$('#search').on({
			'show.bs.collapse': function () {
				$('#filter-toggle-button')
					.removeClass('glyphicon-chevron-down')
					.addClass('glyphicon-chevron-up')
			},
			'hide.bs.collapse':function () {
				$('#filter-toggle-button')
					.removeClass('glyphicon-chevron-up')
					.addClass('glyphicon-chevron-down')
			}
		})
        $('#clear-form-button').on('click', function () {
            $('#search *:input').not('[type=button], [type=submit]').val('')
        })

	})

<?php ob_start() ?></script><?php ob_end_clean() // for IDE support  ?>
<?php $this->Html->scriptEnd() ?>

<?php $this->start('navigation') //============================================================ ?>
<div class="navigation clearfix">
	<h1 class="pull-left">テレアポ一覧</h1>
	<div class="pull-right">
		<?= $this->Paginator->link('新規登録', ['action' => 'add'], ['class' => 'btn btn-default']) ?>

	</div>
</div>
<?php $this->end() // navigation ============================================================ ?>

<div class="panel panel-info">
	<div class="panel-heading">
		<h4 class="panel-title">
			<a data-toggle="collapse" href="#search" style="display: block">
				<span class="btn-toggle"><span id="filter-toggle-button" class="glyphicon glyphicon-chevron-down"></span></span>
				<span class="glyphicon glyphicon-filter"></span> 絞り込み
			</a>
		</h4>
	</div>
	<div id="search" class="panel-collapse collapse">
		<div class="panel-body">

			<?= $this->Form->create('Search', ['type' => 'get', 'url' => 'index']) ?>

				<div class="row">
					<div class="col-xs-6 col-sm-4 col-md-3 col-lg-2">
						<?= $this->Form->input('contact_date_from', ['type' => 'date', 'label' => '対応日（から）']) ?>

					</div>
					<div class="col-xs-6 col-sm-4 col-md-3 col-lg-2">
						<?= $this->Form->input('contact_date_to', ['type' => 'date', 'label' => '対応日（まで）']) ?>

					</div>
					<div class="col-xs-6 col-sm-4 col-md-3 col-lg-2">
						<?= $this->Form->input('contact_time_from', ['type' => 'time', 'label' => '時間（から）']) ?>

					</div>
					<div class="col-xs-6 col-sm-4 col-md-3 col-lg-2">
						<?= $this->Form->input('contact_time_to', ['type' => 'time', 'label' => '時間（まで）']) ?>

					</div>
				</div>
				<div class="row">
					<div class="col-xs-6 col-sm-4 col-md-3 col-lg-2">
						<?= $this->Form->input('staff_code', ['label' => 'テレアポ担当者コード']) ?>

					</div>
					<div class="col-xs-6 col-sm-4 col-md-3 col-lg-2">
						<?= $this->Form->input('staff_name', ['label' => 'テレアポ担当者名']) ?>

					</div>
					<div class="col-xs-6 col-sm-4 col-md-3 col-lg-2">
						<?= $this->Form->input('result', ['label' => '応答状況']) ?>

					</div>
					<div class="col-xs-6 col-sm-4 col-md-3 col-lg-2">
						<?= $this->Form->input('level', ['label' => 'レベル']) ?>

					</div>
				</div>
				<div class="row">
					<div class="col-xs-6 col-sm-4 col-md-3 col-lg-2">
						<?= $this->Form->input('name', ['label' => '顧客名']) ?>

					</div>
					<div class="col-xs-6 col-sm-4 col-md-3 col-lg-2">
						<?= $this->Form->input('phone', ['label' => 'TEL']) ?>

					</div>
					<div class="col-xs-6 col-sm-4 col-md-3 col-lg-2">
						<?= $this->Form->input('business', ['label' => '業種']) ?>

					</div>
					<div class="col-xs-6 col-sm-4 col-md-3 col-lg-2">
						<?= $this->Form->input('personality', ['label' => '株式・その他']) ?>

					</div>
					<div class="col-xs-6 col-sm-4 col-md-3 col-lg-2">
						<?= $this->Form->input('prefecture', ['label' => '都道府県']) ?>

					</div>
					<div class="col-xs-6 col-sm-4 col-md-3 col-lg-2">
						<?= $this->Form->input('address', ['label' => '住所']) ?>

					</div>
				</div>
				<div class="row">
					<div class="col-xs-6 col-sm-4 col-md-3 col-lg-2">
						<?= $this->Form->input('carrier', ['label' => '携帯キャリア']) ?>

					</div>
					<div class="col-xs-6 col-sm-4 col-md-3 col-lg-2">
						<?= $this->Form->input('quantity_min', ['type' => 'number', 'label' => '携帯台数（以上）']) ?>

					</div>
					<div class="col-xs-6 col-sm-4 col-md-3 col-lg-2">
						<?= $this->Form->input('quantity_max', ['type' => 'number', 'label' => '携帯台数（以下）']) ?>

					</div>
					<div class="col-xs-6 col-sm-4 col-md-3 col-lg-2">
						<?= $this->Form->input('contract', ['label' => '法人携帯']) ?>

					</div>
					<div class="col-xs-6 col-sm-4 col-md-3 col-lg-2">
						<?= $this->Form->input('visit_result', ['label' => '訪問結果']) ?>

					</div>
                    <div class="col-xs-6 col-sm-4 col-md-3 col-lg-2">
                        <?= $this->Form->input('responsible_title', ['label' => '担当者役職']) ?>

                    </div>
				</div>
				<div class="row">
					<div class="col-xs-6 col-sm-4 col-md-3 col-lg-2">
						<?= $this->Form->input('note', ['label' => '備考']) ?>

					</div>
					<div class="col-xs-6 col-sm-4 col-md-3 col-lg-2">
						<?= $this->Form->input('appointment_note', ['label' => '確アポ内容']) ?>

					</div>
				</div>
				<div class="form-group form-actions">

					<?= $this->Form->submit('検索', ['class' => 'btn-primary', 'div' => false]) ?>
					<?= $this->Form->submit('詳細画面へ', ['class' => 'btn-info', 'div' => false, 'name' => 'detail']) ?>

                    <?= $this->Form->button('クリア', ['type' => 'button', 'class' => 'btn btn-default', 'id' => 'clear-form-button']) ?>

				</div>
			</form>

		</div>
	</div>
</div>

<div class="panel panel-default">
	<div class="panel-heading">
		<h2 class="panel-title">一覧</h2>
	</div>

<?php if (empty($customers)) : ?>

	<div class="alert">
		検索条件に該当する顧客は見つかりませんでした。
	</div>

<?php else : ?>

	<div class="panel-body">
		<?= $this->element('pager') ?>

	</div>

	<div style="overflow: auto; overflow-y: auto; max-height: 652px">
		<table class="table table-condensed table-hover" style="margin-bottom: 0">
			<thead>
			<tr>
				<th><?= $this->Paginator->sort('level', 'レベル') ?></th>
				<th><?= $this->Paginator->sort('contract', '法人携帯') ?></th>
				<th><?= $this->Paginator->sort('carrier', '携帯キャリア') ?></th><!-- 携帯キャリア -->
				<th><?= $this->Paginator->sort('quantity', '携帯台数') ?></th><!-- 携帯台数 -->
                <th><?= $this->Paginator->sort('dealer', '購入店') ?></th><!-- 携帯台数 -->
				<th><?= $this->Paginator->sort('name', '顧客名') ?></th>
				<th><?= $this->Paginator->sort('staff_name', 'テレアポ担当者名') ?></th>
				<th><?= $this->Paginator->sort('phone', 'TEL') ?></th>
				<th><?= $this->Paginator->sort('contact_date', '対応日') ?></th>
				<th><?= $this->Paginator->sort('contact_time', '時間') ?></th>
				<th><?= $this->Paginator->sort('responsible_name', '担当者名') ?></th>
				<th><?= $this->Paginator->sort('responsible_title', '担当者役職') ?></th>
				<th><?= $this->Paginator->sort('result', '応答状態') ?></th>
				<th><?= $this->Paginator->sort('business', '業種') ?></th>
				<th><?= $this->Paginator->sort('address', '住所') ?></th>
			</tr>
			</thead>
			<tbody>
<?php foreach ($customers as $i => $_) : ?>
				<tr>
					<td><?= h(Hash::get($_, 'Customer.level')) ?></td>
					<td><?= h(Set::enum(Hash::get($_, 'Customer.contract'), $contracts)) ?></td>
					<td><?= h(Set::enum(Hash::get($_, 'Customer.carrier'), $carriers)) ?></td>
					<td><?= h(Hash::get($_, 'Customer.quantity')) ?></td>
                    <td><?= h(Set::enum(Hash::get($_, 'Customer.dealer'), $dealers)) ?></td>
					<td class="long-column"><?= $this->Paginator->link(Hash::get($_, 'Customer.name'), ['action' => 'edit', Hash::get($_, 'Customer.id'), $this->Paginator->counter('{:start}') + $i], ['id' => 'p' . ($this->Paginator->counter('{:start}') + $i), 'class' => 'anchor']) ?></td>
                    <td><?= h(Hash::get($_, 'Customer.staff_name')) ?></td>
					<td><?= h(Hash::get($_, 'Customer.phone')) ?></td>
					<td><?= h($this->Time->shortDate(Hash::get($_, 'Customer.contact_date'), '')) ?></td>
					<td><?= h($this->Time->shortTime(Hash::get($_, 'Customer.contact_time'), '')) ?></td>
					<td><?= h(Hash::get($_, 'Customer.responsible_name')) ?></td>
					<td><?= h(Hash::get($_, 'Customer.responsible_title')) ?></td>
					<td><?= h(Set::enum(Hash::get($_, 'Customer.result'), $results)) ?></td>
					<td><?= h(Hash::get($_, 'Customer.business')) ?></td>
					<td><?= h(Hash::get($_, 'Customer.address')) ?></td>
				</tr>
<?php endforeach ?>
			</tbody>
		</table>
	</div>

<?php endif ?>

</div>
