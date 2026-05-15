<?php
/**
 * @author Serge Rodovnichenko <sergerod@gmail.com>
 * @license http://www.webasyst.com/terms/#eula Webasyst Commercial
 */
class shopSyrrepordersPluginBackendReportdataController extends waJsonController
{
    public function execute()
    {
        $timeframe = shopReportsSalesAction::getTimeframeParams();

        $Setting = new waAppSettingsModel();
        $workflow = shopWorkflow::getConfig();
        $all_states = array_keys($workflow['states']);

        $graph_settings = waRequest::post('orders_graph');
        $orders_states  = waRequest::post('orders_state');

        if ($graph_settings) {
            $Setting->set(array('shop', 'syrreporders'), 'orders_graph', serialize($graph_settings));
        }
        if ($orders_states) {
            $Setting->set(array('shop', 'syrreporders'), 'orders_states', serialize($orders_states));
        }

        $conditions = array(
            'start_date'    => $timeframe[0],
            'end_date'      => $timeframe[1],
            'group'         => $timeframe[2],
            'orders_states' => $orders_states ?: $all_states,
        );

        $Order = new shopSyrreporderspluginorderModel();
        $rows  = $Order->getOrderStats($conditions);

        $count_data = $sales_data = $shipping_data = $discount_data = $tax_data = $avg_data = array();

        foreach ($rows as $row) {
            $count = (float)$row['count'];
            $total = (float)$row['total'];
            $count_data[]   = array($row['date'], $count);
            $sales_data[]   = array($row['date'], $total);
            $shipping_data[]= array($row['date'], (float)$row['shipping']);
            $discount_data[]= array($row['date'], (float)$row['discount']);
            $tax_data[]     = array($row['date'], (float)$row['tax']);
            $avg_data[]     = array($row['date'], $count > 0 ? $total / $count : 0);
        }

        $this->response = array(
            'count'           => $count_data,
            'sales'           => $sales_data,
            'shipping'        => $shipping_data,
            'discount'        => $discount_data,
            'tax'             => $tax_data,
            'avg_ticket_price'=> $avg_data,
        );
    }
}
