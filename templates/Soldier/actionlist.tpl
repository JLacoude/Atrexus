<?php if($this->actions->count() > 0):?>
<tr class="<?php if($this->isEnnemy):?> ennemy<?php endif;?><?php if($this->isCurrent):?> current<?php endif;?>">
  <td><?=$this->Y?></td>
  <td><?=$this->X?></td>
  <td><?=$this->_lang->get('soldier')?> <?=$this->ID?></td>
  <td>
<?php 
foreach($this->actions as $action):
  $action->display('actionlist');
endforeach;
?>
  </td>
</tr>
<?php endif;?> 