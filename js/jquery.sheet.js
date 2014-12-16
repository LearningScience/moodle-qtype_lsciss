/*
jQuery.sheet() Spreadsheet with Calculations Plugin
Version: 1.01
http://code.google.com/p/jquerysheet/
		
Copyright (C) 2010 Robert Plummer
Dual licensed under the LGPL and GPL licenses.
http://www.gnu.org/licenses/
*/

/*
	Dimensions Info:
		When dealing with size, it seems that outerHeight is generally the most stable cross browser
		attribute to use for bar sizing.  We try to use this as much as possible.  But because col's
		don't have boarders, we subtract or add jS.attrH.boxModelCorrection() for those browsers.
	tr/td column and row Index VS cell/column/row index
		DOM elements are all 0 based (tr/td/table)
		Spreadsheet elements are all 1 based (A1, A1:B4, TABLE2:A1, TABLE2:A1:B4)
		Column/Row/Cell
	sheet import and export methods structure (jS.importSheet.xml(obj), jS.importSheet.json(obj), jS.exportSheet.xml(), jS.exportSheet.json());
		xml structure:
			//xml
			<documents>
				<document> //repeats
					<metadata>
						<columns>{Column_Count}</columns>
						<rows>{Row_Count}</rows>
						<title></title>
					</metadata>
					<data>
						<r{Row_Index}> //repeats
							<c{Column_Index}></c{Column_Index}> //repeats
						</r{Row_Index}>
					</data>
				</document>
			</documents>
		json structure:
			var documents = [
				document: { //repeats
					metadata: {
						columns: Column_Count,
						rows: Row_Count,
						title: ''
					},
					data: {
						r{Row_Index}: { //repeats
							c{Column_Index}: '' //repeats
						}
					}
				}
			];
	DOCTYPE:
		It is recommended to use STRICT doc types on the viewing page when using sheet to ensure that the heights/widths of bars and sheet rows show up correctly
		Example of recommended doc type: <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
*/

jQuery.fn.extend( {
	sheet: function (settings) {
		settings = jQuery.extend( {
			urlGet: "type/lsspreadsheet/js/documentation.html", //local url, if you want to get a sheet from a url
            urlLsControls : "type/lsspreadsheet/lscontrols.html",
            urlSave: "save.html", //local url, for use only with the default save for sheet
			editable: true, //bool, Makes the jSheetControls_formula & jSheetControls_fx appear
            lssquestion: true, //show ls spreadsheet question menu
			urlMenu: null,//"type/lsspreadsheet/js/menu.html", //local url, for the menu to the right of title
			newColumnWidth: 120, //int, the width of new columns or columns that have no width assigned
			title: null, //html, general title of the sheet group
			inlineMenu:	null, //html, menu for editing sheet
			buildSheet: false, //bool, string, or object
								//bool true - build sheet inside of parent
								//bool false - use urlGet from local url
								//string  - '{number_of_cols}x{number_of_rows} (5x100)
																//object - table
			calcOff: 		false, //bool, turns calculationEngine off (no spreadsheet, just grid)
			log: 			false, 							//bool, turns some debugging logs on (jS.log('msg'))
			lockFormulas: 	false, 							//bool, turns the ability to edit any formula off
			parent: 		this, 							//object, sheet's parent, DON'T CHANGE
			colMargin: 		18, 							//int, the height and the width of all bar items, and new rows
			fnBefore: 		function() {}, 					//fn, fires just before jQuery.sheet loads
			fnAfter: 			function() {}, 				//fn, fires just after all sheets load
			fnSave: 		function() {jS.saveSheet();}, //fn, default save function, more of a proof of concept
			fnOpen: 		function() { 					//fn, by default allows you to paste table html into a javascript prompt for you to see what it looks likes if you where to use sheet
				var t = prompt('Paste your table html here');
				if (t) {
					jS.openSheet(t);
				}
			},
			fnClose: 		function() {}, //fn, default clase function, more of a proof of concept
			joinedResizing: false, //bool, this joins the column/row with the resize bar
			boxModelCorrection: 2 //int, attempts to correct the differences found in heights and widths of different browsers, if you mess with this, get ready for the must upsetting and delacate js ever
		}, settings);
		jQuery.fn.sheet.settings = jS.s = settings;
		jS.s.fnBefore();
		
		var obj;
		if (jS.s.buildSheet) {//override urlGet, this has some effect on how the topbar is sized
			if (typeof(jS.s.buildSheet) == 'object') {
				obj = jS.s.buildSheet;
			} else if (jS.s.buildSheet == true || jS.s.buildSheet == 'true') {
				obj = jQuery(jQuery(this).html());
			} else if (jS.s.buildSheet.match(/x/i)) {
				obj = jS.controlFactory.sheet(jS.s.buildSheet);
			}
		}
		
		//We need to take the sheet out of the parent in order to get an accurate reading of it's height and width
		//jQuery(this).html(jS.s.loading);
		jQuery(this).html('');
		
		jS.s.width = jQuery(this).width();
		jS.s.height = jQuery(this).height();
		
		if (jS.s.log) {
			jQuery(jS.s.parent).after('<textarea id="' + jS.id.log + '" />');
		} else {
			jS.log = function() {}; //save time in recursion
		}
		
		if (!jQuery.scrollTo) {
			jS.followMe = function() {};
		}
		
		jS.log('Startup');
		

		//Make functions upper and lower case compatible
		for (var k in cE.fn) {
			var kLower = k.toLowerCase();
			if (kLower != k) {
				cE.fn[kLower] = cE.fn[k];
			}
		}
		
		jQuery(window).resize(function() {
			jS.s.width = jQuery(jS.s.parent).width();
			jS.s.height = jQuery(jS.s.parent).height();
			jS.sheetSyncSize();
		});
		
		jS.openSheet(obj);		
	}
});

