tinymce.PluginManager.add('iconpicker', function (editor, url) {
	var iconclass = "icons icon-";
    var defaultIconpicker = [];

    var iconpicker = editor.settings.iconpicker || defaultIconpicker,
		fullIconpickerList = editor.settings.extended_iconpicker ? iconpicker.concat(editor.settings.extended_iconpicker) : iconpicker;
		
	
    function getHtml() {
        var iconpickerHtml;

        iconpickerHtml = '<table role="presentation" class="mce-grid mce-grid-iconpicker">';

        tinymce.each(fullIconpickerList, function (row) {
            iconpickerHtml += '<tr>';

            tinymce.each(row, function (icon) {
                iconpickerHtml += '<td><a href="#" data-mce-iconclass="' + icon.iconclass + '" tabindex="-1" title="' + icon.title + '"><span class="' +
                    icon.iconclass + '"></span></a></td>';
            });

            iconpickerHtml += '</tr>';
        });

        iconpickerHtml += '</table>';

        return iconpickerHtml;
    }

    function concatArray(array) {
        var each = tinymce.each, result = [];
        each(array, function (item) {
            result = result.concat(item);
        });
        return result.length > 0 ? result : array;
    }

    function findAndReplaceDOMText(regex, node, replacementNode, captureGroup, schema) {
        var m, matches = [], text, count = 0, doc;
        var blockElementsMap, hiddenTextElementsMap, shortEndedElementsMap;

        doc = node.ownerDocument;
        blockElementsMap = schema.getBlockElements(); // H1-H6, P, TD etc
        hiddenTextElementsMap = schema.getWhiteSpaceElements(); // TEXTAREA, PRE, STYLE, SCRIPT
        shortEndedElementsMap = schema.getShortEndedElements(); // BR, IMG, INPUT

        function getMatchIndexes(m, captureGroup) {
            captureGroup = captureGroup || 0;

            var index = m.index;

            if (captureGroup > 0) {
                var cg = m[captureGroup];
                index += m[0].indexOf(cg);
                m[0] = cg;
            }

            return [index, index + m[0].length, [m[0]]];
        }

        function getText(node) {
            var txt;

            if (node.nodeType === 3) {
                return node.data;
            }

            if (hiddenTextElementsMap[node.nodeName] && !blockElementsMap[node.nodeName]) {
                return '';
            }

            txt = '';

            if (blockElementsMap[node.nodeName] || shortEndedElementsMap[node.nodeName]) {
                txt += '\n';
            }

            if ((node = node.firstChild)) {
                do {
                    txt += getText(node);
                } while ((node = node.nextSibling));
            }

            return txt;
        }

        function stepThroughMatches(node, matches, replaceFn) {
            var startNode, endNode, startNodeIndex,
                endNodeIndex, innerNodes = [], atIndex = 0, curNode = node,
                matchLocation = matches.shift(), matchIndex = 0;

            out: while (true) {
                if (blockElementsMap[curNode.nodeName] || shortEndedElementsMap[curNode.nodeName]) {
                    atIndex++;
                }

                if (curNode.nodeType === 3) {
                    if (!endNode && curNode.length + atIndex >= matchLocation[1]) {
                        // We've found the ending
                        endNode = curNode;
                        endNodeIndex = matchLocation[1] - atIndex;
                    } else if (startNode) {
                        // Intersecting node
                        innerNodes.push(curNode);
                    }

                    if (!startNode && curNode.length + atIndex > matchLocation[0]) {
                        // We've found the match start
                        startNode = curNode;
                        startNodeIndex = matchLocation[0] - atIndex;
                    }

                    atIndex += curNode.length;
                }

                if (startNode && endNode) {
                    curNode = replaceFn({
                        startNode: startNode,
                        startNodeIndex: startNodeIndex,
                        endNode: endNode,
                        endNodeIndex: endNodeIndex,
                        innerNodes: innerNodes,
                        match: matchLocation[2],
                        matchIndex: matchIndex
                    });

                    // replaceFn has to return the node that replaced the endNode
                    // and then we step back so we can continue from the end of the
                    // match:
                    atIndex -= (endNode.length - endNodeIndex);
                    startNode = null;
                    endNode = null;
                    innerNodes = [];
                    matchLocation = matches.shift();
                    matchIndex++;

                    if (!matchLocation) {
                        break; // no more matches
                    }
                } else if ((!hiddenTextElementsMap[curNode.nodeName] || blockElementsMap[curNode.nodeName]) && curNode.firstChild) {
                    // Move down
                    curNode = curNode.firstChild;
                    continue;
                } else if (curNode.nextSibling) {
                    // Move forward:
                    curNode = curNode.nextSibling;
                    continue;
                }

                // Move forward or up:
                while (true) {
                    if (curNode.nextSibling) {
                        curNode = curNode.nextSibling;
                        break;
                    } else if (curNode.parentNode !== node) {
                        curNode = curNode.parentNode;
                    } else {
                        break out;
                    }
                }
            }
        }

        /**
        * Generates the actual replaceFn which splits up text nodes
        * and inserts the replacement element.
        */
        function genReplacer(nodeName) {
            var makeReplacementNode;

            if (typeof nodeName != 'function') {
                var stencilNode = nodeName.nodeType ? nodeName : doc.createElement(nodeName);

                makeReplacementNode = function () {
                    var clone = stencilNode.cloneNode(false);
                    return clone;
                };
            } else {
                makeReplacementNode = nodeName;
            }

            return function replace(range) {
                var before, after, parentNode, startNode = range.startNode,
                    endNode = range.endNode;

                if (startNode === endNode) {
                    var node = startNode;

                    parentNode = node.parentNode;
                    if (range.startNodeIndex > 0) {
                        // Add `before` text node (before the match)
                        before = doc.createTextNode(node.data.substring(0, range.startNodeIndex));
                        parentNode.insertBefore(before, node);
                    }

                    // Create the replacement node:
                    var el = makeReplacementNode();
                    parentNode.insertBefore(el, node);
                    if (range.endNodeIndex < node.length) {
                        // Add `after` text node (after the match)
                        after = doc.createTextNode(node.data.substring(range.endNodeIndex));
                        parentNode.insertBefore(after, node);
                    }

                    node.parentNode.removeChild(node);

                    return el;
                }
            };
        }

        text = getText(node);
        if (!text) {
            return;
        }
        while ((m = regex.exec(text))) {
            matches.push(getMatchIndexes(m, captureGroup));
        }

        if (matches.length) {
            count = matches.length;
            stepThroughMatches(node, matches, genReplacer(replacementNode));
        }

        return count;
    }
/*
	function applyFormat(format, value) {
		editor.undoManager.transact(function() {
			editor.focus();
			editor.formatter.apply(format, {value: value});
			editor.nodeChanged();
		});
	}

	function removeFormat(format) {
		editor.undoManager.transact(function() {
			editor.focus();
			editor.formatter.remove(format, {value: null}, null, true);
			editor.nodeChanged();
		});
	}
*/
	
	function onButtonClick() {
		var self = this;
/*
		if (self._color) {
			applyFormat(self.settings.format, self._color);
		} else {
			removeFormat(self.settings.format);
		}
		*/
		showDialog(); // AH
	}
	
	function showDialog(imageList) {
		var win, data = {}, dom = editor.dom, icoElm = editor.selection.getNode();
		var classListCtrl;


		function onSubmitForm() {

			data = tinymce.extend(data, win.toJSON());

			// Setup new data excluding style properties
			/*eslint dot-notation: 0*/
			data = {
				"class": 'cc-iconcontainer ' + data["class"]
			};

			editor.undoManager.transact(function() {
				if (icoElm) {
					dom.setAttribs(icoElm, data);
				}

			});
		}

		if (icoElm.nodeName == 'SPAN' && !icoElm.getAttribute('data-mce-object') && !icoElm.getAttribute('data-mce-placeholder')) {
			data = {
				"class": dom.getAttrib(icoElm, 'class').replace(/(cc-iconcontainer) ?/, "")
			};
		} else {
			icoElm = null;
			return false;
		}

		function buildListItems(inputList, itemCallback, startItems) {
			function appendItems(values, output) {
				output = output || [];

				tinymce.each(values, function(item) {
					var menuItem = {text: item.text || item.title};

					if (item.menu) {
						menuItem.menu = appendItems(item.menu);
					} else {
						menuItem.value = item.value;
						itemCallback(menuItem);
					}

					output.push(menuItem);
				});

				return output;
			}

			return appendItems(inputList, startItems || []);
		}

		if (editor.settings.icon_class_list) {
			classListCtrl = {
				name: 'class',
				type: 'listbox',
				label: 'Class',
				values: buildListItems(
					editor.settings.icon_class_list,
					function(item) {
						if (item.value) {
							item.textStyle = function() {
								return editor.formatter.getCssText({inline: 'span', classes: ['cc-iconcontainer ' + item.value]});
							};
						}
					}
				)
			};
		}

		// General settings shared between simple and advanced dialogs
		var generalFormItems = [];

		generalFormItems.push(classListCtrl);


		// Simple default dialog
		win = editor.windowManager.open({
			title: 'Icon',
			data: data,
			body: generalFormItems,
			onSubmit: onSubmitForm
		});
	}

    editor.addButton('iconpicker', {
        type: 'colorbutton',
		format: 'forecolor',
        icon: 'preview',
		stateSelector: 'span.cc-iconcontainer',
		panel: {
            autohide: true,
            html: getHtml,
            onclick: function (e) {
				
				e.preventDefault();
                
				var linkElm = editor.dom.getParent(e.target, 'a');

                if (linkElm) {
				
					var icoSel = $(tinyMCE.activeEditor.selection.getContent());
					var newIco = '<span class="' + linkElm.getAttribute('data-mce-iconclass') + '" role="icon"><!-- cc-icon --></span>';
					
					// if replace icon
					if(icoSel.hasClass('cc-iconcontainer')){
						newIco = icoSel.html(newIco).prop('outerHTML') + '&nbsp;';
					// else add icon with container
					}else{
						newIco = '<span class="cc-iconcontainer">' + newIco + '</span>&nbsp;';
					}
                    
					editor.insertContent(newIco);
                    this.hide();
                }
            }
        },
		onclick: onButtonClick,
        tooltip: 'Iconpicker'
    });
	
	
    editor.on('init', function() {

		var cssURL = url + '/css/iconpicker.css';
		
		if(editor.dom
		&& typeof(editor.dom.loadCSS) == "function"
		){
			editor.dom.loadCSS(cssURL);
		}else{
			head.load({tmceiconpicker: cssURL});			
		}
	
	});
	
	// makes an empty span.cc-iconcontainer tag selectable
	editor.on("mousedown", function(e) {
		
		e = e.target;
		
		var icoItem		= $(e).closest('.cc-iconcontainer');
		var editArea	= icoItem.closest('.mce-content-body');
		
		if(icoItem.length) {
		
			var icoFillPH	= icoItem.children('.icon-selection-fill');
			var fillSpan	= '<span class="icon-selection-fill">&nbsp;</span>';
			
			if(!icoFillPH.length){
				icoItem.prepend(fillSpan);
				icoItem.append(fillSpan);
			}
						
			icoItem.on('mouseup', function(e) {
			
				window.setTimeout(function() {
					editor.selection.setCursorLocation(icoItem[0]);
					editor.selection.select(icoItem[0]);
				}, 1);
			});
		}
	});
			
	editor.on('nodeChange', function(e) {
		// remove class 'selected'
		var editArea	= $(e.element).closest('.mce-content-body');
		var icoItem		= $(e.element).closest('.cc-iconcontainer');
				
		editArea.find('.cc-iconcontainer').removeAttr("data-mce-selected");
		
		//add class 'selected' for styling
		if(icoItem.length){
			icoItem.attr('data-mce-selected', 1);
		}
	});
	
	// Remove selection fill helper spans on save
	editor.on('SaveContent', function(ed) {
		var fillSpanRx	= /\s*<span class="icon-selection-fill">\s*&nbsp;\s*<\/span>\s*/;
		ed.content = ed.content.replace(RegExp(fillSpanRx, "g"), "");
		$(ed.element).html(ed.content);
	});
});
/*

tinymce.PluginManager.add('iconpicker', function(editor, url) {
    // Add a button that opens a window
    editor.addButton('iconpicker', {
		type: 'splitbutton',
		text: 'My iconpicker button',
		icon: false,
		onclick: function() {
			editor.insertContent('Main button');
		},
		menu: [
			{text: 'Menu item 1', onclick: function() {editor.insertContent('<span class="icons">&nbsp;</span>');}},
			{text: 'Menu item 2', onclick: function() {editor.insertContent('Menu item 2');}}
		]
	});
});

/*
tinymce.PluginManager.add('iconpicker', function(editor, url) {
    editor.on('init', function() {
        var cssURL = url + '/css/iconpicker.css';
        if(document.createStyleSheet){
            document.createStyleSheet(cssURL);
        } else {
            cssLink = editor.dom.create('link', {
                        rel: 'stylesheet',
                        href: cssURL
                      });
            document.getElementsByTagName('head')[0].
                      appendChild(cssLink);
        }
    });
 
    editor.addButton('iconpicker', 
         {tooltip      : 'my plugin button',
          icon         : 'iconpicker',
          type         : 'menubutton',
          menu         : [{items    : 'Click me', 
	 	    	          onclick : function() { alert('Clicked!');}}]
	});
});
*/
