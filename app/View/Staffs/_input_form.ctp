<?php
/**
 * $Id: _input_form.ctp 71 2014-10-14 12:12:28Z h.ushito@nicosys.jp $
 *
 * @copyright (c) 2014 Nicosys Co. Ltd.
 */

$NEW = $this->request->action === 'add';
$PROFILE = $this->request->action === 'profile';

?>
<?= $this->fetch('content') //============================================================ ?>

<div class="panel panel-info">
	<div class="panel-heading">
		<h3 class="panel-title"></h3>
	</div>
	<div class="panel-body">

		<?= $this->Form->create(null, ['class' => 'form-horizontal', 'inputDefaults' => [
			'controls' => 'col-sm-10',
			'label' => ['class' => 'col-sm-2 control-label'],
		]]) ?>

		<?= $this->Form->input('code', ['disabled' => $PROFILE]) ?>

		<?= $this->Form->input('name') ?>

		<?= $this->Form->input('email') ?>

		<?= $this->Form->input('office_id', ['disabled' => $PROFILE]) ?>

		<?= $this->Form->input('role', ['disabled' => $PROFILE]) ?>

		<?= $this->Form->input('password', ['value' => '', 'required' => $NEW]) ?>

		<?= $this->Form->input('password_confirmation', ['value' => '', 'required' => $NEW, 'type' => 'password', 'label' => ['text' => 'パスワードの確認', 'class' => 'col-sm-2 control-label']]) ?>

<?php if (!$PROFILE) : ?>

		<div class="form-group">
			<div class="col-sm-offset-2 col-sm-10">
				<div class="checkbox">
					<label>
						<?= $this->Form->checkbox('locked') ?> ロック
					</label>
				</div>
			</div>
		</div>
<?php endif ?>

		<div class="form-group form-actions">
			<div class="col-sm-offset-2 col-sm-10">
				<?= $this->fetch('form-actions') //============================================================ ?>
			</div>
		</div>

		<?= $this->Form->end() ?>

	</div>

</div>