var jS = jQuery.sheet = {
	version: '1.01',
	i: 0,
	sheetCount: 0,
	s: {},//s = settings object, used for shorthand, populated from jQuery.sheet
	obj: {//obj = object references
		parent: 			function() {return jQuery(jS.s.parent);},
		ui:					function() {return jQuery('#' + jS.id.ui);},
		sheet: 				function() {return jQuery('#' + jS.id.sheet + jS.i);},
		sheetAll: 			function() {return jQuery('.' + jS.cl.sheet);},
		barTop: 			function() {return jQuery('#' + jS.id.barTop + jS.i);},
		barTopAll:			function() {return jQuery('.' + jS.cl.barTop);},
		barTopParent: 		function() {return jQuery('#' + jS.id.barTopParent + jS.i);},
		barTopParentAll:	function() {return jQuery('.' + jS.cl.barTopParent);},
		barLeft: 			function() {return jQuery('#' + jS.id.barLeft + jS.i);},
		barLeftAll:			function() {return jQuery('.' + jS.cl.barLeft);},
		barLeftParent: 		function() {return jQuery('#' + jS.id.barLeftParent + jS.i);},
		barLeftParentAll:	function() {return jQuery('.' + jS.cl.barLeftParent);},
		barCorner:			function() {return jQuery('#' + jS.id.barCorner + jS.i);},
		barCornerAll:		function() {return jQuery('.' + jS.cl.barCorner);},
		barCornerParent:	function() {return jQuery('#' + jS.id.barCornerParent + jS.i);},
		barCornerParentAll: function() {return jQuery('.' + jS.cl.barCornerParent);},
		barSelected:		function() {return jQuery('.' + jS.cl.barSelected);},
		cell: 				function() {return jQuery('.' + jS.cl.cell);},
		controls:			function() {return jQuery('#' + jS.id.controls);},
		formula: 			function() {return jQuery('#' + jS.id.formula);},
		label: 				function() {return jQuery('#' + jS.id.label);},
		fx:					function() {return jQuery('#' + jS.id.fx);},
		pane: 				function() {return jQuery('#' + jS.id.pane + jS.i);},
		paneAll:			function() {return jQuery('.' + jS.cl.pane);},
		log: 				function() {return jQuery('#' + jS.id.log);},
		menu:				function() {return jQuery('#' + jS.id.menu);},
		uiDefault:			function() {return jQuery('.' + jS.cl.uiDefault);},
		uiActive:			function() {return jQuery('.' + jS.cl.uiActive);},
		uiBase:				function() {return jQuery('.' + jS.cl.uiBase);},
		uiCell:				function() {return jQuery('.' + jS.cl.uiCell);},
		toggle:				function() {return jQuery('.' + jS.cl.toggle);},
		tableBody: 			function() {return document.getElementById(jS.id.sheet + jS.i);},
		tableControl:		function() {return jQuery('#' + jS.id.tableControl + jS.i);},
		tableControlAll:	function() {return jQuery('.' + jS.cl.tableControl);},
		tab:				function() {return jQuery('#' + jS.id.tab + jS.i);},
		tabAll:				function() {return jQuery('.' + jS.cl.tab);},
		tabContainer:		function() {return jQuery('#' + jS.id.tabContainer);}
	},
	id: {//id = id's references
		sheet: 			'jSheet',//This con probably be just about any value as long as it's not a duplicated id
		ui:				'jSheetUI',
		barTop: 		'jSheetBarTop',
		barTopParent: 	'jSheetBarTopParent',
		barLeft: 		'jSheetBarLeft',
		barLeftParent: 	'jSheetBarLeftParent',
		barCorner:		'jSheetBarCorner',
		barCornerParent:'jSheetBarCornerParent',
		controls:		'jSheetControls',
		formula: 		'jSheetControls_formula',
		label: 			'jSheetControls_loc',
		fx:				'jSheetControls_fx',
		pane: 			'jSheetEditPane',
		log: 			'jSheetLog',
		menu:			'jSheetMenu',
		tableControl:	'tableControl',
		tab:			'jSheetTab',
		tabContainer:	'jSheetTabContainer'
	},
	cl: {//cl = class references
		sheet: 			'jSheet',
		barTop: 		'jSheetBarTop',
		barTopParent: 	'jSheetBarTopParent',
		barLeft: 		'jSheetBarLeft',
		barLeftParent: 	'jSheetBarLeftParent',
		barCorner:		'jSheetBarCorner',
		barCornerParent:'jSheetBarCornerParent',
		pane: 			'jSheetEditPane',
		cell: 			'jSheetCellActive',
		barSelected: 	'jSheetBarItemSelected',
		uiDefault:		'ui-state-default',
		uiActive:		'ui-state-active',
		uiBase:			'ui-widget-content',
		uiParent: 		'ui-widget-content ui-corner-all',
		uiSheet:		'ui-widget-content',
		uiBar: 			'ui-widget-header',
		uiPane: 		'ui-widget-content',
		uiMenuUl: 		'ui-widget-header',
		uiMenuLi: 		'ui-widget-header',
		uiMenuHighlighted: 'ui-state-highlight',
		uiControl: 		'ui-widget-header ui-corner-top',
		uiControlTextBox:'ui-widget-content',
		uiCell:			'themeRoller_activeCell',
		uiCellHighlighted: 'ui-state-highlight',
		tableControl:	'tableControl',
		toggle:			'cellStyleToggle',
		tab:			'jSheetTab',
		barTopTd:		'barTop',
		barLeftTd:		'barLeft',
		sheetPaneTd:	'sheetPane'
	},
	controlFactory: {
		addRowMulti: function(qty) {
			if (!qty) {
				qty = prompt('How many rows would you like to add?');
			}
			if (qty) {
				for (var i = 0; i <= qty; i++) {
					jS.controlFactory.addRow();
				}
			}
			jS.setTdIds();
		},
		addColumnMulti: function(qty) {
			if (!qty) {
				qty = prompt('How many columns would you like to add?');
			}
			if (qty) {
				for (var i = 0; i <= qty; i++) {
					jS.controlFactory.addColumn();
				}
			}
			jS.setTdIds();
		},
		addRow: function(atRow, insertBefore, atRowQ) {
			if (!atRowQ) {
				if (!atRow && jS.rowLast > -1) {
					atRowQ = ':eq(' + jS.rowLast + ')';
				} else if (!atRow || jS.cellLast.row < 1) {
					//if atRow has no value, lets just add it to the end.
					atRowQ = ':last';
					atRow = false;
				} else if (atRow === true) {//if atRow is boolean, then lets add it just after the currently selected row.
					atRowQ = ':eq(' + (jS.cellLast.row - 1) + ')';
				} else {
					//If atRow is a number, lets add it at that row
					atRowQ = ':eq(' + (atRow - 1) + ')';
				}
			}
			
			jS.evt.cellEditAbandon();
			var currentRow = jS.obj.sheet().find('tr' + atRowQ);
			var newRow = currentRow.clone();
			newRow.find('td').andSelf().height(jS.attrH.height(currentRow.find('td:first'), true));
			
			jQuery(newRow).find('td')
				.html('')
				.attr('class', '')
				.removeAttr('formula')
				.keydown(function(e) {
					return jS.evt.formulaKeyDown(e, true);
				});
			if (insertBefore) {
				newRow.insertBefore(currentRow);
			} else {
				newRow.insertAfter(currentRow);
			}
			
			var currentBar = jS.obj.barLeft().find('div' + atRowQ);
			var newBar = currentBar.clone();
			
			jS.themeRoller.newBar(newBar);
			
			newBar
				.html(parseInt(currentBar.text()) + 1)
				.removeClass(jS.cl.uiActive)
				.height(jS.attrH.height(newRow));
			
			//jS.log('New row at: ' + (parseInt(currentBar.text()) + 1));
			
			if (insertBefore) {
				newBar.insertBefore(currentBar);
			} else {
				newBar.insertAfter(currentBar);
			}
			
			if (atRow || atRowQ) {//If atRow equals anything it means that we inserted at a point, because of this we need to update the labels
				jS.obj.barLeft().find('div').each(function(i) {
					jQuery(this).text(i + 1);
				});
			}

			jS.setTdIds();
			jS.obj.pane().scroll();
		},
		addColumn: function(atColumn, insertBefore, atColumnQ) {
			if (!atColumnQ) {
				if (!atColumn && jS.colLast > -1) {
					atColumn = ':eq(' + jS.colLast + ')';
				} else if (!atColumn || jS.cellLast.col < 1) {
					//if atColumn has no value, lets just add it to the end.
					atColumn = ':last';
				} else if (atColumn === true) {
					//if atColumn is boolean, then lets add it just after the currently selected row.
					atColumn = ':eq(' + (jS.cellLast.col - 1) + ')';
				} else {
					//If atColumn is a number, lets add it at that row
					atColumn = ':eq(' + (atColumn - 1) + ')';
				}
			} else {
				atColumn = atColumnQ;
			}

			jS.evt.cellEditAbandon();
			
			//there are 3 obj that need managed here div, col, and each tr's td
			//Lets get the current div & col, then later we go through each row
			var currentBar = jS.obj.barTop().find('div' + atColumn);
			var currentCol = jS.obj.sheet().find('col' + atColumn);
			
			//Lets create our new bar, cell, and col
			var newBar = currentBar.clone().width(jS.s.newColumnWidth - jS.attrH.boxModelCorrection());
			var newCol = currentCol.clone().width(jS.s.newColumnWidth);
			var newCell = jQuery('<td></td>');
			
			//This is just to get the new label
			var currentIndex = cE.columnLabelIndex(currentBar.text());
			var newLabel = cE.columnLabelString(currentIndex + 1);
			jS.log('New Column: ' + currentIndex + ', ' + newLabel);
			
			if (insertBefore) {
				currentCol.before(newCol);
				currentBar.before(newBar);
			} else {
				currentCol.after(newCol);
				currentBar.after(newBar);
			}
				
			//Add new spreadsheet column to top
			
			var j = 0;
			var addNewCellFn;
			if (insertBefore) {
				addNewCellFn = function(obj) {
					jQuery(obj).find('td' + atColumn).before(
						newCell.clone()
					);
				};
			} else {
				addNewCellFn = function(obj) {
					jQuery(obj).find('td' + atColumn).after(
						newCell.clone()
					);
				};
			}
			
			jS.obj.sheet().find('tr').each(function(i) {
				addNewCellFn(this);
				j++;
			});
			
			//jS.log('Sheet length: ' + j);		
			
			if (atColumn) {//If atColumn equals anything it means that we inserted at a point, because of this we need to update the labels
				jS.obj.barTop().find('div').each(function(i) {
					jQuery(this).text(cE.columnLabelString(i + 1));
				});
			}
			
			jS.attrH.syncSheetWidthFromTds();
			
			jS.setTdIds();
			jS.obj.pane().scroll();
		},
		barLeft: function(reload, o) {//Works great!
			jS.obj.barLeft().remove();
			var barLeft = jQuery('<div border="1px" id="' + jS.id.barLeft + jS.i + '" class="' + jS.id.barLeft + '" />').height('10000px');
			var heightFn;
			if (reload) { //This is our standard way of detecting height when a sheet loads from a url
				heightFn = function(i, objSource, objBar) {
					objBar.height(parseInt(objSource.outerHeight()) - jS.attrH.boxModelCorrection());
				};
			} else { //This way of detecting height is used becuase the object has some problems getting
					//height because both tr and td have height set
					//This corrects the problem
					//This is only used when a sheet is already loaded in the pane
				heightFn = function(i, objSource, objBar) {
					objBar.height(parseInt(objSource.css('height').replace('px','')) - jS.attrH.boxModelCorrection());
				};
			}
			
			jS.evt.barMouseDown.height(barLeft);
			
			o.find('tr').each(function(i) {
				var child = jQuery('<div>' + (i + 1) + '</div>');
				jQuery(barLeft).append(child);
				heightFn(i, jQuery(this), child);
			});
			barLeft.appendTo(jS.obj.barLeftParent());
		},
		barTop: function(reload, o) { //Works great!
			jS.obj.barTop().remove();
			var barTop = jQuery('<div id="' + jS.id.barTop + jS.i + '" class="' + jS.id.barTop + '" />').width('10000px');
			barTop.height(jS.s.colMargin);
			
			var parents;
			var widthFn;
			
			if (reload) {
				parents = o.find('tr:first td');
				widthFn = function(obj) {
					return jS.attrH.width(obj);
				};
			} else {
				parents = o.find('col');
				widthFn = function(obj) {
					return parseInt(jQuery(obj).css('width').replace('px','')) - jS.attrH.boxModelCorrection();
				};
			}
			
			jS.evt.barMouseDown.width(barTop);
			
			parents.each(function(i) {
				var v = cE.columnLabelString(i + 1);
				var w = widthFn(this);
				
				var child = jQuery("<div>" + v + "</div>")
					.width(w)
					.height(jS.s.colMargin);
				barTop.append(child);
			});
			
			// Prepend one colgroup/col element that covers the new row headers.
			//jS.attrH.syncSheetWidthFromTds();
			
			jS.obj.barTopParent().append(barTop);
		},
		header: function() {
			jS.obj.controls().remove();
			jS.obj.tabContainer().remove();
			
			var header = jQuery('<div id="' + jS.id.controls + '"></div>');
			
			var firstRow = jQuery('<table cellpadding="0" cellspacing="0" border="0"><tr /></table>').prependTo(header);
			var firstRowTr = jQuery('<tr />');
			
			if (jS.s.title) {
				firstRowTr.append(jQuery('<td style="width: auto;text-align: center;" />').html(jS.s.title));
			}
			
			if (jS.s.inlineMenu && jS.s.editable) {
				firstRowTr.append(jQuery('<td style="text-align: center;" />').html(jS.s.inlineMenu));

			}
			if (jS.s.editable) {
				//Page Menu Control	
				if (jQuery.mbMenu) {
					jQuery('<div />').load(jS.s.urlMenu, function(o) {
						jQuery('<td style="width: 50px; text-align: center;" id="' + jS.id.menu + '" class="rootVoices ui-corner-tl" />')
							.html(o)
							.prependTo(firstRowTr)
							.buildMenu({
								menuWidth:		100,
								openOnRight:	false,
								containment: 	jS.s.parent.id,
								hasImages:		false,
								fadeInTime:		0,
								fadeOutTime:	0,
								adjustLeft:		2,
								minZindex:		"auto",
								adjustTop:		10,
								opacity:		.95,
								shadow:			false,
								closeOnMouseOut:true,
								closeAfter:		1000
							})
							.hover(function() {
								jQuery(this).addClass('ui-state-highlight');
							}, function() {
								jQuery(this).removeClass('ui-state-highlight');
							});
					})
					.hover(function() {
						jQuery(this).addClass('ui-state-highlight');
					}, function() {});
				}
				
				//Edit box menu
				var secondRow = jQuery('<table cellpadding="0" cellspacing="0" border="0">' +
						'<tr>' +
							'<td style="width: 35px; text-align: right;" id="' + jS.id.label + '"></td>' +
							'<td style="width: 10px;" id="' + jS.id.fx + '">fx</td>' + 
							'<td>' +
								'<textarea id="' + jS.id.formula + '"></textarea>' +
							'</td>' +
						'</tr>' +
					'</table>').appendTo(header);
					
				secondRow.keydown(function(e) {
					return jS.evt.formulaKeyDown(e);
				});
			}
            if (jS.s.lssquestion) {
                var thirdRow = jQuery('<div id="lsscontrols_div></div>"');
                thirdRow.appendTo(header);
            }
			
			firstRowTr.appendTo(firstRow);



            
            var tabParent = jQuery('<div id="' + jS.id.tabContainer + '">' + 
							(jS.s.editable ? '<span class="ui-widget-header ui-corner-bottom" title="Add a spreadsheet" i="-1">+</span>' : '<span />') + 
						'</div>')
					.mousedown(jS.evt.tabOnMouseDown);

			jS.obj.parent()
				.html('')
				.append(header) //add controls header
				.append('<div id="' + jS.id.ui + '" class="' + jS.id.ui + '">'); //add spreadsheet control
				//.after(tabParent);
		},
		sheet: function(size) {
			if (!size) {
				size = jS.s.buildSheet;
			}
			size = size.toLowerCase().split('x');

			var columnsCount = parseInt(size[0]);
			var rowsCount = parseInt(size[1]);
			
			//Create elements before loop to make it faster.
			var newSheet = jQuery('<table border="1px" class="' + jS.cl.sheet + '" id="' + jS.id.sheet + jS.i + '"></table>');
			var standardTd = '<td></td>';
			var tds = '';
			
			//Using -- is many times faster than ++
			for (var i = columnsCount; i >= 1; i--) {
				tds += standardTd;
			}

			var standardTr = '<tr height="' + jS.s.colMargin + '" style="height: ' + jS.s.colMarg + ';">' + tds + '</tr>';
			var trs = '';
			for (var i = rowsCount; i >= 1; i--) {
				trs += standardTr;
			}
			
			newSheet.html('<tbody>' + trs + '</tbody>');
			
			newSheet.width(columnsCount * jS.s.newColumnWidth);
			//jS.attrH.syncSheetWidthFromTds(newSheet);
			
			return newSheet;
		},
		sheetUI: function(obj, i, fn, reloadBars) {
			if (!i) {
				jQuery('.tableControl').remove();
				jS.sheetCount = 0;
				jS.i = 0;
			} else {
				jS.sheetCount++;
				jS.i = jS.sheetCount;
				i = jS.i;
			}
			
			var objContainer = jS.controlFactory.table(true).appendTo(jS.obj.ui());
			jS.obj.pane().html(obj);
					
			jS.tuneTableForSheetUse(obj);
						
			jS.sheetDecorate(obj);
			
			jS.controlFactory.barTop(reloadBars, obj);
			jS.controlFactory.barLeft(reloadBars, obj);
		
			jS.sheetTab(true);
			
			if (jS.s.editable) {
				obj
					.mousedown(jS.evt.cellOnMouseDown)
					.click(jS.s.lockFormulas ? jS.evt.cellOnClickLocked : jS.evt.cellOnClickReg);
			}
			
			jS.themeRoller.start(i);

			jS.setTdIds(obj);
			
			jS.evt.scrollBars();
			
			jS.addTab();
			
			if (fn) {
				fn();
			}
			
			jS.log('Sheet Initialized');
			
			jS.s.fnAfter();
			
			return objContainer;
		},
		table: function() {
			return jQuery('<table cellpadding="0" cellspacing="0" border="0" id="' + jS.id.tableControl + jS.i + '" class="' + jS.cl.tableControl + ' ui-corner-bottom">' +
				'<tbody>' +
					'<tr>' + 
						'<td id="' + jS.id.barCornerParent + jS.i + '" class="' + jS.cl.barCornerParent + '">' + //corner
							'<div style="height: ' + jS.s.colMargin + '; width: ' + jS.s.colMargin + ';" id="' + jS.id.barCorner + jS.i + '" class="' + jS.cl.barCorner +'" onClick="jS.cellSetActiveAll();" title="Select All">&nbsp;</div>' +
						'</td>' + 
						'<td class="' + jS.cl.barTopTd + '">' + //barTop
							'<div id="' + jS.id.barTopParent + jS.i + '" class="' + jS.cl.barTopParent + '"></div>' +
						'</td>' +
					'</tr>' +
					'<tr>' +
						'<td class="' + jS.cl.barLeftTd + '">' + //barLeft
							'<div style="width: ' + jS.s.colMargin + ';" id="' + jS.id.barLeftParent + jS.i + '" class="' + jS.cl.barLeftParent + '"></div>' +
						'</td>' +
						'<td class="' + jS.cl.sheetPaneTd + '">' + //pane
							'<div id="' + jS.id.pane + jS.i + '" class="' + jS.cl.pane + '"></div>' +
						'</td>' +
					'</tr>' +
				'</tbody>' +
			'</table>');
		},
		chart: function(type, data, legend, axisLabels, w, h, row) {
			if (jGCharts) {
				var api = new jGCharts.Api();
				function refine(v) {
					var refinedV = new Array();
					jQuery(v).each(function(i) {
						refinedV[i] = jS.manageHtmlToText(v[i] + '');
					});
					return refinedV;
				}
				var o = {};
				
				if (type) {
					o.type = type;
				}
				
				if (data) {
					data = data.filter(function(v) {return (v ? v : 0);}); //remove nulls
					o.data = data;
				}
				
				if (legend) {
					o.legend = refine(legend);
				}
				
				if (axisLabels) {
					o.axis_labels = refine(axisLabels);
				}
				
				if (w || h) {
					o.size = w + 'x' + h;
				}
				
				return jS.controlFactory.safeImg(api.make(o), row);
			} else {
				return jQuery('<div>Charts are not enabled</div>');
			}
		},
		safeImg: function(src, row) {
			return jQuery('<img />')
				.hide()
				.load(function() { //prevent the image from being too big for the row
					jQuery(this).fadeIn(function() {
						jQuery(this).addClass('safeImg');
						jS.attrH.setHeight(parseInt(row), 'cell', false);
					});
				})
				.attr('src', src);
		}
	},
	sizeSync: {
	
	},
	evt: {
		keyDownHandler: {
			enterOnTextArea: function(e) {
				if (!e.shiftKey) {
					return jS.evt.cellClick(key.DOWN);
				} else {
					return true;
				}
			},
			enter: function(e) {
				if (!jS.cellLast.isEdit && !e.ctrlKey) {
					return jS.evt.cellClick();
				} else {
					return jS.evt.cellClick(key.DOWN);
				}
			},
			tab: function(e) {
				if (e.shiftKey) {
					return jS.evt.cellClick(key.LEFT);
				} else {
					return jS.evt.cellClick(key.RIGHT);
				}
			},
			textAreaKeyDown: function(e) {
				switch (e.keyCode) {
					case key.ENTER:return jS.evt.keyDownHandler.enterOnTextArea(e);
						break;
					case key.TAB:return jS.evt.keyDownHandler.tab(e);
						break;
				}
			},
			formulaKeyDown: function(e) {
				switch (e.keyCode) {
					case key.ESCAPE:jS.evt.cellEditAbandon();break;
					case key.TAB:return jS.evt.keyDownHandler.tab(e);break;
					case key.ENTER:return jS.evt.keyDownHandler.enter(e);break;
					case key.LEFT:
					case key.UP:
					case key.RIGHT:
					case key.DOWN:return jS.evt.cellClick(e.keyCode);break;
					default:jS.cellLast.isEdit = true;
				}
			}
		},
		formulaKeyDown: function(e, isTextArea) {
			//Switch is much faster than if statements
			//I found that it's much easier to go from the origin key (up, down, left, right, tab, enter) and then detect if the ctrl key or shift keys are down.
			//It's just difficult to look at later on and it's probably faster overall
			return (isTextArea ? jS.evt.keyDownHandler.textAreaKeyDown(e) : jS.evt.keyDownHandler.formulaKeyDown(e));
		},
		cellEditDone: function(bsheetClearActive) {
			switch (jS.cellLast.isEdit) {
				case true:
					// Any changes to the input controls are stored back into the table, with a recalc.
					var td = jS.cellLast.td;
					var recalc = false;
					
					//Lets ensure that the cell being edited is actually active
					if (td && td.hasClass(jS.cl.cell)) { 
						//This should return either a val from textbox or formula, but if fails it tries once more from formula.
						var v = jS.cellTextArea(td, true);

						//inputFormula.value;
						var noEditFormula = false;
						var noEditNumber = false;
						var noEditNull = false;
						var editedFormulaToFormula = false;
						var editedFormulaToReg = false;
						var editedRegToFormula = false;
						var editedRegToReg = false;
						var editedToNull = false;
						var editedNumberToNumber = false;
						var editedNullToNumber = false;
						
						var tdFormula = td.attr('formula');
						var tdPrevVal = td.attr('prevVal');

						if (v) {
							if (v.charAt(0) == '=') { //This is now a formula
								if (v != tdFormula) { //Didn't have a formula before but now does
									editedFormulaToFormula = true;
									jS.log('edit, new formula, possibly had formula');
								} else if (tdFormula) { //Updated using inline edit
									noEditFormula = true;
									jS.log('no edit, has formula');
								} else {
									jS.log('no edit, has formula, unknown action');
								}
							} else if (tdFormula) { //Updated out of formula
								editedRegToFormula = true;
								jS.log('edit, new value, had formula');
							} else if (!isNaN(parseInt(v))) {
								if ((v != tdPrevVal && v != jS.obj.formula().val()) || (td.text() != v)) {
									editedNumberToNumber = true;
									jS.log('edit, from number to number, possibly in function');
								} else {
									noEditNumber = true;
									jS.log('no edit, is a number');
								}
							} else { //Didn't have a formula before of after edit
								editedRegToReg = true;
								jS.log('possible edit from textarea, has value');
							}
						} else { //No length value
							if (td.html().length > 0 && tdFormula) {
								editedFormulaToReg = true;
								jS.log('edit, null value from formula');
							} else if (td.html().length > 0 && tdFormula) {
								editedToNull = true;
								jS.log('edit, null value from formula');
							
							} else {
								noEditNull = true;
								jS.log('no edit, null value');
							}
						}
						
						td.removeAttr('prevVal');
						var vHTML = jS.manageTextToHtml(v);
						if (noEditFormula) {
							td.html(tdPrevVal);
						} else if (editedFormulaToFormula) {
							recalc = true;
                            //want to keep formula visible if calcOff==true
                            if(jS.s.calcOff){
                                td.attr('formula', v.replace(/\n/g, ' ')).html(v);
                            } else {
                                td.attr('formula', v.replace(/\n/g, ' ')).html('');
                            }
						} else if (editedFormulaToReg) {
							recalc = true;
							td.removeAttr('formula').html(vHTML);
						} else if (editedRegToFormula) {
							recalc = true;
							td.removeAttr('formula').html(vHTML);
						} else if (editedRegToReg) {
							td.html(vHTML);
						} else if (noEditNumber) {
							td.html(vHTML); 
						} else if (noEditNull) {
							td.html(vHTML);
						} else if (editedNumberToNumber) {
							recalc = true;
							td.html(vHTML);
						} else if (editedToNull) {
							recalc = true;
							td.removeAttr('formula').html('');
						}
						
						if (recalc) {
							jS.calc(jS.i);
						}
						
						if (bsheetClearActive != false) {
							// Treats null == true.
							jS.sheetClearActive();
						}
						
						jS.attrH.setHeight(jS.cellLast.row, 'cell');
						
						jS.obj.formula().focus().select();
						jS.cellLast.isEdit = false;
					}
					break;
				default:
					jS.attrH.setHeight(jS.cellLast.row, 'cell', false);
					jS.sheetClearActive();
			}
		},
		cellEditAbandon: function(skipCalc) {
			jS.themeRoller.clearCell();
			jS.themeRoller.clearBar();
			if (!skipCalc) {
				var v = jS.cellTextArea(jS.cellLast.td, true);
				if (v) {
					jS.cellLast.td.html(jS.manageTextToHtml(v));
					jS.sheetClearActive();
					if (v.charAt(0) == '=') {
						jS.calc(jS.i);
					}
				} else { //Even if the cell is blank, that doesn't mean it's not active
					jS.sheetClearActive();
					jS.calc(jS.i);
				}
			}
			
			jS.cellLast.td = jS.obj.sheet().find('td:first');
			jS.cellLast.row = jS.cellLast.col = 0;
			0;
			jS.rowLast = jS.colLast = -1;
			
			jS.fxUpdate('', true);

			return false;
		},
		cellClick: function(keyCode) { //invoces a click on next/prev cell
			var h = 0;
			var v = 0;
			switch (keyCode) {
				case key.UP:v--;break;
				case key.DOWN:v++;break;
				case key.LEFT:h--;break;
				case key.RIGHT:h++;break;
			}
			jQuery(jS.getTd(jS.i, jS.cellLast.row + v, jS.cellLast.col + h)).click();
			
			return false;
		},
		cellOnMouseDown: function(e) {
			if (e.altKey) {
				jS.cellSetActiveMulti(e);
				jQuery(document).mouseup(function() {
					jQuery(this).unbind('mouseup');
					var v = jS.obj.formula().val();
					jS.obj.formula().val(v + jS.getTdRange());
				});
			} else {
               	var active = jS.cellSetActiveMulti(e);
                if(active){
                    var x=1;
                }
                var multicells =jS.cellSetActiveMulti(e);
				return multicells;
			}			
		},
		cellOnClickLocked: function(e) {
			if (!isNaN(e.target.cellIndex)) {
				if (!jQuery(e.target).attr('formula')) {
					jS.evt.cellOnClickManage(jQuery(e.target));
				}
			} else {
				jS.evt.cellEditAbandon();
				jS.obj.formula().focus().select();
			}
		},
		cellOnClickReg: function(e) {
			if (!isNaN(e.target.cellIndex)) {		
				jS.evt.cellOnClickManage(jQuery(e.target));
			} else { //this won't be a cell
				var clickable = jQuery(e.target).hasClass('clickable');
				if (!clickable) {
					jS.obj.formula().focus().select();
				} else { //this is an inline control
					//jS.cellEditAbandon(true);
				}
			}
		},
		cellOnClickManage: function(td) {
			if (!td.hasClass(jS.cl.cell)) { //initial click
				jS.cellEdit(td);
				jS.log('click cell');
			} else { //inline edit, 2nd click
				jS.cellLast.isEdit = jS.isSheetEdit = true;
				jS.cellTextArea(td, false, true);
				jS.themeRoller.cell(td);
				jS.log('click, textarea over table activated');
			}
            
			//jS.followMe(td);
		},
		tabOnMouseDown: function(e) {
			var i = jQuery(e.target).attr('i');
			
			if (i != '-1' && i != jS.i) {
				jS.setActiveSheet(jQuery('#' + jS.id.tableControl + i), i);jS.calc(i);
			} else if (i != '-1' && jS.i == i) {
				jS.sheetTab();
			} else {
				jS.addSheet();
			}
			return false;
		},
		resizeBar: function(e, o) {
			//Resize Column & Row & Prototype functions are private under class jSheet		
			var target = jQuery(e.target);
			var resizeBar = {
				start: function(e) {
					
					jS.log('start resize');
					//I never had any problems with the numbers not being ints but I used the parse method
					//to ensuev non-breakage
					o.offset = target.offset();
					o.tdPageXY = [o.offset.left, o.offset.top][o.xyDimension];
					o.startXY = [e.pageX, e.pageY][o.xyDimension];
					o.i = o.getIndex(target);
					o.srcBarSize = o.getSize(target);
					o.edgeDelta = o.startXY - (o.tdPageXY + o.srcBarSize);
					o.min = 10;
					
					if (jS.s.joinedResizing) {
						o.resizeFn = function(size) {
							o.setDesinationSize(size);
							o.setSize(target, size);
						};
					} else {
						o.resizeFn = function(size) {
							o.setSize(target, size);
						};
					}
					
					//We start the drag sequence
					if (Math.abs(o.edgeDelta) <= o.min) {
						//some ui enhancements, lets the user know he's resizing
						jQuery(e.target).parent().css('cursor', o.cursor);
						
						jQuery(document)
							.mousemove(resizeBar.drag)
							.mouseup(resizeBar.stop);
						
						return true; //is resizing
					} else {
						return false; //isn't resizing
					}
				},
				drag: function(e) {
					var newSize = o.min;

					var v = o.srcBarSize + ([e.pageX, e.pageY][o.xyDimension] - o.startXY);
					if (v > 0) {// A non-zero minimum size saves many headaches.
						newSize = Math.max(v, o.min);
					}

					o.resizeFn(newSize);
					return false;
				},
				stop: function(e) {	
					o.setDesinationSize(o.getSize(target));
					
					jQuery(document)
						.unbind('mousemove')
						.unbind('mouseup');

					jS.obj.formula()
						.focus()
						.select();
					
					target.parent().css('cursor', 'pointer');
					
					jS.log('stop resizing');
				}
			};
			
			return resizeBar.start(e);
		},
		scrollBars: function(killTimer) {
			var o = { //cut down on recursion, grabe them once
				pane: jS.obj.pane(), 
				barLeft: jS.obj.barLeftParent(), 
				barTop: jS.obj.barTopParent()
			};
			
			jS.obj.pane().scroll(function() {
				o.barTop.scrollLeft(o.pane.scrollLeft());//2 lines of beautiful jQuery js
				o.barLeft.scrollTop(o.pane.scrollTop());
			});
		},
		barMouseDown: {
			select: function(o, e, selectFn, resizeFn) {
				var isResizing = jS.evt.resizeBar(e, resizeFn);
						
				if (!isResizing) {
					selectFn(e.target);
					o
						.unbind('mouseover')
						.mouseover(function(e) {
							selectFn(e.target, true);
						})
						.mouseup(function() {
							o
								.unbind('mouseover')
								.unbind('mouseup');
						});
				}
				
				return false;
			},
			first: 0,
			last: 0,
			height: function(o) {			
				var selectRow = function () {};
				
				o //let any user resize
					.unbind('mousedown')
					.mousedown(function(e) {
						jS.evt.barMouseDown.first = jS.evt.barMouseDown.last = jS.rowLast = jS.getBarLeftIndex(e.target);
						jS.evt.barMouseDown.select(o, e, selectRow, jS.rowResizer);
						
						return false;
					});
				if (jS.s.editable) { //only let editable select
					selectRow = function(o, keepCurrent) {
						if (!keepCurrent) { 
							jS.themeRoller.clearCell();
							jS.themeRoller.clearBar();
						}
						
						var i = jS.getBarLeftIndex(o);
						
						jS.rowLast = i; //keep track of last row for inserting new rows
						
						jS.evt.barMouseDown.last = (i > jS.evt.barMouseDown.last ? i : jS.evt.barMouseDown.last);
						
						jS.fxUpdate((jS.evt.barMouseDown.first + 1) + ':' + (jS.evt.barMouseDown.last + 1), true);
						
						jS.cellSetActiveMultiRow(jS.evt.barMouseDown.last);
					};
				}
			},
			width: function(o) {
				var selectColumn = function() {};
				
				o //let any user resize
					.unbind('mousedown')
					.mousedown(function(e) {
						jS.evt.barMouseDown.first = jS.evt.barMouseDown.last = jS.colLast = jS.getBarTopIndex(e.target);
						jS.evt.barMouseDown.select(o, e, selectColumn, jS.columnResizer);
						
						return false;
					});
				if (jS.s.editable) { //only let editable select
					selectColumn = function(o, keepCurrent) {
						if (!keepCurrent) { 
							jS.themeRoller.clearCell();
							jS.themeRoller.clearBar();
						}
						var i = jS.getBarTopIndex(o);
						
						jS.colLast = i; //keep track of last column for inserting new columns
						
						jS.evt.barMouseDown.last = (i > jS.evt.barMouseDown.last ? i : jS.evt.barMouseDown.last);
						
						jS.fxUpdate(cE.columnLabelString(jS.evt.barMouseDown.first + 1) + ':' + cE.columnLabelString(jS.evt.barMouseDown.last + 1), true);
						
						jS.cellSetActiveMultiColumn(jS.evt.barMouseDown.last);
					};
				}
			}
		}
	},
	tuneTableForSheetUse: function(obj) {
		obj
			.addClass(jS.cl.sheet)
			.attr('id', jS.id.sheet + jS.i)
			.attr('border', '1px');
		obj.find('.' + jS.cl.uiCell).removeClass(jS.cl.uiCell);
		obj.find('td')
			.css('background-color', '')
			.css('color', '')
			.css('height', '')
			.attr('height', '');
	},
	attrH: {//Attribute Helpers
	//I created this object so I could see, quickly, which attribute was most stable.
	//As it turns out, all browsers are different, thus this has evolved to a much uglier beast
		width: function(obj, skipCorrection) {
			return jQuery(obj).outerWidth() - jS.attrH.boxModelCorrection(skipCorrection);
		},
		widthReverse: function(obj, skipCorrection) {
			return jQuery(obj).outerWidth() + jS.attrH.boxModelCorrection(skipCorrection);
		},
		height: function(obj, skipCorrection) {
			return jQuery(obj).outerHeight() - jS.attrH.boxModelCorrection(skipCorrection);
		},
		heightReverse: function(obj, skipCorrection) {
			return jQuery(obj).outerHeight() + jS.attrH.boxModelCorrection(skipCorrection);
		},
		syncSheetWidthFromTds: function(obj) {
			var entireWidth = 0;
			obj = (obj ? obj : jS.obj.sheet());
			obj.find('tr:first').find('td').each(function() {
				entireWidth += jQuery(this).width();
			});
			obj.width(entireWidth);
		},
		boxModelCorrection: function(skipCorrection) {
			var correction = 0;
			if (jQuery.support.boxModel && !skipCorrection) {
				correction = jS.s.boxModelCorrection;
			}
			return correction;
		},
		setHeight: function(i, from, skipCorrection, obj) {
			var correction = 0;
			var h = 0;
			var fn;
			
			switch(from) {
				case 'cell':
					obj = (obj ? obj : jS.obj.barLeft().find('div').eq(i));
					h = jS.attrH.height(jQuery(jS.getTd(jS.i, i, 0)).parent().andSelf(), skipCorrection);
					break;
				case 'bar':
					obj = (obj ? obj : jQuery(jS.getTd(jS.i, i, 0)).parent().andSelf());
					h = jS.attrH.heightReverse(jS.obj.barLeft().find('div').eq(i), skipCorrection);
					break;
			}
			
			if (h) {
				jQuery(obj)
					.height(h)
					.css('height', h)
					.attr('height', h);
			}

			return obj;
		}
	},
	setTdIds: function(o) {
		o = (o ? o : jS.obj.sheet());
		o.find('tr').each(function(row) {
			jQuery(this).find('td').each(function(col) {
				jQuery(this).attr('id', jS.getTdId(jS.i, row, col));
			});
		});
	},
	setControlIds: function() {
		var resetIds = function(o, id) {
			o.each(function(i) {
				jQuery(this).attr('id', id + i);
			});
		}
		
		resetIds(jS.obj.sheetAll().each(function() {
			jS.setTdIds(jQuery(this));
		}), jS.id.sheet);
		
		resetIds(jS.obj.barTopAll(), jS.id.barTop);
		resetIds(jS.obj.barTopParentAll(), jS.id.barTopParent);
		resetIds(jS.obj.barLeftAll(), jS.id.barLeft);
		resetIds(jS.obj.barLeftParentAll(), jS.id.barLeftParent);
		resetIds(jS.obj.barCornerAll(), jS.id.barCorner);
		resetIds(jS.obj.barCornerParentAll(), jS.id.barCornerParent);
		resetIds(jS.obj.tableControlAll(), jS.id.tableControl);
		resetIds(jS.obj.paneAll(), jS.id.pane);
		resetIds(jS.obj.tabAll().each(function(j) {
			jQuery(this).attr('i', j);
		}), jS.id.tab);
	},
	columnResizer: {
		xyDimension: 0,
		getIndex: function(td) {
			return jS.getBarTopIndex(td);
		},
		getSize: function(o) {
			return jS.attrH.width(o, true);
		},
		setSize: function(o, v) {
			o.width(v);
		},
		setDesinationSize: function(w) {
			jS.sheetSyncSizeToDivs();
			
			jS.obj.sheet().find('col').eq(this.i)
				.width(w)
				.css('width', w)
				.attr('width', w);
			
			jS.obj.pane().scroll();
		},
		cursor: 'w-resize'
	},
	rowResizer: {
		xyDimension: 1,
			getIndex: function(o) {
				return jS.getBarLeftIndex(o);
			},
			getSize: function(o) {
				return jS.attrH.height(o, true);
			},
			setSize: function(o, v) {
				if (v) {
				o
					.height(v)
					.css('height', v)
					.attr('height', v);
				}
				return jS.attrH.height(o);
			},
			setDesinationSize: function() {
				//Set the cell height
				jS.attrH.setHeight(this.i, 'bar', true);
				
				//Reset the bar height if the resized row don't match
				jS.attrH.setHeight(this.i, 'cell', false);
				
				jS.obj.pane().scroll();
			},
			cursor: 's-resize'
	},
	toggleHide: {//These are not ready for prime time
		row: function(i) {
			if (!i) {//If i is empty, lets get the current row
				i = jS.obj.cell().parent().attr('rowIndex');
			}
			if (i) {//Make sure that i equals something
				var o = jS.obj.barLeft().find('div').eq(i);
				if (o.is(':visible')) {//This hides the current row
					o.hide();
					jS.obj.sheet().find('tr').eq(i).hide();
				} else {//This unhides
					//This unhides the currently selected row
					o.show();
					jS.obj.sheet().find('tr').eq(i).show();
				}
			} else {
				alert('No row selected.');
			}
		},
		rowAll: function() {
			jS.obj.sheet().find('tr').show();
			jS.obj.barLeft().find('div').show();
		},
		column: function(i) {
			if (!i) {
				i = jS.obj.cell().attr('cellIndex');
			}
			if (i) {
				//We need to hide both the col and td of the same i
				var o = jS.obj.barTop().find('div').eq(i);
				if (o.is(':visible')) {
					jS.obj.sheet().find('tbody tr').each(function() {
						jQuery(this).find('td').eq(i).hide();
					});
					o.hide();
					jS.obj.sheet().find('colgroup col').eq(i).hide();
					jS.toggleHide.columnSizeManage();
				}
			} else {
				alert('Now column selected.');
			}
		},
		columnAll: function() {
		
		},
		columnSizeManage: function() {
			var w = jS.obj.barTop().width();
			var newW = 0;
			var newW = 0;
			jS.obj.barTop().find('div').each(function() {
				var o = jQuery(this);
				if (o.is(':hidden')) {
					newW += o.width();
				}
			});
			jS.obj.barTop().width(w);
			jS.obj.sheet().width(w);
		}
	},
	addTab: function() {
		jQuery('<span class="ui-corner-bottom ui-widget-header">' + 
				'<a class="' + jS.cl.tab + '" id="' + jS.id.tab + jS.i + '" i="' + jS.i + '">' + jS.sheetTab(true) + '</a>' + 
			'</span>')
				.insertBefore(
					jS.obj.tabContainer().find('span:last')
				);
	},
	sheetDecorate: function(o) {	
		jS.formatSheet(o);
		jS.sheetSyncSizeToCols(o);
		jS.sheetDecorateRemove();
	},
	formatSheet: function(o) {
		if (o.find('tbody').length < 1) {
			o.wrapInner('<tbody />');
		}
		
		if (o.find('colgroup').length < 1 || o.find('col').length < 1) {
			o.remove('colgroup');
			var colgroup = jQuery('<colgroup />');
			o.find('tr:first').find('td').each(function() {
				//var w = jQuery(this).width();
				//jQuery(this)
				//	.width(w)
				//	.css('width', w)
				//	.attr('width', w);
				jQuery('<col />')
					.width(jS.s.newColumnWidth)
					.css('width', jS.s.newColumnWidth + 'px')
					.attr('width', jS.s.newColumnWidth + 'px')
					.appendTo(colgroup);
			});
			o.find('tr').each(function() {
				jQuery(this)
					.height(jS.s.colMargin)
					.css('height', jS.s.colMargin + 'px')
					.attr('height', jS.s.colMargin + 'px');
			});
			colgroup.prependTo(o);
		}
	},
	themeRoller: {
		start: function() {
			//Style sheet			
			jS.obj.parent().addClass(jS.cl.uiParent);
			jS.obj.sheet().addClass(jS.cl.uiSheet);
			//Style bars
			jS.obj.barLeft().find('div').addClass(jS.cl.uiBar);
			jS.obj.barTop().find('div').addClass(jS.cl.uiBar);
			jS.obj.barCornerParent().addClass(jS.cl.uiBar);
			
			jS.obj.controls().addClass(jS.cl.uiControl);
			jS.obj.fx().addClass(jS.cl.uiControl);
			jS.obj.label().addClass(jS.cl.uiControl);
			jS.obj.formula().addClass(jS.cl.uiControlTextBox);
		},
		cell: function(td) {
			jS.themeRoller.clearCell();
			if (td) {
				jQuery(td)
					.addClass(jS.cl.uiCellHighlighted)
					.addClass(jS.cl.uiCell);
			}
		},
		clearCell: function() {
			jS.obj.uiActive().removeClass(jS.cl.uiActive);
			jS.obj.uiCell()
				.removeAttr('style')
				.removeClass(jS.cl.uiCellHighlighted)
				.removeClass(jS.cl.uiCell);
		},
		newBar: function(obj) {//This is for a tr
			jQuery(obj).addClass(jS.cl.uiBar);
		},
		barTop: function(i) {
			jS.obj.barTop().find('div').eq(i).addClass(jS.cl.uiActive);
		},
		barLeft: function(i) {
			jS.obj.barLeft().find('div').eq(i).addClass(jS.cl.uiActive);
		},
		barObj: function(obj) {
			jQuery(obj).addClass(jS.cl.uiActive);
		},
		clearBar: function() {
			jS.obj.barTop().find('.' + jS.cl.uiActive).removeClass(jS.cl.uiActive);
			jS.obj.barLeft().find('.' + jS.cl.uiActive).removeClass(jS.cl.uiActive);
		},
		resize: function() {
			// add resizable jquery.ui if available
			if (jQuery.ui) {
				// resizable container div
				var o;
				var barTop;
				var barLeft;
				var controlsHeight;
				var parent = jQuery(jS.s.parent);
				
				parent.resizable('destroy').resizable({
					minWidth: jS.s.width * 0.5,
					minHeight: jS.s.height * 0.5,
					ghost: true,
					stop: function() {						
						jS.s.width = parent.width();
						jS.s.height = parent.height();
						jS.sheetSyncSize();
					}
				});
				// resizable formula area - a bit hard to grab the handle but is there!
				var formulaResizeParent = jQuery('<span />');
				jS.obj.formula().wrap(formulaResizeParent).parent().resizable({
					minHeight: jS.obj.formula().height(), 
					maxHeight: 78,
					handles: 's',
					resize: function(e, ui) {
						jS.obj.formula().height(ui.size.height);
						jS.sheetSyncSize();
					}
				});
			}
		}
	},
	manageHtmlToText: function(v) {
		v = jQuery.trim(v);
		if (v.charAt(0) != "=") {
			v = v.replace(/&nbsp;/g, ' ')
				.replace(/&gt;/g, '>')
				.replace(/&lt;/g, '<')
				.replace(/\t/g, '')
				.replace(/\n/g, '')
				.replace(/<br>/g, '\r')
				.replace(/<BR>/g, '\n');

			//jS.log("from html to text");
		}
		return v;
	},
	manageTextToHtml: function(v) {	
		v = jQuery.trim(v);
		if (v.charAt(0) != "=") {
			v = v.replace(/\t/g, '&nbsp;&nbsp;&nbsp;&nbsp;')
				.replace(/ /g, '&nbsp;')
				.replace(/>/g, '&gt;')
				.replace(/</g, '&lt;')
				.replace(/\n/g, '<br>')
				.replace(/\r/g, '<br>');
			
			//jS.log("from text to html");
		}
		return v;
	},
	sheetDecorateRemove: function(makeClone) {
		var obj = (makeClone ? jS.obj.sheetAll().clone() : jS.obj.sheetAll());
		
		//remove class jSheetCellActive
		jQuery(obj).find('.' + jS.cl.cell).removeClass(jS.cl.cell);
		//remove class ui-state-highlight
		jQuery(obj).find('.' + jS.cl.uiCellHighlighted).removeClass(jS.cl.uiCellHighlighted);
		//remove class themeRoller_activeCell
		
		jQuery(obj).find('.' + jS.cl.uiCell).removeClass(jS.cl.uiCell);
		//IE Bug, match width with css width
		jQuery(obj).find('col').each(function(i) {
			var v = jQuery(this).css('width');
			v = ((v + '').match('px') ? v : v + 'px');
			jQuery(obj).find('col').eq(i).attr('width', v);
		});
		
		return obj;
	},
	fxUpdate: function(v, setDirect) {
		if (!setDirect) {
			jS.obj.label().html(cE.columnLabelString(v[1] + 1) + (v[0] + 1));
		} else {
			jS.obj.label().html(v);
		}
	},
	cellEdit: function(td) {
		//This finished up the edit of the last cell
		jS.evt.cellEditDone();
		var loc = jS.getTdLocation(td);
	
        
        
        
		//Show where we are to the user
		jS.fxUpdate(loc);
		
		var v = td.attr('formula');
		if (!v) {
			v = jS.manageHtmlToText(td.html());
		}
		
		jS.obj.formula()
			.val(v)
			.focus()
			.select();
		jS.cellSetActive(td, loc);
	},
	cellSetActive: function(td, loc) {
        lsCellSetActive(td, loc);
        jS.obj.lsSelected =[];
//        jS.obj.lsSelected = [td[0].id]=td[0].id ;
        //jS.obj.lsSelected->td[0].id=0;
		jS.cellLast.td = td; //save the current cell/td
		jS.cellLast.row = jS.rowLast = loc[0];
		jS.cellLast.col = jS.colLast = loc[1];
		
		jS.themeRoller.cell(td); //themeroll the cell and bars
		jS.themeRoller.barLeft(jS.cellLast.row);
		jS.themeRoller.barTop(jS.cellLast.col);
		
		td.addClass(jS.cl.cell); //add classes
		jS.obj.barLeft().find('div').eq(jS.cellLast.row).addClass(jS.cl.barSelected);
		jS.obj.barTop().find('div').eq(jS.cellLast.col).addClass(jS.cl.barSelected);
	},
	colLast: -1,
	rowLast: -1,
	cellLast: {
		td: null,
		row: null,
		col: null,
		isEdit: false
	},   
	cellStyleToggle: function(setClass, removeClass) {
		//Lets check to remove any style classes
		if (removeClass) {
			removeClass = removeClass.split(',');
			
			jQuery(removeClass).each(function() {
				jS.obj.uiCell().removeClass(this);
			});
		}
		//Now lets add some style
		if (jS.obj.uiCell().hasClass(setClass)) {
			jS.obj.uiCell().removeClass(setClass);
		} else {
			jS.obj.uiCell().addClass(setClass);
		}
		jS.obj.formula()
			.focus()
			.select();
		return false;
	},
	context: {},
	calc: function(tableI, fuel) {
		jS.log('Calculation Started');
		if (!jS.s.calcOff) {
			cE.calc(new jS.tableCellProvider(tableI), jS.context, fuel);
			jS.isSheetEdit = false;
		}
		jS.log('Calculation Ended');
	},
	cellTextArea: function(td, returnVal, makeEdit, setVal) {
		//Remove Textarea and transfer value.
		var v;
		if (td) {
			if (!makeEdit) {
				var textArea = td.find('textarea');
				var textAreaVal = textArea.val();
				if (textAreaVal || jS.obj.formula().attr('disabled')) {
					jS.log('Textarea value used');
					v = textAreaVal;
					textArea.remove();
					//td
					//	.css('text-align', '')
					//	.css('vertical-align', '');
				} else {
					jS.log('Formula value used');
					v = jS.obj.formula().val();
				}
				jS.obj.formula().removeAttr('disabled');
			} else {
				if (setVal) {
					v = setVal;
				} else {
					v = jS.obj.formula().val();
				}
				
				jS.obj.formula().attr('disabled', 'true');
				
				var textArea = jQuery('<textarea id="tempText" class="clickable" />');
				var h = jS.attrH.height(td);
				
				//There was an error in some browsers where they would mess this up.
				td.parent().height(h + jS.attrH.boxModelCorrection());
				//create text area.  Agian, strings are faster than DOM.
				textArea
					.height(h < 75 ? 75 : h)
					.val(v)
					.click(function(){
						return false;
					})
					.keydown(function(e) {
						return jS.evt.formulaKeyDown(e, true);
					});
				
				//Se we can look at the past value after edit.
				if (td.attr('formula')) {
					td.attr('prevVal', td.text()).removeAttr('formula');
				}
				//add it to cell
				td.html(textArea);
				//focus textarea
				textArea
					.focus()
					.select();
			}
			if (returnVal) {
				return v;
			}
		}
	},
	refreshLabelsColumns: function(){
		var w = 0;
		jS.obj.barTop().find('div').each(function(i) {
			jQuery(this).text(cE.columnLabelString(i+1));
			w += jQuery(this).width();
		});
		return w;
	},
	refreshLabelsRows: function(){
		jS.obj.barLeft().find('div').each(function(i) {
			jQuery(this).text((i + 1));
		});
	},
	addSheet: function(size) {
		size = (size ? size : prompt(jS.newSheetDialog));
		if (size) {
			jS.evt.cellEditAbandon();
			jS.setDirty(true);
			var newSheetControl = jS.controlFactory.sheetUI(jS.controlFactory.sheet(size), jS.sheetCount + 1, function() { 
				jS.setActiveSheet(newSheetControl, jS.sheetCount + 1);
			}, true);
		}
	},
	deleteSheet: function() {
		jS.obj.tableControl().remove();
		jS.obj.tabContainer().children().eq(jS.i).remove();
		jS.i = 0;
		jS.sheetCount--;
		
		jS.setControlIds();
		
		jS.setActiveSheet(jS.obj.tableControl(), jS.i);
	},
	deleteRow: function() {
		var v = confirm("Are you sure that you want to delete that row? Fomulas will not be updated.");
		if (v) {
			jS.obj.barLeft().find('div').eq(jS.rowLast).remove();
			jS.obj.sheet().find('tr').eq(jS.rowLast).remove();
			
			jS.evt.cellEditAbandon();
			
			jS.setTdIds();
			jS.refreshLabelsRows();
			jS.obj.pane().scroll();
			
			jS.rowLast = -1;
		}		
	},
	deleteColumn: function() {
		var v = confirm("Are you sure that you want to delete that column? Fomulas will not be updated.");
		if (v) {
			jS.obj.barTop().find('div').eq(jS.colLast).remove();
			jS.obj.sheet().find('colgroup col').eq(jS.colLast).remove();
			jS.obj.sheet().find('tr').each(function(i) {
					jQuery(this).find('td').eq(jS.colLast).remove();
			});
			
			jS.evt.cellEditAbandon();
			
			var w = jS.refreshLabelsColumns();
			jS.setTdIds();
			jS.obj.sheet().width(w);
			jS.obj.pane().scroll();
			
			jS.colLast = -1;
		}		
	},
	sheetTab: function(get) {
		var sheetTab = '';
		if (get) {
			sheetTab = jS.obj.sheet().attr('title');
			sheetTab = (sheetTab ? sheetTab : 'Spreadsheet ' + (jS.i + 1));
		} else {
			var newTitle = prompt("What would you like the sheet's title to be?", jS.sheetTab(true));
			if (!newTitle) { //The user didn't set the new tab name
				sheetTab = jS.obj.sheet().attr('title');
				newTitle = (sheetTab ? sheetTab : 'Spreadsheet' + (jS.i + 1));
			} else {
				jS.setDirty(true);
				jS.obj.sheet().attr('title', newTitle);
				jS.obj.tab().html(newTitle);
				
				sheetTab = newTitle;
			}
		}
		return sheetTab;
	},
	print: function(o) {
		var w = window.open();
		w.document.write("<html><body><xmp>" + o + "\n</xmp></body></html>");
		w.document.close();
	},
	viewSource: function(pretty) {
		var sheetClone = jS.sheetDecorateRemove(true);
		
		var s = "";
		if (pretty) {
			jQuery(sheetClone).each(function() {
				s += jS.HTMLtoPrettySource(this);
			});
		} else {
			s += jQuery('<div />').html(sheetClone).html();
		}
		
		jS.print(s);
		
		return false;
	},
	saveSheet: function() {
		var v = jS.sheetDecorateRemove(true);
		var s = jQuery('<div />').html(v).html();

		jQuery.ajax({
			url: jS.s.urlSave,
			type: 'POST',
			data: 's=' + s,
			dataType: 'html',
			success: function(data) {
				jS.setDirty(false);
				alert('Success! - ' + data);
			}
		});
	},
	HTMLtoCompactSource: function(node) {
		var result = "";
		if (node.nodeType == 1) {
			// ELEMENT_NODE
			result += "<" + node.tagName;
			hasClass = false;
			
			var n = node.attributes.length;
			for (var i = 0, hasClass = false; i < n; i++) {
				var key = node.attributes[i].name;
				var val = node.getAttribute(key);
				if (val) {
					if (key == "contentEditable" && val == "inherit") {
						continue;
						// IE hack.
					}
					if (key == "class") {
						hasClass = true;
						jQuery(val).removeClass(jS.cl.cell);
					}
					
					if (typeof(val) == "string") {
						result += " " + key + '="' + val.replace(/"/g, "'") + '"';
					} else if (key == "style" && val.cssText) {
						result += ' style="' + val.cssText + '"';
					}
				}
			}

			if (node.tagName == "TABLE" && !hasClass) {
				// IE hack, where class doesn't appear in attributes.
				result += ' class="jSheet"';
			}
			if (node.tagName == "COL") {
				// IE hack, which doesn't like <COL..></COL>.
				result += '/>';
			} else {
				result += ">";
				var childResult = "";
				jQuery(node.childNodes).each(function() {
					childResult += jS.HTMLtoCompactSource(this);
				});
				result += childResult;
				result += "</" + node.tagName + ">";
			}

		} else if (node.nodeType == 3) {
			// TEXT_NODE
			result += node.data.replace(/^\s*(.*)\s*$/g, "$1");
		}
		return result;
	},
	HTMLtoPrettySource: function(node, prefix) {
		if (!prefix) {
			prefix = "";
		}
		var result = "";
		if (node.nodeType == 1) {
			// ELEMENT_NODE
			result += "\n" + prefix + "<" + node.tagName;
			var n = node.attributes.length;
			for (var i = 0; i < n; i++) {
				var key = node.attributes[i].name;
				var val = node.getAttribute(key);
				if (val) {
					if (key == "contentEditable" && val == "inherit") {
						continue; // IE hack.
					}
					if (typeof(val) == "string") {
						result += " " + key + '="' + val.replace(/"/g, "'") + '"';
					} else if (key == "style" && val.cssText) {
						result += ' style="' + val.cssText + '"';
					}
				}
			}
			if (node.childNodes.length <= 0) {
				result += "/>";
			} else {
				result += ">";
				var childResult = "";
				var n = node.childNodes.length;
				for (var i = 0; i < n; i++) {
					childResult += jS.HTMLtoPrettySource(node.childNodes[i], prefix + "  ");
				}
				result += childResult;
				if (childResult.indexOf('\n') >= 0) {
					result += "\n" + prefix;
				}
				result += "</" + node.tagName + ">";
			}
		} else if (node.nodeType == 3) {
			// TEXT_NODE
			result += node.data.replace(/^\s*(.*)\s*$/g, "$1");
		}
		return result;
	},
	followMe: function(td) {
		jS.obj.pane().stop().scrollTo(td, {
			margin: true,
			axis: 'xy',
			duration: 100,
			offset: {
				top: - jS.s.height / 3,
				left: - jS.s.width / 5
			}
		});
	},
	count: {
		rows: function() {
			return jS.getBarLeftIndex(jS.obj.barLeft().find('div:last').text());
		},
		columns: function() {
			return jS.getBarTopLocatoin(jS.obj.barTop().find('div:last').text());
		}
	},
	isRowHeightSync: [],
	setActiveSheet: function(o, i) {
		if (o) {
			o.show().siblings().hide();
			jS.obj.tabContainer().find('.ui-state-highlight').removeClass('ui-state-highlight');
			jS.i = i;
			jS.obj.tab().parent().addClass('ui-state-highlight');
			
		} else {
			i = 0;
			jS.obj.tableControl().siblings().not('div').hide();
			jS.obj.tabContainer().find('.ui-state-highlight').removeClass('ui-state-highlight');
			jS.obj.tab().parent().addClass('ui-state-highlight');
		}
		
		if (!jS.isRowHeightSync[i]) { //this makes it only run once, no need to have it run every time a user changes a sheet
			jS.isRowHeightSync[i] = true;
			jS.obj.sheet().find('tr').each(function(j) {
				jS.attrH.setHeight(j, 'cell');
				/*
				fixes a wired bug with height in chrome and ie
				It seems that at some point during the sheet's initializtion the height for each
				row isn't yet clearly defined, this ensures that the heights for barLeft match 
				that of each row in the currently active sheet when a user uses a non strict doc type.
				*/
			});
		}
		
		jS.sheetSyncSize();
		jS.replaceWithSafeImg(jS.obj.sheet().find('img'));
	},
	openSheetURL: function ( url ) {
		jS.s.urlGet = url;
		return jS.openSheet();
	},
	openSheet: function(o) {
		if (!jS.isDirty ? true : confirm("Are you sure you want to open a different sheet?  All unsaved changes will be lost.")) {
			jS.controlFactory.header();
			
			var fnAfter = function(i, l) {
				if (i == (l - 1)) {
					jS.i = 0;
					jS.setActiveSheet();
					jS.themeRoller.resize();
					for (var i = 0; i <= jS.sheetCount; i++) {
						jS.calc(i);
					}
				}
			};
			
			if (!o) {
				jQuery('<div />').load(jS.s.urlGet, function() {
					var sheets = jQuery(this).find('table');
					sheets.each(function(i) {
						jS.controlFactory.sheetUI(jQuery(this), i, function() { 
							fnAfter(i, sheets.length);
						}, true);
					});
				});
			} else {
				var sheets = jQuery('<div />').html(o).find('table');
				sheets.each(function(i) {
					jS.controlFactory.sheetUI(jQuery(this), i,  function() { 
						fnAfter(i, sheets.length);
					}, false);
				});
			}
			return true;
		} else {
			return false;
		}
	},
	newSheetDialog: "What size would you like to make your spreadsheet? Example: '5x10' creates a sheet that is 5 columns by 10 rows.",
	newSheet: function() {
		var size = prompt(jS.newSheetDialog);
		if (size) {
			jS.openSheet(jS.controlFactory.sheet(size));
		}
	},
	importRow: function(rowArray) {
		jS.controlFactory.addRow(null, null, ':last');

		var error = "";
		jS.obj.sheet().find('tr:last td').each(function(i) {
			jQuery(this).removeAttr('formula');
			try {
				//To test this, we need to first make sure it's a string, so converting is done by adding an empty character.
				if ((rowArray[i] + '').charAt(0) == "=") {
					jQuery(this).attr('formula', rowArray[i]);					
				} else {
					jQuery(this).html(rowArray[i]);
				}
			} catch(e) {
				//We want to make sure that is something bad happens, we let the user know
				error += e + ';\n';
			}
		});
		
		if (error) {//Show them the errors
			alert(error);
		}
		//Let's recalculate the sheet just in case
		jS.setTdIds();
		jS.calc(jS.i);
	},
	importColumn: function(columnArray) {
		jS.controlFactory.addColumn();

		var error = "";
		jS.obj.sheet().find('tr').each(function(i) {
			var o = jQuery(this).find('td:last');
			try {
				//To test this, we need to first make sure it's a string, so converting is done by adding an empty character.
				if ((columnArray[i] + '').charAt(0) == "=") {
					o.attr('formula', columnArray[i]);					
				} else {
					o.html(columnArray[i]);
				}
			} catch(e) {
				//We want to make sure that is something bad happens, we let the user know
				error += e + ';\n';
			}
		});
		
		if (error) {//Show them the errors
			alert(error);
		}
		//Let's recalculate the sheet just in case
		jS.setTdIds();
		jS.calc(jS.i);
	},
	importSheet: {
		xml: function (data) { //Will not accept CDATA tags
			var table = jQuery('<table />');
			var tbody = jQuery('<tbody />').appendTo(table);
			
			jQuery(data).find('document').each(function() { //document
				var metaData = jQuery(this).find('metadata');
				var columnCount = metaData.find('columns').text();
				var rowCount = metaData.find('rows').text();
				var title = metaData.find('title').html();
				jQuery(this).find('data').children().each(function(i) { //rows
					var thisRow = jQuery('<tr />');
					jQuery(this).children().each(function(j) { //columns
						var o = jQuery(this).html();
						if (o.charAt(0) == '=') {
							thisRow.append('<td formula="' + o + '" />');
						} else {
							thisRow.append('<td>' + o + '</td>');
						}
					});
					tbody.append(thisRow);
				});
			});
			
			return table;
		},
		json: function(data) {
			jS.i = jS.sheetCount;
//			sheet = eval('(' + data + ')');
            sheet = eval(data);
            sheet = sheet[0];
			size_c = sheet["metadata"]["columns"] * 1 + 5;
			size_r = sheet["metadata"]["rows"] * 1 + 1;
			title = sheet["metadata"]["title"];
			title = (title ? title : "");
			
			var table = jQuery("<table id='" + jS.id.sheet + jS.i + "' class='" + jS.cl.sheet + "' title='" + title + "' />");
			
			var cur_row;
            var cur_column;
			for(var x = 0; x <= size_r; x++)
			{
				cur_row = jQuery('<tr height="' + jS.s.colMargin + 'px" />').appendTo(table);
				
				for(var y = 0; y <= size_c; y++)
				{
					cur_row.append('<td id="' + 'table' + jS.i + '_' + 'cell_c' + y + '_r' + x + '" />');
				}
			}
			
			for (row in sheet["data"])
			{
				for (column in sheet["data"][row])
				{
					cur_val = sheet["data"][row][column];
					cur_column = table.find('#table' + jS.i + '_' + 'cell_' + column + '_' + row).text(cur_val);
					
					if (cur_val.charAt(0) == '=')
					{
						cur_column.attr("formula", cur_val);
					}
				}
			}
			
			return table;
		}
	},
	exportSheet: {
		xml: function (skipCData) {
			var sheetClone = jS.sheetDecorateRemove(true);			
			var result = "";
			
			var cdata = ['<![CDATA[',']]>'];
			
			if (skipCData) {
				cdata = ['',''];
			}
			
			jQuery(sheetClone).each(function() {
				var x = '';
				var title = jQuery(this).attr('title');
				
				var count = 0;
				var cur_column = cur_row = '';
				var max_column = max_row = 0;
				jQuery(this).find('tr').each(function(i){
					count = 0;
					max_row = i;
					jQuery(this).find('td').each(function(){
						count++;
						
						var id = jQuery(this).attr('id');
						var txt = jQuery.trim(jQuery(this).text());
						var pos = id.search(/cell_c/i);
						var pos2 = id.search(/_r/i);
						
						if (txt != '' && pos != -1 && pos2 != -1) {
							cur_column = id.substr(pos+6, pos2-(pos+6));
							cur_row = id.substr(pos2+2);
							
							if (max_column < cur_column) max_column = cur_column;
							
							if (max_row < cur_row) max_row = cur_row;
							
							if (count == 1) x += '<r'+cur_row+'>';
							
							var formula = jQuery(this).attr('formula');
							if (formula)
							{
								txt = formula;
							}
							
							x += '<c' + cur_column + '>' + cdata[0] + txt + cdata[1] + '</c' + cur_column + '>';
						}
					});
					
					if (cur_row != '')
						x += '</r'+cur_row+'>';
					cur_column = cur_row = '';
				});
				
				result += '<document>' + 
							'<metadata>' + 
								'<columns>' + (parseInt(max_column) + 1) + '</columns>' +  //length is 1 based, index is 0 based
								'<rows>' + (parseInt(max_row) + 1) + '</rows>' +  //length is 1 based, index is 0 based
								'<title>' + title + '</title>' + 
							'</metadata>' + 
							'<data>' + x + '</data>' + 
						'</document>';
			});
			
			return '<documents>' + result + '</documents>';
		},
		json: function() {
			var sheetClone = jS.sheetDecorateRemove(true);
			var docs = []; //documents
			
			jQuery(sheetClone).each(function() {
				var doc = {
					metadata:{},
                //  celltype:{},
                //  rangetype:{},
                    cell:{}
				};
				//var cell = new Array();

				var count = 0;
				var cur_column ='';
                var cur_row = '';
				var max_column = 0;
                var max_row = 0;
				jQuery(this).find('tr').each(function(){
					count = 0;
					jQuery(this).find('td').each(function(){
						count++;
						
						var id = jQuery(this).attr('id');
						var txt = jQuery.trim(jQuery(this).text());
                        var ctype =  jQuery(this).attr('celltype'); //celltype
                        //var cell;
                       
                        
                        if ((ctype === undefined) || ctype ==="")
                        {
                            ctype = null;
                        }
						var pos = id.search(/cell_c/i);
						var pos2 = id.search(/_r/i);

                     	if ((txt != '' || ctype != null) && pos != -1 && pos2 != -1) {
							cur_column = parseInt(id.substr(pos+6, pos2-(pos+6)));
							cur_row = parseInt(id.substr(pos2+2));
							
							if (max_column < cur_column) max_column = cur_column;
							
							if (max_row < cur_row) max_row = cur_row;
										
							var formula = jQuery(this).attr('formula');
							if (formula)
							{
								txt = formula;
							}
					
                            var celltype = jQuery(this).attr('celltype');
							if (celltype === undefined)
							{
                                celltype = null;
                            }

                            var rangetype = jQuery(this).attr('rangetype');
							if (rangetype === undefined)
							{
                                rangetype = null;
                            }
  
                            celltype = jQuery(this).attr('celltype');
                            if (celltype === undefined) celltype = "";
                            
                            rangetype = jQuery(this).attr('rangetype');
                            if (rangetype === undefined) rangetype = "";

                            value = jQuery(this).html();
                            if (value === undefined) value = "";

                            formula = jQuery(this).attr('formula');
                            if (formula === undefined) formula = "";

                            var chart = jQuery(this).attr('chart');
                            if (chart === undefined) chart = "";
                            
                            var feedback = jQuery(this).attr('feedback');

                            
                            if (feedback === undefined) feedback = "";
                            feedback.replace(/"/g,"'");

                            doc['cell'][jQuery.trim(jQuery(this).attr('id'))] = {
                                "celltype":celltype,
                                "rangetype":rangetype,
                                "textvalue":value,
                                "formula":formula,
                                "col":cur_column,
                                "row":cur_row,
                                "feedback":feedback,
                                "chart":chart
                            };
 
						}
                        
                       
                        
					});
					
					
					cur_column = cur_row = '';
				});
				doc['metadata'] = {
					"columns": parseInt(max_column) + 1, //length is 1 based, index is 0 based
					"rows": parseInt(max_row) + 1, //length is 1 based, index is 0 based
					"title": jQuery(this).attr('title')
				};
				docs.push(doc); //append to documents
			});
			return docs;
		},
		html: function() {
			return jS.sheetDecorateRemove(true);
		}
	},
	sheetSyncSizeToDivs: function() {
		var newSheetWidth = 0;
		jS.obj.barTop().find('div').each(function() {
			newSheetWidth += parseInt(jQuery(this).outerWidth());
		});
		jS.obj.sheet().width(newSheetWidth);
	},
	sheetSyncSizeToCols: function(o) {
		var newSheetWidth = 0;
		o.find('colgroup col').each(function() {
			newSheetWidth += jQuery(this).width();
		});
		o.width(newSheetWidth);
	},
	sheetSyncSize: function() {
		var h = jS.s.height;
		if (!h) {
			h = 400; //Height really needs to be set by the parent
		} else if (h < 200) {
			h = 200;
		}
		
		jS.obj.parent().height(h);
		
		var w = jS.s.width - jS.attrH.width(jS.obj.barLeftParent()) - (jS.attrH.boxModelCorrection());
		
		h = h - jS.attrH.height(jS.obj.controls()) - jS.attrH.height(jS.obj.barTopParent()) - (jS.attrH.boxModelCorrection() * 2);
		
		jS.obj.pane()
			.height(h)
			.width(w)
			.parent()
				.width(w);
		
		jS.obj.ui()
			.width(w + jS.attrH.width(jS.obj.barLeftParent()));
				
		jS.obj.barLeftParent()
			.height(h);
		
		jS.obj.barTopParent()
			.width(w)
			.parent()
				.width(w);
	},
	cellFind: function(v) {
		if(!v) {
			v = prompt("What are you looking for in this spreadsheet?");
		}
		if (v) {//We just do a simple uppercase/lowercase search.
			var obj = jS.obj.sheet().find('td:contains("' + v + '")');
			
			if (obj.length < 1) {
				obj = jS.obj.sheet().find('td:contains("' + v.toLowerCase() + '")');
			}
			
			if (obj.length < 1) {
				obj = jS.obj.sheet().find('td:contains("' + v.toUpperCase() + '")');
			}
			
			obj = obj.eq(0);
			if (obj.length > 0) {
				obj.click();
			} else {
				alert('No results found.');
			}
		}
	},
	cellSetActiveMulti: function(e) {
        jS.obj.lsSelected =[];
		var o = {
			startRow: e.target.parentNode.rowIndex,
			startColumn: e.target.cellIndex
		};//These are the events used to selected multiple rows.
		jS.obj.sheet()
			.mousemove(function(e) {
				o.endRow = e.target.parentNode.rowIndex;
				o.endColumn = e.target.cellIndex;
				for (var i = o.startRow; i <= o.endRow; i++) {
					for (var j = o.startColumn; j <= o.endColumn; j++) {
						var td = jS.getTd(jS.i, i, j);
                        
						jQuery(td)
							.addClass(jS.cl.uiCell)
							.addClass(jS.cl.uiCellHighlighted);
                        if (td != undefined){
                            jS.obj.lsSelected[td.id]=td.id;
                        }
					}
				}
			})
			.mouseup(function() {
				jS.obj.sheet()
					.unbind('mousemove')
					.unbind('mouseup');
			});
			
			//this helps with multi select so that when you are selecting cells you don't select the text within them
			if (e.target != jS.cellLast.td && jQuery(e.target).hasClass('clickable') == false) {
				jS.themeRoller.clearCell();
				jS.themeRoller.clearBar();
				return false;
			}
	},
	cellSetActiveAll: function() {
		if (jS.s.editable) {
			var rowCount = 0;
			var colCount = 0;
			
			jS.obj.barLeft().find('div').each(function(i) {
				jS.cellSetActiveMultiRow(i);
				rowCount++;
			});
			jS.obj.barTop().find('div').each(function(i) {
				jS.themeRoller.barTop(i);
				colCount++;
			});
			
			jS.fxUpdate('A1:' + cE.columnLabelString(colCount) + rowCount, true);
		}
	},
	cellSetActiveMultiColumn: function(i) {
		jS.obj.sheet().find('tr').each(function() {
			var o = jQuery(this).find('td').eq(i);
			o
				.addClass(jS.cl.uiCell)
				.addClass(jS.cl.uiCellHighlighted);
		});
		
		jS.themeRoller.barTop(i);
	},
	celletActiveMultiRow: function(i) {
		jS.obj.sheet().find('tr').eq(i).find('td')
			.addClass(jS.cl.uiCell)
			.addClass(jS.cl.uiCellHighlighted);
		
		jS.themeRoller.barLeft(i);
	},
	sheetClearActive: function() {
		jS.obj.formula().val('');
		jS.obj.cell().removeClass(jS.cl.cell);
		jS.obj.barSelected().removeClass(jS.cl.barSelected);
	},
	getTdRange: function() {
		//three steps here,
		//Get td's
		//Get locations
		//Get labels for locationa and return them
		
		var cells = jS.obj.uiCell().not('.' + jS.cl.cell);
		
		if (cells.length) {
			var loc = { //tr/td column and row index
				first: jS.getTdLocation(cells.first()),
				last: jS.getTdLocation(cells.last())
			};
			
			//Adjust 0 based tr/td to cell/column/row index
			loc.first[0]++;
			loc.first[1]++;
			loc.last[0]++;
			loc.last[1]++;
			
			var label = {
				first: cE.columnLabelString(loc.first[1]) + loc.first[0],
				last: cE.columnLabelString(loc.last[1]) + loc.last[0]
			};
			
			return label.first + ":" + label.last;
		} else {
			return '';
		}
	},
	getTdId: function(tableI, row, col) {
		return 'table' + tableI + '_cell_c' + col + '_r' + row;
	},
	getTd: function(tableI, row, col) {
		return document.getElementById(jS.getTdId(tableI, row, col));
	},
	getTdLocation: function(td) {
		var col = parseInt(td[0].cellIndex);
		var row = parseInt(td[0].parentNode.rowIndex);
		return [row, col];
		// The row and col are 1-based.
	},
	getBarLeftIndex: function(o) {
		var i = jQuery.trim(jQuery(o).text());
		return parseInt(i) - 1;
	},
	getBarTopIndex: function(o) {
		var i = cE.columnLabelIndex(jQuery.trim(jQuery(o).text()));
		return parseInt(i) - 1;
	},
	tableCellProvider: function(tableI) {
		this.tableBodyId = 'jSheet' + tableI;
		this.tableI = tableI;
		this.cells = {};
	},
	tableCell: function(tableI, row, col) {
		this.tableBodyId = 'jSheet' + tableI;
		this.tableI = tableI;
		this.row = row;
		this.col = col;
		this.value = jS.EMPTY_VALUE;
		
		//this.prototype = new cE.cell();
	},
	EMPTY_VALUE: {},
	time: {
		now: new Date(),
		last: new Date(),
		diff: function() {
			return Math.abs(Math.ceil(this.last.getTime() - this.now.getTime()) / 1000).toFixed(5);
		},
		set: function() {
			this.last = this.now;
			this.now = new Date();
		},
		get: function() {
			return this.now.getHours() + ':' + this.now.getMinutes() + ':' + this.now.getSeconds();
		}
	},
	log: function(msg) {  //The log prints: {Current Time}, {Seconds from last log};{msg}
		jS.time.set();
		jS.obj.log().prepend(jS.time.get() + ', ' + jS.time.diff() + '; ' + msg + '<br />\n');
	},
	replaceWithSafeImg: function(o) {  //ensures all pictures will load and keep their respective bar the same size.
		o.each(function() {			
			var src = jQuery(this).attr('src');
			jQuery(this).replaceWith(jS.controlFactry.safeImg(src, jS.getTdLocation(jQuery(this).parent())[0]));
		});
	},
	
	isDirty:  false,
	setDirty: function(dirty) {jS.isDirty = dirty;},
	appendToFormula: function(v, o) {
		var formula = jS.obj.formula();
		if (formula.attr('disabled')) {
			formula = jS.cellLast.td.find('textarea');
		}
		
		var fV = formula.val();
		
		if (fV.charAt(0) != '=') {
			fV = '=' + fV;
		}
		
		formula.val(fV + v);
	}
};

jS.tableCellProvider.prototype = {
	getCell: function(tableI, row, col) {
		if (typeof(col) == "string") {
			col = cE.columnLabelIndex(col);
		}
		var key = tableI + "," + row + "," + col;
		var cell = this.cells[key];
		if (!cell) {
			var td = jS.getTd(tableI, row - 1, col - 1);
			if (td) {
				cell = this.cells[key] = new jS.tableCell(tableI, row, col);
			}
		}
		return cell;
	},
	getNumberOfColumns: function(row) {
		var tableBody = document.getElementById(this.tableBodyId);
		if (tableBody) {
			var tr = tableBody.rows[row];
			if (tr) {
				return tr.cells.length;
			}
		}
		return 0;
	},
	toString: function() {
		result = "";
		jQuery('#' + (this.tableBodyId) + ' tr').each(function() {
			result += this.innerHTML.replace(/\n/g, "") + "\n";
		});
		return result;
	}
};

jS.tableCell.prototype = {
	getTd: function() {
		return document.getElementById(jS.getTdId(this.tableI, this.row - 1, this.col - 1));
	},
	setValue: function(v, e) {
		this.error = e;
		this.value = v;
		jQuery(this.getTd()).html(v ? v: ""); //I know this is slower than innerHTML = '', but sometimes stability just rules!
	},
	getValue: function() {
		var v = this.value;
		if (v === jS.EMPTY_VALUE && !this.getFormula()) {
			v = this.getTd().innerHTML;
			v = this.value = (v.length > 0 ? cE.parseFormulaStatic(v) : null);

		}
		return (v === jS.EMPTY_VALUE ? null: v);
	},
	getFormat: function() {
		return jQuery(this.getTd()).attr("format");
	},
	setFormat: function(v) {
		jQuery(this.getTd()).attr("format", v);
	},
	getFormulaFunc: function() {
		return this.formulaFunc;
	},
	setFormulaFunc: function(v) {
		this.formulaFunc = v;
	},
	getFormula: function() {
		return jQuery(this.getTd()).attr('formula');
	},
	setFormula: function(v) {
		if (v && v.length > 0) {
			jQuery(this.getTd()).attr('formula', v);
		} else {
			jQuery(this.getTd()).removeAttr('formula');
		}
	}
};

var key = {
	BACKSPACE: 			8,
	CAPS_LOCK: 			20,
	COMMA: 				188,
	CONTROL: 			17,
	DELETE: 			46,
	DOWN: 				40,
	END: 				35,
	ENTER: 				13,
	ESCAPE: 			27,
	HOME: 				36,
	INSERT: 			45,
	LEFT: 				37,
	NUMPAD_ADD: 		107,
	NUMPAD_DECIMAL: 	110,
	NUMPAD_DIVIDE: 		111,
	NUMPAD_ENTER: 		108,
	NUMPAD_MULTIPLY: 	106,
	NUMPAD_SUBTRACT: 	109,
	PAGE_DOWN: 			34,
	PAGE_UP: 			33,
	PERIOD: 			190,
	RIGHT: 				39,
	SHIFT: 				16,
	SPACE: 				32,
	TAB: 				9,
	UP: 				38
};

var cE = jQuery.calculationEngine = {
	TEST: {},
	ERROR: "#VALUE!",
	cFN: {//cFN = compiler functions, usually mathmatical
		sum: 	function(x, y) {return x + y;},
		max: 	function(x, y) {return x > y ? x: y;},
		min: 	function(x, y) {return x < y ? x: y;},
		count: 	function(x, y) {return (y != null) ? x + 1: x;},
		clean: function(v) {
			if (typeof(v) == 'string') {
				v = v.replace(cE.regEx.amp, '&')
						.replace(cE.regEx.nbsp, ' ')
						.replace(/\n/g,'')
						.replace(/\r/g,'');
			}
			return v;
		},
		input: {
			select: {
				obj: function() {return jQuery('<select style="width: 100%;" onchange="cE.cFN.input.setValue(jQuery(this).val(), jQuery(this).parent());" class="clickable" />');}
			},
			radio: {
				obj: function(v) {
					var radio = jQuery('<span class="clickable" />');
					var name = cE.cFN.input.radio.name();
					for (var i = 0; i < (v.length <= 25 ? v.length : 25); i++) {
						if (v[i]) {
							radio.append('<input onchange="cE.cFN.input.setValue(jQuery(this).val(), jQuery(this).parent().parent());" type="radio" value="' + v[i] + '" name="' + name + '" />' + v[i] + '<br />');
						}
					}
					return radio;
				},
				name: function() {
					return 'table' + cE.thisCell.tableI + '_cell_c' + (cE.thisCell.col - 1) + '_r' + (cE.thisCell.row - 1) + 'radio';
				}
			},
			checkbox: {
				obj: function(v) {
					return jQuery('<input onclick="cE.cFN.input.setValue(jQuery(this).is(\':checked\') + \'\', jQuery(this).parent());" type="checkbox" value="' + v + '" />' + v + '<br />');
				}
			},
			setValue: function(v, p) {
				p.attr('selectedvalue', v);
				jS.calc(cE.calcState.i);
			},
			getValue: function() {
				return jQuery(jS.getTd(cE.thisCell.tableI, cE.thisCell.row - 1, cE.thisCell.col - 1)).attr('selectedvalue');
			}
		}
	},
	fn: {//fn = standard functions used in cells
		HTML: function(v) {
			return jQuery(v);
		},
		IMG: function(v) {
			return jS.controlFactory.safeImg(v, cE.calcState.row, cE.calcState.col);
		},
		AVERAGE:	function(values) { 
			var arr = cE.foldPrepare(values, arguments);
			return cE.fn.SUM(arr) / cE.fn.COUNT(arr); 
		},
		AVG: 		function(values) { 
			return cE.fn.AVERAGE(values);
		},
		COUNT: 		function(values) {return cE.fold(cE.foldPrepare(values, arguments), cE.cFN.count, 0);},
		SUM: 		function(values) {return cE.fold(cE.foldPrepare(values, arguments), cE.cFN.sum, 0, true);},
		MAX: 		function(values) {return cE.fold(cE.foldPrepare(values, arguments), cE.cFN.max, Number.MIN_VALUE, true);},
		MIN: 		function(values) {return cE.fold(cE.foldPrepare(values, arguments), cE.cFN.min, Number.MAX_VALUE, true);},
		ABS	: 		function(v) {return Math.abs(cE.fn.N(v));},
		CEILING: 	function(v) {return Math.ceil(cE.fn.N(v));},
		FLOOR: 		function(v) {return Math.floor(cE.fn.N(v));},
		INT: 		function(v) {return Math.floor(cE.fn.N(v));},
		ROUND: 		function(v, decimals) {
			return cE.fn.FIXED(v, (decimals ? decimals : 0), false);
		},
		RAND: 		function(v) {return Math.random();},
		RND: 		function(v) {return Math.random();},
		TRUE: 		function() {return 'TRUE';},
		FALSE: 		function() {return 'FALSE';},
		NOW: 		function() {return new Date ( );},
		TODAY: 		function() {return Date( Math.floor( new Date ( ) ) );},
		DAYSFROM: 	function(year, month, day) { 
			return Math.floor( (new Date() - new Date (year, (month - 1), day)) / 86400000);
		},
		IF:			function(v, t, f){
			t = cE.cFN.clean(t);
			f = cE.cFN.clean(f);
			
			try {v = eval(v);} catch(e) {};
			try {t = eval(t);} catch(e) {};
			try {t = eval(t);} catch(e) {};

			if (v == 'true' || v == true || v > 0 || v == 'TRUE') {
				return t;
			} else {
				return f;
			}
		},
		FIXED: 		function(v, decimals, noCommas) { 
			if (decimals == null) {
				decimals = 2;
			}
			var x = Math.pow(10, decimals);
			var s = String(Math.round(cE.fn.N(v) * x) / x); 
			var p = s.indexOf('.');
			if (p < 0) {
				p = s.length;
				s += '.';
			}
			for (var i = s.length - p - 1; i < decimals; i++) {
				s += '0';
			}
			if (noCommas == true) {// Treats null as false.
				return s;
			}
			var arr	= s.replace('-', '').split('.');
			var result = [];
			var first  = true;
			while (arr[0].length > 0) { // LHS of decimal point.
				if (!first) {
					result.unshift(',');
				}
				result.unshift(arr[0].slice(-3));
				arr[0] = arr[0].slice(0, -3);
				first = false;
			}
			if (decimals > 0) {
				result.push('.');
				var first = true;
				while (arr[1].length > 0) { // RHS of decimal point.
					if (!first) {
						result.push(',');
					}
					result.push(arr[1].slice(0, 3));
					arr[1] = arr[1].slice(3);
					first = false;
				}
			}
			if (v < 0) {
				return '-' + result.join('');
			}
			return result.join('');
		},
		TRIM:		function(v) { 
			if (typeof(v) == 'string') {
				v = jQuery.trim(v);
			}
			return v;
		},
		HYPERLINK: function(link, name) {
			name = (name ? name : 'LINK');
			return jQuery('<a href="' + link + '" target="_new" class="clickable">' + name + '</a>');
		},
		DOLLAR: 	function(v, decimals, symbol) { 
			if (decimals == null) {
				decimals = 2;
			}
			
			if (symbol == null) {
				symbol = '$';
			}
			
			var r = cE.fn.FIXED(v, decimals, false);
			
			if (v >= 0) {
				return symbol + r; 
			} else {
				return '-' + symbol + r.slice(1);
			}
		},
		VALUE: 		function(v) {return parseFloat(v);},
		N: 			function(v) {if (v == null) {return 0;}
						  if (v instanceof Date) {return v.getTime();}
						  if (typeof(v) == 'object') {v = v.toString();}
						  if (typeof(v) == 'string') {v = parseFloat(v.replace(cE.regEx.n, ''));}
						  if (isNaN(v))		   {return 0;}
						  if (typeof(v) == 'number') {return v;}
						  if (v == true)			 {return 1;}
						  return 0;},
		PI: 		function() {return Math.PI;},
		POWER: 		function(x, y) {
			return Math.pow(x, y);
		},
		
		//Note, form objects are experimental, they don't work always as expected
		INPUT: {
			SELECT:	function(v, noBlank) {
				v = cE.foldPrepare(v, arguments);
				
				var selectObj = cE.cFN.input.select.obj();
				
				if (!noBlank) {
					selectObj.append('<option value="">Select a value</option>');
				}
				
				for (var i = 0; i < (v.length <= 50 ? v.length : 50); i++) {
					if (v[i]) {
						selectObj.append('<option value="' + v[i] + '">' + v[i] + '</option>');
					}
				}
				
				selectObj.val(cE.cFN.input.getValue());
				
				return selectObj;
			},
			SELECTVAL:	function(v) {
				return jQuery(v).val();
			},
			RADIO: function(v) {
				v = cE.foldPrepare(v, arguments);
				var o = cE.cFN.input.radio.obj(v);
				
				o.find('input[value="' + cE.cFN.input.getValue() + '"]').attr('CHECKED', 'true');
				
				return o;
			},
			RADIOVAL: function(v) {
				v = cE.foldPrepare(v, arguments);
				return jQuery(v).find('input:checked').val();
			},
			CHECKBOX: function(v) {
				v = cE.foldPrepare(v, arguments)[0];
				var o = cE.cFN.input.checkbox.obj(v);
				var checked = cE.cFN.input.getValue();
				if (checked == 'true' || checked == true) {
					o.attr('CHECKED', 'TRUE');
				} else {
					o.removeAttr('CHECKED');
				}
				return o;
			},
			CHECKBOXVAL: function(v) {
				v = cE.foldPrepare(v, arguments);
				return jQuery(v).val();
			},
			ISCHECKED:		function(v) {
				var checked = jQuery(v).is(":checked");
				if (checked) {
					return 'TRUE';
				} else {
					return 'FALSE';
				}
			}
		},
		CHART: {
			BAR:	function(v, legend, axisLabels, w, h) {
				return jS.controlFactory.chart(null, cE.foldPrepare(v, arguments), legend, axisLabels, w, h, cE.calcState.row - 1);
			},
			BARH:	function(v, legend, axisLabels, w, h) {
				return jS.controlFactory.chart('bhg', cE.foldPrepare(v, arguments), legend, axisLabels, w, h, cE.calcState.row - 1);
			},
			SBAR:	function(v, legend, axisLabels, w, h) {
				return jS.controlFactory.chart('bvs', cE.foldPrepare(v, arguments), legend, axisLabels, w, h, cE.calcState.row - 1);
			},
			SBARH:	function(v, legend, axisLabels, w, h) {
				return jS.controlFactory.chart('bhs', cE.foldPrepare(v, arguments), legend, axisLabels, w, h, cE.calcState.row - 1);
			},
			LINE:	function(v, legend, axisLabels, w, h) {
				return jS.controlFactory.chart('lc', cE.foldPrepare(v, arguments), legend, axisLabels, w, h, cE.calcState.row - 1);
			},
			PIE:	function(v, legend, axisLabels, w, h) {
				return jS.controlFactory.chart('p', cE.foldPrepare(v, arguments), legend, axisLabels, w, h, cE.calcState.row - 1);
			},
			PIETHREED:	function(v, legend, axisLabels, w, h) {
				return jS.controlFactory.chart('p3', cE.foldPrepare(v, arguments), legend, axisLabels, w, h, cE.calcState.row - 1);
			},
			CUSTOM:	function(type, v, legend, axisLabels, w, h) {
				return jS.controlFactory.chart(type, cE.foldPrepare(v, arguments), legend, axisLabels,  w, h, cE.calcState.row - 1);
			}
		}
	},
	calcState: {},
	calc: function(cellProvider, context, startFuel) {
		// Returns null if all done with a complete calc() run.
		// Else, returns a non-null continuation function if we ran out of fuel.  
		// The continuation function can then be later invoked with more fuel value.
		// The fuelStart is either null (which forces a complete calc() to the finish) 
		// or is an integer > 0 to slice up long calc() runs.  A fuelStart number
		// is roughly matches the number of cells to visit per calc() run.
		cE.calcState = { 
			cellProvider:	cellProvider, 
			context: 		(context != null ? context: {}),
			row: 			1, 
			col: 			1,
			i:				cellProvider.tableI,
			done:			false,
			stack:			[],
			calcMore: 		function(moreFuel) {
								cE.calcState.fuel = moreFuel;
								return cE.calcLoop();
							}
		};
		return cE.calcState.calcMore(startFuel);
	},
	cell: function() {
		prototype: {// Cells don't know their coordinates, to make shifting easier.
			getError = 			function()	 {return this.error;},
			getValue = 			function()	 {return this.value;},
			setValue = 			function(v, e) {this.value = v;this.error = e;},
			getFormula	 = 		function()  {return this.formula;},	 // Like "=1+2+3" or "'hello" or "1234.5"
			setFormula	 = 		function(v) {this.formula = v;},
			getFormulaFunc = 	function()  {return this.formulaFunc;},
			setFormulaFunc = 	function(v) {this.formulaFunc = v;},
			toString = 			function() {return "Cell:[" + this.getFormula() + ": " + this.getValue() + ": " + this.getError() + "]";};
		}
	}, // Prototype setup is later.
	columnLabelIndex: function(str) {
		// Converts A to 1, B to 2, Z to 26, AA to 27.
		var num = 0;
		for (var i = 0; i < str.length; i++) {
			var digit = str.charCodeAt(i) - 65 + 1;	   // 65 == 'A'.
			num = (num * 26) + digit;
		}
		return num;
	},
	parseLocation: function(locStr) { // With input of "A1", "B4", "F20",
		if (locStr != null &&								  // will return [1,1], [4,2], [20,6].
			locStr.length > 0 &&
			locStr != "&nbsp;") {
			for (var firstNum = 0; firstNum < locStr.length; firstNum++) {
				if (locStr.charCodeAt(firstNum) <= 57) {// 57 == '9'
					break;
				}
			}
			return [ parseInt(locStr.substring(firstNum)),
					 cE.columnLabelIndex(locStr.substring(0, firstNum)) ];
		} else {
			return null;
		}
	},
	columnLabelString: function(index) {
		// The index is 1 based.  Convert 1 to A, 2 to B, 25 to Y, 26 to Z, 27 to AA, 28 to AB.
		// TODO: Got a bug when index > 676.  675==YZ.  676==YZ.  677== AAA, which skips ZA series.
		//	   In the spirit of billg, who needs more than 676 columns anyways?
		var b = (index - 1).toString(26).toUpperCase();   // Radix is 26.
		var c = [];
		for (var i = 0; i < b.length; i++) {
			var x = b.charCodeAt(i);
			if (i <= 0 && b.length > 1) {				   // Leftmost digit is special, where 1 is A.
				x = x - 1;
			}
			if (x <= 57) {								  // x <= '9'.
				c.push(String.fromCharCode(x - 48 + 65)); // x - '0' + 'A'.
			} else {
				c.push(String.fromCharCode(x + 10));
			}
		}
		return c.join("");
	},
	regEx: {
		n: 					/[\$,\s]/g,
		cell: 				/\$?([a-zA-Z]+)\$?([0-9]+)/g, //A1
		range: 				/\$?([a-zA-Z]+)\$?([0-9]+):\$?([a-zA-Z]+)\$?([0-9]+)/g, //A1:B4
		remoteCell:			/\$?(SHEET+)\$?([0-9]+):\$?([a-zA-Z]+)\$?([0-9]+)/g, //SHEET1:A1
		remoteCellRange: 	/\$?(SHEET+)\$?([0-9]+):\$?([a-zA-Z]+)\$?([0-9]+):\$?([a-zA-Z]+)\$?([0-9]+)/g, //SHEET1:A1:B4
		amp: 				/&/g,
		gt: 				/</g,
		lt: 				/>/g,
		nbsp: 				/&nbsp;/g
	},
	str: {
		amp: 	'&amp;',
		lt: 	'&lt;',
		gt: 	'&gt;',
		nbsp: 	'&nbps;'
	},
	parseFormula: function(formula, dependencies, thisTableI) { // Parse formula (without "=" prefix) like "123+SUM(A1:A6)/D5" into JavaScript expression string.
		var nrows = null;
		var ncols = null;
		if (cE.calcState.cellProvider != null) {
			nrows = cE.calcState.cellProvider.nrows;
			ncols = cE.calcState.cellProvider.ncols;
		}
		
		//Cell References Range - Other Tables
		formula = formula.replace(cE.regEx.remoteCellRange, 
			function(ignored, TableStr, tableI, startColStr, startRowStr, endColStr, endRowStr) {
				var res = [];
				var startCol = cE.columnLabelIndex(startColStr);
				var startRow = parseInt(startRowStr);
				var endCol   = cE.columnLabelIndex(endColStr);
				var endRow   = parseInt(endRowStr);
				if (ncols != null) {
					endCol = Math.min(endCol, ncols);
				}
				if (nrows != null) {
					endRow = Math.min(endRow, nrows);
				}
				for (var r = startRow; r <= endRow; r++) {
					for (var c = startCol; c <= endCol; c++) {
						res.push("SHEET" + (tableI) + ":" + cE.columnLabelString(c) + r);
					}
				}
				return "[" + res.join(",") + "]";
			}
		);
		
		//Cell References Fixed - Other Tables
		formula = formula.replace(cE.regEx.remoteCell, 
			function(ignored, tableStr, tableI, colStr, rowStr) {
				tableI = parseInt(tableI) - 1;
				colStr = colStr.toUpperCase();
				if (dependencies != null) {
					dependencies['SHEET' + (tableI) + ':' + colStr + rowStr] = [parseInt(rowStr), cE.columnLabelIndex(colStr), tableI];
				}
				return "(cE.calcState.cellProvider.getCell((" + (tableI) + "),(" + (rowStr) + "),\"" + (colStr) + "\").getValue())";
			}
		);
		
		//Cell References Range
		formula = formula.replace(cE.regEx.range, 
			function(ignored, startColStr, startRowStr, endColStr, endRowStr) {
				var res = [];
				var startCol = cE.columnLabelIndex(startColStr);
				var startRow = parseInt(startRowStr);
				var endCol   = cE.columnLabelIndex(endColStr);
				var endRow   = parseInt(endRowStr);
				if (ncols != null) {
					endCol = Math.min(endCol, ncols);
				}
				if (nrows != null) {
					endRow = Math.min(endRow, nrows);
				}
				for (var r = startRow; r <= endRow; r++) {
					for (var c = startCol; c <= endCol; c++) {
						res.push(cE.columnLabelString(c) + r);
					}
				}
				return "[" + res.join(",") + "]";
			}
		);
		
		//Cell References Fixed
		formula = formula.replace(cE.regEx.cell, 
			function(ignored, colStr, rowStr) {
				colStr = colStr.toUpperCase();
				if (dependencies != null) {
					dependencies['SHEET' + thisTableI + ':' + colStr + rowStr] = [parseInt(rowStr), cE.columnLabelIndex(colStr), thisTableI];
				}
				return "(cE.calcState.cellProvider.getCell((" + thisTableI + "),(" + (rowStr) + "),\"" + (colStr) + "\").getValue())";
			}
		);
		return formula;
	},	
	parseFormulaStatic: function(formula) { // Parse static formula value like "123.0" or "hello" or "'hello world" into JavaScript value.
		if (formula == null) {
			return null;
		} else {
			var formulaNum = formula.replace(cE.regEx.n, '');
			var value = parseFloat(formulaNum);
			if (isNaN(value)) {
				value = parseInt(formulaNum);
			}
			if (isNaN(value)) {
				value = (formula.charAt(0) == "\'" ? formula.substring(1): formula);
			}
			return value;
		}
	},
	calcLoop: function() {
		if (cE.calcState.done == true) {
			return null;
		} else {
			while (cE.calcState.fuel == null || cE.calcState.fuel > 0) {
				if (cE.calcState.stack.length > 0) {
					var workFunc = cE.calcState.stack.pop();
					if (workFunc != null) {
						workFunc(cE.calcState);
					}
				} else if (cE.calcState.cellProvider.formulaCells != null) {
					if (cE.calcState.cellProvider.formulaCells.length > 0) {
						var loc = cE.calcState.cellProvider.formulaCells.shift();
						cE.visitCell(cE.calcState.i, loc[0], loc[1]);
					} else {
						cE.calcState.done = true;
						return null;
					}
				} else {
					if (cE.visitCell(cE.calcState.i, cE.calcState.row, cE.calcState.col) == true) {
						cE.calcState.done = true;
						return null;
					}

					if (cE.calcState.col >= cE.calcState.cellProvider.getNumberOfColumns(cE.calcState.row - 1)) {
						cE.calcState.row++;
						cE.calcState.col =  1;
					} else {
						cE.calcState.col++; // Sweep through columns first.
					}
				}
				
				if (cE.calcState.fuel != null) {
					cE.calcState.fuel -= 1;
				}
			}
			return cE.calcState.calcMore;
		}
	},
	formula: null,
	formulaFunc: null,
	visitCell: function(tableI, r, c) { // Returns true if done with all cells.
		var cell = cE.calcState.cellProvider.getCell(tableI, r, c);
		if (cell == null) {
			return true;
		} else {
			var value = cell.getValue();
			if (value == null) {
				this.formula = cell.getFormula();
				if (this.formula) {
					if (this.formula.charAt(0) == '=') {
						this.formulaFunc = cell.getFormulaFunc();
						if (this.formulaFunc == null ||
							this.formulaFunc.formula != this.formula) {
							this.formulaFunc = null;
							try {
								var dependencies = {};
								var body = cE.parseFormula(this.formula.substring(1), dependencies, tableI);
								this.formulaFunc = function() {
									with (cE.fn) {
										return eval(body);
									}
								};
								
								this.formulaFunc.formula = this.formula;
								this.formulaFunc.dependencies = dependencies;
								cell.setFormulaFunc(this.formulaFunc);
							} catch (e) {
								cell.setValue(cE.ERROR + ': ' + e);
							}
						}
						if (this.formulaFunc) {
							cE.calcState.stack.push(cE.makeFormulaEval(cell, r, c, this.formulaFunc));

							// Push the cell's dependencies, first checking for any cycles. 
							var dependencies = this.formulaFunc.dependencies;
							for (var k in dependencies) {
								if (dependencies[k] instanceof Array &&
									(cE.checkCycles(dependencies[k][0], dependencies[k][1], dependencies[k][2]) == true) //same cell on same sheet
								) {
									cell.setValue(cE.ERROR + ': cycle detected');
									cE.calcState.stack.pop();
									return false;
								}
							}
							for (var k in dependencies) {
								if (dependencies[k] instanceof Array) {
									cE.calcState.stack.push(cE.makeCellVisit(dependencies[k][2], dependencies[k][0], dependencies[k][1]));
								}
							}
						}
					} else {
						cell.setValue(cE.parseFormulaStatic(this.formula));
					}
				}
			}
			return false;
		}
	},
	makeCellVisit: function(tableI, row, col) {
		var fn = function() { 
			return cE.visitCell(tableI, row, col);
		};
		fn.row = row;
		fn.col = col;
		return fn;
	},
	thisCell: null,
	makeFormulaEval: function(cell, row, col, formulaFunc) {
		cE.thisCell = cell;
		var fn = function() {
			try {
				var v = formulaFunc();

				switch(typeof(v)) {
					case "string":
						v = v
							.replace(cE.regEx.amp, cE.str.amp)
							.replace(cE.regEx.lt, cE.str.lt)
							.replace(cE.regEx.gt, cE.str.gt)
							.replace(cE.regEx.nbsp, cE.str.nbsp);
				}

				cell.setValue(v);
				
			} catch (e) {
				//This shouldn't need to be used, usually throws an error when a cell is empty
				//cell.setValue(cE.ERROR + ': ' + e);
			}
		};
		fn.row = row;
		fn.col = col;
		return fn;
	},
	checkCycles: function(row, col, tableI) {
		for (var i = 0; i < cE.calcState.stack.length; i++) {
			var item = cE.calcState.stack[i];
			if (item.row != null && 
				item.col != null &&
				item.row == row  &&
				item.col == col &&
				tableI == cE.calcState.i
			) {
				return true;
			}
		}
		return false;
	},
	foldPrepare: function(firstArg, theArguments) { // Computes the best array-like arguments for calling fold().
		if (firstArg != null &&
			firstArg instanceof Object &&
			firstArg["length"] != null) {
			return firstArg;
		} else {
			return theArguments;
		}
	},
	fold: function(arr, funcOfTwoArgs, result, castToN) {
		for (var i = 0; i < arr.length; i++) {
			result = funcOfTwoArgs(result, (castToN == true ? cE.fn.N(arr[i]): arr[i]));
		}
		return result;
	}
};