<?php

/**
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Ajax;

use DUP_PRO_Handler;
use DUP_PRO_Schedule_Entity;
use DUP_PRO_Storage_Entity;
use DUP_PRO_U;
use Duplicator\Controllers\SchedulePageController;
use Duplicator\Core\CapMng;
use Exception;

class ServicesStorage extends AbstractAjaxService
{
    const STORAGE_BULK_DELETE   = 1;
    const STORAGE_GET_SCHEDULES = 5;

    /**
     * Init ajax calls
     *
     * @return void
     */
    public function init()
    {
        $this->addAjaxCall("wp_ajax_duplicator_pro_storage_bulk_actions", "bulkActions");
    }

    /**
     * Storage bulk actions handler
     *
     * @return void
     * @throws \Exception
     */
    public function bulkActions()
    {
        DUP_PRO_Handler::init_error_handler();
        check_ajax_referer('duplicator_pro_storage_bulk_actions', 'nonce');

        $json       = array(
            'success'   => false,
            'message'   => '',
            'schedules' => array()
        );
        $isValid    = true;
        $inputData  = filter_input_array(INPUT_POST, array(
            'storage_ids' => array(
                'filter'  => FILTER_VALIDATE_INT,
                'flags'   => FILTER_REQUIRE_ARRAY,
                'options' => array(
                    'default' => false
                )
            ),
            'perform' => array(
                'filter'  => FILTER_VALIDATE_INT,
                'flags'   => FILTER_REQUIRE_SCALAR,
                'options' => array(
                    'default' => false
                )
            )
        ));
        $storageIDs = $inputData['storage_ids'];
        $action     = $inputData['perform'];

        if (empty($storageIDs) || in_array(false, $storageIDs) || $action === false) {
            $isValid = false;
        }

        try {
            CapMng::can(CapMng::CAP_STORAGE);

            if (!$isValid) {
                throw new \Exception(DUP_PRO_U::__("Invalid Request."));
            }

            foreach ($storageIDs as $id) {
                switch ($action) {
                    case self::STORAGE_BULK_DELETE:
                        DUP_PRO_Storage_Entity::delete_by_id($id);
                        break;
                    case self::STORAGE_GET_SCHEDULES:
                        foreach (DUP_PRO_Schedule_Entity::get_schedules_by_storage_id($id) as $schedule) {
                            $json["schedules"][] = array(
                                "id"            => $schedule->getId(),
                                "name"          => $schedule->name,
                                "hasOneStorage" => count($schedule->storage_ids) <= 1,
                                "editURL"       => SchedulePageController::getInstance()->getEditUrl($schedule->getId())
                            );
                        }
                        break;
                    default:
                        throw new \Exception("Invalid action.");
                }
            }
            //SORT_REGULAR allows to do array_unique on multidimensional arrays
            $json["schedules"] = array_unique($json["schedules"], SORT_REGULAR);
            $json["success"]   = true;
        } catch (\Exception $ex) {
            $json['message'] = $ex->getMessage();
        }

        die(json_encode($json));
    }
}
