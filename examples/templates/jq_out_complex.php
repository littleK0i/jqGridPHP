<script>

<?=$jq_loader->render('jq_out_complex');?>
$grid.filterToolbar();

</script>
	
<div id="descr">
	To handle a complex query with 'GROUP BY' part or sub-selectes - <b>wrap it</b> into higher level SELECT.<br>
	Database query optimizers easily recognize that trick and produce fine execution plans.<br><br>
	
	You can <b>filter</b> and <b>sort</b> by result of aggregate function or sub-query like common values.<br>
	It just works. No need for special handling at all. 
</div>