<?php $this->_form->start(Url::generate('Play', 'captureHeadquarter')); ?>
  <p>
    <?php $this->_form->addInput('hidden', 'headquarterId', '', $this->headquarterId); ?>
    <?php $this->_form->addInput('submit', '', '', $this->_lang->get('capture')); ?>
  </p>
<?php $this->_form->end(); ?>