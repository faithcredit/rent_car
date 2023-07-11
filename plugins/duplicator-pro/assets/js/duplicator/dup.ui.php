<script>
/*! ============================================================================
*  UI NAMESPACE: All methods at the top of the Duplicator Namespace
*  =========================================================================== */
(function ($) {

    /*  Stores the state of a view into the database  */
    DupPro.UI.SaveViewStateByPost = function (key, value)
    {
       if (key != undefined && value != undefined) {
           jQuery.ajax({
                type: "POST",
                url: ajaxurl,
                dataType: "json",
                data: {
                    action: 'DUP_PRO_UI_ViewState_SaveByPost', 
                    key: key, 
                    value: value, 
                    nonce: '<?php echo wp_create_nonce('DUP_PRO_UI_ViewState_SaveByPost'); ?>'
                },
                success: function (data) {},
                error: function (data) {}
           });
       }
    }

    DupPro.UI.SaveMulViewStatesByPost = function (states)
    {
       jQuery.ajax({
           type: "POST",
           url: ajaxurl,
           dataType: "json",
           data: {action: 'DUP_PRO_UI_ViewState_SaveByPost', states: states, nonce: '<?php echo wp_create_nonce('DUP_PRO_UI_ViewState_SaveByPost'); ?>'},
           success: function (data) {},
           error: function (data) {}
       });
    }

    DupPro.UI.SetScanMode = function ()
    {
       var scanMode = jQuery('#scan-mode').val();

       if (scanMode == <?php echo DUP_PRO_DB::PHPDUMP_MODE_MULTI; ?>) {
           jQuery('#scan-multithread-size').show();
           jQuery('#scan-chunk-size-label').show();
       } else {
           jQuery('#scan-multithread-size').hide();
           jQuery('#scan-chunk-size-label').hide();
       }

    }

    DupPro.UI.IsSaveViewState = true;
    /*  Toggle MetaBoxes */
    DupPro.UI.ToggleMetaBox = function ()
    {
       var $title = jQuery(this);
       var $panel = $title.parent().find('.dup-box-panel');
       var $arrowParent = $title.parent().find('.dup-box-arrow');
       var $arrow = $title.parent().find('.dup-box-arrow i');
       var key = $panel.attr('id');
       var value = $panel.is(":visible") ? 0 : 1;
       $panel.toggle();

       if (DupPro.UI.IsSaveViewState) {
           DupPro.UI.SaveViewStateByPost(key, value);
       }

       if (value) {
           $arrowParent.attr("aria-expanded", true);
           $arrow.removeClass().addClass('fa fa-caret-up');
       } else {
           $arrowParent.attr("aria-expanded", false);
           $arrow.removeClass().addClass('fa fa-caret-down');
       }

       return false;
    }

    DupPro.UI.ClearTraceLog = function (reload)
    {
       var reload = reload || 0;
       jQuery.ajax({
           type: "POST",
           url: ajaxurl,
           data: {
               action: 'duplicator_pro_delete_trace_log',
               nonce: '<?php echo wp_create_nonce('duplicator_pro_delete_trace_log'); ?>'
           },
           success: function (respData) {
               if (reload && respData.success) {
                   window.location.reload();
               }
           },
           error: function (data) {}
       });
       return false;
    }

    /*  Toggle Password input */
    DupPro.UI.TogglePasswordDisplay = function (display, inputID)
    {
       if (display) {
           document.getElementById(inputID).type = "text";
       } else {
           document.getElementById(inputID).type = "password";
       }
    }

    /* Clock generator, used to show an active clock.
    * Intended use is to be called once per page load
    * such as:
    *      <div id="dpro-clock-container"></div>
    *      DupPro.UI.Clock(DupPro._WordPressInitTime); */
    DupPro.UI.Clock = function ()
    {
       var timeDiff;
       var timeout;

       function addZ(n) {
           return (n < 10 ? '0' : '') + n;
       }

       function formatTime(d) {
           return addZ(d.getHours()) + ':' + addZ(d.getMinutes()) + ':' + addZ(d.getSeconds());
       }

       return function (s) {

           var now = new Date();
           var then;
           // Set lag to just after next full second
           var lag = 1015 - now.getMilliseconds();

           // Get the time difference when first run
           if (s) {
               s = s.split(':');
               then = new Date(now);
               then.setHours(+s[0], +s[1], +s[2], 0);
               timeDiff = now - then;
           }

           now = new Date(now - timeDiff);
           jQuery('#dpro-clock-container').html(formatTime(now));
           timeout = setTimeout(DupPro.UI.Clock, lag);
       };
    }();

    /*  Runs callback function when form values change */
    DupPro.UI.formOnChangeValues = function(form, callback) {
        let previousValues = form.serialize();

        $('form :input').on('change input', function() {
            if (previousValues !== form.serialize()) {
                previousValues = form.serialize();
                callback();
            }
        });

        $('.dup-pseudo-checkbox, #dbnone, #dball').on('click', function() {
            if (previousValues !== form.serialize()) {
                previousValues = form.serialize();
                callback();
            }
        });
    };

})(jQuery);
</script>
