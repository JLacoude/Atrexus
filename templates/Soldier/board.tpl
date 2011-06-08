<td class="<?php if($this->isEnnemy):?> ennemy<?php endif;?><?php if($this->isCurrent):?> current<?php endif;?>">
  <h4><?=$this->_lang->get('soldier')?> <?=$this->ID?></h4>
<?php 
foreach($this->actions as $action):
  $action->display('board');
endforeach;
?>
</td>
