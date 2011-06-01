<td<?php if($this->isEnnemy):?> class="ennemy"<?php endif;?>>
<?php
 $this->_form->start(Url::generate('Play')); ?>
  <h4><?=$this->_lang->get('hq')?> <?=$this->ID?></h4>
  <p>
    <?php $this->_form->addInput('hidden', 'hqId', '', $this->ID); ?>
    <?php $this->_form->addInput('submit', 'seeHq', '', $this->_lang->get('details')); ?>
  </p>
<?php $this->_form->end(); ?>
</td>
