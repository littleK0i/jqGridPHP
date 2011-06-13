<div id="accordion">
	<? foreach($_SECTIONS as $k => $s): ?>
	<h3><a href="#"><?=$s['name']?></a></h3>
	<div>
		<ul>
		<? foreach($s['items'] as $item_id => $item_name): ?>
			<li<? if(('jq_' . $item_id) == $grid): ?> class="active"<? endif; ?>><a href="?render=jq_<?=$item_id?>"><?=$item_name?></a></li>
		<? endforeach; ?>
		</ul>
	</div>
	<? endforeach; ?>
</div>