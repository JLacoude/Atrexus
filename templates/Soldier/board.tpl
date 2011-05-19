<td>
<?php $this->_form->start(Url::generate('Play')); ?>
  <h4><?=$this->_lang->get('soldier')?> <?=$this->ID?></h4>
  <p>
    <?php $this->_form->addInput('hidden', 'soldierId', '', $this->ID); ?>
    <?php $this->_form->addInput('submit', 'soldierHq', '', $this->_lang->get('details')); ?>
  </p>
<?php $this->_form->end(); ?>
</td>
