<?php
/**
 * @author Serge Rodovnichenko <sergerod@gmail.com>
 * @license http://www.webasyst.com/terms/#eula Webasyst Commercial
 */
class shopSyrrepordersPluginBackendReportweekdaysdataController extends waJsonController
{
    public function execute()
    {
        $timeframe = shopReportsSalesAction::getTimeframeParams();

        $Setting = new waAppSettingsModel();
        $workflow = shopWorkflow::getConfig();
        $all_states = array_keys($workflow['states']);

        $weekdays_states = waRequest::post('weekdays_state');
        if ($weekdays_states) {
            $Setting->set(array('shop', 'syrreporders'), 'weekdays_states', serialize($weekdays_states));
        }

        $conditions = array(
            'start_date'      => $timeframe[0],
            'end_date'        => $timeframe[1],
            'weekdays_states' => $weekdays_states ?: $all_states,
        );

        $Order = new shopSyrreporderspluginorderModel();
        $this->response = $Order->getOrderStatsByDow($conditions);
    }
}
