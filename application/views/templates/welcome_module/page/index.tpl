{%extends file="common_module/page/layout.tpl"%}

{%block name="css_block"%}
{%require name="welcome:welcome_module/static/css/style.css"%}
{%/block%}

{%block name="content_block"%}
<!-- Preloader -->

<div id="preloader">
    <div id="status"></div>
</div>

<!-- Home start -->

<section id="home" class="pfblock-image screen-height">
    <div class="home-overlay"></div>
    <div class="intro">
        <h1>Welcome to Spir</h1>
        <div class="start">A PHP7.x MVCS Framework (By {%$data['authorName']%})</div>
    </div>
</section>

<!-- Home end -->



{%/block%}

{%block name="js_block"%}
{%require name="welcome:welcome_module/static/js/modernizr.custom.js"%}
{%/block%}