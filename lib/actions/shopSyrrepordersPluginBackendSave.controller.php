<?php
/**
 * @author Serge Rodovnichenko <sergerod@gmail.com>
 * 
 * @license http://www.webasyst.com/terms/#eula Webasyst Commercial
 * @version 1.0.0
 */

/**
 * Settings Save Action
 *
 * @package webasyst.shop.plugin.syrreporders.controller
 */
class shopSyrrepordersPluginBackendSaveController extends waJsonController
{
    public function execute()
    {
        $Setting = new waAppSettingsModel();
        $data = waRequest::post("shop_syrreporders");
        
        $data = array_merge(array('report_orders'=>0, 'report_weekdays'=>0), $data);
        
        $Setting->set(array('shop', 'syrreporders'), 'report_orders', $data['report_orders']);
        $Setting->set(array('shop', 'syrreporders'), 'report_weekdays', $data['report_weekdays']);
        
        $this->response['message'] = _wp("Saved");
    }
}
