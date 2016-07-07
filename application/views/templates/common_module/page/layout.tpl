<!DOCTYPE html>
{%html framework="welcome:welcome_module/common_module/static/js/mod.js"%}
{%head%}
<meta charset="utf-8" />
<title>{%$data['title']%}</title>
<meta name="renderer" content="webkit">
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<style>
    html, body {
        width: 100%;
        height: 100%;
        font-family: "Helvetica Neue", Helvetica, Arial, "Microsoft YaHei", sans-serif;
    }
</style>

{%require name="welcome:common_module/static/bootstrap/css/bootstrap.min.css"%}
{%require name="welcome:common_module/static/css/font-awesome.min.css"%}
{%require name="welcome:common_module/static/css/simple-line-icons.css"%}
{%require name="welcome:common_module/static/css/animate.css"%}

{%block name="css_block"%}{%/block%}

<!-- Javascript files -->
{%require name="welcome:common_module/static/js/jquery-1.11.1.min.js"%}
{%require name="welcome:common_module/static/bootstrap/js/bootstrap.min.js"%}
{%require name="welcome:common_module/static/js/jquery.parallax-1.1.3.js"%}
{%require name="welcome:common_module/static/js/imagesloaded.pkgd.js"%}
{%require name="welcome:common_module/static/js/jquery.sticky.js"%}
{%require name="welcome:common_module/static/js/smoothscroll.js"%}
{%require name="welcome:common_module/static/js/wow.min.js"%}
{%require name="welcome:common_module/static/js/jquery.easypiechart.js"%}
{%require name="welcome:common_module/static/js/waypoints.min.js"%}
{%require name="welcome:common_module/static/js/jquery.cbpQTRotator.js"%}
{%require name="welcome:common_module/static/js/custom.js"%}
{%/head%}

{%body%}
{%block name="content_block"%}{%/block%}
{%block name="js_block"%}{%/block%}
{%/body%}
{%/html%}