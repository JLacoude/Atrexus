<?=$config->get('html.doctype')?>
<html>
<head>
  <title><?=$config->get('html.defaultTitle')?></title>
  <link rel="stylesheet" type="text/css" href="css/main.css"/>
  <meta http-equiv="Content-Type" content="text/html; charset=<?=$config->get('html.charset')?>">
</head>
<body>
<ul id="mainMenu">
  <li class="first"><a href="<?= Url::generate('')?>"><?=$lang->get('home')?></a></li>
  <li><a href="<?= Url::generate('Play')?>"><?=$lang->get('play')?></a></li>
  <?php if($user->isRegistered()):?>
  <li><a href="<?= Url::generate('Account')?>"><?=$lang->get('manageAccount')?></a></li>
  <li><a href="<?= Url::generate('Main', 'logout')?>"><?=$lang->get('logout')?></a></li>
  <?php else: ?>
  <li><a href="<?= Url::generate('Main', 'showLoginForm')?>"><?=$lang->get('login')?></a></li>
  <li><a href="<?= Url::generate('Register')?>"><?=$lang->get('register')?></a></li>
  <?php endif; ?>
  <li class="last"></li>
</ul>
<?php if(!empty($messages['error'])):?>
<ul class="errorMessages">
  <?php foreach($messages['error'] as $error):?>
  <li><?= $error?></li>
   <?php endforeach;?>
</ul>
<?php endif;?>
<?php if(!empty($messages['success'])):?>
<ul class="successMessages">
  <?php foreach($messages['success'] as $success):?>
  <li><?= $success?></li>
   <?php endforeach;?>
</ul>
<?php endif;?>
<div id="content">
