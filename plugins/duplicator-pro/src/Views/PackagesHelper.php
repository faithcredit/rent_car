<?php

namespace Duplicator\Views;

use Duplicator\Core\Views\TplMng;

class PackagesHelper
{
    /**
     * render package row
     *
     * @param \DUP_PRO_Package $package package of current row
     *
     * @return void
     */
    public static function tablePackageRow(\DUP_PRO_Package $package)
    {
        $tplMng = TplMng::getInstance();
        $tplMng->render(
            'admin_pages/packages/package_row',
            [
                'package' => $package
            ]
        );
    }
}
