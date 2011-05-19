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