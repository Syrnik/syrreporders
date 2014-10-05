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
        $workflow = shopWorkflow::getConfig();
        
        $conditions["weekdays_states"] = unserialize($this->Setting->get(array('shop', 'syrreporders'), 'weekdays_states', serialize(array_keys($workflow["states"]))));

        $stats["dow"] = array(
            "data" => $this->Order->getOrderStatsByDow($conditions),
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

        $this->view->assign(compact('stats', 'currency'));
        $this->view->assign('all_order_states', $workflow['states']);
        $this->view->assign('weekdays_states', $conditions["weekdays_states"]);
        
    }
}
