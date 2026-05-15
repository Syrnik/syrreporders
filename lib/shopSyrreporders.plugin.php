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

        if (version_compare(wa()->whichUI(), '2.0', '>=')) {
            $html = "<script>\n";
            if (empty($settings['report_orders']) && empty($settings['report_weekdays'])) {
                return [];
            }
            if (!empty($settings['report_orders'])) {
                $html .= <<<HTML
$.reports.syrordersAction = function(){
            this.load("?plugin=syrreporders&action=report"+this.getTimeframeParams());
        };
HTML;
            }
            if (!empty($settings['report_weekdays'])) {
                $html .= <<<HTML
        $.reports.syrweekdaysAction = function(){
            this.load("?plugin=syrreporders&action=reportweekdays"+this.getTimeframeParams());
        };
HTML;
            }

            $html .= "</script>";
            return ['html' => $html];
        } else {
            return ['menu_li' => $content];
        }
    }

    public function backendExtendedMenu(array $params): void
    {
        $settings = $this->getSettings();
        if (isset($params['menu']['reports']['submenu'])) {
            $shop_backend_url = wa('shop')->getAppUrl(null, true);

            if (!empty($settings['report_orders'])) {
                $params['menu']['reports']['submenu'][] = [
                    'name' => _wp('Orders'),
                    'url' => "$shop_backend_url?action=reports#/syrorders/"
                ];
            }
            if (!empty($settings['report_weekdays'])) {
                $params['menu']['reports']['submenu'][] = [
                    'name' => _wp('Weekdays'),
                    'url' => "$shop_backend_url?action=reports#/syrweekdays/"
                ];
            }
        }
    }
}
