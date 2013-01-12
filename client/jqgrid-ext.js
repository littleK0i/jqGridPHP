//---------------
// Override some defaults
//---------------

$.extend($.jgrid.defaults,
{
	loadComplete : function(data)
	{
		$.jgrid.ext.errorHandler(data);
	}
});

//----------------
// Ext Functions
//---------------

$.jgrid.ext = 
{
	errorHandler : function(obj)
	{
		if(obj && obj.error)
		{
			$.jgrid.info_dialog($.jgrid.errors.errcap,obj.error_msg,$.jgrid.edit.bClose);
		}
	},
	ajaxFormProxy: function(opts, act)
	{
		opts.url = $(this).getGridParam('url');
		opts.iframe = true;
		
		var $form = $('#FrmGrid_' + $(this).getGridParam('id'));
		
		//use normal ajax-call when no files to upload
		if($form.find(':file[value!=""]').size() == 0)
		{
			$.ajax(opts);
			return;
		}

		//Prevent non-file inputs double serialization
		var ele = $form.find(':input').not(':file');
		
		ele.each(function()
		{
			$(this).data('name', $(this).attr('name'));
			$(this).removeAttr('name');
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
		'target': ''
	}
});

$.extend($.fn.fmatter, 
{
	ext_link: function(cellvalue, options, rowdata, act)
	{
		var def = options.ext_link;
		var opt = options.colModel.formatoptions;
		
		opt = $.extend(null, def, opt);
		
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
		
		return $.jgrid.format('<a href="{0}" class="{1}" target="{2}">{3}</a>', href, opt['class'], opt['target'], cellvalue);
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
	
	recreateForm : true
});

$.extend($.jgrid.del,
{
	afterSubmit: function(data)
	{
		var json = $.jgrid.parse(data.responseText);

		if(typeof(json.error_msg) != 'undefined')
		{
			return [false, json.error_msg, 0];
		}

		return [true, null, 0];
	},

	recreateForm : true
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
			
			if(!$.browser.msie && !$.browser.opera)
			{
				$grid.jqGrid('extLoading', true);
			}
			
			$('html').append($frame);
		});
	},
	
	'extBindEvents' : function()
	{
		$(this).bind('jqGridGridComplete', function()
		{
			$(this).jqGrid('extHighlight');
			$(this).jqGrid('extFooterAgg');
		});
	},
	
	'extGroupHeader': function(opts)
	{
		var $t = this[0];
		
		var $labels = $(this).closest('.ui-jqgrid-view').find('.ui-jqgrid-htable .ui-jqgrid-labels');
		var $first_row = $labels.clone().removeClass('ui-jqgrid-labels').removeAttr('role');
		var $group_row = $('<tr>');
		
		var $th = $('<th>').addClass('ui-state-default ui-th-ltr');

		var skip = 0;
		var free = 0;
		
		for(var i in $t.p.colModel)
		{
			if($t.p.colModel[i].hidden)
			{
				$th.clone().hide().appendTo($group_row);
				continue;
			}
		
			if(skip)
			{
				skip--;
				continue;
			}
			
			var idx = $t.p.colModel[i].name;
			
			if(opts[idx])
			{
				if(free)
				{
					$th.clone().attr('colspan', free).appendTo($group_row);
					free = 0;
				}
				
				$th.clone().attr('colspan', opts[idx][0]).text(opts[idx][1]).appendTo($group_row);
				skip = opts[idx][0] - 1;
			}
			else
			{
				free++;
			}
		}
		
		//last free th
		if(free)
		{
			$th.clone().attr('colspan', free).appendTo($group_row);
		}
		
		$first_row.find('TH').height(0).text('').removeAttr('role').removeAttr('id');
		$labels.before($first_row).before($group_row);
		
		//preserve orig event
		if($.isFunction($t.p.resizeStop))
		{
			var resizeStop = $t.p.resizeStop;
		}
		
		$t.p.resizeStop = function(nw,idx)
		{
			$first_row.find('TH').eq(idx).width(nw);
			if($.isFunction(resizeStop)) resizeStop.call(this, nw, idx);
		};
	},
	
	'updateGroupHeaderWR' : function()
	{
		return this.each(function()
		{
			var $t = this;
			if(!$t.grid || !$t.p.groupHeaderWR) return;
			
			var $hDiv   = $($t.grid.hDiv);
			var $labels = $hDiv.find('.ui-jqgrid-labels');
			
			var $first_row = $labels
				.clone()
				.removeClass('ui-jqgrid-labels')
				.removeAttr('role')
				.addClass('ui-jqgrid-labels-firstrow');
				
			$first_row.find('TH').height(0).text('').removeAttr('role').removeAttr('id');
			
			var $group_row = $('<tr>').addClass('ui-jqgrid-labels-grouprow');
			
			var th = '<th class="ui-state-default ui-th-' + $t.p.direction + '"></th>';
			
			//Iterate columns
			var colspan = 0;
			var prev_hgroup = null;
			
			for(var i in $t.p.colModel)
			{
				var col = $t.p.colModel[i];
				var hgroup = col.hgroup ? col.hgroup : '';
				
				if(col.hidden) continue;
				if(prev_hgroup === null)  prev_hgroup = hgroup; //first non-hidden column becomes initial group
				
				if(prev_hgroup == hgroup)
				{
					colspan++;
				}
				else
				{
					var $th = $(th).attr('colspan', colspan);
					if($t.p.groupHeaderWR[prev_hgroup]) $th.text($t.p.groupHeaderWR[prev_hgroup].label);
					$th.appendTo($group_row);
					
					prev_hgroup = hgroup;
					colspan = 1;
				}
			}
			
			//Last th
			if(colspan)
			{
				var $th = $(th).attr('colspan', colspan);
				if($t.p.groupHeaderWR[prev_hgroup]) $th.text($t.p.groupHeaderWR[prev_hgroup].label);
				$th.appendTo($group_row);
			}
			
			//Update DOM
			$hDiv.find('.ui-jqgrid-labels-firstrow, .ui-jqgrid-labels-grouprow').remove();
			$labels.before($first_row).before($group_row);
			
			//Preserve orig event
			//we have to move it to the core of resizing
			if($.isFunction($t.p.resizeStop))
			{
				var resizeStop = $t.p.resizeStop;
			}
			
			$t.p.resizeStop = function(nw,idx)
			{
				$first_row.find('TH').eq(idx).width(nw);
				if($.isFunction(resizeStop)) resizeStop.call(this, nw, idx);
			};
		});
	},
	
	'destroyGroupHeaderWR' : function()
	{
		return this.each(function()
		{
			var $t = this;
			if(!$t.grid) return;
			
			$($t.grid.hDiv).find('.ui-jqgrid-labels-firstrow, .ui-jqgrid-labels-grouprow').remove();
		});
	}
});