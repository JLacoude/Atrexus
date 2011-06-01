<td class="action action_<?=$this->type?>">
<?php $this->_form->start(Url::generate('Play', 'createSoldier')); ?>
  <?php if($this->type == 'headquarter'):?>
  <h4><?=$this->_lang->get('create')?></h4>
   <p><?php echo sprintf($this->_lang->get('cost'), $this->cost, $this->availableAP);?></p>
   <p><?php $this->_form->addInput('hidden', 'X', '', $this->X); ?>
    <?php $this->_form->addInput('hidden', 'Y', '', $this->Y); ?>
    <?php $this->_form->addInput('submit', 'soldierHq', '', $this->_lang->get('submitCreate')); ?></p>
<?php else:?>
<?php endif;?>
<?php $this->_form->end(); ?>
</td>
