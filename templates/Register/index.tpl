<h1><?=$lang->get('registerTitle')?></h1>
<p><?=$lang->get('registerDesc')?></p>
<?php $form->start(Url::generate('Register', 'saveAccount')); ?>
<p>
<label><?=$lang->get('login')?><?php $form->addInput('text', 'login', 'login'); ?></label><br/>
<label><?=$lang->get('pass')?><?php $form->addInput('password', 'password', 'password'); ?></label><br/>
<label><?=$lang->get('confirm')?><?php $form->addInput('password', 'passwordVerif', 'passwordVerif'); ?></label><br/>
<label><?=$lang->get('email')?><?php $form->addInput('text', 'email', 'email'); ?><?=$lang->get('emailDesc')?></label><br/>
<?php $form->addInput('submit', '', '', $lang->get('register')); ?>
</p>
<?php $form->end(); ?>
