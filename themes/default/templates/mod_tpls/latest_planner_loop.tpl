    	<article class="listEntry {altClass}">
			<div class="{t_class:panel}">
				<header class="{t_class:panelhead}">
					{dataLink}
						<time datetime="{datetime}" class="dataDate {t_class:right}">
							<span class="dataDay">{dataDay}</span><span class="dataMonth">{dataMonth}</span><span class="dataYear">{dataYear}</span><span class="pastDateHint {t_class:labelwarn}">{plannerPast}</span>
						</time>
						<h1 class="dataHeader {t_class:paneltitle}">{dataHeader}</h1>
					</a>
				</header>
				<div class="{t_class:panelbody}">
					<div class="dataTeaser">{dataTeaser}</div>
					{teaserImg}
					{dataLink}
						<span class="readMoreSpan {t_class:right}">
							<span class="readMore {t_class:btn} {t_class:btndef}">{more}</span>
						</span>
					</a>
				</div>
				<footer class="{t_class:panelfoot}">
					<div class="{t_class:row}">
						{rating}
						{comments}
					</div>
				</footer>
			</div>
        </article>