<dl id="itemStats">
  <dt><?=$lang->get('personnaAPTitle')?></dt>
  <dd><?=sprintf($lang->get('remainingAP'), $this->personna['AP'], $this->ruleset['personna.maxAp'])?></dd>
   <?php $this->personna['item']->display('itemStats');?>
</dl>
<?php
if(!empty($this->personna['logs'])):
?>
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
<table id="actionList">
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
<table id="mainBoard">
  <tr>
    <td>Y\X</td>
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