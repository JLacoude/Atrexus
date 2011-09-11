<div id="resetBox">
  <h1><?=$lang->get('resetTitle')?></h1>
  <p><?=$lang->get('resetDesc')?></p>
  <?php $form->start(Url::generate('Account', 'resetPassword')); ?>
  <label><?=$lang->get('username')?><?php $form->addInput('text', 'login', 'login'); ?></label><br/>
  <label><?=$lang->get('emailForReset')?><?php $form->addInput('text', 'email', 'email'); ?></label><br/>
  <?php $form->addInput('submit', '', '', $lang->get('reset')); ?>
  <?php $form->end(); ?>
</div>