//---------------
// Overrride some defaults
//---------------

$.extend($.jgrid.defaults,
{
	datatype: 'json',
	mtype: 'POST',
	
	altRows: false,
	altclass: 'altrow',
	
	loadui: 'block',
	hidegrid: false,
	hoverrows: false,
	
	viewrecords: true,
	scrollOffset: 21,

	width: 800,
	height: 290
});

//----------------
// Ext Functions
//---------------

$.jgrid.ext = 
{
	ajaxFormProxy: function(opts, act)
	{
		opts.url = $(this).getGridParam('url');
		
		if(act.substring(0, 4) == 'del_')
		{
			$.ajax(opts);
		}
		
		opts.iframe = true;
		
		$.extend(opts.data, {'_json_mode' : 'ajaxForm'});
		
		var $form = $('#FrmGrid_' + $(this).getGridParam('id'));
		var ele = $form.find('INPUT,TEXTAREA').not(':file');
		
		ele.each(function()
		{
			$(this).data('name', $(this).attr('name')).removeAttr('name');
		});
		
		$form.ajaxSubmit(opts);
		
		ele.each(function()
		{
			$(this).attr('name', $(this).data('name'));
		});
	}
};

//---------------
// Ext Formatters
//---------------

$.extend($.jgrid.formatter,
{
	ext_link	:
	{
		'href'  : '#',
		'class' : '',
		'target': '_blank'
	}
});

$.extend($.fn.fmatter, 
{
	ext_link: function(cellvalue, options, rowdata, act)
	{
		var def = options.ext_link;
		var opt = options.colModel.formatoptions;
		
		//Prevent creating new object every time!
		var $grid = (typeof(window['$' + options.gid]) == 'object') ? window['$' + options.gid] : $('#' + options.gid);
		
		var href = opt.href.replace(/\{(.+?)\}/g, function(arg)
		{
			var arg = arg.substring(1, arg.length - 1);
			
			if(act == 'add')
			{
				return rowdata[$grid.jqGrid('getColIndex', arg)];
			}
			else
			{
				//it is very tricky code here..
				if(typeof(rowdata[arg]) != 'undefined')
				{
					return rowdata[arg];
				}
				else
				{
					return $grid.jqGrid('getCell', options.rowId, arg);
				}
			}
		});
		
		return $.jgrid.format('<a href="{0}" class="{1}" target="{2}">{3}</a>', href, opt.class, opt.target, cellvalue);
	}
});

//---------------
// Form edit error handler
//---------------

$.extend($.jgrid.edit,
{
	afterSubmit: function(data)
	{
		var json = $.jgrid.parse(data.responseText);
		var new_id = json.new_id || 0;
		
		if(typeof(json.error_msg) != 'undefined')
		{
			return [false, json.error_msg, new_id];
		}
		
		return [true, null, new_id];
	},
	
	recreateForm : true //recreating form removes a lot of butthurt of form editing
});

//---------------
// Ext Functions
//---------------

$.jgrid.extend(
{
	'getColIndex'	: function(col)
	{
		var $t = this[0];
		
		if(typeof($t.p.colIndex) == 'undefined')
		{
			$t.p.colIndex = {};
			for(var idx in $t.p.colModel)
			{
				$t.p.colIndex[$t.p.colModel[idx].name] = idx;
			}
		}
		
		return (typeof $t.p.colIndex[col] != 'undefined') ? $t.p.colIndex[col] : -1;
	},
	
	'resetColIndex'	  : function()
	{
		var $t = this[0];
		$t.p.colIndex = undefined;
	},
	
	'extRequest'		: function(data, success, lock, rows)
	{
		return this.each(function()
		{
			var $grid = $(this);
			var postData = {};
			
			if(rows)
			{
				if($grid.jqGrid('getGridParam', 'multiselect'))
				{
					postData['id[]'] = $grid.jqGrid('getGridParam', 'selarrrow');
				}
				else
				{
					postData['id'] = $grid.jqGrid('getGridParam', 'selrow');
				}
				
				if(!postData['id[]'] && !postData['id'])
				{
					$.jgrid.info_dialog($.jgrid.errors.errcap,$.jgrid.errors.norecords,jQuery.jgrid.edit.bClose);
					return;
				}
			}
			
			if(lock) $grid.jqGrid('extLoading');
			
			$.post($grid.jqGrid('getGridParam', 'url'), $.extend(postData, data), function(ret)
			{
				if($.isFunction(success))
				{
					success.call($grid, ret);
				}
				
				if(lock) $grid.trigger('reloadGrid');
			});
		});
	},
	
	'extLoading'		: function(state)
	{
		return this.each(function()
		{
			var id = $(this).attr('id');
			$("#lui_"+id).toggle(state !== false);
			$("#load_"+id).toggle(state !== false);
		});
	},
	
	'extHighlight'	: function()
	{
		return this.each(function()
		{
			var _class = this.p.userData._class;
			if(typeof _class != 'object') return;
			
			var $grid = $(this);
			
			for(var row_idx in _class)
			{
				var type = typeof(_class[row_idx]);
				
				if(type == 'string')
				{
					$grid.jqGrid('setRowData', row_idx, '', _class[row_idx]);
				}
				else if(type == 'object')
				{
					for(var cell_idx in _class[row_idx])
					{
						if(cell_idx == '_row')
						{
							$grid.jqGrid('setRowData', row_idx, '', _class[row_idx][cell_idx]);
						}
						else
						{
							console.log(row_idx);
							console.log(cell_idx);
							console.log(_class[row_idx][cell_idx]);
							$grid.jqGrid('setCell', row_idx, cell_idx, '', _class[row_idx][cell_idx]);
						}
					}
				}
			}
		});
	},
	
	'extFooterAgg'	: function()
	{
		return this.each(function()
		{
			var $grid = $(this);
			if(typeof $grid.jqGrid('getGridParam', 'userData')['agg'] != 'object') return;
			console.dir($grid.jqGrid('getGridParam', 'userData')['agg']);
			$grid.jqGrid('footerData', 'set', $grid.jqGrid('getGridParam', 'userData')['agg']);
		});
	},
	
	'extExport'	: function(data, success)
	{
		return this.each(function(type)
		{
			$grid = $(this);
			
			var url  = $grid.jqGrid('getGridParam', 'url');
			var postData = $grid.jqGrid('getGridParam', 'postData');
			
			var $frame = $('<iframe src="' + url + '&' + $.param($.extend(null, postData, {'_out' : 'export'}, data)) + '" style="display:none;"></iframe>');
			
			$frame.load(function()
			{
				if($.isFunction(success))
				{
					success.call($grid);
				}
				
				$grid.jqGrid('extLoading', false);
			});
			
			
			$grid.jqGrid('extLoading', true);
			$('html').append($frame);
		});
	},
	
	/* under construction */
	'extPreserve' : function()
	{
		this.delegate('TR.jqgrow', 'click', function()
		{
			var $grid = $(this).closest('.ui-jqgrid-btable');
			
			if($grid.jqGrid('getGridParam', 'multiselect'))
			{
				
			}
		});
	}
});