<script>
/*! ============================================================================
*  DEBUG NAMESPACE: Objects used to help debug
*  =========================================================================== */

//GLOBAL CONSTANTS
Duplicator.Debug.AJAX_RESPONSE = false;
Duplicator.Debug.AJAX_TIMER = null;

/*  Reloads the current window
 *  @param data     An xhr object  */
Duplicator.Debug.ReloadWindow = function (data, queryString)
{
    if (Duplicator.Debug.AJAX_RESPONSE) {
        DupPro.Pack.ShowError('debug on', data);
    } else {
        var url = window.location.href;
        if (typeof queryString !== 'undefined') {
            var character = '?';
            if (url.indexOf('?') > -1) {
                character = '&';
            }
            url += character + queryString;
        }
        window.location = url;
    }
};

/* Basic Util Methods here */
Duplicator.Debug.OpenLogWindow = function (log)
{
    var logFile = log || null;
    if (logFile == null) {
        window.open('?page=duplicator-pro-tools', 'Log Window');
    } else {
        window.open('<?php echo DUPLICATOR_PRO_SSDIR_URL; ?>' + '/' + log)
    }
};

/* Starts a timer for Ajax calls */
Duplicator.Debug.StartAjaxTimer = function ()
{
    Duplicator.Debug.AJAX_TIMER = new Date();
};

/*  Ends a timer for Ajax calls */
Duplicator.Debug.EndAjaxTimer = function ()
{
    var endTime = new Date();
    Duplicator.Debug.AJAX_TIMER = (endTime.getTime() - Duplicator.Debug.AJAX_TIMER) / 1000;
};
</script>
