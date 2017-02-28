		<div id="header">
			<div id="topBox">
				<div id="topHeader">
					<div id="loggedUser">
						<a href="{admin_root}" class="cc-topbar-button cc-button button btn loggedUser-button" data-ajax="true" title="{s_title:admin}">
							<span class="cc-topbar-icon cc-admin-icons cc-icon-meter loggedUser-icon">&nbsp;</span>
							<span class="loggedUser-header">{s_header:admin}</span>
							<span class="loggedUser-name">{loggeduser}</span>
						</a>
						<a href="{admin_root}?logout=true" class="cc-topbar-button cc-button button btn button-icon-only logoutUser-button" title="{s_title:logout}">
							<span class="cc-topbar-icon cc-admin-icons cc-icon-unlocked logoutUser-icon">&nbsp;</span>
						</a>
					</div>
					<button type="button" class="cc-topbar-button cc-button button btn button-icon-only cc-menu-toggle cc-action-togglesidebar" data-target="left" title="{s_title:toggle}">
						<span class="cc-topbar-icon cc-admin-icons cc-icon-menu-left cc-menu-toggle-icon">&nbsp;</span>
					</button>
					{preview}
				</div><!-- end #topHeader -->
			</div><!-- end #topBox -->
			{HEAD}
		</div><!-- end #header -->
		<div id="mainContent" class="{maincon_class}">
			{open_headerbox}
			<button type="button" class="cc-button button btn button-icon-only cc-bar-toggle cc-action-togglesidebar right" data-target="right" title="{s_title:toggle}">
				<span class="cc-admin-icons cc-icon-menu-right cc-menu-toggle-icon">&nbsp;</span>
			</button>
			{MAIN}
			<p class="clearfloat">&nbsp;</p>
			<p class="up lastUpLink cc-button button"><a href="#top">&#9650; {s_link:up}</a></p>
		</div>
		<div id="left" class="{leftbar_class}">
			<div id="logoDiv">
				<a href="{admin_root}" data-ajax="true" title="{s_title:admin}">
					<img src="{admin_logo}" alt="logo Concise WMS" id="conciseLogo" />
				</a>
			</div>
			{admin_menu}
			{LEFT}
			<button type="button" class="cc-button button btn button-icon-only cc-bar-toggle cc-action-togglesidebar" data-target="left" title="{s_title:toggle}">
				<span class="cc-admin-icons cc-icon-cancel-circle cc-menu-toggle-icon">&nbsp;</span>
			</button>
		</div><!-- end #sidebar -->
		<div id="right" class="{rightbar_class}">
			{account}
			{lang_menu}
			<div id="menuBox">
				<div id="mainMenu" class="previewMenu" title="{s_title:pagepreview} &#9658; {s_header:mainmenu}">
				   {preview_menu}
				</div>
				<div id="topMenu" class="previewMenu" title="{s_title:pagepreview} &#9658; {s_header:topmenu}">
				   {top_menu}
				</div>
				<div id="footMenu" class="previewMenu" title="{s_title:pagepreview} &#9658; {s_header:footmenu}">
				   {foot_menu}
				</div>
				<div id="nonMenu" class="previewMenu" title="{s_title:pagepreview} &#9658; {s_header:nonmenu}">
				   {non_menu}
				</div>
			</div>
			{RIGHT}
			<button type="button" class="cc-button button btn button-icon-only cc-bar-toggle cc-action-togglesidebar" data-target="right" title="{s_title:toggle}">
				<span class="cc-admin-icons cc-icon-cancel-circle cc-menu-toggle-icon">&nbsp;</span>
			</button>
		</div><!-- end #right -->
		<div id="footer">
			<p>
				<span>concise wms &#x00BB;</span> Version {cwms_version}
				<a href="http://www.hermani-webrealisierung.de" target="_blank">hermani <strong>webrealisierung</strong></a>
			</p>
			{FOOT}
		</div><!-- end #footer -->