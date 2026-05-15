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

        $workflow   = shopWorkflow::getConfig();
        $all_states = array_keys($workflow['states']);

        $orders_states = waRequest::post('orders_state');

        $conditions = array(
            'start_date'    => $timeframe[0],
            'end_date'      => $timeframe[1],
            'group'         => $timeframe[2],
            'orders_states' => $orders_states ?: $all_states,
        );

        $Order = new shopSyrreporderspluginorderModel();
        $rows  = $Order->getOrderStats($conditions);

        $count_data = $sales_data = $shipping_data = $discount_data = $tax_data = $avg_data = $table_rows = array();

        foreach ($rows as $row) {
            $count = (float)$row['count'];
            $total = (float)$row['total'];
            $ship  = (float)$row['shipping'];
            $disc  = (float)$row['discount'];
            $tax   = (float)$row['tax'];

            $count_data[]    = array($row['date'], $count);
            $sales_data[]    = array($row['date'], $total);
            $shipping_data[] = array($row['date'], $ship);
            $discount_data[] = array($row['date'], $disc);
            $tax_data[]      = array($row['date'], $tax);
            $avg_data[]      = array($row['date'], $count > 0 ? $total / $count : 0);

            $table_rows[] = array(
                'date'     => $row['date'],
                'count'    => $count,
                'total'    => $total,
                'shipping' => $ship,
                'discount' => $disc,
                'tax'      => $tax,
            );
        }

        $this->response = array(
            'count'           => $count_data,
            'sales'           => $sales_data,
            'shipping'        => $shipping_data,
            'discount'        => $discount_data,
            'tax'             => $tax_data,
            'avg_ticket_price'=> $avg_data,
            'table'           => $table_rows,
        );
    }
}
