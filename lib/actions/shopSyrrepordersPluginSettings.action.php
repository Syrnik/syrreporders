<?php
/**
 * @author Serge Rodovnichenko <sergerod@gmail.com>
 * 
 * @license http://www.webasyst.com/terms/#eula Webasyst Commercial
 * @version 1.0.0
 */

/**
 * Settings Action
 *
 * @package webasyst.shop.plugin.syrreporders.controller
 */
class shopSyrrepordersPluginSettingsAction extends waViewAction
{
    public function execute()
    {
        $defaults = array(
            "report_orders" => 1,
            "report_weekdays" => 1
        );
        
        $Setting = new waAppSettingsModel();
        
        $settings = $Setting->get(array('shop', 'syrreporders'));
        if(!is_array($settings)) {
            $settings = array();
        }
        
        $this->view->assign('settings', array_merge($defaults, $settings));
    }
}
