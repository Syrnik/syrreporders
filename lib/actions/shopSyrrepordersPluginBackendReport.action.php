<?php
/**
 * @author Serge Rodovnichenko <sergerod@gmail.com>
 *
 * @license http://www.webasyst.com/terms/#eula Webasyst Commercial
 * @version 2.2.0
 * 
 * 2.1.0 - Filter by orders states. Fixed bug with last day of period
 * 2.2.0 - Average Ticket price graph added
 */

/**
 * Report Action
 *
 * @package webasyst.shop.plugin.syrreporders.controller
 */
class shopSyrrepordersPluginBackendReportAction extends waViewAction
{

    /** @var array Graphs shown by default */
    private $default_graphs = array(
        'count'=>1,
        'totals'=>1,
        'shipping'=>1,
        'discount'=>1,
        'tax'=>1,
        'avg_ticket_price'=>1
        );

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
            // Move to the DB query???
            $avg_ticket_price_data[] = array($row['date'], ((float)$row['count'] > 0 ? (float)$row['total'] / (float)$row['count'] : 0 ));
            $table_data[$k]["avg_ticket_price"] = (float)$row['count'] > 0 ? (float)$row['total'] / (float)$row['count'] : 0;
        }

        $this->view->assign('chart_data', array(
            'sales'=>$sales_data,
            'count'=>$count_data,
            'shipping'=>$shipping_data,
            'discount'=>$discount_data,
            'tax'=>$tax_data,
            'avg_ticket_price' => $avg_ticket_price_data
            ));

        $this->view->assign(compact('currency', 'table_data'));

        $this->view->assign('group_by', $conditions['group']);
        $this->view->assign('orders_graph', $this->getOrderGraphsSettings());
        $this->view->assign('orders_states', $conditions["orders_states"]);
        $this->view->assign('orderStates', $workflow['states']);
    }
    
    /**
     * To show new graphs that aren't configured yet
     * 
     * @return array
     */
    private function getOrderGraphsSettings()
    {
        $order_graphs_settings = unserialize($this->Setting->get(array('shop', 'syrreporders'), 'orders_graph', serialize($this->default_graphs)));
        return array_merge($this->default_graphs, $order_graphs_settings);
    }

}
