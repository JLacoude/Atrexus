<td class="hq<?php if($this->isEnnemy):?> foe<?php else:?> friend<?php endif;?><?php if($this->isCurrent):?> current<?php endif;?>">
  <h4><?=$this->_lang->get('hq')?> <?=$this->ID?></h4>
<?php 
foreach($this->actions as $action):
  $action->display('board');
endforeach;
?>
</td>
