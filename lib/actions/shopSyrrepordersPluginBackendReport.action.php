<?php
/**
 * @author Serge Rodovnichenko <sergerod@gmail.com>
 *
 * @license http://www.webasyst.com/terms/#eula Webasyst Commercial
 * @version 1.0.0
 */

/**
 * Report Action
 *
 * @package webasyst.shop.plugin.syrreporders.controller
 */
class shopSyrrepordersPluginBackendReportAction extends waViewAction
{
    public function execute()
    {
        $timeframe = array_combine(array('start_date', 'end_date', 'group'), shopReportsSalesAction::getTimeframeParams());
        $Order = new shopSyrreporderspluginorderModel();
        $default_currency = wa()->getConfig()->getCurrency();
        $max_sales = 0;
        $max_orders = 0;
        
        $sales = $Order->getOrderStats($timeframe);
        
        $stats['dow']['data'] = $Order->getOrderStatsByDow($timeframe);
        $stats['dow']['max_count'] = 0;
        $stats['dow']['max_sum'] = 0;
        $stats['dow']['top']['count'] = array();
        $stats['dow']['top']['sum'] = array();
        
        foreach($stats['dow']['data'] as $dow) {
            
            if((float)$dow['count'] > $stats['dow']['max_count']) {
                $stats['dow']['max_count'] = (float)$dow['count'];
                $stats['dow']['top']['count'] = array($dow);
            } else if((float)$dow['count'] == $stats['dow']['max_count']) {
                $stats['dow']['top']['count'][] = $dow;
            }
            
            if((float)$dow['total'] > $stats['dow']['max_sum']) {
                $stats['dow']['max_sum'] = (float)$dow['total'];
                $stats['dow']['top']['sum'] = array($dow);
            } else if((float)$dow['total'] == $stats['dow']['max_sum']) {
                $stats['dow']['top']['sum'][] = $dow;
            }
            
        }
        
        foreach($sales as $row) {
            $max_sales = max($max_sales, (float)$row['total']);
            $max_orders = max($max_orders, (float)$row['count']);
        }
        
        $chart_data = array();
//        var_dump($sales);
        foreach($sales as $k=>$row) {
            $sales[$k]['total_percent'] = $max_sales ? ($row['total']*100 / ifempty($max_sales, 1)) : 0;
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
        $this->view->assign('currency', $default_currency);
        $this->view->assign('table_data', $sales);
        $this->view->assign('group_by', $timeframe['group']);
        $this->view->assign('stats', $stats);
    }
}
