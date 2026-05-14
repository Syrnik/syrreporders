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
        wa()->getResponse()
                ->addJs('wa-content/js/jquery-plugins/jquery-plot/plugins/jqplot.barRenderer.min.js')
                ->addJs('wa-content/js/jquery-plugins/jquery-plot/plugins/jqplot.categoryAxisRenderer.min.js')
                ->addJs('wa-content/js/jquery-plugins/jquery-plot/plugins/jqplot.pointLabels.min.js');
        return array('menu_li' => $content);
    }

}
