                    <div id="mediaList-html-{e_area}{e_connr}" class="row cc-row mediaList html" style="{e_display-html}" data-type="html" data-fe="true">
						<button class="closeDetailsBox btnDetailsBox cc-button close button-icon-only" title="{s_title:close}"><span class="cc-admin-icons cc-icons cc-icon-cancel-circle"></span></button>
						<h4 class="cc-h4">[{e_contype}]</h4>
	                    <form action="{system_root}/access/editElements.php?page=admin&action=fe-edit&area={e_conarea}&connr={e_connr}&id={e_id}&type=html&lang={currlang}&red={currpage}" method="post">
                        <label for="html-{e_area}{e_connr}">HTML</label>
                        <textarea name="htmlContent" id="html-{e_area}{e_connr}" class="htmlTextBox code" rows="15" cols="50">{e_htmlContent}</textarea>
                        <br class="clearfloat" /><br />
						<label class="markBox" style="{e_display-alllangs}">
                            <input type="checkbox" name="alllangs" id="alllangs-html-{e_area}{e_connr}" class="alllangs" style="{e_display-alllangs}" />
                        </label>
                        <label for="alllangs-html-{e_area}{e_connr}" style="{e_display-alllangs}" class="inline-label">{s_label:takechange2}</label>
                        <br class="clearfloat" />
						<div class="feButtonPanel button-panel">
							<button type="submit" class="cc-button button-icon-only button feEditButton submit html" name="feedit_html" value="{s_button:savechanges}">
								<span class="cc-admin-icons cc-icons cc-icon-ok">&nbsp;</span>
								{s_button:savechanges}
							</button>
						</div>
                        </form>
					</div>