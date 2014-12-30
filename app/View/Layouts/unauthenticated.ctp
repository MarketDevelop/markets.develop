<?php
/**
 * $Id: unauthenticated.ctp 16 2014-09-25 10:48:08Z h.ushito@nicosys.jp $
 *
 * @copyright (c) 2014 Nicosys Co. Ltd.
 */
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

	<?= $this->Html->css('application') ?>

	<?= $this->Html->css('signin') ?>


	<?= $this->fetch('meta') ?>
	<?= $this->fetch('css') ?>

</head>

<body>

<!-- Begin page content -->
<div class="container">

	<?= $this->Session->flash() ?>

	<?= $this->fetch('content') ?>

</div>

<?= $this->Html->script('https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js') ?>
<?= $this->Html->script('bootstrap.min') ?>
<?= $this->fetch('script') ?>

</body>
</html>
