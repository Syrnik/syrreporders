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

    /** @var shopSyrreporderspluginorderModel */
    private $Order;

    /** @var waAppSettingsModel */
    private $Setting;

    public function __construct($params = null)
    {
        $this->Order = new shopSyrreporderspluginorderModel();
        $this->Setting = new waAppSettingsModel();

        parent::__construct($params);
    }

    public function execute()
    {
        $conditions = array_combine(array('start_date', 'end_date', 'group'), shopReportsSalesAction::getTimeframeParams());
        $currency = wa()->getConfig()->getCurrency();
        $max_sales = 0;
        $max_orders = 0;

        $workflow = shopWorkflow::getConfig();
        $conditions["orders_states"] = unserialize($this->Setting->get(array('shop', 'syrreporders'), 'orders_states', serialize(array_keys($workflow["states"]))));

        $table_data = $this->Order->getOrderStats($conditions);

        foreach($table_data as $row) {
            $max_sales = max($max_sales, (float)$row['total']);
            $max_orders = max($max_orders, (float)$row['count']);
        }

        foreach($table_data as $k=>$row) {
            $table_data[$k]['total_percent'] = $max_sales ? ($row['total']*100 / ifempty($max_sales, 1)) : 0;
            $sales_data[] = array($row['date'], (float)$row['total']);
            $count_data[] = array($row['date'], (float)$row['count']);
            $shipping_data[] = array($row['date'], (float)$row['shipping']);
            $discount_data[] = array($row['date'], (float)$row['discount']);
            $tax_data[] = array($row['date'], (float)$row['tax']);
        }

        $this->view->assign('chart_data', array(
            'sales'=>$sales_data,
            'count'=>$count_data,
            'shipping'=>$shipping_data,
            'discount'=>$discount_data,
            'tax'=>$tax_data,
            ));

        $this->view->assign(compact('currency', 'table_data'));

        $this->view->assign('group_by', $conditions['group']);
        $this->view->assign('orders_graph', unserialize($this->Setting->get(array('shop', 'syrreporders'), 'orders_graph', serialize($this->default_graphs))));
        $this->view->assign('orders_states', $conditions["orders_states"]);
        $this->view->assign('orderStates', $workflow['states']);
    }

}
