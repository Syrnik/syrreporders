<?php
/**
 * @author Serge Rodovnichenko <sergerod@gmail.com>
 *
 * @license http://www.webasyst.com/terms/#eula Webasyst Commercial
 * @version 1.1.0
 */

/**
 * Report Action
 *
 * @package webasyst.shop.plugin.syrreporders.controller
 */
class shopSyrrepordersPluginBackendReportAction extends waViewAction
{

    private $default_graphs = array('count'=>1, 'totals'=>1, 'shipping'=>1, 'discount'=>1, 'tax'=>1);

    public function execute()
    {
        $timeframe = array_combine(array('start_date', 'end_date', 'group'), shopReportsSalesAction::getTimeframeParams());
        $default_currency = wa()->getConfig()->getCurrency();
        $max_sales = 0;
        $max_orders = 0;

        $Order = new shopSyrreporderspluginorderModel();
        $Setting = new waAppSettingsModel();

        $sales = $Order->getOrderStats($timeframe);

        foreach($sales as $row) {
            $max_sales = max($max_sales, (float)$row['total']);
            $max_orders = max($max_orders, (float)$row['count']);
        }

        $chart_data = array();

        foreach($sales as $k=>$row) {
            $sales[$k]['total_percent'] = $max_sales ? ($row['total']*100 / ifempty($max_sales, 1)) : 0;
            $sales_data[] = array($row['date'], (float)$row['total']);
            $count_data[] = array($row['date'], (float)$row['count']);
            $shipping_data[] = array($row['date'], (float)$row['shipping']);
            $discount_data[] = array($row['date'], (float)$row['discount']);
            $tax_data[] = array($row['date'], (float)$row['tax']);
        }
        
        $wf = shopWorkflow::getConfig();
//        var_dump($wf['states']['new']);

        $this->view->assign('chart_data', array(
            'sales'=>$sales_data,
            'count'=>$count_data,
            'shipping'=>$shipping_data,
            'discount'=>$discount_data,
            'tax'=>$tax_data,
            ));
        $this->view->assign('currency', $default_currency);
        $this->view->assign('table_data', $sales);
        $this->view->assign('group_by', $timeframe['group']);
        $this->view->assign('orders_graph', unserialize($Setting->get(array('shop', 'syrreporders'), 'orders_graph', serialize($this->default_graphs))));
        $this->view->assign('orderStates', $wf['states']);
    }

}
