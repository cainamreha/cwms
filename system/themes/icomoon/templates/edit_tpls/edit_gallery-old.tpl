                    <div id="mediaList-gallery-{e_area}{e_connr}" class="row cc-row mediaList gallery" style="{e_display-gall}" data-type="gallery" data-fe="true">
						<button class="closeDetailsBox btnDetailsBox cc-button close button-icon-only" title="{s_title:close}"><span class="cc-admin-icons cc-icons cc-icon-cancel-circle"></span></button>
						<h4 class="cc-h4"{e_hideempty}>{e_conDef-0}</h4>
						<div class="fullBox">
							<div class="halfBox"{e_hideempty}>
								<span class="showListBox openList" title="{s_title:extendgall}" data-type="gallery" data-url="{root}/system/access/listMedia.php?page=admin&amp;action=edit&amp;type=gallery&amp;gal={e_conDef-0}">
								<button type="button" class="cc-button button-icon-only button openList" value="{s_title:extendgall}">
									<span class="cc-admin-icons cc-icons cc-icon-gallery">&nbsp;</span>
									{s_title:extendgall}
								</button>
								</span>
							</div>
							<div class="halfBox">
								<a class="cc-button button-icon-only button feEditButton new" href="{admin_root}?task=modules&type=gallery&name=" title="{s_title:goto} &#9658; {s_nav:admingallery}">
									<span class="cc-admin-icons cc-icons cc-icon-gallery">&nbsp;</span>
									{s_link:newgall}
								</a>
							</div>
                        </div>
                        <br class="clearfloat" />
						<hr />
						<div class="fullBox">
							<div class="subBox halfBox">
								<label>{s_label:galltype}</label>
								{e_galltypes}
								<input type="hidden" name="oldGallType" class="oldGallType" value="{e_conDef-1}" />
							</div>
							<div class="subBox halfBox">
								<label>{s_button:gallchoose}</label>
								<span class="showListBox" data-url="{system_root}/access/listPages.php?page=admin&type=gallery&lang={currlang}&nodialog=1">
									<button type="button" class="cc-button button-icon-only button feEditButton openList" value="{s_button:gallchoose}">
										<span class="cc-admin-icons cc-icons cc-icon-gallery">&nbsp;</span>
										{s_button:gallchoose}
									</button>
								</span>
							</div>
						</div>
						<br class="clearfloat" /><br />
						<label class="markBox" style="{e_display-alllangs}">
                            <input type="checkbox" name="alllangs" id="alllangs-gall-{e_area}{e_connr}" class="alllangs" style="{e_display-alllangs}" />
                        </label>
                        <label for="alllangs-gall-{e_area}{e_connr}" style="{e_display-alllangs}" class="inline-label">{s_label:takechange2}</label>
                        <br class="clearfloat" />
						<div class="feButtonPanel button-panel">
							<button type="submit" class="cc-button button-icon-only button feEditButton submit gallery" name="feedit_gallery" value="{s_button:savechanges}">
								<span class="cc-admin-icons cc-icons cc-icon-ok">&nbsp;</span>
								{s_button:savechanges}
							</button>
						</div>
						<input type="hidden" name="script" value="{system_root}/access/editElements.php?page=admin&action=fe-edit&area={e_conarea}&connr={e_connr}&id={e_id}&type=gall&lang={currlang}" />
					</div>