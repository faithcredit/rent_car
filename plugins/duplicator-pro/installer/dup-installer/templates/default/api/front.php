<?php

/**
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

 /**
 * Variables
  *
 * @var object[] $apiControllers
 * @var string $dupVersion
 */
?><!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="robots" content="noindex,nofollow">
        <title>WordPress Duplicator</title>
        <style>
            div#content {width:950px !important}
            div#api-area {margin:auto; line-height:21px }
            div#api-area table {width:100%}
            div#api-area table td:first-child{width:40%; padding-right:15px}
            div#api-area table td{vertical-align:top; text-align:left}
            iframe#api-results {margin:auto; width:97%; height:90%; border:1px solid silver; min-height: 500px}
            div.api-details {font-size:11px}
            form.api-form {display:none; padding-left:20px}
            form.api-form input[type=text] {width:100%; font-size:12px; padding:3px}
            input#api-results-txt {width:96% !important; background: #efefef;}
            div#api-area {padding:15px}
            div.api-area a.operation {font-size:20px; text-decoration: none !important;}
            div.api-area pre {font-size:11px; line-height: 13px; padding: 2px; border:1px solid silver; background: #efefef; border-radius: 3px}
        </style>    
        <?php
        require_once(DUPX_INIT . "/assets/inc.libs.css.php");
        require_once(DUPX_INIT . "/assets/inc.css.php");
        ?>
        <script src="<?php echo DUPX_INIT_URL; ?>/assets/inc.libs.js?v=<?php echo $dupVersion; ?>"></script>
        <?php
        require_once(DUPX_INIT . "/assets/inc.js.php");
        ?>
        <script>
            function RequestAPI(template, test) {
                var url = window.location.href;
                url = url + template;
                if (test == 0) {
                    $('#api-results-txt').val(url);
                    $('#api-results').attr('src', url);
                } else {
                    window.open(url, 'api-window');
                }
            }
        </script>
    </head>
    <body>

        <div id="content">

            <table cellspacing="0" class="header-wizard">
                <tr>
                    <td style="width:100%;">
                        <div style="font-size:22px; padding:5px 0px 0px 0px">&nbsp; Duplicator Pro - Installer - API</div>
                    </td>
                    <td style="white-space:nowrap; text-align:right"></td>
                </tr>
                <tr>
                    <td><div style="margin:4px 0px 10px 15px;"></div></td>
                    <td class="wiz-dupx-version">version:   <?php echo $dupVersion ?> </td>
                </tr>
            </table>    

            <div id="api-area">
                <div class="hdr-main">
                    API ROUTES:
                    <!--div style="float:right; font-size:12px">
                        <input type="checkbox" name="api-debug" id="api-debug">
                        <label for="api-debug">Debug Routes</label>
                    </div-->
                </div> 
                <div class="api-area">
                    <table>
                        <tr>
                            <td>
                                <b>OPERATIONS:</b>
                                <?php foreach ($apiControllers as $class) : ?>
                                    <div style="padding: 5px 0 5px 0;">
                                        <?php
                                        $id   = uniqid();
                                        $name = str_replace('/cpnl/', 'cpnl/', $class->operation);
                                        ?>
                                        <a 
                                            href="javascript:void(0)" 
                                            onclick="$('#frm-<?php echo DUPX_U::esc_js($id); ?>').toggle()" 
                                            class="operation"
                                        >
                                            &#xbb;<?php echo DUPX_U::esc_html($name); ?>
                                        </a><br/>

                                        <form id="frm-<?php echo $id ?>" class="api-form" autocomplete="off" >
                                            <input 
                                                id="txt-<?php echo DUPX_U::esc_attr($id); ?>" 
                                                type="text" 
                                                value="<?php echo DUPX_U::esc_attr($class->template); ?>" ><br/>
                                            <a 
                                                href="javascript:void(0)" 
                                                onclick="RequestAPI($('#txt-<?php echo DUPX_U::esc_js($id); ?>').val(), 0)"
                                            >
                                                [Test]
                                            </a> &nbsp;
                                            <a 
                                                href="javascript:void(0)" 
                                                onclick="RequestAPI($('#txt-<?php echo DUPX_U::esc_js($id); ?>').val(), 1)"
                                            >
                                                [New Window]
                                            </a> &nbsp;
                                            <div class="api-details" id="details-<?php echo DUPX_U::esc_attr($id); ?>">
                                                <?php DUPX_U::dump($class, true); ?>
                                            </div>
                                        </form>
                                    </div>
                                <?php endforeach; ?>                    
                            </td>
                            <td>
                                <b>TEST RESULTS:</b> <br/>
                                <input id="api-results-txt" type="text" readonly="true" /> <br/>
                                <iframe id="api-results" />
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
