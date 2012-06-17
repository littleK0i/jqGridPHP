<script>
var opts = {
	'ondblClickRow' : function(id)
	{
		$jqMiscAjaxDialog.jqGrid('extLoading', true);
		
		$.get($(this).jqGrid('getGridParam', 'url'),
		{
			'oper' : 'dialog', 
			'id'   : id
		}, function(ret)
		{
			var $cont = $('<DIV>');
			
			$cont.dialog({
				'modal'     : true,
				'width'     : 520,
				'height'    : 280,
				'title'     : 'Customer details: ' + id,
				'resizable' : false,
				'close'     : function(event, ui)
				{
					$('.ui-dialog-content').dialog('destroy');
					$cont.remove();
				}
			});
			
			$cont.html(ret.html);
			
			$jqMiscAjaxDialog.jqGrid('extLoading', false);
		});
	}
};

<?= $rendered_grid ?>
</script>

<div id="descr_rus">
	Пример загрузки таблиц в диалоговом окне через AJAX.<br>
	Дважды кликните на любом ряде таблицы.<br><br>

	По просьбе с phpclub.ru.
</div>