<?php

/**
 * @package Duplicator\Installer
 */

namespace Duplicator\Installer\Bootstrap;

use Duplicator\Libs\DupArchive\DupArchive;

class BootstrapView
{
    const VIEW_ERROR    = 'error';
    const VIEW_REDIRECT = 'redirect';
    const VIEW_PASSWORD = 'pwd';

    /** @var BootstrapRunner */
    protected $boot = null;

    /**
     * Class contructor
     */
    public function __construct()
    {
        $this->boot = BootstrapRunner::getInstance();
    }

    /**
     * Redirect to instller
     *
     * @return void
     */
    public function redirectToInsaller()
    {
        $this->renderHeader();
        ?>
        <div style="text-align: center; margin: 150px 0; font-size: 20px;">
            <b>Initializing Installer. Please wait...</b>
        </div>
        <?php
        echo $this->boot->getRedirectForm();
        $this->renderFooter();
    }

    /**
     * Render password request page
     *
     * @return void
     */
    public function renderPassword()
    {
        $this->renderHeader();
        $errorMsg = $this->boot->getErrorMessage();
        if (strlen($errorMsg) > 0) {
            $this->renderTopMessages($errorMsg);
        }
        ?>
        <form id="archive_password_required" method="post" >
            <div id="pwd-check-fail" class="error-pane no-display">
                <p>
                    <?php echo $errorMsg; ?>
                </p>
            </div>

            <div id="header-main-wrapper">
                <div class="hdr-main">
                    Password Required
                </div>
            </div>

            <div class="margin-top-0 margin-bottom-2">
                <div id="pass-quick-help-info" class="box info">
                    This archive was created with an encryption enabled password.  Please provide the password to  extract the archive file.<br/>
                    <small>
                        Lost passwords for encrypted archives cannot be recovered by support.
                        If the password was lost then a new archive will need to be created.
                    </small>
                </div>
            </div>

            <div class="dupx-opts" >
                <div id="wrapper_item_secure-pass" class="param-wrapper param-form-type-pwdtoggle margin-bottom-2 has-main-label">
                    <label class="container">
                        <span class="label main-label">Password:</span>
                        <span class="input-container">
                            <span class="input-item input-password-group input-postfix-btn-group">
                                <input 
                                    value="" 
                                    type="password" 
                                    maxlength="150" 
                                    name="<?php echo BootstrapRunner::NAME_PWD; ?>" 
                                    id="param_item_secure-pass" 
                                    autocomplete="off"
                                >
                                <button type="button" class="postfix" title="Show the password">
                                    <?php $this->renderEyeFont(); ?>
                                    <?php $this->renderEyeSlashFont(); ?>
                                </button>
                            </span>
                        </span>
                    </label>
                </div>
            </div>

            <div class="footer-buttons" >
                <div class="content-center" >
                    <button type="submit" name="secure-btn" value="secure" id="secure-btn" class="default-btn" >
                        Submit
                    </button>
                </div>
            </div>
        </form>
        <script>
            var button = document.querySelector('#wrapper_item_secure-pass button.postfix');
            var inputPwd = document.querySelector('#param_item_secure-pass');
            var eye =  document.querySelector('#wrapper_item_secure-pass .icon_eye');
            var eye_slash =  document.querySelector('#wrapper_item_secure-pass .icon_eye_slash');
            inputPwd.focus();
            button.onclick = function changeContent() {
                if (inputPwd.getAttribute('type') === 'password') {
                    inputPwd.setAttribute("type", "text");
                    eye.classList.add('no-display');
                    eye_slash.classList.remove('no-display');
                } else {
                    inputPwd.setAttribute("type", "password");
                    eye.classList.remove('no-display');
                    eye_slash.classList.add('no-display');
                }
            }
        </script>
        <?php
        $this->renderFooter();
    }

