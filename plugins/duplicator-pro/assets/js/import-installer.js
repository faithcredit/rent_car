/*! dup import installer */
(function ($) {
    DupProImportInstaller = {
        installerIframe: $('#dpro-pro-import-installer-iframe'),
        init: function () {
            DupProImportInstaller.installerIframe.on("load", function () {
                DupProImportInstaller.installerIframe.contents()
                    .find('#page-step1')
                    .on('click', '> .ui-dialog #db-install-dialog-confirm-button', function () {
                        $('#dup-pro-import-installer-modal').removeClass('no-display');
                    });
            });
        },
        resizeIframe: function () {
            let height = DupProImportInstaller.installerIframe.contents()
                .find('html').css('overflow', 'hidden')
                .outerHeight(true);
            console.log('height', height);
            DupProImportInstaller.installerIframe.css({
                'height': height + 'px'
            })
        }
    }

    DupProImportInstaller.init();
    DuplicatorTooltip.load();

})(jQuery);