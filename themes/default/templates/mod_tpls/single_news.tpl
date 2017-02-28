<article class="{t_class:row}">
	<div class="dataDetail{class}">
    	<header class="dataHeaderBox {t_class:fullrow}">
			<span class="date {t_class:right}">
				<time datetime="{datetime}" class="dataDate {t_class:labeldef}">
					{dataDate}
				</time>
			</span>
			<div class="dataNavShort">
				{prevDataShort}
				{nextDataShort}
				{backDataShort}
			</div>
			<h1 class="dataHeader">{dataHeader}</h1>
            {dataEditButtons}
		</header>
		<div class="dataMainContentBox {t_class:fullrow}">
			<div class="{t_class:row}">
				<div class="col-md-6 teaserImgBox {t_class:centertxt} {t_class:right} {t_class:marginbm}">{objOutput_img}</div>
				<div class="col-md-6 dataTeaser">{dataTeaser}</div>
				<div class="col-md-6 dataText">{dataText}</div>
				<div class="dataObjects">{objOutput}</div>
			</div>
		</div>
        <footer class="{t_class:fullrow} {t_class:margintm} {t_class:marginbm}">
			<div class="{t_class:fullrow} {t_class:well}">
				<div class="{t_class:halfrow}">
					{dataTags}
				</div>
				<div class="{t_class:halfrow}">
					<span class="likeLabel {t_class:left}">Seite empfehlen:&nbsp;</span>
					{like}
				</div>
				{rating}
			</div>
			<div class="{t_class:fullrow} {t_class:well}">
				{comments}
			</div>
            {adblock}
        </footer>
	</div>
</article>
<hr />
<div class="dataNav {t_class:margintm} {t_class:marginbm}" role="navigation">
  	{nextData}
  	{prevData}
	{backData}
</div>