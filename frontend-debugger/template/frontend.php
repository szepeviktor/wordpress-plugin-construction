<?php
/*
Template Name: Frontend Debugger Template
Version: 1.0
Description: Pseudo template for Frontend Debugger
*/

if ( ! function_exists( 'add_filter' ) ) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit();
}

$fd = Frontend_Debugger::get_instance();
$fd->run_template();

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" />
<meta name="generator" content="WordPress Frontend Debugger plugin" />
<title>source: <?php wp_title(); ?></title>

<link href="//cdnjs.cloudflare.com/ajax/libs/normalize/3.0.2/normalize.min.css" type="text/css" rel="stylesheet" />
<!-- http://google-code-prettify.googlecode.com/svn/trunk/README.html -->
<link href="//google-code-prettify.googlecode.com/svn/trunk/styles/desert.css" type="text/css" rel="stylesheet" />
<style type="text/css">
    html { background-color:#fff; color:#000; }
    body { min-width: 580px; }
    h1, #control-panel .part { color: #333333; padding-left: 10px; }
    #control-panel .part { font-size: 2em; font-weight: bold; cursor: pointer; text-decoration: none; }
    #control-panel .part:hover { color: black; }
    pre { padding: 2px; border: 1px solid #888888; color: #BDB76B; white-space: pre-wrap;
        tab-size: 4; -moz-tab-size: 4; }
    pre.no-wrap { white-space: pre; }
    pre span::selection { color: #333333; background: #F0E68C; }
    pre span::-moz-selection { color: #333333; background: #F0E68C; }
    ol.linenums { padding-left: 48px; line-height: 1.2em; }
    .transition ol.linenums { transition: padding 1s; }
    ol.hide-linenums { padding-left: 2px; }
    li.L0, li.L1, li.L2, li.L3, li.L4, li.L5, li.L6, li.L7, li.L8, li.L9 { background: inherit !important;
        list-style-type: decimal !important; }
    ol.hide-linenums li.L0,
    ol.hide-linenums li.L1,
    ol.hide-linenums li.L2,
    ol.hide-linenums li.L3,
    ol.hide-linenums li.L4,
    ol.hide-linenums li.L5,
    ol.hide-linenums li.L6,
    ol.hide-linenums li.L7,
    ol.hide-linenums li.L8,
    ol.hide-linenums li.L9 { list-style-type: none !important; }
    li:after { content: '\021B5'; } /* &crarr; */
    .transition li:after { transition: opacity 0.5s; }
    li.no-end:after { opacity: 0; }
    #includes li:after { content: none; }
    #control-panel { position: fixed; top: 5px; left: 300px; padding: 6px 1px; min-width: 235px;
        background: rgba(128,128,128,0.50); border: 2px solid gray; box-shadow: 0 0 2px gray; border-radius: 5px; }
    /* http://flatuicolors.com/ */
    #control-panel button { background: #27ae60; margin: 0 5px; opacity: 0.70; }
    #control-panel button.on { background: #e74c3c; }
</style>

<script async src="//google-code-prettify.googlecode.com/svn/loader/run_prettify.js" type="text/javascript"></script>
<script type="text/javascript">
    function supports_html5_storage() {
        try {
            return 'localStorage' in window && window['localStorage'] !== null;
        } catch (e) {
            return false;
        }
    }
    window.onload = function () {
        var i, linenums, wrap, lineends,
            buttonLinenums, buttonWrap, ButtonLineends;

        if (!supports_html5_storage()) {
            alert('Please upgrade your browser.');
            return;
        }

        function getValue(name) {
            var value;

            value = localStorage.getItem(name);
            if (value === null) {
                return true;
            } else {
                return JSON.parse(value);
            }
        }

        function setValue(name, value) {
            if (typeof value === 'undefined') {
                value = true;
            }
            localStorage.setItem(name, JSON.stringify(value));
        }

        function toggleLinenums(target) {
            var parts = document.getElementsByClassName('linenums');

            target.classList.toggle('on');
            for (i = 0; i < parts.length; i++) {
                parts[i].classList.toggle('hide-linenums');
            }
        }

        function toggleWrap(target) {
            var codes = document.getElementsByTagName('pre');

            target.classList.toggle('on');
            for (i = 0; i < codes.length; i++) {
                codes[i].classList.toggle('no-wrap');
            }
        }

        function toggleLineends(target) {
            var listElements = document.getElementsByTagName('li');

            target.classList.toggle('on');
            for (i = 0; i < listElements.length; i++) {
                listElements[i].classList.toggle('no-end');
            }
        }

        // line numbers
        buttonLinenums = document.getElementById('toggle-linenums');
        linenums = getValue('linenums');
        if (! linenums) {
            toggleLinenums(buttonLinenums);
        }
        buttonLinenums.addEventListener('click', function (event) {
            linenums = !linenums;
            setValue('linenums', linenums);
            toggleLinenums(event.target);
        });

        // wrap long lines
        buttonWrap = document.getElementById('toggle-wrap');
        wrap = getValue('wrap');
        if (! wrap) {
            toggleWrap(buttonWrap);
        }
        buttonWrap.addEventListener('click', function (event) {
            wrap = !wrap;
            setValue('wrap', wrap);
            toggleWrap(event.target);
        });

        // line ends
        ButtonLineends = document.getElementById('toggle-lineends');
        lineends = getValue('lineends');
        if (! lineends) {
            toggleLineends(ButtonLineends);
        }
        ButtonLineends.addEventListener('click', function (event) {
            lineends = !lineends;
            setValue('lineends', lineends);
            toggleLineends(event.target);
        });

        // enable transitions
        setTimeout(function () {
            document.documentElement.classList.add('transition');
        }, 10);

    }
</script>
</head>
<body>

<h1>Header</h1>
<pre id="header-html" class="prettyprint linenums lang-html">
<?php

print $fd->part['header'];

?>
</pre>

<h1>Thumbnails</h1>
<pre id="thumbnail-html" class="prettyprint linenums lang-html">
<?php

print $fd->part['thumbnails'];

?>
</pre>

<h1>The Loop</h1>
<pre id="loop-html" class="prettyprint linenums lang-html">
<?php

print $fd->part['content'];;

?>
</pre>

<h1>Footer</h1>
<pre id="footer-html" class="prettyprint linenums lang-html">
<?php

print $fd->part['footer'];;

?>
</pre>

<h1>Included files</h1>
<pre id="includes" class="prettyprint lang-php">
<?php

var_export( $fd->part['includes'] );

?>
</pre>

<div id="control-panel">
    <a href="#header-html" class="part" title="Header">H</a>
    <a href="#thumbnail-html" class="part" title="Thumbnails">T</a>
    <a href="#loop-html" class="part" title="The Loop">L</a>
    <a href="#footer-html" class="part" title="Footer">F</a>
    <button id="toggle-linenums" title="Toogle line numbers">Line #</button>
    <button id="toggle-wrap" title="Toggle long line wrapping">Wrap</button>
    <button id="toggle-lineends" title="Toggle visible line ends">Line ends</button>
</div>
</body>
</html>