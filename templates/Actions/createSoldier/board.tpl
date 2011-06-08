<?php $this->_form->start(Url::generate('Play', 'createSoldier')); ?>
  <h4><?=$this->_lang->get('create')?></h4>
   <p><?php echo sprintf($this->_lang->get('cost'), $this->cost);?></p>
   <p><?php $this->_form->addInput('hidden', 'X', '', $this->X); ?>
    <?php $this->_form->addInput('hidden', 'Y', '', $this->Y); ?>
    <?php $this->_form->addInput('submit', '', '', $this->_lang->get('submitCreate')); ?></p>
<?php $this->_form->end();?>
