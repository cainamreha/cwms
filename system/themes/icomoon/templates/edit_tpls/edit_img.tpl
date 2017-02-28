                    <div id="mediaList-img-{e_area}{e_connr}" class="cc-fe-medialist-specific cc-fe-box cc-contype-img cc-row fullBox mediaList images" style="{e_display-img}" data-type="images" data-fe="true">
						<button class="closeDetailsBox btnDetailsBox cc-button close button-icon-only" title="{s_title:close}"><span class="cc-admin-icons cc-icons cc-icon-cancel-circle"></span></button>
						<h4 class="cc-contype-heading cc-h4">[{e_contype}] {e_conDef-0}</h4>
						<div class="previewImgDiv">
							{e_previewImage}
						</div>
						<span class="showListBox" data-type="images" data-url="{system_root}/access/listMedia.php?page=admin&lang={currlang}&nodialog=1&type=images">
							<button type="button" class="cc-button button-icon-only button feEditButton openList" value="{s_button:imgfolder}">
								<span class="cc-admin-icons cc-icons cc-icon-image">&nbsp;</span>
								{s_button:imgfolder}
							</button>
							<input type="hidden" name="script" value="{system_root}/access/editElements.php?page=admin&action=fe-edit&area={e_conarea}&connr={e_connr}&id={e_id}&type=img&lang={currlang}" />
						</span>
						<button type="button" class="cc-button button showListBox keepListBox openFilemanager button-icon-only button-small right" value="" title="Filemanager" data-url="{system_root}/access/listMedia.php?page=admin&amp;action=elfinder&amp;root=" data-type="filemanager">
							<span class="cc-admin-icons cc-icons cc-icon-filemanager openList filemanager" aria-hidden="true">&nbsp;</span>
						</button>
                        <br class="clearfloat" />
                        <div class="cc-columns cc-col-6 leftBox subBox">
							{e_uploader}
							<label for="imgclass-{e_area}{e_connr}">{s_label:imgclass}</label>
							<select name="imgclass" class="imgclass" data-imgclass="{e_imgclass}">
								<option value="">aus</option>
								<option value="f">Image with frame</option>
								<option value="nf">Image without frame</option>
								<option value="r">Rounded image</option>
								<option value="rf">Rounded image with frame</option>
								<option value="c">Circular image</option>
								<option value="cf">Circular image with frame</option>
							</select>
                        </div>
                        <div class="cc-columns cc-col-6 rightBox subBox">
							<label for="imgalt-{e_area}{e_connr}">{s_label:alttag} (alt-tag)</label>
							<input type="text" name="imgalt" id="imgalt-{e_area}{e_connr}" class="imgalt" value="" />
							<label for="imgtitle-{e_area}{e_connr}">{s_label:titletag} (title-tag)</label>
							<input type="text" name="imgtitle" id="imgtitle-{e_area}{e_connr}" class="imgtitle" value="" />
							<label for="imgcap-{e_area}{e_connr}">{s_label:caption}</label>
							<input type="text" name="imgcap" id="imgcap-{e_area}{e_connr}" class="imgcap" value="" />
							<label for="imglink-{e_area}{e_connr}">Link</label>
							<input type="text" name="imglink" id="imglink-{e_area}{e_connr}" class="imglink" value="" />
							<label>Extra</label>
							<div class="fieldBox clearfix">
							<label for="extra-default-{e_area}{e_connr}"><input type="radio" checked="checked" id="extra-default-{e_area}{e_connr}" class="imgextra" name="imgextra" value="0">{s_common:non}</label>
							<label for="extra-enlarge-{e_area}{e_connr}"><input type="radio" id="extra-enlarge-{e_area}{e_connr}" class="imgextra" name="imgextra" value="1">Enlargable</label>
							<label for="extra-zoom-{e_area}{e_connr}"><input type="radio" id="extra-zoom-{e_area}{e_connr}" class="imgextra" name="imgextra" value="2">Zoom</label>
							</div>
                        </div>
                        <br class="clearfloat" /><br />
						<label class="markBox" style="{e_display-alllangs}">
							<input type="checkbox" name="alllangs" id="alllangs-img-{e_area}{e_connr}" class="alllangs" style="{e_display-alllangs}" />
						</label>
						<label for="alllangs-img-{e_area}{e_connr}" style="{e_display-alllangs}" class="inline-label">{s_label:takechange2}</label>
                        <br class="clearfloat" />
						<div class="feButtonPanel button-panel">
							<button type="submit" class="cc-button button-icon-only button feEditButton submit image" name="feedit_img" value="{s_button:savechanges}">
								<span class="cc-admin-icons cc-icons cc-icon-ok">&nbsp;</span>
								{s_button:savechanges}
							</button>
						</div>
                    </div>