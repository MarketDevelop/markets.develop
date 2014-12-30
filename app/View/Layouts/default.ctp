<?php
/**
 * $Id: default.ctp 75 2014-10-15 08:27:22Z h.ushito@nicosys.jp $
 *
 * @copyright (c) 2014 Nicosys Co. Ltd.
 */

App::uses('AuthComponent', 'Controller/Component');
App::uses('Staff', 'Model');

?>
<!DOCTYPE html>
<html lang="en">
<head>

	<?= $this->Html->charset() ?>

	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="">
	<meta name="author" content="">

	<title><?= empty($title_for_layout) ? '' : h($title_for_layout) . ' - ' ?>テレアポ管理システム</title>

	<?= $this->Html->meta('icon') ?>

	<!-- Bootstrap core CSS -->
	<?= $this->Html->css('bootstrap.min') ?>
	<?= $this->Html->css('jquery-ui.min') ?>

	<?= $this->Html->css('application') ?>

	<?= $this->fetch('meta') ?>
	<?= $this->fetch('css') ?>

</head>

<body style="<?= $this->fetch('navigation') ? 'padding-top: 112px' : '' ?>">

<!-- Fixed navbar -->
<div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
	<div class="container-fluid">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<?= $this->Html->link('テレアポ管理システム', '/', ['class' => 'navbar-brand']) ?>

		</div>
		<div class="navbar-collapse collapse">
			<ul class="nav navbar-nav">
				<li><?= $this->Html->link('テレアポ', '/customers') ?></li>
<?php if (AuthComponent::user('role') == Staff::ROLE_ADMINISTRATOR) : ?>
				<li><?= $this->Html->link('スタッフ', '/staffs') ?></li>
				<li><?= $this->Html->link('オフィス', '/offices') ?></li>
				<li><?= $this->Html->link('CSV 取込', '/customers/import') ?></li>
<?php endif ?>
			</ul>
			<ul class="nav navbar-nav navbar-right">
				<li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown"><span class="glyphicon glyphicon-user"></span> <?= h(AuthComponent::user('name')) ?> <span class="caret"></span></a>
					<ul class="dropdown-menu" role="menu">
						<li><?= $this->Html->link('プロフィール', '/staffs/profile') ?></li>
						<li class="divider"></li>
						<li><?= $this->Html->link('ログアウト', '/auth/logout') ?></li>
					</ul>
				</li>
			</ul>
		</div>
		<!--/.nav-collapse -->
	</div>
</div>

<?php if ($this->fetch('navigation')) : ?>

<div class="navigation-panel">
	<div class="container-fluid">
		<?= $this->fetch('navigation') ?>
	</div>
</div>

<?php endif ?>

<!-- Begin page content -->
<div class="container-fluid">

	<?= $this->Session->flash() ?>

	<?= $this->fetch('content') ?>

</div>

<div class="footer">
	<div class="container-fluid">
		<p class="text-right"></p>
	</div>
</div>

<?= $this->Html->script('https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js') ?>
<?= $this->Html->script('bootstrap.min') ?>
<?= $this->Html->script('jquery-ui.min') ?>
<?= $this->Html->script('jquery-ui-combobox') ?>
<?= $this->Html->script('application') ?>

<?= $this->fetch('script') // view specific script  ?>

</body>
</html>
