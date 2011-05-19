<?=$config->get('html.doctype')?>
<html>
<head>
  <title><?=$config->get('html.defaultTitle')?></title>
  <link rel="stylesheet" type="text/css" href="css/main.css"/>
</head>
<body>
<ul>
  <li><a href="<?= Url::generate('')?>"><?=$lang->get('home')?></a></li>
  <li><a href="<?= Url::generate('Play')?>"><?=$lang->get('play')?></a></li>
  <?php if($user->isRegistered()):?>
  <li><a href="<?= Url::generate('Account')?>"><?=$lang->get('manageAccount')?></a></li>
  <li><a href="<?= Url::generate('Main', 'logout')?>"><?=$lang->get('logout')?></a></li>
  <?php else: ?>
  <li><a href="<?= Url::generate('Main', 'showLoginForm')?>"><?=$lang->get('login')?></a></li>
  <li><a href="<?= Url::generate('Register')?>"><?=$lang->get('register')?></a></li>
  <?php endif; ?>
</ul>
<?php if(!empty($messages['error'])):?>
<ul class="errorMessages">
  <?php foreach($messages['error'] as $error):?>
  <li><?= $error?></li>
   <?php endforeach;?>
</ul>
<?php endif;?>

