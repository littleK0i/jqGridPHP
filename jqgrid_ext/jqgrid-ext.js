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
	errorHandler : function(obj)
	{
		if(obj.error)
		{
			$.jgrid.info_dialog($.jgrid.errors.errcap,obj.error_msg,$.jgrid.edit.bClose);
		}
	},
	ajaxFormProxy: function(opts, act)
	{
		//get url
		opts.url = $(this).getGridParam('url');
		
		//use normal ajax-call for del
		if(act.substring(0, 4) == 'del_')
		{
			$.ajax(opts);
		}
		
		//force iframe
		opts.iframe = true;
		
		var $form = $('#FrmGrid_' + $(this).getGridParam('id'));
		var ele = $form.find('INPUT,TEXTAREA').not(':file');
		
		//Prevent non-file inputs double serialization
		ele.each(function()
		{
			$(this).data('name', $(this).attr('name')).removeAttr('name');
		});
		
		//Send only previously generated data + files
		$form.ajaxSubmit(opts);
		
		//Set names back after form being submitted
		setTimeout(function()
		{
			ele.each(function()
			{
				$(this).attr('name', $(this).data('name'));
			});
		}, 200);
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
		
		return $.jgrid.format('<a href="{0}" class="{1}" target="{2}">{3}</a>', href, opt['class'], opt.target, cellvalue);
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
	
	'extRequest'		: function(data, options)
	{
		
		return this.each(function()
		{
			var $t = this;
			var $grid = $(this);
			
			//-----------
			// Settings
			//-----------
			
			var settings = {
				'url'		: $grid.getGridParam('url'),
				'selrow'    : false,
				'lock'      : true,
				'reload'    : true,
				'success'   : null,
				'error'		: null
			};
			
			$.extend(settings, options);
			
			//-----------
			// Prepare
			//-----------
			
			var postData = {};
			
			if(settings.selrow)
			{
				if($grid.jqGrid('getGridParam', 'multiselect'))
				{
					postData['id[]'] = $grid.jqGrid('getGridParam', 'selarrrow');
					
					if(postData['id[]'].length < 1)
					{
						$.jgrid.info_dialog($.jgrid.errors.errcap,$.jgrid.errors.norecords,$.jgrid.edit.bClose);
						return;
					}
				}
				else
				{
					postData['id'] = $grid.jqGrid('getGridParam', 'selrow');
					
					if(!postData['id'])
					{
						$.jgrid.info_dialog($.jgrid.errors.errcap,$.jgrid.errors.norecords,$.jgrid.edit.bClose);
						return;
					}
				}
			}
			
			if(settings.lock)
			{
				$grid.jqGrid('extLoading');
			}
			
			//-----------
			// Request
			//-----------
			
			$.post(settings.url, $.extend(postData, data), function(ret)
			{
				if(ret.error)
				{
					if(settings.error)
					{
						settings.error.call($t, ret);
					}
					else
					{
						$.jgrid.ext.errorHandler(ret);
					}
				}
				else if($.isFunction(settings.success))
				{
					settings.success.call($t, ret);
				}
					
				if(settings.lock)
				{
					$grid.jqGrid('extLoading', false);
				}
				
				if(settings.reload)
				{
					$grid.trigger('reloadGrid');
				}
			}, 'json');
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