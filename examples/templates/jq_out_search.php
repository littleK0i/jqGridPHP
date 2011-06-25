<script>

<?=$jq_loader->render('jq_out_search');?>
$grid.filterToolbar();

</script>
	
<div id="descr">
	This example shows the full potential of toolbar search.<br>
	Column option <b>search_op</b> defines the search method for each column.<br><br>
	
	Try the following searches:<br>
	1. <b>1,2,3</b> for column <b>ID</b> (IN search)<br>
	2. <b>&gt;10</b> for column <b>Order id</b> (Numeric search)<br>
	3. Any delivery type (Equal search)<br>
	4. Any on <b>Delivery cost</b> (nothing will happen)<br>
	5. <b>ohn</b> for column <b>Customer name</b> (LIKE search)<br>
	6. <b>jQuery</b> for column <b>Book name</b> (custom search)<br>
	7. <b>123</b> for column <b>Book name</b> (custom search - will find books by id)<br>
	8. <b>&lt;=100</b> for column <b>Price</b> (auto search assumed numeric search)
</div>