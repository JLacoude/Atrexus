<?php $this->_form->start(Url::generate('Play', 'moveSoldier')); ?>
   <p><?php $this->_form->addInput('hidden', 'X', '', $this->X); ?>
    <?php $this->_form->addInput('hidden', 'Y', '', $this->Y); ?>
    <?php $this->_form->addInput('submit', '', '', $this->_lang->get('submitMove')); ?></p>
<?php $this->_form->end(); ?>
