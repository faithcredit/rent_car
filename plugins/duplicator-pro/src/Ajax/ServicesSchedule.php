<?php

/**
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Ajax;

use DUP_PRO_Handler;
use DUP_PRO_Log;
use DUP_PRO_Package;
use DUP_PRO_Package_Runner;
use DUP_PRO_Schedule_Entity;
use DUP_PRO_U;
use Duplicator\Core\CapMng;
use Error;
use Exception;
use stdClass;

class ServicesSchedule extends AbstractAjaxService
{
    const SCHEDULE_BULK_DELETE     = 1;
    const SCHEDULE_BULK_ACTIVATE   = 2;
    const SCHEDULE_BULK_DEACTIVATE = 3;

    /**
     * Init ajax calls
     *
     * @return void
     */
    public function init()
    {
        $this->addAjaxCall('wp_ajax_duplicator_pro_schedule_bulk_action', 'bulkAction');
        $this->addAjaxCall('wp_ajax_duplicator_pro_get_schedule_infos', 'getScheduleInfo');
        $this->addAjaxCall('wp_ajax_duplicator_pro_run_schedule_now', 'runScheduleNow');
    }

    /**
     * Schedule bulk actions
     *
     * @return void
     */
    public function bulkAction()
    {
        DUP_PRO_Handler::init_error_handler();
        check_ajax_referer('duplicator_pro_schedule_bulk_action', 'nonce');

        $isValid     = true;
        $json        = array(
            'success' => true,
            'message' => '',
        );
        $inputData   = filter_input_array(INPUT_POST, array(
            'schedule_ids' => array(
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
        $scheduleIDs = $inputData['schedule_ids'];
        $action      = $inputData['perform'];

        if (empty($scheduleIDs) || in_array(false, $scheduleIDs) || $action === false) {
            $isValid = false;
        }

        try {
            CapMng::can(CapMng::CAP_SCHEDULE);

            if (!$isValid) {
                throw new Exception(DUP_PRO_U::__("Invalid Request."));
            }

            foreach ($scheduleIDs as $id) {
                switch ($action) {
                    case self::SCHEDULE_BULK_DELETE:
                        DUP_PRO_Schedule_Entity::deleteById($id);
                        break;
                    case self::SCHEDULE_BULK_ACTIVATE:
                        $schedule = DUP_PRO_Schedule_Entity::getById($id);
                        if (count($schedule->storage_ids) === 0) {
                            $json['success']  = false;
                            $json['message'] .= "Could not activate schedule with ID " . $schedule->getId() .
                                " because it has no Storages.<br>";
                        } else {
                            $schedule->active = true;
                            $schedule->save();
                        }
                        break;
                    case self::SCHEDULE_BULK_DEACTIVATE:
                        $schedule         = DUP_PRO_Schedule_Entity::getById($id);
                        $schedule->active = false;
                        $schedule->save();
                        break;
                    default:
                        throw new Exception("Invalid schedule bulk action.");
                }
            }
        } catch (Exception $ex) {
            $json['success'] = false;
            $json['message'] = $ex->getMessage();
        }

        die(json_encode($json));
    }

    /**
     * Get schedule info action
     *
     * { schedule_id, is_running=true|false, last_ran_string}
     *
     * @return void
     */
    public function getScheduleInfo()
    {
        DUP_PRO_Handler::init_error_handler();
        check_ajax_referer('duplicator_pro_get_schedule_infos', 'nonce');
        CapMng::can(CapMng::CAP_SCHEDULE);
        $schedules      = DUP_PRO_Schedule_Entity::getAll();
        $schedule_infos = array();

        if (count($schedules) > 0) {
            $package = DUP_PRO_Package::get_next_active_package();

            foreach ($schedules as $schedule) {
                $schedule_info = new stdClass();

                $schedule_info->schedule_id     = $schedule->getId();
                $schedule_info->last_ran_string = $schedule->get_last_ran_string();

                if ($package != null) {
                    $schedule_info->is_running = ($package->schedule_id == $schedule->getId());
                } else {
                    $schedule_info->is_running = false;
                }

                array_push($schedule_infos, $schedule_info);
            }
        }

        $json_response = json_encode($schedule_infos);
        die($json_response);
    }

    /**
     * Run schedule action
     *
     * @return void
     */
    public function runScheduleNow()
    {
        DUP_PRO_Handler::init_error_handler();
        check_ajax_referer('duplicator_pro_run_schedule_now', 'nonce');

        $json = array(
            'success' => false,
            'message' => '',
        );

        try {
            CapMng::can(CapMng::CAP_SCHEDULE);
            $schedule_id = filter_input(INPUT_POST, 'schedule_id', FILTER_VALIDATE_INT);

            if ($schedule_id === false) {
                throw new Exception(DUP_PRO_U::__("Invalid schedule id"));
            }

            $schedule = DUP_PRO_Schedule_Entity::getById($schedule_id);

            if ($schedule == false) {
                DUP_PRO_Log::trace("Attempted to queue up a job for non existent schedule $schedule_id");
                throw new Exception(DUP_PRO_U::__("Invalid schedule id"));
            }

            DUP_PRO_Log::trace("Inserting new package for schedule $schedule->name due to manual request");
            // Just inserting it is enough since init() will automatically pick it up and schedule a cron in the near future.
            $schedule->insert_new_package(true);
            DUP_PRO_Package_Runner::kick_off_worker();

            $json = array(
                'success' => true,
                'message' => '',
            );
        } catch (Exception $e) {
            $json['success'] = false;
            $json['message'] = $e->getMessage();
        } catch (Error $e) {
            $json['success'] = false;
            $json['message'] = $e->getMessage();
        }

        die(json_encode($json));
    }
}
