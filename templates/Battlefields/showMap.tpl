<h1><?=sprintf($lang->get('battlefieldMapTitle'), $this->battlefieldName)?></h1>
<div class="mapBox">
  <img src="<?=$this->picturePath?>" class="map"/>
  <div class="legend">
    <ul>
      <?php foreach($this->hives as $hive):?>
      <li>
	<span style="background:rgb(<?=$hive['color']['soldier']['r']?>, <?=$hive['color']['soldier']['g']?>, <?=$hive['color']['soldier']['b']?>)" class="colorLegend"></span>
	<span style="background:rgb(<?=$hive['color']['hq']['r']?>, <?=$hive['color']['hq']['g']?>, <?=$hive['color']['hq']['b']?>)" class="colorLegend"></span>
	<span class="name"><?=htmlentities($hive['name'])?></span>
      </li>
      <?php endforeach;?>
    </ul>
  </div>
  <div class="clearer"></div>
</div>