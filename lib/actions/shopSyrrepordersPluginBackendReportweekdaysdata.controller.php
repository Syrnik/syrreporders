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

        $workflow   = shopWorkflow::getConfig();
        $all_states = array_keys($workflow['states']);

        $weekdays_states = waRequest::post('weekdays_state');

        $conditions = array(
            'start_date'      => $timeframe[0],
            'end_date'        => $timeframe[1],
            'weekdays_states' => $weekdays_states ?: $all_states,
        );

        $Order    = new shopSyrreporderspluginorderModel();
        $dow_data = $Order->getOrderStatsByDow($conditions);

        $max_count = 0;
        $max_sum   = 0;
        $top_count = array();
        $top_sum   = array();

        foreach ($dow_data as $dow) {
            $c = (float)$dow['count'];
            $s = (float)$dow['total'];

            if ($c > $max_count) {
                $max_count = $c;
                $top_count = array($dow);
            } elseif ($c == $max_count && $max_count > 0) {
                $top_count[] = $dow;
            }

            if ($s > $max_sum) {
                $max_sum = $s;
                $top_sum = array($dow);
            } elseif ($s == $max_sum && $max_sum > 0) {
                $top_sum[] = $dow;
            }
        }

        $this->response = array(
            'dow' => array_values($dow_data),
            'top' => array(
                'count' => array_values($top_count),
                'sum'   => array_values($top_sum),
            ),
        );
    }
}
