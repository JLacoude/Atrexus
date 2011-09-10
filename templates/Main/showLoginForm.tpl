<div id="loginBox">
<?php if($this->timeBeforeNextLogin > 0):?>
<p><?=sprintf($this->_lang->get('tooManyAttempts'), floor($this->timeBeforeNextLogin/60), $this->timeBeforeNextLogin%60)?></p>
<?php else:?>
<?php $form->start(Url::generate('Main', 'login')); ?>
<label><?=$lang->get('login')?> : <?php $form->addInput('text', 'login', 'login'); ?></label><br/>
<label><?=$lang->get('pass')?> : <?php $form->addInput('password', 'password', 'password'); ?></label><br/>
<?php $form->addInput('submit', '', '', $lang->get('login')); ?>
<?php $form->end(); ?>
<?php endif;?>
</div>