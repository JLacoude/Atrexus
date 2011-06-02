<td class="<?php if($this->isEnnemy):?> ennemy<?php endif;?><?php if($this->isCurrent):?> current<?php endif;?>">
  <h4><?=$this->_lang->get('soldier')?> <?=$this->ID?></h4>
  <?php if(!$this->isEnnemy && !$this->isCurrent):?>
  <?php $this->_form->start(Url::generate('Play', 'bindTo')); ?>
  <p>
    <?php $this->_form->addInput('hidden', 'positionId', '', $this->positionId); ?>
    <?php $this->_form->addInput('submit', '', '', $this->_lang->get('bind')); ?>
  </p>
  <?php $this->_form->end(); ?>
  <?php endif;?>
</td>
