// Editor myTinyMCE-FE
// Theme styles/defaults laden
head.load({ccthemestyles: cc.httpRoot + '/system/access/js/ccThemeStyles.min.js'}, function(){

(function($){

	$.getTinyMCE_FE = function(elemHeight){

		// Theme styles
		cc.getThemeStyles();
		
		// tinyMCE-FE init
		tinyMCE.init({
			inline : true,
			encoding: "UTF-8",
			entity_encoding : "named",
			entities : "160,nbsp,38,amp,60,lt,62,gt",
			selector : ".feTextEdit",
			relative_urls: false,
			document_base_url : cc.httpRoot + "/",
			convert_urls : false,
			remove_script_host : false,
			forced_root_block : false,
			convert_newlines_to_brs: false,
			force_br_newlines: false,
			force_p_newlines: true,
			language : cc.adminLang,
			theme : "modern",
			skin : "concise",
			//content_css : [cc.httpRoot + "/themes/" + cc.activeTheme + "/css/main.css" + cc.cacheNoExt, cc.httpRoot + "/themes/" + cc.activeTheme + "/css/icons.css" + cc.cacheNoExt, cc.httpRoot + "/themes/" + cc.activeTheme + "/css/style.css" + cc.cacheNoExt],
			width : "calc(100% + 2px)",
			maxHeight : elemHeight,
			insertdatetime_formats: ["%d.%m.%Y", "%H:%M Uhr", "%H:%M:%S", "%m-%d-%Y", "%D", "%H:%M:%S %p"],
			visualblocks_default_state: false,
			//indentation : '18px',
			schema: "html5",
			extended_valid_elements: "span[id|class|style|title|aria-hidden|role|itemprop|itemscope|itemtype|data-*],div[id|class|style|title|aria-hidden|role|itemprop|itemscope|itemtype|data-*],meta[*],style[*]",
			valid_children : "span.cc-iconcontainer[span[role='icon']]",
			plugins : ["advlist autolink link image lists charmap print preview hr anchor pagebreak","searchreplace wordcount visualblocks visualchars codemagic insertdatetime media nonbreaking","save table contextmenu directionality template paste textcolor","iconpicker","colorpicker","imagetools"],
			menubar: "format edit view insert tools table colorpicker",
			toolbar1: "undo redo | fontselect | alignleft aligncenter alignright alignjustify | forecolor | iconpicker | codemagic",
			toolbar2: "bold italic | fontsizeselect | bullist numlist outdent indent | link image media",
			textcolor_map: cc.customPalette,
			font_formats: cc.themeFonts,
			fontsize_formats: "8px 10px 12px 14px 18px 24px 36px 48px 60px 72px 90px 128px 144px 180px",
			style_formats: [
				{title: 'Headers', items: [
					{title: "Header 1", format: "h1"},
					{title: "Header 2", format: "h2"},
					{title: "Header 3", format: "h3"},
					{title: "Header 4", format: "h4"},
					{title: "Header 5", format: "h5"},
					{title: "Header 6", format: "h6"}
				]},
				{title: 'Blocks', items: [
					{title: "Div", format: "div"},
					{title: "Paragraph", format: "p"},
					{title: "Blockquote", format: "blockquote"},
					{title: "Pre", format: "pre"},
					{title: "Figcaption", block: "figcaption"}
				]},
				{title: "Inline", items: [
					{title: "Bold", icon: "bold", format: "bold"},
					{title: "Italic", icon: "italic", format: "italic"},
					{title: "Underline", icon: "underline", format: "underline"},
					{title: "Strikethrough", icon: "strikethrough", format: "strikethrough"},
					{title: "Superscript", icon: "superscript", format: "superscript"},
					{title: "Subscript", icon: "subscript", format: "subscript"},
					{title: "Code", icon: "code", format: "code"},
					{title: 'Strong', icon: "bold", inline: 'strong', wrapper: false, merge_siblings: true},
					{title: 'Em', icon: "italic", inline: 'em', wrapper: false, merge_siblings: true}
				]},
				/*
				{title: "Alignment", items: [
					{title: "Left", icon: "alignleft", format: "alignleft"},
					{title: "Center", icon: "aligncenter", format: "aligncenter"},
					{title: "Right", icon: "alignright", format: "alignright"},
					{title: "Justify", icon: "alignjustify", format: "alignjustify"}
				]},
				*/
				{title: 'Containers', items: [
					{title: 'Section', block: 'section', wrapper: true, merge_siblings: false},
					{title: 'Container', block: 'div', classes: 'container', wrapper: true, merge_siblings: false},
					{title: 'Row', block: 'div', classes: 'row', wrapper: true, merge_siblings: false},
					{title: "Div", format: "div", wrapper: true, merge_siblings: false},
					{title: "Column", items: [
						{title: "Column 1", block: "div", selector: "div", classes: "{t_class:col-1}", wrapper: true, merge_siblings: false},
						{title: "Column 2", block: "div", selector: "div", classes: "{t_class:col-2}", wrapper: true, merge_siblings: false},
						{title: "Column 3", block: "div", selector: "div", classes: "{t_class:col-3}", wrapper: true, merge_siblings: false},
						{title: "Column 4", block: "div", selector: "div", classes: "{t_class:col-4}", wrapper: true, merge_siblings: false},
						{title: "Column 5", block: "div", selector: "div", classes: "{t_class:col-5}", wrapper: true, merge_siblings: false},
						{title: "Column 6", block: "div", selector: "div", classes: "{t_class:col-6}", wrapper: true, merge_siblings: false},
						{title: "Column 7", block: "div", selector: "div", classes: "{t_class:col-7}", wrapper: true, merge_siblings: false},
						{title: "Column 8", block: "div", selector: "div", classes: "{t_class:col-8}", wrapper: true, merge_siblings: false},
						{title: "Column 9", block: "div", selector: "div", classes: "{t_class:col-9}", wrapper: true, merge_siblings: false},
						{title: "Column 10", block: "div", selector: "div", classes: "{t_class:col-10}", wrapper: true, merge_siblings: false},
						{title: "Column 11", block: "div", selector: "div", classes: "{t_class:col-11}", wrapper: true, merge_siblings: false},
						{title: "Column 12", block: "div", selector: "div", classes: "{t_class:col-12}", wrapper: true, merge_siblings: false}
					]},
					{title: 'Article', block: 'article', wrapper: true, merge_siblings: false},
					{title: 'Blockquote', block: 'blockquote', wrapper: true},
					{title: 'Hgroup', block: 'hgroup', wrapper: true},
					{title: 'Aside', block: 'aside', wrapper: true},
					{title: 'Figure', block: 'figure', wrapper: true}
				]},
				{title: 'Buttons', items: [
					{title: 'Button default', selector: 'button,a', classes: 'btn btn-default', merge_siblings: false},
					{title: 'Button primary', selector: 'button,a', classes: 'btn btn-primary', merge_siblings: false},
					{title: 'Button secondary', selector: 'button,a', classes: 'btn btn-secondary', merge_siblings: false},
					{title: 'Button info', selector: 'button,a', classes: 'btn btn-info', merge_siblings: false},
					{title: 'Button link', selector: 'button,a', classes: 'btn btn-link', merge_siblings: false}
				]},
				{title: 'Theme Styles', items: 
					cc.defaultThemeStyles
				}
			],
			visualblocks_default_state: false,
			end_container_on_empty_block: true,
			image_list : cc.httpRoot + "/system/access/tmce4.imgList.php",
			image_class_list: [
					{title: 'None', value: ''},
					{title: 'Image with frame', value: (cc.feTheme.styles.imgf ? cc.feTheme.styles.imgf : "imgFrame")},
					{title: 'Image without frame', value: (cc.feTheme.styles.imgnf ? cc.feTheme.styles.imgnf : "imgNoFrame img-default")},
					{title: 'Rounded image', value: (cc.feTheme.styles.imgr ? cc.feTheme.styles.imgr : "imgNoFrame img-responsive img-rounded")},
					{title: 'Rounded image with frame', value: (cc.feTheme.styles.imgrf ? cc.feTheme.styles.imgrf : "imgFrame img-framed img-responsive img-rounded")},
					{title: 'Circular image', value: (cc.feTheme.styles.imgc ? cc.feTheme.styles.imgc : "imgNoFrame img-responsive img-circle")},
					{title: 'Circular image with frame', value: (cc.feTheme.styles.imgcf ? cc.feTheme.styles.imgcf : "imgFrame img-framed img-responsive img-circle")}
			],
			icon_class_list: cc.iconEffects,
			table_class_list: [
					{title: 'None', value: ''},
					{title: 'Table', value: 'table'},
					{title: 'Table bordered', value: 'table table-bordered'},
					{title: 'Table striped', value: 'table table-striped'},
					{title: 'Table condensed', value: 'table table-condensed'},
					{title: 'Table hover', value: 'table table-hover'},
					{title: 'Table responsive', value: 'table table-responsive'}
			],
			link_list : cc.httpRoot + "/system/access/tmce4.linkList.php",
			link_class_list: [
					{title: 'None', value: ''},
					{title: 'Link', value: 'link'},
					{title: 'External link', value: 'extLink'},
					{title: 'Button link', value: 'button btn btn-link'},
					{title: 'Button default', value: 'button btn btn-default'},
					{title: 'Button primary', value: 'button btn btn-primary'},
					{title: 'Button secondary', value: 'button btn btn-secondary'},
					{title: 'Button info', value: 'button btn btn-info'},
					{title: 'Button success', value: 'button btn btn-success'},
					{title: 'Button warning', value: 'button btn btn-warning'},
					{title: 'Button danger', value: 'button btn btn-danger'}
			],
			iconpicker: cc.defaultIconpicker,
			init_instance_callback : function(edId){
									var inst = tinyMCE.get(edId.id);
									var repl = inst.getContent().replace(/{#root_img}/g, cc.imageDir).replace(/{#root}/g, cc.httpRoot);
									inst.execCommand("mceSetContent",true,repl);
									},	
			file_browser_callback : myFileBrowser
		});
	};
})(jQuery);
});