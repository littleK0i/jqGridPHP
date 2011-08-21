<div id="accordion">
	<? foreach($_SECTIONS as $k => $s): ?>
	<h3><a href="#"><?=$s['name'][$lang]?></a></h3>
	<div>
		<ul>
		<? foreach($s['items'] as $item_id => $item_name): ?>
			<li<? if(jqGrid_Utils::score2camel('jq', $item_id) == $grid): ?> class="active"<? endif; ?>><a href="?render=<?=jqGrid_Utils::score2camel('jq', $item_id)?>"><?=$item_name[$lang]?></a></li>
		<? endforeach; ?>
		</ul>
	</div>
	<? endforeach; ?>
</div>