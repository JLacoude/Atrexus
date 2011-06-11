<li>
<?=sprintf($this->_lang->get('attackSoldierText'), $this->time, $this->by_id, $this->login, $this->target_id, $this->damages)?>
<?php
   if($this->kill):
     echo sprintf($this->_lang->get('killSoldierText'), $this->target_id);
endif;
?>
</li>
