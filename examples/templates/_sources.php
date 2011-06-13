<div id="tabs" style="width: 800px; margin-top: 15px;">
	<ul>
		<li><a href="#tabs-info">Info</a></li>
		<li><a href="#tabs-php">PHP</a></li>
		<li><a href="#tabs-js">JS</a></li>
	</ul>
	
	<div id="tabs-info">
	
	</div>
	
	<div id="tabs-php">
		<pre><code class="php"><?=htmlspecialchars($source_php);?></code></pre>
	</div>
	
	<div id="tabs-js">
		<pre><code class="javascript"><?=trim(htmlspecialchars($source_js));?></pre>
	</div>
</div>