    /**
     * Render message
     *
     * @param string $message message
     *
     * @return void
     */
    protected function renderTopMessages($message)
    {
        ?>
        <div id="page-top-messages">
            <div class="notice next-step l-critical">
                <?php $this->renderExclamationCircle(); ?>
                <div class="title">
                    <b><?php echo $message; ?></b>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render error page
     *
     * @return void
     */
    public function renderError()
    {
        $auto_refresh = isset($_POST['auto-fresh']) ? true : false;
        $this->renderHeader();
        ?>
        <h2 style="color:maroon">Setup Notice:</h2>
        <div class="errror-notice">An error has occurred. In order to load the full installer please resolve the issue below.</div>
        <div class="errror-msg">
            <?php echo $this->boot->getErrorMessage(); ?>
        </div>
        <br/><br/>

        <h2>Server Settings:</h2>
        <table class='settings'>
            <?php if ($this->boot->isZip()) { ?>
                <tr>
                    <td>ZipArchive:</td>
                    <td><?php echo BootstrapUtils::isPhpZipAvaiable() ? '<i class="pass">Enabled</i>' : '<i class="fail">Disabled</i>'; ?> </td>
                </tr>
                <tr>
                    <td>ShellExec&nbsp;Unzip:</td>
                    <td><?php echo BootstrapUtils::isShellZipAvailable() ? '<i class="pass">Enabled</i>' : '<i class="fail">Disabled</i>'; ?> </td>
                </tr>
            <?php } else { ?>
                <tr>
                    <td>PHP OpenSSL Module:</td>
                    <td><?php echo DupArchive::isEncryptionAvaliable() ? '<i class="pass">Enabled</i>' : '<i class="fail">Disabled</i>'; ?> </td>
                </tr>                
            <?php } ?>
            <tr>
                <td>Extraction&nbsp;Path:</td>
                <td><?php echo $this->boot->targetRoot; ?></td>
            </tr>
            <tr>
                <td>Installer Path:</td>
                <td><?php echo $this->boot->targetDupInstFolder; ?></td>
            </tr>
            <tr>
                <td>Archive Size:</td>
                <td>
                    <b>Expected Size:</b> <?php echo BootstrapUtils::readableByteSize($this->boot->archiveExpectedSize); ?>  &nbsp;
                    <b>Actual Size:</b>   <?php echo BootstrapUtils::readableByteSize($this->boot->archiveActualSize); ?>
                </td>
            </tr>
            <tr>
                <td>Boot Log</td>
                <td>
                    <a target='_blank' href="<?php echo basename($this->boot->getBootLogFilePath()); ?>" >
                        dup-installer-bootlog__[HASH].txt
                    </a>
                </td>
            </tr>
        </table>
        <br/><br/>
        <div style="font-size:11px">
            Please Note: ZipArchive or ShellExec Unzip will need to be enabled for the installer to
            run automatically otherwise a manual extraction
            will need to be performed. In order to run the installer manually follow the instructions to
            <a href='https://snapcreek.com/duplicator/docs/faqs-tech/#faq-installer-015-q' target='_blank'>
                manually extract
            </a> before running the installer.
        </div>
        <script>
            function AutoFresh() {
                document.getElementById('error-form').submit();
            }
        <?php if ($auto_refresh) : ?>
                var duration = 10000; //10 seconds
                var counter = 10;
                var countElement = document.getElementById('count-down');

                setTimeout(function () {
                    window.location.reload(1);
                }, duration);
                setInterval(function () {
                    counter--;
                    countElement.innerHTML = (counter > 0) ? counter.toString() : "0";
                }, 1000);

        <?php endif; ?>
        </script>
        <?php
        $this->renderFooter();
    }

    /**
     * Render page header
     *
     * @return void
     */
    protected function renderHeader()
    {
        ?><!DOCTYPE html>
        <html>
            <head>
                <meta name="robots" content="noindex,nofollow">
                <title>Duplicator Pro Installer</title>
                <link rel="icon" href="data:;base64,iVBORw0KGgo=">
                <?php $this->renderCSS(); ?>
            </head>
            <body>
                <div id="content">
                    <table cellspacing="0" class="header-wizard">
                        <tr>
                            <td style="width:100%;">
                                <div class="dupx-branding-header">
                                    <?php $this->renderBoltFont(); ?> Duplicator Pro
                                </div>
                            </td>
                            <td class="wiz-dupx-version">
                                version: <?php echo $this->boot->getVersion(); ?>
                                <div style="padding: 6px 0">
                                    <a target='_blank' href="<?php echo basename($this->boot->getBootLogFilePath()); ?>">
                                        dup-installer-bootlog__[HASH].txt
                                    </a>
                                </div>
                            </td>
                        </tr>
                    </table>
                    <div id="content-inner">
        <?php
    }

    /**
     * Render page footer
     *
     * @return void
     */
    protected function renderFooter()
    {
        ?>               
                    </div>
                </div>
            </body>
        <html>
        <?php
    }

    /**
     * Render bolt font
     *
     * @return void
     */
    protected function renderBoltFont()
    {
        ?>
        <svg class="icon_bolt" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" style="height: 23px;">
            <path d="M296 160H180.6l42.6-129.8C227.2 15 215.7 0 200 0H56C44 0 33.8 8.9 32.2 20.8l-32 240C-1.7 275.2 9.5 288 24 288h118.7L96.6 482.5c-3.6 15.2 8 29.5 23.3 29.5 8.4 0 16.4-4.4 20.8-12l176-304c9.3-15.9-2.2-36-20.7-36z"/><?php // phpcs:ignore ?>
        </svg>
        <?php
    }

    /**
     * Rend eye font
     *
     * @return void
     */
    protected function renderEyeFont()
    {
        ?>
        <svg class="icon_eye" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" style="height: 16px;">
            <path d="M572.52 241.4C518.29 135.59 410.93 64 288 64S57.68 135.64 3.48 241.41a32.35 32.35 0 0 0 0 29.19C57.71 376.41 165.07 448 288 448s230.32-71.64 284.52-177.41a32.35 32.35 0 0 0 0-29.19zM288 400a144 144 0 1 1 144-144 143.93 143.93 0 0 1-144 144zm0-240a95.31 95.31 0 0 0-25.31 3.79 47.85 47.85 0 0 1-66.9 66.9A95.78 95.78 0 1 0 288 160z"/> <?php // phpcs:ignore ?>
        </svg>
        <?php
    }

    /**
     * Rend eye slash font
     *
     * @return void
     */
    protected function renderEyeSlashFont()
    {
        ?>
        <svg class="icon_eye_slash no-display" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512" style="height: 16px;" >
            <path d="M320 400c-75.85 0-137.25-58.71-142.9-133.11L72.2 185.82c-13.79 17.3-26.48 35.59-36.72 55.59a32.35 32.35 0 0 0 0 29.19C89.71 376.41 197.07 448 320 448c26.91 0 52.87-4 77.89-10.46L346 397.39a144.13 144.13 0 0 1-26 2.61zm313.82 58.1l-110.55-85.44a331.25 331.25 0 0 0 81.25-102.07 32.35 32.35 0 0 0 0-29.19C550.29 135.59 442.93 64 320 64a308.15 308.15 0 0 0-147.32 37.7L45.46 3.37A16 16 0 0 0 23 6.18L3.37 31.45A16 16 0 0 0 6.18 53.9l588.36 454.73a16 16 0 0 0 22.46-2.81l19.64-25.27a16 16 0 0 0-2.82-22.45zm-183.72-142l-39.3-30.38A94.75 94.75 0 0 0 416 256a94.76 94.76 0 0 0-121.31-92.21A47.65 47.65 0 0 1 304 192a46.64 46.64 0 0 1-1.54 10l-73.61-56.89A142.31 142.31 0 0 1 320 112a143.92 143.92 0 0 1 144 144c0 21.63-5.29 41.79-13.9 60.11z"/> <?php // phpcs:ignore ?>
        </svg>
        <?php
    }


    /**
     * Rend exclamation circle
     *
     * @return void
     */
    protected function renderExclamationCircle()
    {
        ?>
        <div class="fas" >
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="icon_exclamation_circle" style="height: 16px; margin-top: 2px;" >
                <path fill="#FFFFFF" d="M504 256c0 136.997-111.043 248-248 248S8 392.997 8 256C8 119.083 119.043 8 256 8s248 111.083 248 248zm-248 50c-25.405 0-46 20.595-46 46s20.595 46 46 46 46-20.595 46-46-20.595-46-46-46zm-43.673-165.346l7.418 136c.347 6.364 5.609 11.346 11.982 11.346h48.546c6.373 0 11.635-4.982 11.982-11.346l7.418-136c.375-6.874-5.098-12.654-11.982-12.654h-63.383c-6.884 0-12.356 5.78-11.981 12.654z"/> <?php // phpcs:ignore ?>
            </svg>
        </div>
        <?php
    }


    /**
     * Render CSS
     *
     * @return void
     */
    protected function renderCSS()
    {
        ?>
        <style>
            /*! ******
            HELPER CALSSES
            *******/
            .float-right {
                float: right;
            }
            .float-left {
                float: left;
            }
            .clearfix:before,
            .clearfix:after {
                content: " "; /* 1 */
                display: table; /* 2 */
            }

            .clearfix:after {
                clear: both;
            }

            .no-display { 
                display: none; 
            }

            .hidden {
                visibility: hidden;
                opacity: 0;
            }

            .transparent {
                opacity: 0;
            }

            .monospace {
                font-family: monospace;
            }

            .red {
                color: #AF0000;
            }

            .orangered {
                color: orangered;
            }

            .green {
                color: #008000;
            }

            .maroon {
                color:maroon;
            }

            .silver {
                color:silver;
            }

            .font-size-14 {font-size: 14px}
            .font-size-15 {font-size: 15px}
            .font-size-16 {font-size: 16px}

            .text-center {
                text-align: center;
            }

            .text-right {
                text-align: right;
            }

            .display-inline {
                display: inline;
            }

            .display-inline-block {
                display: inline-block;
            }

            .display-block {
                display: block;
            } 

            .margin-top-0 {
                margin-top: 0;
            }

            .margin-top-1 {
                margin-top: 20px;
            }

            .margin-top-2 {
                margin-top: 40px;
            }

            .margin-top-3 {
                margin-top: 60px;
            }

            .margin-top-4 {
                margin-top: 80px;
            }

            .margin-bottom-0 {
                margin-bottom: 0;
            }

            .margin-bottom-1 {
                margin-bottom: 20px;
            }

            .margin-bottom-2 {
                margin-bottom: 40px;
            }

            .margin-bottom-3 {
                margin-bottom: 60px;
            }

            .margin-bottom-4 {
                margin-bottom: 80px;
            }

            .margin-left-0 {
                margin-left: 0;
            }

            .margin-left-1 {
                margin-left: 20px;
            }

            .margin-left-2 {
                margin-left: 40px;
            }

            .margin-right-0 {
                margin-right: 0;
            }

            .margin-left-1 {
                margin-right: 20px;
            }

            .margin-left-2 {
                margin-right: 40px;
            }

            .auto-updatable button.postfix {
                min-width: 80px;
            }

            .auto-updatable.autoupdate-enabled button.postfix {
                background-color: #13659C;
                color: #fff;
            }

            hr.separator {
                border: 0 none;
                border-bottom:1px solid #dfdfdf;
                margin: 1em 0;
                padding: 0;
            }

            hr.separator.dotted {
                border-bottom:1px dotted #dfdfdf;
            }

            .text-security-disc {
                font-family: dotsfont !important;
                font-size: 10px;
            }

            .text-security-disc::-webkit-input-placeholder {
                font-family: Verdana, Arial, sans-serif !important;
                font-size: 13px;
            }

            .text-security-disc::-ms-input-placeholder {
                font-family: Verdana, Arial, sans-serif !important;
                font-size: 13px;
            }

            .text-security-disc::-moz-placeholder {
                font-family: Verdana, Arial, sans-serif !important;
                font-size: 13px;
            }

            .text-security-disc::placeholder {
                font-family: Verdana, Arial, sans-serif !important;
                font-size: 13px;
            }

            body {
                background-color:transparent;
                color: #000000;
                font-family:Verdana,Arial,sans-serif; 
                font-size:13px
            }
            fieldset {border:1px solid silver; border-radius:3px; padding:10px}
            h3 {
                margin:1px; 
                padding:1px; 
                font-size:13px;
            }

            .generic-box .box-title,
            .hdr-sub1 {
                font-size: 18px;
                font-weight: bold;
            }

            .sub-title {
                font-size:14px;
                margin-bottom: 5px;
            }

            .link-style,
            a {
                text-decoration: underline;
                color: #222;
                transition: all 0.3s;
                cursor: pointer;
            }
            .link-style:hover,
            a:hover{
                color: #13659C;
            }

            .margin-top {
                margin-top: 20px;
            }

            *:focus {
                outline: none !important;
            }

            input:not([type=checkbox]):not([type=radio]):not([type=button]):not(.select2-search__field) , select {
                min-width: 0;
                width: 100%;
                border-radius: 2px;
                border: 1px solid silver;
                padding: 4px;
                padding-left: 4px;
                font-family: Verdana,Arial,sans-serif;
                line-height: 20px;
                height: 30px;
                box-sizing: border-box;
                background-color: white;
                color: black;
                border-radius: 4px;
            }

            input:not([type=checkbox]):not([type=radio]):not([type=button]).w30 , select.w30 {
                width: 30%;
            }

            input:not([type=checkbox]):not([type=radio]):not([type=button]).w50 , select.w50 {
                width: 50%;
            }

            input:not([type=checkbox]):not([type=radio]):not([type=button]).w95 , select.w95 {
                width: 95%;
            }

            input[readonly]:not([type=checkbox]):not([type=radio]):not([type=button]) {
                background-color: #efefef;
                color: #999999;
                cursor: not-allowed;
            }

            textarea[readonly] {
                background-color: #efefef;
            }

            /*input.select2-search__field {
                height: auto;
                width: auto;
                border: 0 none;
                padding: 0;
            }*/

            .copy-to-clipboard-block textarea {
                width: 100%;
                height: 100px;
            }

            .copy-to-clipboard-block button {
                font-size: 14px;
                padding: 5px 8px;
                margin-bottom: 15px;
            }

            select[size]:not([size="1"]) {
                height: auto;
                line-height: 25px;
            }

            select , option {
                color: black;
            }

            select option {
                padding: 5px;
            }

            input:not([type=checkbox]):not([type=radio]):not([type=button]):disabled,
            select:disabled,
            select option:disabled,
            select:disabled option, 
            select:disabled option:focus,
            select:disabled option:active,
            select:disabled option:checked {
                background: #EBEBE4;
                color: #ccc;
                cursor: not-allowed;
            }

            select:disabled,
            select option:disabled,
            select:disabled option, 
            select:disabled option:focus,
            select:disabled option:active,
            select:disabled option:checked  {
                text-decoration: line-through;
            }

            .option-group.option-disabled {
                color: #ccc;
                cursor: not-allowed;
            }

            button.no-layout {
                background: none;
                border: none;
            }

            .input-postfix-btn-group {
                display: flex;
                border: 1px solid darkgray;
                border-radius: 4px;
                overflow: hidden;
            }

            .input-postfix-btn-group input:not([type=checkbox]):not([type=radio]):not([type=button]) {
                flex: 1 1 0;
                border-radius: 0;
                border: 0 none;
                border-right: 1px solid darkgray;
                height: 28px;
            }

            .input-postfix-btn-group .prefix,
            .input-postfix-btn-group .postfix {
                flex: none;
                min-width: 60px;
                box-sizing: border-box;
                padding: 0 10px;
                margin: 0;
                border: 0 none;
                background-color:#CDCDCD;
            }

            .param-wrapper-disabled .input-postfix-btn-group .prefix,
            .param-wrapper-disabled .input-postfix-btn-group .postfix {
                color: #999999;
                pointer-events: none;
                cursor: not-allowed;
            }

            .param-wrapper.small .input-postfix-btn-group .prefix,
            .param-wrapper.small .input-postfix-btn-group .postfix {
                min-width: 0;
            }

            .input-postfix-btn-group button {
                cursor: pointer;
            }

            .input-postfix-btn-group button:hover {
                border: 0 none;
                background-color: #13659C;
                color: white;
            }


            .param-wrapper span .checkbox-switch {
                top: 2px;
            }

            .param-wrapper.align-right {
                float: right;
            }

            .param-wrapper.align-right > .container > .main-label {
                width: auto;
            }

            .wpinconf-check-wrapper {
                flex: none;
                width: 100px;
            }

            #wrapper_item_subsite_id.param-wrapper-disabled,
            #wrapper_item_subsite_owr_id.param-wrapper-disabled,
            #wrapper_item_subsit_owr_slug.param-wrapper-disabled,
            #wrapper_item_users_mode.param-wrapper-disabled {
                display: none;
            }

            .btn-group {
                display: inline-flex;
                border: 1px solid silver;
                border-radius: 5px;
                overflow: hidden;
            }

            .btn-group button {
                flex: 1 1 0;
                background-color: #E4E4E4; 
                border: 0 none !important;
                border-right: 1px solid silver !important;
                padding: 6px; 
                cursor: pointer; 
                float: left;
                font-size: 14px;
            }

            .overwrite_sites_list {
                display: flex;
                flex-direction: column;
                row-gap: 20px;
            }

            .param-form-type-sitesowrmap .overwrite_site_item {
                display: flex;
                flex-wrap: wrap;
                gap: 5px 20px;
            }

            .param-form-type-sitesowrmap .overwrite_site_item .del_item {
                float: right;
                font-size: 25px;
                line-height: 1;
            }

            .param-form-type-sitesowrmap .overwrite_site_item .del_item.disabled {
                color: silver;
            }

            .param-form-type-sitesowrmap .overwrite_site_item > .col {
                flex: 1 1 0;
            }
            .param-form-type-sitesowrmap .overwrite_site_item.title > .col {
                border-bottom: 1px solid #D3D3D3;
                padding-bottom: 5px;
                font-weight: bold;
            }

            .param-form-type-sitesowrmap .overwrite_site_item > .col.del {
                flex-grow: 0;
                font-size: 18px;
                border:none;
            }

            .param-form-type-sitesowrmap .overwrite_sites_list.no-multiple .overwrite_site_item > .col.del,
            .param-form-type-sitesowrmap .overwrite_sites_list.no-multiple .overwrite_site_item.add_item {
                display: none;
            }

            .param-form-type-sitesowrmap .overwrite_site_item > .full {
                flex: 0 0 100%;
            }

            .param-form-type-sitesowrmap .target_select_wrapper {
                position: relative;
            }

            .param-form-type-sitesowrmap .target_select_wrapper .new-slug-wrapper {
                position: absolute;
                top: 0;
                right: 22px;
                width: 280px;
            }

            .param-form-type-sitesowrmap .target_select_wrapper .new-slug-wrapper  input {
                background: #EFEFEF;
                border-radius: 0;
            }

            .param-form-type-sitesowrmap .sub-note {
                word-wrap: anywhere;
            }

            .param-form-type-sitesowrmap .sub-note .site-slug {
                font-weight: bold;
                display: inline-block;
                padding: 2px;
                background: #EFEFEF;
                border-radius: 2px;
            }

            .btn-group.small button {
                padding: 3px 7px 3px 7px;
                font-size: 11px;
            }

            .btn-group button:last-child {
                border-right: none !important; 
            }

            .btn-group:after {
                content: "";
                clear: both;
                display: table;
            }

            .btn-group button:hover,
            .btn-group button.active {
                background-color: #13659C;
                color: #FFF;
            }

            .box {
                border: 1px solid silver;
                padding: 10px;
                background: #f9f9f9;
                border-radius:2px;
            }

            .box *:first-child {
                margin-top: 0;
            }

            .box *:last-child {
                margin-bottom: 0;
            }

            .box.warning {
                color: maroon;
                border-color: maroon;
            }

            /* ============================
            COMMON VIEWS
            ============================ */
            body,
            div#content,
            form.content-form {
                line-height: 1.5;
            }

            /*Lets revisit this later.  Right now anything over 900px gives the overall feel of an elongated flow and the
            inputs look too spread out. If we can iron out some of those issues with multi-columns and the notices view better
            then we can try and work more towards a full fluid layout*/
            #content {
                border:1px solid #CDCDCD; 
                margin: 20px auto; 
                border-radius:2px;
                box-shadow:0 8px 6px -6px #999;
                font-size:13px;
                width: calc(900px + 42px);
                max-width: calc(100vw - 40px);
                box-sizing: border-box;
            }

