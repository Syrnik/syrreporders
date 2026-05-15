<?php
/**
 * @author Serge Rodovnichenko <sergerod@gmail.com>
 *
 * @license http://www.webasyst.com/terms/#eula Webasyst Commercial
 * @copyright Serge Rodovnichenko <serge@syrnik.com>, 2014-2026
 */

/**
 * Main plugin
 *
 * @package webasyst.shop.plugin.syrreporders
 */
class shopSyrrepordersPlugin extends shopPlugin
{
    const PLUGIN_ID = 'syrreporders';

    /**
     * Handler for backend_reports hook
     *
     * @return array
     * @throws SmartyException
     * @throws waException
     */
    public function backendReports(): array
    {
        $view = wa()->getView();
        $settings = $this->getSettings();
        $view->assign('settings', $settings);
        $content = $view->fetch($this->path . "/templates/menuitem.html");
        $this->addCss('css/syrreporders.css');
        $this->addJs('js/syrreporders-orders.js');
        $this->addJs('js/syrreporders-weekdays.js');
        return ['menu_li' => $content];
    }
}
