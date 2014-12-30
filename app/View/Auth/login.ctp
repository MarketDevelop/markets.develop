<?php
/**
 * $Id: login.ctp 6 2014-09-22 08:17:09Z h.ushito@nicosys.jp $
 *
 * @copyright (c) 2014 Nicosys Co. Ltd.
 */

$this->assign('title_for_layout', 'ログイン');
?>

	<?= $this->Form->create(null, ['class' => 'form-signin']) ?>

		<h2 class="form-signin-heading">テレアポ管理</h2>

		<?= $this->Form->input('email') ?>

		<?= $this->Form->input('password') ?>

		<div class="checkbox">
			<label>
				<input type="checkbox" name="ticket"> ログインしたままにする
			</label>
		</div>
		<button class="btn btn-lg btn-primary btn-block" type="submit">ログイン</button>

	<?= $this->Form->end() ?>
