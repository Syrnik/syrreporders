<?php
/**
 * @author Serge Rodovnichenko <sergerod@gmail.com>
 *
 * @license http://www.webasyst.com/terms/#eula Webasyst Commercial
 * @version 2.3.0
 *
 * 2.3.0 - Filters moved to localStorage; data loaded via AJAX on page load
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
        $currency = wa()->getConfig()->getCurrency();
        $workflow = shopWorkflow::getConfig();

        $this->view->assign('currency',         $currency);
        $this->view->assign('all_order_states', $workflow['states']);
    }
}
