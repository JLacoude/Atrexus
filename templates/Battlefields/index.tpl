<h1><?=$lang->get('battlefieldTitle')?></h1>
<div class="desc"><?=$lang->get('battlefieldDesc')?></div>
<?php if(empty($this->availableBattlefields)):?>
<?=$lang->get('noBattlefield')?>
<?php else:?>
<ul>
<?php foreach($this->availableBattlefields as $battlefield):?>
<li class="<?=$battlefield['visibility']?>Battlefield">
  <?php $form->start(Url::generate('Play', 'enterBattlefield')); ?>
  <p>
    <?= htmlspecialchars($battlefield['name'], ENT_COMPAT, 'UTF-8')?>
    <?php $form->addInput('hidden', 'id', 'id', $battlefield['ID']); ?>
    <?php if(empty($battlefield['personna_id'])):?>
    <?php $form->addSelect('hiveId', 'hiveId', '', $battlefield['hiveList'], 'ID', 'name');?>
    <?php else:?>
   <?=sprintf($lang->get('availableAP'), $battlefield['AP'])?>
    <?php endif;?>
    <?php $form->addInput('submit', '', '', $lang->get('enterBattlefield')); ?>
    <a href="<?=Url::generate('Battlefields', 'showMap', '&amp;', array('battlefieldId' => $battlefield['ID']))?>"><?=$lang->get('seeMap')?></a>
  </p>
 <?php $form->end(); ?>
</li>
<?php endforeach;?>
</ul>
<?php endif;?>
