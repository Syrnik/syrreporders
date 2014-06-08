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
        }
        
        $chart_data = array();
        
        foreach($sales as $k=>$row) {
            $sales[$k]['total_percent'] = $max_sales ? ($row['total']*100 / ifempty($max_sales, 1)) : 0;
            $chart_data[] = array($row['date'], $row['total']);
        }
        
        $this->view->assign('chart_data', $chart_data);
        $this->view->assign('group_by', $timeframe['group']);
        $this->view->assign('stats', $stats);
    }
}
