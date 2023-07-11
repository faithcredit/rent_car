<?php

/*! ============================================================================
*  UI CTRL NAMESPACE: Used to store custom Javascript Controls
*  =========================================================================== */
?><script>

(function ($) {

    /**
     * Creates a flat tab re-usable tab system
     *
     * @param string id     The DOM element of the id to generate the tabs
     * @returns void
     *
     * @example:
     *
     * <script> Duplicator.UI.Ctrl.tabsFlat('tabs-flat-id'); <script>
     *
     * <div id="tabs-flat-id" class="dup-tabs-flat">
     *     <div class="data-tabs">
     *         <a href="javascript:void(0)" class="tab active">tab-1</a>
     *         <a href="javascript:void(0)" class="tab">tab-2</a>
     *     </div>
     *   <div class="data-panels">
     *       <div class="panel">p-1</div>
     *       <div class="panel">p-2</div>
     *   </div>
     * </div>
     *
     */
    Duplicator.UI.Ctrl.tabsFlat = function(id) {
        var keyTabs     = `#${id}.dup-tabs-flat > div.data-tabs`;
        var keyPnls     = `#${id}.dup-tabs-flat > div.data-panels`;
        var $panels     = $(`${keyPnls} > div.panel`);
        var startIndex  = $(`${keyTabs} > a.tab.active`).index('a.tab');

        //Register Click Event
        $(`${keyTabs} > a.tab`).on('click', function() {

            var index   = $(this).index('a.tab');

            //Hide all
            $(`${keyTabs} > a.tab`).removeClass('active');
            $(`${keyPnls} > div.panel`).hide();

            //Show active
            $(this).addClass('active');
            $($panels.get(index)).show();
        });

        //init
        $($panels.get(startIndex)).show();
    };


    /**
     * Creates a vertical tab re-usable tabbing system
     *
     * @param string id     The DOM element of the id to generate the tabs
     * @returns void
     *
     * @example:
     *
     * <script> Duplicator.UI.Ctrl.tabsVert('tab-id'); < /script>
     *
     * <div id="tab-id" class="dup-tabs-vert">
     *     <div class="data-tabs">
     *          <div class="void">text</div>
     *          <div class="tab active">tab-1</div>
     *          <div class="tab">tab-2</div>
     *      </div>
     *      <div class="data-panels">
     *          <div class="panel">panel-1</div>
     *          <div class="panel">panel-2</div>
     *      </div>
     * </div>
     *
     */
    Duplicator.UI.Ctrl.tabsVert = function(id) {

        var keyTabs     = `#${id}.dup-tabs-vert > div.data-tabs`;
        var keyPnls     = `#${id}.dup-tabs-vert > div.data-panels`;
        var $panels     = $(`${keyPnls} > div.panel`);
        var startIndex  = $(`${keyTabs} > div.tab.active`).index('div.tab');

        /*Click Event: */
        $(`${keyTabs} div.tab`).on('click', function() {

            var index   = $(this).index('div.tab');
            var $panels = $(`${keyPnls} > div.panel`);

            //Hide all
            $(`${keyTabs} > div.tab`).removeClass('active');
            $(`${keyPnls} > div.panel`).hide();

            //Show active
            $(this).addClass('active');
            $($panels.get(index)).show();
        });

        //init
        $($panels.get(startIndex)).show();
    };


    /**
     * Toggles the spinner for the help item
     *
     * @param string id     The DOM element of the id to generate the spinner
     * @param string height The CSS height of the spinner control (default 250px)
     * @param string width  The CSS width of the spinner control (default 100%)
     * @returns void
     *
     * @example
     *
     * <script>  Duplicator.UI.Ctrl.Spinner('spinner-id'); < /script>
     *
     * <div id="spinner-id" class="dup-spinner">
     *     <div class="area-left">
     *         <i class="fas fa-chevron-circle-left area-arrow"></i>
     *      </div>
     *     <div class="area-data">
     *         <div class="item active">panel-1</div>
     *         <div class="item">panel-2</div>
     *      </div>
     *      <div class="area-right">
     *          <i class="fas fa-chevron-circle-right"></i>
     *      </div>
     *      <div class="area-nav">
     *          <span class="num"></span>
     *          <progress class="progress"></progress>
     *     </div>
     * </div>
     *
     */
    Duplicator.UI.Ctrl.Spinner = class {

        //Setup the Object
        constructor(id, height='350px', width='100%') {
            this.id         = id;
            this.height     = height;
            this.width      = width;
            this.items      = $(`#${id} > .area-data > .item`);
            this.maxItems   = this.items.length - 1;
            this.init();
        }

        //Initilize the object for use
        init() {
            var id       = this.id;
            var $items   = this.items;
            var max      = this.maxItems
            var start    = $(`#${id} .item.active`).index() + 1;

            /*Click Event: Left/Right */
            $(`#${id} > .area-right, #${id} > .area-left`).on('click', function() {

                var index = $(`#${id} .item.active`).index();

                //Left or Right
                index = ($(this).hasClass('area-right'))
                    ?  (index >= max)  ? 0   : index + 1
                    :  (index === 0)   ? max : index - 1;

                $items.removeClass('active').hide();
                $($items[index]).addClass('active').show();

                //Progress bar
                $(`#${id} .num`).html(`${index + 1} of ${max + 1}`);
                $(`#${id} progress`).val(index + 1);
            });

            //init
            $(`#${id} div.area-nav progress`).attr('max', `${max + 1}`);
            $(`#${id} div.area-nav progress`).attr('value', '1');
            $(`#${id} div.area-nav .num`).html(`${start} of ${max + 1}`);
            $(`#${id}.dup-spinner`).css({'height' : this.height, 'width' : this.width});
        }

        //Set the active panel to the index provided
        setPanel(index) {
            this.items.removeClass('active').hide();
            $(this.items.get(index)).addClass('active').show();
            $(`#${this.id} .num`).html(`${index + 1} of ${this.maxItems + 1}`);
            $(`#${this.id} progress`).val(index + 1);
        }
    };
})(jQuery);
</script>