            .debug-params div#content {
                margin: 20px; 
            }

            #content-inner {
                margin: 20px;
                position: relative;
            }

            #content-loader-wait {        
                font-weight: bold;
                text-align: center;
                vertical-align: middle;
            }

            #body-step4 #content-inner {
                padding-bottom: 0;
            }

            div.logfile-link {float:right; font-weight:normal; font-size:11px; font-style:italic}

            /* Header */
            table.header-wizard {width:100%; box-shadow:0 5px 3px -3px #999; background-color:#E0E0E0; font-weight:bold}
            .wiz-dupx-version {
                white-space:nowrap; 
                color:#777; 
                font-size:11px; 
                font-style:italic; 
                text-align:right;  
                padding:5px 15px 5px 0; 
                line-height:14px; 
                font-weight:normal
            }
            .wiz-dupx-version a { color:#999; }
            div.dupx-branding-header {font-size:26px; padding: 10px 0 7px 15px;}

            .dupx-overwrite {color:black;}

            .dupx-pass {display:inline-block; color:green;}
            .dupx-fail {display:inline-block; color:#AF0000;}
            .dupx-warn {display:inline-block; color:#555;}
            .dupx-notice {display:inline-block; color:#000;}
            i[data-tooltip].fa-question-circle {cursor: pointer; color:#888888}

            #wrapper_item_install-type input[value="6"] + .label-checkbox::after,
            #wrapper_item_install-type input[value="7"] + .label-checkbox::after
            {
                content: "Beta";
                border-radius:4px; 
                color:#fff; 
                padding:0 3px 0 3px;  
                font-size:11px; 
                min-width:30px; 
                text-align:center; 
                font-weight:normal;
                background-color:maroon;
                padding: 2px 4px;

            }

            .status-badge {
                border-radius:4px; 
                color:#fff; 
                padding:0 3px 0 3px;  
                font-size:11px; 
                min-width:30px; 
                text-align:center; 
                font-weight:normal;
            }
            .status-badge.right {
                float: right; 
            }
            .status-badge.pass,
            .status-badge.good,
            .status-badge.success {
                background-color:#418446
            }
            .status-badge.pass::after {
                content: "Pass"
            }
            .status-badge.good::after {
                content: "Pass"
            }
            .status-badge.success::after {
                content: "Success"
            }
            .status-badge.fail {
                background-color:maroon;
            }
            .status-badge.fail::after {
                content: "Fail"
            }
            .status-badge.hwarn {
                background-color: #a15e19;
            }
            .status-badge.hwarn::after {
                content: "Warn"
            }
            .status-badge.warn {
                background-color: #555555;
            }
            .status-badge.warn::after {
                content: "Notice"
            }

            .default-btn,
            .secondary-btn {
                transition: all 0.2s ease-out;
                color: #FEFEFE;
                font-size: 16px;
                border-radius: 5px;
                padding: 7px 15px;
                background-color: #13659C;
                border: 1px solid gray;
                line-height: 18px;
                text-decoration: none;
                display: inline-block;
                white-space: nowrap;
                min-width: 100px;
                text-align: center;
            }

            .default-btn.small,
            .secondary-btn.small {
                font-size: 13px;
                padding: 3px 10px;
                min-width: 80px;
            }

            .default-btn:hover {
                color: #13659C;
                border-color: #13659C;
                background-color: #FEFEFE;
            }

            .default-btn.disabled,
            .default-btn:disabled {
                color:silver;         
                background-color: #EDEDED;
                border: 1px solid silver;
            }

            .secondary-btn {
                color: #333333;         
                background-color: #EDEDED;
                border: 1px solid #333333;
            }

            .secondary-btn:hover {
                color: #FEFEFE;         
                background-color: #999999;
            }

            .log-ui-error {padding-top:2px; font-size:13px}
            #progress-area {
                padding:5px; 
                margin:150px 0; 
                text-align:center;
            }
            .progress-text {font-size:1.7em; margin-bottom:20px}
            #secondary-progress-text { font-size:.85em; margin-bottom:20px }
            #progress-notice:not(:empty) { color:maroon; font-size:.85em; margin-bottom:20px; }

            #ajaxerr-data {
                min-height: 300px;
            }

            #ajaxerr-data .pre-content,
            #ajaxerr-data .html-content {
                padding:6px; 
                box-sizing: border-box;
                width:100%; 
                border:1px solid silver; 
                border-radius:3px;
                background-color:#F1F1F1; 
                overflow-y:scroll; 
                line-height:20px
            }

            #ajaxerr-data .pre-content {
                height:300px;
            }

            #ajaxerr-data .iframe-content {
                width: 100%;
                height: 300px;
                overflow: auto;
                box-sizing: border-box;
                border:1px solid silver;
                border-radius:3px;
            }

            #header-main-wrapper {
                position: relative;
                padding:0 0 5px 0; 
                border-bottom:1px solid #D3D3D3; 
                margin: 0 0 20px 0;
                display: flex;
            }

            #header-main-wrapper .dupx-logfile-link {
                font-weight:normal; 
                font-style:italic; 
                font-size:11px;
                position: absolute;
                bottom: 2px;
                right: 0;
            }


            #header-main-wrapper .hdr-main {
                font-size:22px; 
                font-weight:bold; 
                flex: 1 1 auto;
            }

            #header-main-wrapper .hdr-secodary {
                flex: 0 1 auto;
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                min-width: 150px;
            }

            .hdr-secodary .installer-log {
                font-size: 12px;
                font-style: italic;
                text-align: right;
            }

            #installer-switch-wrapper  {
                text-align:right
            }

            #installer-switch-wrapper .btn-group {
                width: 120px;
            }

            .generic-box { 
                border: 1px solid #DEDEDE;
                border-radius: 2px;
                margin-bottom: 20px;
            }

            .generic-box .box-title { 
                padding: 4px 7px;
                border-bottom: 1px solid #DEDEDE;
                background-color:#f9f9f9; 
                border-radius:2px 2px 0 0;
            }

            .generic-box .box-content { 
                padding: 20px;
            }

            .generic-box .box-content *:first-child {
                margin-top: 0;
            }

            .generic-box .box-content *:last-child {
                margin-bottom: 0;
            }

            div.sub-header {
                font-size:11px; 
                font-style:italic; 
                font-weight:normal
            }
            .hdr-main .step { 
                color:#DB4B38  
            }

            .hdr-sub1 {
                border:1px solid #D3D3D3;
                padding: 4px 7px;
                background-color:#E0E0E0;
                border-radius:2px 2px 0 0;
            }

            .hdr-sub1.open {
                border-radius: 2px;
                margin-bottom: 20px;
            }

            .hdr-sub1 a {cursor:pointer; text-decoration: none !important}
            .hdr-sub1 i.fa,
            .hdr-sub1 i.fas,
            .box-title i.fa,
            .box-title i.fas {
                font-size:15px; 
                display:inline-block; 
                margin-right:5px; 
                position: relative;
                bottom: 1px;
            }

            .hdr-sub1 .status-badge {
                margin-top: 4px;
            }

            .hdr-sub1-area {
                border: 0 solid #D3D3D3;
                border-top: 0 none;
                border-radius: 0 0 2px 2px;
                padding: 20px;
                margin-bottom: 20px;
                position: relative;
                background-color:#fff;
            }

            .hdr-sub1-area.tabs-area {
                padding: 5px 5px 0 5px;
            }

            .hdr-sub1-area.tabs-area .ui-tabs-nav {
                border-radius: 0;
                border: 0 none;
            }

            .hdr-sub1-area.tabs-area .ui-tabs {
                margin: 0;
                padding: 0;
                border: 0 none;
            }

            .hdr-sub1-area.tabs-area .ui-tabs-tab {
                margin: 3px 5px 0 0;
            }

            .hdr-sub1-area.tabs-area .ui-tabs-panel {
                position: relative;
                padding: 10px;
            }

            .hdr-sub2 {font-size:15px; padding:2px 2px 2px 0; font-weight:bold; margin-bottom:5px; border:none}
            .hdr-sub3 {font-size:15px; padding:2px 2px 2px 0; border-bottom:1px solid #D3D3D3; font-weight:bold; margin-bottom:5px;}
            .hdr-sub3.warning::before {
                content: "\f071";
                font-family: "Font Awesome 5 Free";
                font-weight: 900;
                font-style: normal;
                font-variant: normal;
                text-rendering: auto;
                line-height: 1;
                font-size: 12px;
                margin-right: 5px;
                color: #AF0000;
            }
            .hdr-sub4 {font-size:15px; padding:7px; border:1px solid #D3D3D3;; font-weight:bold; background-color:#e9e9e9;}
            .hdr-sub4:hover  {background-color:#dfdfdf; cursor:pointer}
            .toggle-hdr:hover {cursor:pointer; background-color:#f1f1f1; border:1px solid #dcdcdc; }
            .toggle-hdr:hover a{color:#000}
            .ui-widget-header {border: none; border-bottom: 1px solid #D3D3D3 !important; background:#fff}


            [data-type="toggle"] > i.fa,
            i.fa.fa-toggle-empty { min-width: 8px; }

            /* ============================
            NOTICES
            ============================ */
            /* step messages */

            .notice {
                background: #fff;
                border:1px solid #dfdfdf;
                border-left: 4px solid #fff;
                margin: 5px 0;
                padding: 5px;
                border-radius: 2px;
                font-size: 12px;
            }

            .section .notice:first-child {
                margin-top: 0;
            }

            .section .notice:last-child {
                margin-bottom: 0;
            }

            .notice.next-step {
                margin: 20px 0;
                padding: 10px;
            }

            .notice-report {
                border-left: 4px solid #fff;
                padding-left: 0;
                padding-right: 0;
                margin-bottom: 4px;
            }

            .next-step .title-separator {
                margin-top: 10px;
                padding-top: 10px;
                border-top: 1px solid lightgray;
            }

            .notice .info pre {
                margin: 0;
                padding: 0 0 10px 0;
                overflow: auto;
            }

            .notice-report .title {
                padding: 0 10px;
            }

            .notice-report .title.close {
                padding-bottom: 5px;
            }

            .notice-report .info {
                border-top: 1px solid #dedede;
                padding: 10px;
                background: #FAFAFA;
            }

            .notice-report .info *:first-child {
                margin-top: 0;
            }

            .notice-report .info *:last-child{
                margin-bottom: 0;
            }

            .notice-report .info pre {
                font-size: 11px;
            }

            .notice.l-info,
            .notice.l-notice {
                border-left-color: #197b19;
            }
            .notice.l-swarning {
                border-left-color: #636363;
            }
            .notice.l-hwarning {
                border-left-color: #636363;
            }
            .notice.l-critical {
                border-left-color: maroon;
            }
            .notice.l-fatal {
                border-left-color: #000000;
            }

            .notice.next-step {
                position: relative;
            }

            .notice.next-step.l-info,
            .notice.next-step.l-notice {
                border-color: #197b19;
            }
            .notice.next-step.l-swarning {
                border-color: #636363;
            }
            .notice.next-step.l-hwarning {
                border-color: #636363;
            }
            .notice.next-step.l-critical {
                border-color: maroon;
            }
            .notice.next-step.l-fatal {
                border-color: #000000;
            }

            .notice.next-step > .title {
                padding-left: 30px;
            }

            .notice.next-step > .fas {
                display: block;
                position: absolute;
                height: 20px;
                width: 20px;
                line-height: 20px;
                text-align: center;
                color: white;
                border-radius:2px;
            }

            .notice.next-step.l-info > .fas,
            .notice.next-step.l-notice > .fas {
                background-color: #197b19;
            }
            .notice.next-step.l-swarning > .fas {
                background-color: #636363;
            }
            .notice.next-step.l-hwarning > .fas {
                background-color: #636363;
            }
            .notice.next-step.l-critical > .fas {
                background-color: maroon;
            }
            .notice.next-step.l-fatal > .fas{
                background-color: #000000;
            }

            .report-sections-list .section {
                border: 1px solid #DFDFDF;
                margin-bottom: 25px;
                box-shadow: 4px 8px 11px -8px rgba(0,0,0,0.41);
            }

            .report-sections-list .section > .section-title {
                background-color: #efefef;
                padding: 3px;
                font-weight: bold;
                text-align: center;
                font-size: 14px;
            }

            .report-sections-list .section > .section-content {
                padding: 5px;
            }

            .notice-level-status {
                border-radius:2px;
                padding: 2px;
                margin: 1px;
                font-size: 10px;
                display: inline-block;
                color: #FFF;
                font-weight: bold;
                min-width:55px;
            }

            .notice-level-status.l-info,
            .notice-level-status.l-notice {background: #197b19;}
            .notice-level-status.l-swarning {background: #636363;}
            .notice-level-status.l-hwarning {background: #636363;}
            .notice-level-status.l-critical {background: maroon;}
            .notice-level-status.l-fatal {background: #000000;}

            /*Adv Opts */
            .dupx-opts .param-wrapper {
                padding: 5px 0;
            }
            .dupx-opts .param-wrapper .param-wrapper {
                padding: 0;
            }

            .dupx-opts .param-wrapper.param-form-type-hidden{
                margin: 0;
                padding: 0;
                display: none;
            }

            .param-wrapper-disabled {
                color: #999;
            }

            .param-wrapper > .container {
                display: flex;
                flex-direction: row;
                flex-wrap: nowrap;
                align-items: center;
                min-height: 30px;
            }

            .param-wrapper > .container > .main-label {
                flex: none;
                width: 200px;
                font-weight: bold;
                line-height: 1.5;
                box-sizing: border-box;
                padding-right: 5px;
            }

            .param-wrapper.has-main-label > .sub-note {
                margin-left: 200px;
            }

            #tabs-wp-config-file .param-wrapper > .container > .main-label {
                width: 310px;
            }

            #tabs-wp-config-file .param-wrapper.has-main-label > .sub-note {
                margin-left: 310px;
            }

            #tabs-wp-config-file div.help-target {
                padding-top:10px;
            }

            .param-wrapper > .container .input-container {
                flex: 1 1 auto;
            }

            .param-wrapper.small > .container .input-container {
                max-width: 100px;
            }

            .param-wrapper.medium > .container .input-container {
                max-width: 300px;
            }

            .param-wrapper.large > .container .input-container {
                max-width: 500px;
            }

            .param-wrapper.full > .container .input-container {
                max-width: none;
            }

            /*
            .dupx-opts > .param-wrapper:nth-child(2n+1) {
                background-color: #EAEAEA;
            }

            .dupx-opts > .param-wrapper:nth-child(2n) {
                background-color: #F6F6F6;
            }*/

            .param-form-type-radio .option-group {
                display: inline-block;
                min-width: 140px;
            }

            .param-form-type-radio.group-block .option-group {
                display: block;
                line-height: 30px;
            }

            .param-wrapper .sub-note {
                display: block;
                font-size: 11px;
                margin-top:6px;
            }

            .param-wrapper .option-group .sub-note {
                line-height: 1.1;
                margin-top: 0;
                margin-bottom: 8px;
                color: #000000;
            }

            #pass-quick-help-info small {
               color:gray;
               font-style: italic
            }

            .main-form-content {
                min-height: 300px;
            }

            .footer-buttons {
                display: flex;
                width: 100%;
            }

            .footer-buttons .content-left,
            .footer-buttons .content-center {
                flex: 1;
            }
            .footer-buttons .content-center {
                text-align: center;
            }

            h2 {font-size:20px; margin:5px 0 5px 0; border-bottom:1px solid #dfdfdf; padding:3px}

            div.errror-notice {text-align:center; font-style:italic; font-size:11px}
            div.errror-msg { color:maroon; padding: 10px 0 5px 0}
            .pass {color:green}
            .fail {color:red}
            span.file-info {font-size: 11px; font-style: italic}
            div.skip-not-found {padding:10px 0 5px 0;}
            div.skip-not-found label {cursor: pointer}
            table.settings {width:100%; font-size:12px}
            table.settings td {padding: 4px}
            table.settings td:first-child {font-weight: bold}
            .w3-light-grey,.w3-hover-light-grey:hover,.w3-light-gray,.w3-hover-light-gray:hover{
                color:#000!important;background-color:#f1f1f1!important
            }
            .w3-container:after,.w3-container:before,.w3-panel:after,
            .w3-panel:before,.w3-row:after,.w3-row:before,
            .w3-row-padding:after,.w3-row-padding:before,
            .w3-cell-row:before,.w3-cell-row:after,
            .w3-clear:after,.w3-clear:before,.w3-bar:before,.w3-bar:after {
                content:"";display:table;clear:both
            }
            .w3-green,.w3-hover-green:hover{color:#fff!important;background-color:#4CAF50!important}
            .w3-container{padding:0.01em 16px}
            .w3-center{display:inline-block;width:auto; text-align: center !important}
        </style>
        <?php
    }
}
