<div id="statsAndLogs">
  <div class="innerWrapper">
  <h2><?=$lang->get('statsTitle')?></h2>
<dl id="itemStats">
  <dt><?=$lang->get('personnaAPTitle')?></dt>
  <dd><?=sprintf($lang->get('remainingAP'), $this->personna['AP'], $this->ruleset['personna.maxAp'])?></dd>
   <?php $this->personna['item']->display('itemStats');?>
  <dd class="last"></dd>
</dl>
<?php
if(!empty($this->personna['logs'])):
?>
  <h2><?=$lang->get('logsTitle')?></h2>
<ul id="logs">
<?php
foreach($this->personna['logs'] as $log):
  $log->display('boardloglist');
endforeach;
?>
</ul>
<?php 
endif;
?>
  </div>
</div>
<div  id="actionList">
  <div class="innerWrapper">
  <h2><?=$lang->get('actionsTitle')?></h2>
<?php $this->_form->start(Url::generate('Battlefields', 'leave')); ?>
   <p><?php $this->_form->addInput('submit', '', '', $this->_lang->get('exitBattlefield')); ?></p>
<?php $this->_form->end(); ?>  
<table>
  <tr>
    <th>Y</th>
    <th>X</th>
    <th><?=$lang->get('item')?></th>
    <th><?=$lang->get('actions')?></th>
  </tr>
<?php 
for($y = ($this->personna['Y'] + $this->ruleset['game.viewDistance']); $y >= ($this->personna['Y'] - $this->ruleset['game.viewDistance']); $y--):
for($x = ($this->personna['X'] - $this->ruleset['game.viewDistance']); $x <= ($this->personna['X'] + $this->ruleset['game.viewDistance']); $x++):
  if(!empty($this->viewData[$x][$y])):
    $this->viewData[$x][$y]->display('actionlist');
  endif;
  endfor;
 endfor;?>
</table>
  </div>
</div>
<div  id="mainBoard">
  <h2><?=$lang->get('viewTitle')?></h2>
<table>
  <tr>
    <th>Y\X</th>
<?php for($x = ($this->personna['X'] - $this->ruleset['game.viewDistance']); $x <= ($this->personna['X'] + $this->ruleset['game.viewDistance']); $x++):?>
    <th><?=$x?></th>
<?php endfor;?>
  </tr>
<?php for($y = ($this->personna['Y'] + $this->ruleset['game.viewDistance']); $y >= ($this->personna['Y'] - $this->ruleset['game.viewDistance']); $y--):?>
  <tr>
    <th><?=$y?></th>
<?php for($x = ($this->personna['X'] - $this->ruleset['game.viewDistance']); $x <= ($this->personna['X'] + $this->ruleset['game.viewDistance']); $x++):?>
<?php if(isset($this->viewData[$x][$y])):?>
<?php $this->viewData[$x][$y]->display('board');?>
<?php else: ?>
    <td></td>
<?php endif;?>
<?php endfor;?>
  </tr>
<?php endfor;?>
</table>
</div> 