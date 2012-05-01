<?php $rendered_grid = $jq_loader->render('jqWelcome'); ?>

<script>
var opts = {
	'sortname' : 'id',
	'sortorder': 'desc',
	'height' : 240,
	'width'  : 800
};

<?= $rendered_grid ?>
</script>