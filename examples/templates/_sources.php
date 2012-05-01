<div id="tabs" style="width: 800px; margin-top: 15px;">
	<ul>
		<li><a href="#tabs-info">Info</a></li>
		<li><a href="#tabs-php">PHP Grid</a></li>
		<?php if(isset($source_php2)):?>
		<li><a href="#tabs-php2">PHP Subgrid</a></li>
		<?php endif;?>
		<li><a href="#tabs-js">Template</a></li>
	</ul>
	
	<div id="tabs-info">
	
	</div>
	
	<div id="tabs-php">
		<pre><code class="php"><?=htmlspecialchars($source_php);?></code></pre>
	</div>
	
	<?php if(isset($source_php2)):?>
	<div id="tabs-php2">
		<pre><code class="php"><?=htmlspecialchars($source_php2);?></code></pre>
	</div>
	<?php endif;?>
	
	<div id="tabs-js">
		<pre><code class="javascript"><?=trim(htmlspecialchars($source_js));?></pre>
	</div>
</div>