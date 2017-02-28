	{e_conDef-open}
		<div id="ee-area-{e_area}-conID-{e_connr}" class="editDiv{e_empty}">
			<div class="innerEditDiv cc-element-init{e_empty}{e_hidden}" data-type="{e_contype}" data-columns="{e_cols}" data-maxcols="{e_maxcols}" data-row="{e_row}" data-lang="{s_label:colno}" data-script="{system_root}/access/editElements.php?page=admin&action=fe-resize&area={e_conarea}&connr={e_connr}&id={e_id}&red={e_red}&cols=" data-menu="context" data-target="contextmenu-fe-{e_area}-{e_connr}">
				<div class="conTypeDiv">
                    <span class="conType cc-admin-icons cc-icon-{e_contype} contype-plugins conicon-{e_contype}" style="background-image:url({e_coniconpath}/conicon_{e_contype}.png)" title="{e_contype}">&nbsp;</span>				
                </div>
                <div class="editDivFrame {e_contype}" data-type="{e_contype}" title="{e_area}#{e_connr} &#9658; <strong>{e_contype}{e_subtype}</strong>"></div>
				<div class="editButtons editButtons-panel" data-id="contextmenu-fe-{e_area}-{e_connr}">
                	<button type="button" class="cc-button button-icon-only pastecon editcon" data-action="editcon" data-actiontype="pastecon" data-url="{system_root}/access/editElements.php?page=admin&action=paste&con={e_connr}&conmax={e_conmax}&connr={e_busycon}&id={e_id}&area={e_conarea}&red={e_red}" data-menuitem="true" data-id="item-id-{e_connr}" title="{s_title:paste}" style="{e_display1}">
                	<span class="cc-admin-icons cc-icons cc-icon-arrow-drop-down-circle">&nbsp;</span>
					</button>
					{e_cancelpaste}
					<button type="button" class="cc-button button-icon-only newcon" data-menuitem="true" data-id="item-id-{e_connr}" data-menutitle="{s_title:new}" title="{s_title:new}<br />({s_common:after} {e_area}#{e_connr} &#9658; {e_contype}{e_subtype})">		
					<span class="cc-admin-icons cc-icons cc-icon-add-circle-outline">&nbsp;</span>			
					</button>
					<div class="addCon">
                        <form><input type="hidden" class="ajaxaction" value="{system_root}/access/editElements.php?page=admin&action=new&con={e_connr}&conmax={e_conmax}&connr={e_busycon}&id={e_id}&area={e_conarea}&red={e_red}" title="{s_title:new}<br />({s_common:after} {e_area}#{e_connr} &#9658; {e_contype}{e_subtype})" />
                        {e_chooseNewElement}
                        </form>
                    </div>
                    <button type="button" class="cc-button button-icon-only editcon" data-action="editcon" data-actiontype="newpage" data-url="{admin_root}?task=new" data-menuitem="true" data-id="item-id-{e_connr}" data-menutitle="{s_button:adminnew}" title="{s_button:adminnew}" style="{e_display0}">
                    <span class="cc-admin-icons cc-icons cc-icon-earth cc-icon-newpage">&nbsp;</span>
					</button>
                    <button type="button" class="cc-button button-icon-only cc-fe-edit-text {e_contype}" data-url="{system_root}/access/editElements.php?page=admin&action=fe-editcon&type={e_contype}&con={e_connr}&id={e_id}&area={e_conarea}&red={e_red}" data-menuitem="true" data-id="item-id-{e_connr}" data-menutitle="{s_label:directedit}" title="{s_title:directedit}" style="{e_display-text}">
                    <span class="cc-admin-icons cc-icons cc-icon-edit">&nbsp;</span>
					</button>
                    <button type="button" class="cc-button button-icon-only editcon" data-action="editcon" data-actiontype="editmodule" data-url="{admin_root}?task=modules&type={e_contype}&list_cat=all" data-menuitem="true" data-id="item-id-{e_connr}" data-menutitle="{s_title:goto} &#9658; {e_datatype}" title="{s_title:goto} &#9658; {e_datatype}" style="{e_display7}">
                    <span class="cc-admin-icons cc-icons cc-icon-{e_contype}">&nbsp;</span>
					</button>
                    <button type="button" class="cc-button button-icon-only cc-fe-edit-img {e_contype}" data-url="{system_root}/access/editElements.php?page=admin&action=fe-editcon&type={e_contype}&con={e_connr}&id={e_id}&area={e_conarea}&red={e_red}" data-menuitem="true" data-id="item-id-{e_connr}" data-menutitle="{s_label:chooseimg}" title="{s_label:chooseimg}" style="{e_display-img}">
                    <span class="cc-admin-icons cc-icons cc-icon-image">&nbsp;</span>
					</button>
                    <button type="button" class="cc-button button-icon-only editcon editgall" data-action="editcon" data-actiontype="editgall" data-url="{admin_root}?task=modules&type=gallery&edit_gall=" data-menuitem="true" data-id="item-id-{e_connr}" data-menutitle="{s_title:goto} &#9658; {s_nav:admingallery}" title="{s_title:goto} &#9658; {s_nav:admingallery}" style="{e_display8}">
                    <span class="cc-admin-icons cc-icons cc-icon-images">&nbsp;</span>
					</button>
                    <button type="button" class="cc-button button-icon-only editcon" data-action="editcon" data-actiontype="editform" data-url="{admin_root}?task=forms" data-menuitem="true" data-id="item-id-{e_connr}" data-menutitle="{s_title:goto} &#9658; {s_header:adminforms}" title="{s_title:goto} &#9658; {s_header:adminforms}" style="{e_display9}">
                    <span class="cc-admin-icons cc-icons cc-icon-edit">&nbsp;</span>
					</button>
                	<button type="button" class="cc-button button-icon-only copycon editcon" data-action="editcon" data-actiontype="copycon" data-url="{system_root}/access/editElements.php?page=admin&action=copy&con={e_connr}&id={e_id}&area={e_conarea}&red={e_red}" data-menuitem="true" data-id="item-id-{e_connr}" data-menutitle="{s_title:copy}" title="{s_title:copy}<br />({e_area}#{e_connr} &#9658; {e_contype}{e_subtype})" style="{e_display2}">
                	<span class="cc-admin-icons cc-icons cc-icon-copy">&nbsp;</span>
					</button>
                	<input type="hidden" name="sortsource" value="{system_root}/access/editElements.php?page=admin&action=cut&con={e_connr}&id={e_id}&area={e_conarea}&red={e_red}" />
                	<input type="hidden" name="sorttarget" value="&targetcon={e_connr}&conmax={e_conmax}&connr={e_busycon}&targetid={e_id}&targetarea={e_conarea}" />
                	<button type="button" class="cc-button button-icon-only cutcon editcon" data-action="editcon" data-actiontype="cutcon" data-url="{system_root}/access/editElements.php?page=admin&action=cut&con={e_connr}&id={e_id}&area={e_conarea}&red={e_red}" data-menuitem="true" data-id="item-id-{e_connr}" data-menutitle="{s_title:cut}" title="{s_title:cut}<br />({e_area}#{e_connr} &#9658; {e_contype}{e_subtype})" style="{e_display3}">
                	<span class="cc-admin-icons cc-icons cc-icon-scissors">&nbsp;</span>
					</button>
                	<button type="button" class="cc-button button-icon-only sortcon" data-url="{system_root}/access/editElements.php?page=admin&action=down&con={e_connr}&id={e_id}&area={e_conarea}&red={e_red}" data-menuitem="true" data-id="item-id-{e_connr}" data-menutitle="{s_title:movedown}" title="{s_title:movedown}<br />({e_area}#{e_connr} &#9658; {e_contype}{e_subtype})" style="{e_display4}">
                	<span class="cc-admin-icons cc-icons cc-icon-circle-down">&nbsp;</span>
					</button>
                	<button type="button" class="cc-button button-icon-only sortcon" data-url="{system_root}/access/editElements.php?page=admin&action=up&con={e_connr}&id={e_id}&area={e_conarea}&red={e_red}" data-menuitem="true" data-id="item-id-{e_connr}" data-menutitle="{s_title:moveup}" title="{s_title:moveup}<br />({e_area}#{e_connr} &#9658; {e_contype}{e_subtype})" style="{e_display5}">
                	<span class="cc-admin-icons cc-icons cc-icon-circle-up">&nbsp;</span>
					</button>
                    <span class="switchIcons">
					<button type="button" class="cc-button button-icon-only pubcon editcon" data-publish="0" data-action="editcon" data-actiontype="pubcon" data-url="{system_root}/access/editElements.php?page=admin&action=publish&con={e_connr}&id={e_id}&area={e_conarea}&status=0" data-menuitem="true" data-id="item-id-{e_connr}" data-menutitle="{s_title:publishelement}" title="{s_title:publishelement}<br />({e_area}#{e_connr} &#9658; {e_contype}{e_subtype})" style="{e_display-pub1}">
					<span class="cc-admin-icons cc-icons cc-icon-visibility-off">&nbsp;</span>
					</button>
					<button type="button" class="cc-button button-icon-only pubcon editcon" data-publish="1" data-action="editcon" data-actiontype="pubcon" data-url="{system_root}/access/editElements.php?page=admin&action=publish&con={e_connr}&id={e_id}&area={e_conarea}&status=1" data-menuitem="true" data-id="item-id-{e_connr}" data-menutitle="{s_title:hideelement}" title="{s_title:hideelement}<br />({e_area}#{e_connr} &#9658; {e_contype}{e_subtype})" style="{e_display-pub2}">
					<span class="cc-admin-icons cc-icons cc-icon-visibility">&nbsp;</span>
					</button>
					</span>
                    <button type="button" class="cc-button button-icon-only delcon editcon" data-action="editcon" data-url="{system_root}/access/editElements.php?page=admin&action=del&con={e_connr}&id={e_id}&area={e_conarea}&red={e_red}" data-menuitem="true" data-id="item-id-{e_connr}" data-menutitle="{s_title:delete}" title="{s_title:delete}<br />({e_area}#{e_connr} &#9658; {e_contype}{e_subtype})" style="{e_display6}">
                    <span class="cc-admin-icons cc-icons cc-icon-bin">&nbsp;</span>
					</button>
                    <button type="button" class="cc-button button-icon-only editcon" data-action="editcon" data-actiontype="edit" data-url="{admin_root}?task={e_edittask}&edit_id={e_id}&area={e_conarea}&connr={e_connr}#con{e_connr}" data-menuitem="true" data-id="item-id-{e_connr}" data-menutitle="{s_title:edit}" title="{s_title:edit}<br />({e_area}#{e_connr} &#9658; {e_contype}{e_subtype})">
                    <span class="cc-admin-icons cc-icons cc-icon-cog">&nbsp;</span>
					</button>
                    <span class="feButtonSeparator" style="{e_display-directedit}">&#9658;</span>
                    <button type="button" class="cc-button button-icon-only directedit {e_contype}" data-url="{system_root}/access/editElements.php?page=admin&action=fe-editcon&type={e_contype}&con={e_connr}&id={e_id}&area={e_conarea}&getform=1&red={e_red}" data-menuitem="true" data-id="item-id-{e_connr}" data-menutitle="{s_title:editelement}" title="{s_title:editelement}" style="{e_display-directedit}">
                    <span class="cc-admin-icons cc-icons cc-icon-pencil">&nbsp;</span>
					</button>
                    <button type="button" class="cc-button button-icon-only feEditButton fe-changes apply button-icon-only button-small" data-action="apply" data-menuitem="true" data-id="item-id-{e_connr}" title="<strong>{s_button:takechange}</strong> &#9658; {e_page}: {e_arealang}" style="{e_display-directchange}">
                    <span class="cc-admin-icons cc-icons cc-icon-checkmark">&nbsp;</span>
					</button>
                    <button type="button" class="cc-button button-icon-only feEditButton fe-changes cancel button-icon-only button-small" data-action="cancel" data-menuitem="true" data-id="item-id-{e_connr}" title="<strong>{s_javascript:feeditcancel}</strong> &#9658; {e_page}: {e_arealang}" style="{e_display-directchange}">
                    <span class="cc-admin-icons cc-icons cc-icon-blocked">&nbsp;</span>
					</button>
					<button type="button" class="cc-button button-icon-only movecon" title="{s_title:move}<br />({e_area}#{e_connr} &#9658; {e_contype}{e_subtype})">
					<span class="cc-admin-icons cc-icons cc-icon-arrows">&nbsp;</span>				
					</button>
                    <input type="hidden" name="script" value="{system_root}/access/editElements.php?page=admin&action=fe-changes&area={e_conarea}&connr={e_connr}&id={e_id}&lang={currlang}&param=" />
                    <br class="clearfloat" />
                </div>
				<div id="editDetails-{e_area}{e_connr}" data-eeid="ee-area-{e_area}-conID-{e_connr}" class="editDetailsBox adminArea">{e_editdetails}</div>
                <div id="pageID-{e_id}-area-{e_area}-conID-{e_connr}" class="editContent" data-pageid="{e_id}" data-pagearea="{e_area}" data-connum="{e_connr}">
                    {e_textbegin}
                    {dbcontents}
                    {e_textend}
                </div>
            </div>
        </div>
	{e_conDef-close}