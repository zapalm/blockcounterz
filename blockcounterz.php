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
 * Module BlockCounterz.
 *
 * @author Maksim T. <zapalm@yandex.com>
 */
class BlockCounterz extends Module
{
    /** The product ID of the module on its homepage. */
    const HOMEPAGE_PRODUCT_ID = 43;

    /** Settings option: content */
    const CONF_CONTENT = 'BLOCKCOUNTERZ_CONTENT';

    /** @var string|null Footer content. */
    private static $footerContent;

    /**
     * @inheritdoc
     *
     * @author Maksim T. <zapalm@yandex.com>
     */
    public function __construct()
    {
        $this->name    = 'blockcounterz';
        $this->tab     = 'Blocks';
        $this->version = '1.4.0';
        $this->author  = 'zapalm';

        parent::__construct();

        $this->displayName = $this->l('JavaScript code addition helper');
        $this->description = $this->l('Helps to add any JavaScript code, for example, analytic counter, advertisement or other script.');
    }

    /**
     * @inheritdoc
     *
     * @author Maksim T. <zapalm@yandex.com>
     */
    public function install()
    {
        $content = base64_encode('<!--noindex-->' . PHP_EOL . '<!--/noindex-->');
        Configuration::updateValue(self::CONF_CONTENT, $content);

        $result = parent::install()
            && $this->registerHook('header')
            && $this->registerHook('footer')
            && $this->registerHook('displayBeforeBodyClosingTag')
        ;

        $this->registerModuleOnQualityService('installation');

        return $result;
    }

    /**
     * @inheritdoc
     *
     * @author Maksim T. <zapalm@yandex.com>
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
     *
     * @author Maksim T. <zapalm@yandex.com>
     */
    public function getContent()
    {
        $output = (version_compare(_PS_VERSION_, '1.6', '>=') ? '' : '<h2>' . $this->displayName . '</h2>');

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

        // The block about the module (version: 2021-08-19)
        $modulezUrl    = 'https://prestashop.modulez.ru' . (Language::getIsoById(false === empty($GLOBALS['cookie']->id_lang) ? $GLOBALS['cookie']->id_lang : Context::getContext()->language->id) === 'ru' ? '/ru/' : '/en/');
        $modulePage    = $modulezUrl . self::HOMEPAGE_PRODUCT_ID . '-' . $this->name . '.html';
        $licenseTitle  = 'Academic Free License (AFL 3.0)';
        $output       .=
            (version_compare(_PS_VERSION_, '1.6', '<') ? '<br class="clear" />' : '') . '
            <div class="panel">
                <div class="panel-heading">
                    <img src="' . $this->_path . 'logo.png" width="16" height="16" alt=""/>
                    ' . $this->l('Module info') . '
                </div>
                <div class="form-wrapper">
                    <div class="row">               
                        <div class="form-group col-lg-4" style="display: block; clear: none !important; float: left; width: 33.3%;">
                            <span><b>' . $this->l('Version') . ':</b> ' . $this->version . '</span><br/>
                            <span><b>' . $this->l('License') . ':</b> ' . $licenseTitle . '</span><br/>
                            <span><b>' . $this->l('Website') . ':</b> <a class="link" href="' . $modulePage . '" target="_blank">prestashop.modulez.ru</a></span><br/>
                            <span><b>' . $this->l('Author') . ':</b> ' . $this->author . '</span><br/><br/>
                        </div>
                        <div class="form-group col-lg-2" style="display: block; clear: none !important; float: left; width: 16.6%;">
                            <img width="250" alt="' . $this->l('Website') . '" src="https://prestashop.modulez.ru/img/marketplace-logo.png" />
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
     *
     * @author Maksim T. <zapalm@yandex.com>
     */
    public function hookHeader()
    {
        return '<link href="' . $this->_path . 'views/css/main.css" rel="stylesheet">';
    }

    /**
     * @inheritdoc
     *
     * @author Maksim T. <zapalm@yandex.com>
     */
    public function hookRightColumn($params)
    {
        global $smarty;

        $this->assignCommonVariables();

        $smarty->assign([
            'stat_counters' => base64_decode(Configuration::get(self::CONF_CONTENT)),
        ]);

        return $this->display(__FILE__, 'views/templates/hook/column-right.tpl');
    }

    /**
     * @inheritdoc
     *
     * @author Maksim T. <zapalm@yandex.com>
     */
    public function hookLeftColumn($params)
    {
        $this->assignCommonVariables();

        return $this->hookRightColumn($params);
    }

    /**
     * @inheritdoc
     *
     * @author Maksim T. <zapalm@yandex.com>
     */
    public function hookFooter($params)
    {
        global $smarty;

        if (null === static::$footerContent) {
            $this->assignCommonVariables();

            $smarty->assign([
                'stat_counters' => base64_decode(Configuration::get(self::CONF_CONTENT)),
            ]);

            static::$footerContent = $this->display(__FILE__, 'views/templates/hook/footer.tpl');

            return static::$footerContent;
        }

        return '';
    }

    /**
     * @inheritdoc
     */
    public function hookDisplayBeforeBodyClosingTag($params)
    {
        return $this->hookFooter($params);
    }

    /**
     * Assign common variables.
     *
     * @author Maksim T. <zapalm@yandex.com>
     */
    private function assignCommonVariables()
    {
        global $smarty;

        $smarty->assign([
            'psVersion' => (float)_PS_VERSION_,
        ]);
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
        @file_get_contents('https://prestashop.modulez.ru/scripts/quality-service/index.php?' . http_build_query([
            'data' => json_encode([
                'productId'           => self::HOMEPAGE_PRODUCT_ID,
                'productSymbolicName' => $this->name,
                'productVersion'      => $this->version,
                'operation'           => $operation,
                'status'              => (empty($this->_errors) ? 'success' : 'error'),
                'message'             => (false === empty($this->_errors) ? strip_tags(stripslashes(implode(' ', (array)$this->_errors))) : ''),
                'prestashopVersion'   => _PS_VERSION_,
                'thirtybeesVersion'   => (defined('_TB_VERSION_') ? _TB_VERSION_ : ''),
                'shopDomain'          => (method_exists('Tools', 'getShopDomain') && Tools::getShopDomain() ? Tools::getShopDomain() : (Configuration::get('PS_SHOP_DOMAIN') ? Configuration::get('PS_SHOP_DOMAIN') : Tools::getHttpHost())),
                'shopEmail'           => Configuration::get('PS_SHOP_EMAIL'), // This public e-mail from a shop's contacts can be used by a developer to send only an urgent information about security issue of a module!
                'phpVersion'          => PHP_VERSION,
                'ioncubeVersion'      => (function_exists('ioncube_loader_iversion') ? ioncube_loader_iversion() : ''),
                'languageIsoCode'     => Language::getIsoById(false === empty($GLOBALS['cookie']->id_lang) ? $GLOBALS['cookie']->id_lang : Context::getContext()->language->id),
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        ]));
    }
}