<?php
/**
 * @author Serge Rodovnichenko <sergerod@gmail.com>
 * 
 * @license http://www.webasyst.com/terms/#eula Webasyst Commercial
 * @version 2.2.1
 */

/**
 * Main plugin
 *
 * @package webasyst.shop.plugin.syrreporders
 */
class shopSyrrepordersPlugin extends shopPlugin
{
    const PLUGIN_ID='syrreporders';

    /**
     * Handler for backend_reports hook
     * 
     * @return array
     */
    public function backendReports()
    {
        $view = wa()->getView();
        $settings = $this->getSettings();
        $view->assign('settings', $settings);
        $content = $view->fetch($this->path . "/templates/menuitem.html");
        $response = wa()->getResponse();
        $response->addCss('wa-apps/shop/plugins/syrreporders/css/syrreporders.css');
        $response->addJs('wa-apps/shop/plugins/syrreporders/js/chartjs/chart.umd.min.js');
        $response->addJs('wa-apps/shop/plugins/syrreporders/js/chartjs/chartjs-adapter-date-fns.bundle.min.js');
        $response->addJs('wa-apps/shop/plugins/syrreporders/js/syrreporders-orders.js?v=2.4.0');
        $response->addJs('wa-apps/shop/plugins/syrreporders/js/syrreporders-weekdays.js?v=2.4.0');
        return array('menu_li' => $content);
    }

}
