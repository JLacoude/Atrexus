<?php $this->_form->start(Url::generate('Play', 'bindTo')); ?>
<p>
  <?php $this->_form->addInput('hidden', 'positionId', '', $this->positionId); ?>
  <?php $this->_form->addInput('submit', '', '', $this->_lang->get('bind')); ?>
</p>
<?php $this->_form->end(); ?>
