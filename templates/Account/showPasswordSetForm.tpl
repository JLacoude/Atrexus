<div id="passwordSetBox">
  <h1><?=$lang->get('setTitle')?></h1>
  <?php $form->start(Url::generate('Account', 'setNewPassword')); ?>
  <?php $form->addInput('hidden', 'passwordToken', 'passwordToken', $this->token); ?></label><br/>
  <label><?=$lang->get('newPassword')?><?php $form->addInput('password', 'password', 'password'); ?></label><br/>
  <label><?=$lang->get('newPassword2')?><?php $form->addInput('password', 'password2', 'password2'); ?></label><br/>
  <?php $form->addInput('submit', '', '', $lang->get('setNew')); ?>
  <?php $form->end(); ?>
</div>