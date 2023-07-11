<?php
defined("DUPXABSPATH") or die("");

use Duplicator\Installer\Core\Security;
use Duplicator\Installer\Core\Params\PrmMng;
use Duplicator\Installer\Utils\SecureCsrf;
use Duplicator\Libs\Snap\SnapURL;
use VendorDuplicator\Amk\JsonSerialize\JsonSerialize;

$paramsManager = PrmMng::getInstance();
?>
<script>
    //Unique namespace
    DUPX            = new Object();
    DUPX.Util       = new Object();
    DUPX.UI         = new Object();
    DUPX.Const      = new Object();
    DUPX.GLB_DEBUG = <?php echo $paramsManager->getValue(PrmMng::PARAM_DEBUG) ? 'true' : 'false'; ?>;
    DUPX.dupInstallerUrl = <?php echo JsonSerialize::serialize(SnapURL::getCurrentUrl(false, true)); ?>;
    DUPX.dupSourceUrlData = <?php echo JsonSerialize::serialize($_REQUEST); ?>;

    DUPX.beforeUnloadListener = (event) => {
        event.preventDefault();
        return event.returnValue = "Are you sure you want to exit?";
    };
    DUPX.beforeUnloadCheck = function (enable) {
        if (enable) {
            window.addEventListener("beforeunload", DUPX.beforeUnloadListener);
        } else {
            window.removeEventListener("beforeunload", DUPX.beforeUnloadListener);
        }
    };

    DUPX.redirect = function (url, method, params) {
        var form = $('<form>', {
            method: method,
            action: url
        });

        $.each(params, function (key, value) {
            form.append($('<input>', {
                'type': 'hidden',
                'name': key,
                'value': value
            }));
        });

        $("body").append(form);
        DUPX.beforeUnloadCheck(false);
        form.submit();
    };

    DUPX.redirectMainInstaller = function(method, params) {
        fullParams = $.extend(true, {}, DUPX.dupSourceUrlData, params);
        DUPX.redirect(DUPX.dupInstallerUrl, method, fullParams);
    };

    DUPX.parseJSON = function (mixData) {
        // if mixData is already a object, Then don't parse it
        if (typeof mixData === 'object' && mixData !== null) {
            return mixData;
        }

        try {
            var parsed = JSON.parse(mixData);
            return parsed;
        } catch (e) {
            console.log("JSON parse failed - 1");
            console.log(mixData);
        }

        if (mixData.indexOf('[') > -1 && mixData.indexOf('{') > -1) {
            if (mixData.indexOf('{') < mixData.indexOf('[')) {
                var startBracket = '{';
                var endBracket = '}';
            } else {
                var startBracket = '[';
                var endBracket = ']';
            }
        } else if (mixData.indexOf('[') > -1 && mixData.indexOf('{') === -1) {
            var startBracket = '[';
            var endBracket = ']';
        } else {
            var startBracket = '{';
            var endBracket = '}';
        }

        var jsonStartPos = mixData.indexOf(startBracket);
        var jsonLastPos = mixData.lastIndexOf(endBracket);
        if (jsonStartPos > -1 && jsonLastPos > -1) {
            var expectedJsonStr = mixData.slice(jsonStartPos, jsonLastPos + 1);
            try {
                var parsed = JSON.parse(expectedJsonStr);
                return parsed;
            } catch (e) {
                console.log("JSON parse failed - 2");
                console.log(mixData);
                throw e;
                return false;
            }
        }
        throw "could not parse the JSON";
        return false;
    }

    DUPX.StandarJsonAjaxWrapper = function (action, token, ajaxData, callbackSuccess, callbackFail, options) {
        var ajax_url = document.location.href;
        var currentOptions = jQuery.extend({}, DUPX.standarJsonAjaxOptions, options);

        var ajaxData = $.extend(true, {}, DUPX.dupSourceUrlData, {
            "ctrl_action": 'ajax',
            "ajax_action": action,
            "ajax_csrf_token": token
        }, ajaxData);

        function retryOnFailure(result, textStatus, jqXHR) {
            var retryOptions = Object.assign({}, options);
            retryOptions.numberOfAttempts--;

            if (typeof currentOptions.callbackOnRetry === "function") {
                currentOptions.callbackOnRetry(result, textStatus, jqXHR, retryOptions);
            }

            if (currentOptions.delayRetryOnFailure > 0) {
                setTimeout(function () {
                    DUPX.StandarJsonAjaxWrapper(action, token, ajaxData, callbackSuccess, callbackFail, retryOptions);
                }, currentOptions.delayRetryOnFailure);
            } else {
                DUPX.StandarJsonAjaxWrapper(action, token, ajaxData, callbackSuccess, callbackFail, retryOptions);
            }
        }

        jQuery.ajax({
            type: "POST",
            url: ajax_url,
            dataType: "json",
            timeout: currentOptions.timeOut,
            data: ajaxData,
            beforeSend: currentOptions.beforeSend,
            success: function (result, textStatus, jqXHR) {
                var message = '';
                if (result.success) {
                    if (typeof callbackSuccess === "function") {
                        callbackSuccess(result, textStatus, jqXHR);
                    } else {
                        alert('SUCCESS: ' + result.message);
                    }
                } else {
                    if (currentOptions.retryOnFailure && currentOptions.numberOfAttempts > 0) {
                        retryOnFailure(result, textStatus, jqXHR);
                    } else {
                        if (typeof callbackFail === "function") {
                            callbackFail(result, textStatus, jqXHR);
                        } else {
                            alert('RESPONSE ERROR! ' + result.message);
                        }
                    }
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                var statusText = Boolean(jqXHR.statusText) ? jqXHR.statusText : "Server error";
                
                const result = {
                    'success': false,
                    'message': 'AJAX ERROR! STATUS: ' + jqXHR.status + ' ' + statusText,
                    'errorContent': {
                        'pre': '',
                        'html': '',
                        'iframe': ''
                    },
                    'actionData': null
                };

                if (currentOptions.retryOnFailure && currentOptions.numberOfAttempts > 0) {
                    retryOnFailure(result, statusText, jqXHR);
                    return;
                }
                
                switch(jqXHR.status) {
                    case 200:
                        result.message = 'AJAX ERROR! STATUS: ' + statusText;
                        result.errorContent.html = jqXHR.responseText;
                        break;                    
                    default:
                        break;
                }
                if (jqXHR.status >= 500 && jqXHR.status < 600) {
                    result.errorContent.iframe = jqXHR.responseText;
                }

                if (typeof callbackFail === "function") {
                    callbackFail(result, statusText, jqXHR);
                } else {
                    alert(result.message);
                }

            }
        });
    };

    DUPX.standarJsonAjaxOptions = {
        timeOut: 1800000,
        beforeSend: null,
        retryOnFailure: false,
        numberOfAttempts: 3,
        delayRetryOnFailure: 5000,
        callbackOnRetry: null
    };

    DUPX.showProgressBar = function ()
    {
        DUPX.animateProgressBar('progress-bar');
        $('#ajaxerr-area').hide();
        $('#progress-area').show();
    };

    DUPX.hideProgressBar = function ()
    {
        $('#progress-area').hide(100);
        $('#ajaxerr-area').fadeIn(400);
    };

    DUPX.getFormDataObject = function (formObj) {
        var formArray = $(formObj).serializeArray();
        var returnObj = {};
        for (var i = 0; i < formArray.length; i++) {
            returnObj[formArray[i]['name']] = formArray[i]['value'];
        }
        return returnObj;
    };

    DUPX.animateProgressBar = function (id) {
        //Create Progress Bar
        var $mainbar = $("#" + id);
        $mainbar.progressbar({value: 100});
        $mainbar.height(25);
        runAnimation($mainbar);

        function runAnimation($pb) {
            $pb.css({"padding-left": "0%", "padding-right": "90%"});
            $pb.progressbar("option", "value", 100);
            $pb.animate({paddingLeft: "90%", paddingRight: "0%"}, 3500, "linear", function () {
                runAnimation($pb);
            });
        }
    };

    DUPX.stringifyNumber = function (n) {
        const special = [
            'zeroth', 'first', 'second', 'third', 'fourth', 'fifth', 'sixth', 
            'seventh', 'eighth', 'ninth', 'tenth', 'eleventh', 'twelvth', 'thirteenth', 'fourteenth', 
            'fifteenth', 'sixteenth', 'seventeenth', 'eighteenth', 'nineteenth'
        ];
        const deca = ['twent', 'thirt', 'fourt', 'fift', 'sixt', 'sevent', 'eight', 'ninet'];
        if (n < 20) {
            return special[n];
        }
        if (n % 10 === 0) {
            return deca[Math.floor(n / 10) - 2] + 'ieth';
        }
        return deca[Math.floor(n / 10) - 2] + 'y-' + special[n % 10];
    };

    /**
     * Returns the windows active url */
    DUPX.getNewURL = function (id)
    {
        var filename = window.location.pathname.split('/').pop() || 'main.installer.php';
        var newVal = window.location.href.split("?")[0];
        newVal = newVal.replace("/" + filename, '');
        var last_slash = newVal.lastIndexOf("/");
        newVal = newVal.substring(0, last_slash);
        $("#" + id).val(newVal).keyup();
    };

    DUPX.getNewUrlByDomObj = function (button) {
        var inputId = $(button).parent().find('input').attr('id');
        DUPX.getNewURL(inputId);
    };

    DUPX.editActivate = function (button, msg)
    {
        var buttonObj = $(button);
        var inputObj = buttonObj.parent().find('input');

        if (confirm(msg)) {
            inputObj.removeAttr('readonly').removeClass('readonly');
            buttonObj.hide();
        }
    };

    DUPX.autoUpdateToggle = function (button, msg)
    {
        var buttonObj = $(button);
        var wrapperObj = $(button).closest('.param-wrapper');
        var inputObj = buttonObj.parent().find('input');
        var fromInputObj = $('#' + wrapperObj.data('auto-update-from-input'));

        if (wrapperObj.hasClass('autoupdate-enabled')) {
            if (confirm(msg)) {
                wrapperObj.removeClass('autoupdate-enabled').addClass('autoupdate-disabled');
                buttonObj.text('Manual');
                inputObj.prop('readonly', false);
            }
        } else {
            wrapperObj.removeClass('autoupdate-disabled').addClass('autoupdate-enabled');
            buttonObj.text('Auto');
            inputObj.prop('readonly', true);
            fromInputObj.trigger("change");
        }
        /*
         if (confirm(msg)) {
         inputObj.removeAttr('readonly').removeClass('readonly');
         buttonObj.hide();
         }*/
    };

    /*
     * DUPX.requestAPI({
     *          operation : '/cpnl/create_token/',
     *          data : params,
     *          callback :  function(){});
     */
    DUPX.requestAPI = function (obj)
    {
        var timeout = obj.timeout || 120000;  //default to 120 seconds
        var apiPath = (obj.operation.substr(-1) !== '/') ? apiPath += '/' : obj.operation;
        var urlPath = window.location.pathname;
        var pathName = urlPath.substring(0, urlPath.lastIndexOf("/") + 1);
        var requestURI = window.location.origin + pathName + 'api/router.php' + apiPath + window.location.search

        for (var key in obj.params)
        {
            if (obj.params.hasOwnProperty(key) && typeof (obj.params[key]) != 'undefined')
            {
                obj.params[key] = encodeURIComponent(obj.params[key].replace(/&amp;/g, "&"));
            }
        }

        var tokenData = <?php
        $paramManager = PrmMng::getInstance();
        echo JsonSerialize::serialize([
            PrmMng::PARAM_ROUTER_ACTION => $paramManager->getValue(PrmMng::PARAM_ROUTER_ACTION),
            Security::ROUTER_TOKEN      => SecureCsrf::generate($paramManager->getValue(PrmMng::PARAM_ROUTER_ACTION))
        ]);
        ?>;
        var ajaxData = $.extend({}, tokenData, obj.params);

        if (DUPX.GLB_DEBUG) {
            console.log('==============================================================');
            console.log('API REQUEST: ' + obj.operation);
            console.log(obj.params);
        }

        //Requests to API are capped at 2 minutes
        $.ajax({
            type: "POST",
            cache: false,
            timeout: timeout,
            url: requestURI,
            data: ajaxData,
            success: function (respData) {
                if (DUPX.GLB_DEBUG)
                    console.log(respData);
                try {
                    var data = DUPX.parseJSON(respData);
                } catch (err) {
                    console.error(err);
                    console.error('JSON parse failed for response data: ' + respData);
                    var data = respData;
                }
                obj.callback(data);
            },
            error: function (data) {
                if (DUPX.GLB_DEBUG)
                    console.log(data);
                obj.callback(data);
            }
        });
    }

    DUPX.toggleAll = function (id) {
        $(id + " *[data-type='toggle']").each(function () {
            $(this).trigger('click');
        });
    }

    DUPX.toggleClick = function ()
    {
        var button = $(this);
        var src = 0;
        var id = button.attr('data-target');
        var text = button.text().replace(/\+|\-/, "");
        var icon = button.find('i.fa');
        var target = $(id);
        var list = new Array();

        var style = [
            {open: "fa-minus-square",
                close: "fa-plus-square"
            },
            {open: "fa-caret-down",
                close: "fa-caret-right"
            }];

        //Create src
        for (i = 0; i < style.length; i++) {
            if ($(icon).hasClass(style[i].open) || $(icon).hasClass(style[i].close)) {
                src = i;
                break;
            }
        }

        //Build remove list
        for (i = 0; i < style.length; i++) {
            list.push(style[i].open);
            list.push(style[i].close);
        }

        $(icon).removeClass(list.join(" "));
        if (target.is(':hidden')) {
            (icon.length)
                    ? $(icon).addClass(style[src].open)
                    : button.html("- " + text);
            button.removeClass('open').addClass('close');
            target.show().removeClass('no-display');
        } else {
            (icon.length)
                    ? $(icon).addClass(style[src].close)
                    : button.html("+ " + text);
            button.removeClass('close').addClass('open');
            target.hide().addClass('no-display');
        }
    }


    DUPX.WpItemSwitchInit = function () {
        $('.wpinconf-check-wrapper input').change(function () {
            var paramWrapper = $(this).closest('.param-wrapper');
            if (this.checked) {
                paramWrapper.find('.input-container').show().find('.input-item').prop('disabled', false);
            } else {
                paramWrapper.find('.input-container').hide().find('.input-item').prop('disabled', true);
            }
        });
    }

    DUPX.Util.formatBytes = function (bytes, decimals)
    {
        if (bytes == 0)
            return '0 Bytes';
        var k = 1000;
        var dm = decimals + 1 || 3;
        var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        var i = Math.floor(Math.log(bytes) / Math.log(k));
        return (bytes / Math.pow(k, i)).toPrecision(dm) + ' ' + sizes[i];
    }

    DUPX.initJsSelect = function (selector) {
        $(selector).select2({
            'width': '100%',
            'dropdownAutoWidth': true,
            'minimumResultsForSearch': 10
        });
    };

    $(document).ready(function ()
    {
        DuplicatorTooltip.load();

        DUPX.initJsSelect('select.js-select');

        $('body').on( "click", ".copy-to-clipboard-block button", function(e) {
            e.preventDefault();
            var button = $(this);
            var buttonText = button.html();
            var textarea = button.parent().find("textarea")[0];

            textarea.select();

            try {
                message = document.execCommand('copy') ? "Copied to Clipboard" : 'Unable to copy';
            } catch (err) {
                console.log(err);
            }

            button.html(message);

            setTimeout(function () {
                button.text(buttonText);
            }, 2000);
        });

        DUPX.WpItemSwitchInit();
    });
</script>

<script>
     /**
     *  Fatal error messages shown at top of installer.
     *  Used to control the [show more] and [show all] links
     */
    $(document).ready(function ()
    {
        function moreCheck(moreCont, moreWrap) {
            if (moreWrap.height() > moreCont.height()) {
                moreCont.addClass('more');
            } else {
                moreCont.removeClass('more');
            }
        }

        $('.more-content').each(function () {
            var moreCont = $(this);
            var step = moreCont.data('more-step');
            var moreWrap = $(this).find('.more-wrapper');

            moreCont.find('.more-button').click(function () {
                moreCont.css('max-height', "+=" + step + "px");
                moreCheck(moreCont, moreWrap);
            });

            moreCont.find('.all-button').click(function () {
                moreCont.css('max-height', "none");
                moreCheck(moreCont, moreWrap);
            });

            moreCheck(moreCont, moreWrap);
        });
    });
</script>

<?php
DUPX_U_Html::js();
