<?php
/**
 * Block counters: module for PrestaShop.
 *
 * @author    Maksim T. <zapalm@yandex.com>
 * @copyright 2010 Maksim T.
 * @link      https://prestashop.modulez.ru/en/frontend-features/43-javascript-code-addition-helper.html The module's homepage
 * @license   https://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * @inheritdoc
 */
class BlockCounterz extends Module
{
    /** The product ID of the module on its homepage. */
    const HOMEPAGE_PRODUCT_ID = 43;

    /** Settings option: content */
    const CONF_CONTENT = 'BLOCKCOUNTERZ_CONTENT';

    /**
     * @inheritdoc
     */
    public function __construct()
    {
        $this->name    = 'blockcounterz';
        $this->tab     = 'Blocks';
        $this->version = '1.2.0';
        $this->author  = 'zapalm';

        parent::__construct();

        $this->displayName = $this->l('JavaScript code addition helper');
        $this->description = $this->l('Helps to add any JavaScript code, for example, analytic counter, advertisement or other script.');
    }

    /**
     * @inheritdoc
     */
    public function install()
    {
        $content = base64_encode('<!--noindex-->' . PHP_EOL . '<!--/noindex-->');
        Configuration::updateValue(self::CONF_CONTENT, $content);

        $result = parent::install()
            && $this->registerHook('header')
            && $this->registerHook('footer')
        ;

        $this->registerModuleOnQualityService('installation');

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function uninstall()
    {
        Configuration::deleteByName(self::CONF_CONTENT);

        $result = parent::uninstall();

        $this->registerModuleOnQualityService('uninstallation');

        return $result;
    }

    /**
     * Get module's settings page content.
     *
     * @return string
     */
    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submit_save')) {
            $content = base64_encode(trim(Tools::getValue('counters_text')));
            if (Configuration::updateValue(self::CONF_CONTENT, $content)) {
                $output .= $this->displayConfirmation($this->l('Settings updated'));
            } else {
                $output .= $this->displayError($this->l('Some setting not updated'));
            }
        }

        $output .= '
            <form action="' . $_SERVER['REQUEST_URI'] . '" method="post">
                <fieldset>
                    <legend><img src="' . _PS_ADMIN_IMG_ . 'cog.gif" alt="" title="" />' . $this->l('Settings') . '</legend>
                    <b>' . $this->l('JavaScript code') . ':</b><br />
                    <textarea style="width:100%; height:300px;" name="counters_text">' . base64_decode(Configuration::get(self::CONF_CONTENT)) . '</textarea><br />
                    <center><input type="submit" name="submit_save" value="' . $this->l('Save') . '" class="button" /></center>
                </fieldset>
            </form>
        ';

        $modulezUrl = 'https://prestashop.modulez.ru' . (Language::getIsoById($this->context->cookie->id_lang) === 'ru' ? '/ru/' : '/en/');
        $modulePage = $modulezUrl . '43-javascript-code-addition-helper.html';
        $output .= // 2018-10-17
            (version_compare(_PS_VERSION_, '1.6', '<') ? '<br class="clear" />' : '') . '
            <div class="panel">
                <div class="panel-heading">
                    <img src="' . $this->_path . 'logo.png" width="16" height="16"/>
                    ' . $this->l('Module info') . '
                </div>
                <div class="form-wrapper">
                    <div class="row">               
                        <div class="form-group col-lg-4" style="display: block; clear: none !important; float: left; width: 33.3%;">
                            <span><b>' . $this->l('Version') . ':</b> ' . $this->version . '</span><br/>
                            <span><b>' . $this->l('License') . ':</b> Academic Free License (AFL 3.0)</span><br/>
                            <span><b>' . $this->l('Website') . ':</b> <a class="link" href="' . $modulePage . '" target="_blank">prestashop.modulez.ru</a></span><br/>
                            <span><b>' . $this->l('Author') . ':</b> zapalm <img src="' . $this->_path . 'zapalm24x24.jpg" /><br/><br/>
                        </div>
                        <div class="form-group col-lg-2" style="display: block; clear: none !important; float: left; width: 16.6%;">
                            <img width="250" alt="' . $this->l('Website') . '" src="' . $this->_path . 'marketplace-logo.png" />
                        </div>
                    </div>
                </div>
            </div> ' .
            (version_compare(_PS_VERSION_, '1.6', '<') ? '<br class="clear" />' : '') . '
        ';

        return $output;
    }

    /**
     * @inheritdoc
     */
    public function hookHeader()
    {
        return '<link href="' . $this->_path . 'views/css/main.css" rel="stylesheet">';
    }

    /**
     * @inheritdoc
     */
    public function hookRightColumn($params)
    {
        global $smarty;

        $this->assignCommonVariables();

        $smarty->assign(array(
            'stat_counters' => base64_decode(Configuration::get(self::CONF_CONTENT)),
        ));

        return $this->display(__FILE__, 'views/templates/hook/column-right.tpl');
    }

    /**
     * @inheritdoc
     */
    public function hookLeftColumn($params)
    {
        $this->assignCommonVariables();

        return $this->hookRightColumn($params);
    }

    /**
     * @inheritdoc
     */
    public function hookFooter($params)
    {
        global $smarty;

        $this->assignCommonVariables();

        $smarty->assign(array(
            'stat_counters' => base64_decode(Configuration::get(self::CONF_CONTENT)),
        ));

        return $this->display(__FILE__, 'views/templates/hook/footer.tpl');
    }

    /**
     * Assign common variables.
     */
    private function assignCommonVariables()
    {
        global $smarty;

        $smarty->assign(array(
            'psVersion' => (float)_PS_VERSION_,
        ));
    }

    /**
     * Registers current module installation/uninstallation in the quality service.
     *
     * This method is needed for a developer to quickly find out about a problem with installing or uninstalling a module.
     *
     * @param string $operation The operation. Possible values: installation, uninstallation.
     *
     * @author Maksim T. <zapalm@yandex.com>
     */
    private function registerModuleOnQualityService($operation)
    {
        @file_get_contents('https://prestashop.modulez.ru/scripts/quality-service/index.php?' . http_build_query(array(
            'data' => json_encode(array(
                'productId'           => self::HOMEPAGE_PRODUCT_ID,
                'productSymbolicName' => $this->name,
                'productVersion'      => $this->version,
                'operation'           => $operation,
                'status'              => (empty($this->_errors) ? 'success' : 'error'),
                'message'             => (false === empty($this->_errors) ? strip_tags(stripslashes(implode(' ', $this->_errors))) : ''),
                'prestashopVersion'   => _PS_VERSION_,
                'thirtybeesVersion'   => (defined('_TB_VERSION_') ? _TB_VERSION_ : ''),
                'shopDomain'          => (method_exists('Tools', 'getShopDomain') && Tools::getShopDomain() ? Tools::getShopDomain() : (Configuration::get('PS_SHOP_DOMAIN') ? Configuration::get('PS_SHOP_DOMAIN') : Tools::getHttpHost())),
                'shopEmail'           => Configuration::get('PS_SHOP_EMAIL'), // This public e-mail from a shop's contacts can be used by a developer to send only an urgent information about security issue of a module!
                'phpVersion'          => PHP_VERSION,
                'ioncubeVersion'      => (function_exists('ioncube_loader_iversion') ? ioncube_loader_iversion() : ''),
                'languageIsoCode'     => Language::getIsoById(false === empty($GLOBALS['cookie']->id_lang) ? $GLOBALS['cookie']->id_lang : Context::getContext()->language->id),
            ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        )));
    }
}