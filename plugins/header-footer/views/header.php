<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?= ucfirst(page()) ?> | <?= APP_NAME ?></title>
	<link rel="stylesheet" href="<?= ROOT ?>/assets/css/bootstrap.min.css">
</head>

<body>

<?php do_action(plugin_id().'_main_menu',['links'=>$links])?>