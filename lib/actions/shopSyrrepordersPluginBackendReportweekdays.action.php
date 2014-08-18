<?php
/**
 * @author Serge Rodovnichenko <sergerod@gmail.com>
 *
 * @license http://www.webasyst.com/terms/#eula Webasyst Commercial
 * @version 1.1.0
 */

/**
 * Report Weekdays Action
 *
 * @package webasyst.shop.plugin.syrreporders.controller
 */
class shopSyrrepordersPluginBackendReportweekdaysAction extends waViewAction
{
    public function execute()
    {
        $timeframe = array_combine(array('start_date', 'end_date', 'group'), shopReportsSalesAction::getTimeframeParams());
        $Order = new shopSyrreporderspluginorderModel();
        $default_currency = wa()->getConfig()->getCurrency();

        $stats["dow"] = array(
            "data" => $Order->getOrderStatsByDow($timeframe),
            "max_count" => 0,
            "max_sum" => 0,
            "top" => array(
                "count" => array(),
                "sum" => array()
            )
        );

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

        $this->view->assign('stats', $stats);
        $this->view->assign('currency', $default_currency);
    }
}
