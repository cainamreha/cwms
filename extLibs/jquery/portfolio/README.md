<h2>jQuery-Portfolio</h2>

<p>This plugin helps you display and organize your portfolio pieces in a slick & <b>responsive</b> fashion. Easy to set up and get going. Check out the demo <a href="http://arsenetoumani.github.io/jquery-portfolio/demo/">here</a></p>

I originally wrote this plugin when redesigning my <a href="http://www.arsenetoumani.com">portfolio</a> and later decided to make it available for all!<br/>
Rebranding and updating our portfolios are no fun tasks for us devs, so I thought this plugin would facilitate the process.<br/>

<h2>Demo:</h2>

View the full demo <a href="http://arsenetoumani.github.io/jquery-portfolio/demo/">here</a>

<h2>Installation:</h2>

- Get both `/dist/portfolio.jquery.min.js` and `/dist/portfolio.jquery.min.css` files included in your html file
- Add `$("#MyPortfolio").portfolio({ options });` to your <i>script</i> tags or JS file
- Append and edit (to your liking) the HTML snippet you find in `/demo/index.html`
- That's it! really, you just have to follow what's going on in the `/demo` folder ;)

<h2>Options:</h2>

```javascript
$("#MyPortfolio").portfolio({
    cols: 3 // Number of columns you want your thumbnails to take. Default is 3
    transition: 'slideDown' // What jQuery transition effect you want. Default is slideDown
});
```
For more options, just request in the issue tracker.

<h2>Developers:</h2>

- Clone the repo onto your desktop
- run `npm install` to install all dependencies
- run `gulp` for CSS and JS minification

You really only need to work in the demo folder then run `gulp` later.
