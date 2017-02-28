#jZoom

A tiny and easy-using jQuery plugin for zoomimg images

##Index

* [Quick start](#quick-start)
* [Options](#options)
* [Attentions](#attentions)
* [License](#license)

###中文说明
* [使用说明](#使用说明)
* [配置选项](#配置选项)
* [注意事项](#注意事项)
* [开源协议](#开源协议)

##Quick start

###Install by npm or bower

```bash
$ npm install jzoom
```

```bash
$ bower install jzoom
```

###Include files

```html
<script src="jquery.min.js"></script>
<script src="jzoom.js"></script>
```

Call the plugin on an image's container as simple as

```javascript
$('#container').jzoom();
```

All done !

##Options

Custom the plugin with available options which are listed below.

```javascript
$('#container').jzoom({
    option: value,
    option2: value2,
    ...
});
```

| Option | Type | Default | Description |
|:---:|:---:|:---:|:---:|
| width | Number | 400 | The width of  zooming window. |
| height | Number | 400 | The height of  zooming window. |
| position | String | "right" | The relative positon to the zoomed image or container. "top", "bottom" and "left" are permitted.  |
| offsetX | Number | 20 | The deviation on X-asix, can't be negative. |
| offsetY | Number | 0 | The deviation on Y-asix, can't be negative. |
| opacity | Number | 0.6 | The opacity of lens div. |
| bgColor | String | "#fff" | The background color of lens div. |
| loading | String | "Loading..." | The loading text on zooming window when  big image is loading. |
| suffixName | String | "_big" | The suffix name of big image. |
| imgType | String | the zoomed image type | The type of big image. The default option is recommended. |

##Attentions

1. No CSS needed.

2. The container will be added `position: relative` if it's not positioned.

3. For getting best effect, the big image and the zoomed image should be requested:
    * filenames: If the filename of the zoomed image is "image", then the big image should be "image + suffix name".
    * types: The same types were recommended because some bugs may appear on IE.
    * In same folder.

##License

[MIT License](https://opensource.org/licenses/MIT)

--------

###使用说明

###通过 npm 或 bower 安装

```bash
$ npm install jzoom
```

```bash
$ bower install jzoom
```

###引入文件

```html
<script src="jquery.min.js"></script>
<script src="jzoom.js"></script>
```

然后在想要放大的图片的容器上调用插件，

```javascript
$('#container').jzoom();
```

即可实现默认效果。

###配置选项

自定义插件时，需要在方法中传入一个对象，可配置的选项列于下表。

```javascript
$('#container').jzoom({
    option: value,
    option2: value2,
    ...
});
```

| 选项 | 类型 | 默认值 | 描述 |
|:---:|:---:|:---:|:---:|
| width | Number | 400 | 放大镜窗口的宽度 |
| height | Number | 400 | 放大镜窗口的高度 |
| position | String | "right" | 放大镜窗口相对于图片或容器的位置，还可设置为"top", "bottom", "left" |
| offsetX | Number | 20 | 放大镜窗口在水平方向上的偏移，不能为负 |
| offsetY | Number | 0 | 放大镜窗口在水平方向上的偏移，不能为负 |
| opacity | Number | 0.6 | 镜头div的透明度 |
| bgColor | String | "#fff" | 镜头div的背景色 |
| loading | String | "Loading..." | 加载图片时显示的文字，水平垂直居中 |
| suffixName | String | "_big" | 大图后缀名 |
| imgType | String | 原图格式 | 大图的格式，建议与原图相同 |

###注意事项

1. CSS部分代码已完全集成到JS代码中。

2. 如果图片容器没有进行定位，则会默认添加`position: relative`属性。

3. 大图与原图之间需要有一定的对应关系：
    * 名称：如原图的文件名为image，则大图的文件名需为“image + 后缀名” 。
    * 格式：大图与原图的格式建议相同，因为在IE下有时会出现bug。
    * 位于同一目录下。

##开源协议

基于 [MIT 协议](https://opensource.org/licenses/MIT)。
