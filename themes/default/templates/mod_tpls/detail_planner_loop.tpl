		<article>
			<header>
				<time datetime="{datetime}" class="dataDate">
					{dataDate} <span class="pastDateHint {t_class:labelwarn}">{plannerPast}</span>
				</time>
				<div class="dataNavShort">
					{prevDataShort}
					{nextDataShort}
					{backDataShort}
				</div>
				<h1 class="dataHeader {t_class:paneltitle}">{dataHeader}</h1>
				{dataEditButtons}
			</header>
			{objOutput_img}
			<div class="dataTeaser">{dataTeaser}</div>
			<div class="dataText">{dataText}</div>
			<div class="dataObjects">{objOutput}</div>
			<footer>
				{dataTags}
				{rating}
				{comments}
				<p class="likeLabel">Seite empfehlen:</p>
				{like}
			</footer>
			<br class="clearfloat" />
		</article>