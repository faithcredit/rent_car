/*! dup admin script */
jQuery(document).ready(function ($) {
    $('.duplicator-pro-admin-notice[data-to-dismiss]').each(function () {
        var notice = $(this);
        var notice_to_dismiss = notice.data('to-dismiss');

        notice.find('.notice-dismiss').on('click', function (event) {
            event.preventDefault();
            $.post(ajaxurl, {
                action: 'duplicator_pro_admin_notice_to_dismiss',
                notice: notice_to_dismiss,
                nonce: dup_pro_global_script_data.nonce_admin_notice_to_dismiss
            });
        });
    });

    function dupDashboardUpdate() {
        jQuery.ajax({
            type: "POST",
            url: dup_pro_global_script_data.ajaxurl,
            dataType: "json",
            data: {
                action: 'duplicator_pro_dashboad_widget_info',
                nonce: dup_pro_global_script_data.nonce_dashboard_widged_info
            },
            success: function (result, textStatus, jqXHR) {
                if (result.success) {
                    $('#duplicator_dashboard_widget .dup-last-backup-info').html(result.data.funcData.lastBackupInfo);

                    if (result.data.funcData.isRunning) {
                        $('#duplicator_dashboard_widget #dup-pro-create-new').addClass('disabled');
                    } else {
                        $('#duplicator_dashboard_widget #dup-pro-create-new').removeClass('disabled');
                    }
                }
            },
            complete: function() {
                setTimeout(
                    function(){
                        dupDashboardUpdate();
                    }, 
                    5000
                );
            }
        });
    }
    
    if ($('#duplicator_dashboard_widget').length) {
        dupDashboardUpdate();

        $('#duplicator_dashboard_widget #dup-dash-widget-section-recommended').on('click', function (event) {
            event.stopPropagation();
            
            $(this).closest('.dup-section-recommended').fadeOut();

            jQuery.ajax({
                type: "POST",
                url: dup_pro_global_script_data.ajaxurl,
                dataType: "json",
                data: {
                    action: 'duplicator_pro_dismiss_recommended_plugin',
                    nonce: dup_pro_global_script_data.nonce_dashboard_widged_dismiss_recommended
                },
                success: function (result, textStatus, jqXHR) {
                    // do nothing
                }
            });
        });
    }
});
