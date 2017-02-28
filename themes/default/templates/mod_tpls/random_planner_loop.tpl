    	<article class="listEntry {altClass}">
            <header>
				{dataLink}
					<time datetime="{datetime}" class="dataDate">
						<span class="dataDay">{dataDay}</span><span class="dataMonth">{dataMonth}</span><span class="dataYear">{dataYear}</span> <span class="pastDateHint {t_class:labelwarn}">{plannerPast}</span>
					</time>
             	</a>
				{dataLink}
        	    	<h2 class="dataHeader {t_class:paneltitle}">{dataHeader}</h2>
            	</a>
            </header>
            <div class="dataTeaser">{dataTeaser}</div>
            {teaserImg}
            <p class="clearfloat">&nbsp;</p>
            <footer>
	            {dataLink}
    		        <span class="more">{more}</span>
            	</a>
            </footer>
        </article>