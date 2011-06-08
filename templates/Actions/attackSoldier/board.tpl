<?php $this->_form->start(Url::generate('Play', 'attackSoldier')); ?>
  <p>
    <?php $this->_form->addInput('hidden', 'soldierId', '', $this->ID); ?>
    <?php $this->_form->addInput('submit', '', '', $this->_lang->get('attack')); ?>
  </p>
<?php $this->_form->end(); ?>
