<?php

defined("ABSPATH") or die("");

$inner_page = isset($_REQUEST['inner_page']) ? sanitize_text_field($_REQUEST['inner_page']) : 'templates';

switch ($inner_page) {
    case 'templates':
        include(DUPLICATOR____PATH . '/views/tools/templates/template.list.php');
        break;
    case 'edit':
        include(DUPLICATOR____PATH . '/views/tools/templates/template.edit.php');
        break;
}
