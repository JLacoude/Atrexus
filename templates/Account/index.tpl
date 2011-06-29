<h1><?=$lang->get('accountTitle')?></h1>
<?php $form->start(Url::generate('Account', 'changePassword')); ?>
<h2><?=$lang->get('passwordTitle')?></h2>
<p>
<label><?=$lang->get('pass')?><?php $form->addInput('password', 'password', 'password'); ?></label><br/>
<label><?=$lang->get('confirm')?><?php $form->addInput('password', 'passwordVerif', 'passwordVerif'); ?></label><br/>
<label><?=$lang->get('oldPass')?><?php $form->addInput('password', 'oldPassword', 'oldPassword'); ?></label><br/>
<?php $form->addInput('submit', '', '', $lang->get('change')); ?>
</p>
<?php $form->end(); ?>
<?php $form->start(Url::generate('Account', 'changeEmail')); ?>
<h2><?=$lang->get('emailTitle')?></h2>
<p>
<label><?=$lang->get('email')?><?php $form->addInput('text', 'email', 'email', $user->email); ?></label><br/>
<label><?=$lang->get('oldPass')?><?php $form->addInput('password', 'oldPassword'); ?></label><br/>
<?php $form->addInput('submit', '', '', $lang->get('change')); ?>
</p>
<?php $form->end(); ?>
