    	<article class="listEntry {altClass}">
			<div class="{t_class:threequaters} {t_class:alpha}">
				<header>
					{dataLink}
						<time datetime="{datetime}" class="dataDate">
							<span class="dataDay">{dataDay}</span><span class="dataMonth">{dataMonth}</span><span class="dataYear">{dataYear}</span> <span class="pastDateHint {t_class:labelwarn}">{plannerPast}</span>
						</time>
						<h1 class="dataHeader {t_class:paneltitle}">{dataHeader}</h1>
					</a>
				</header>
				<div class="dataTeaser">{dataTeaser}</div>
			</div>
			<div class="{t_class:quaterrow} {t_class:omega}">
                {dataLink}
                    <span class="readMoreSpan {t_class:right}">
						<span class="readMore {t_class:btn} {t_class:btndef}">{more}</span>
					</span>
                </a>
			</div>
            <footer>
				<div class="{t_class:row}">
					{dataOrder}
					{rating}
					{comments}
				</div>
            </footer>
        </article>