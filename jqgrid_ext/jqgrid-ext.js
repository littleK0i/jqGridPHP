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
	afterSubmit: function(xhr)
	{
		data = $.jgrid.parse(xhr.responseText);
		
		var new_id = (typeof(data.new_id) != 'undefined') ? data.new_id : null;
		
		if(typeof(data.error_msg) != 'undefined')
		{
			return [false, data.error_msg, new_id];
		}
		
		return [true, null, new_id];
	}
});

//---------------
// Ext Functions
//---------------

$.jgrid.extend(
{
	'getColIndex'	: function(col)
	{
		if(!isNan(col)) return col;
		
		var idx = -1;
		
		this.each(function()
		{
			var colModel = $(this).jqGrid('getGridParam', 'colModel');
			
			for(var i in colModel)
			{
				if(colModel[i].name === col)
				{
					idx = i;
					break;
				}
			}
		});
		
		return idx;
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