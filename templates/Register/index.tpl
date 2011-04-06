<?php $form->start(Url::generate('Register', 'saveAccount')); ?>
<label><?=$lang->get('login')?> : <?php $form->addInput('text', 'login', 'login'); ?></label><br/>
<label><?=$lang->get('pass')?> : <?php $form->addInput('password', 'password', 'password'); ?></label><br/>
<label><?=$lang->get('confirm')?> : <?php $form->addInput('password', 'passwordVerif', 'passwordVerif'); ?></label><br/>
<label><?=$lang->get('email')?> : <?php $form->addInput('text', 'email', 'email'); ?></label><br/>
<?php $form->addInput('submit', '', '', $lang->get('register')); ?>
<?php $form->end(); ?>
