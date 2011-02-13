<?=$config->get('html.doctype')?>
<html>
<head>
  <title><?=$config->get('html.defaultTitle')?></title>
</head>
<body>
<?php if(!empty($messages['error'])):?>
<ul class="errorMessages">
  <?php foreach($messages['error'] as $error):?>
  <li><?= $error?></li>
   <?php endforeach;?>
</ul>
<?php endif;?>

