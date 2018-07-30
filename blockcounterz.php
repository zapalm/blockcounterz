<?php
/**
 * Block counters: module for PrestaShop.
 *
 * @author    Maksim T. <zapalm@yandex.com>
 * @copyright 2010 Maksim T.
 * @link      https://prestashop.modulez.ru/en/frontend-features/43-block-counters.html The module's homepage
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
    /** Settings option: content */
    const CONF_CONTENT = 'BLOCKCOUNTERZ_CONTENT';

    /**
     * @inheritdoc
     */
    public function __construct()
    {
        $this->name    = 'blockcounterz';
        $this->tab     = 'Blocks';
        $this->version = '1.1.1';
        $this->author  = 'zapalm';

        parent::__construct();

        $this->displayName = $this->l('Stat counters block');
        $this->description = $this->l('Adds a block to display stat counters.');
    }

    /**
     * @inheritdoc
     */
    public function install()
    {
        $content = base64_encode('<!--noindex-->' . PHP_EOL . '<!--/noindex-->');
        Configuration::updateValue(self::CONF_CONTENT, $content);

        return parent::install()
            && $this->registerHook('header')
            && $this->registerHook('footer')
        ;
    }

    /**
     * @inheritdoc
     */
    public function uninstall()
    {
        Configuration::deleteByName(self::CONF_CONTENT);

        return parent::uninstall();
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
                    <b>' . $this->l('Stat counters code') . ':</b><br />
                    <textarea style="width:100%; height:300px;" name="counters_text">' . base64_decode(Configuration::get(self::CONF_CONTENT)) . '</textarea><br />
                    <center><input type="submit" name="submit_save" value="' . $this->l('Save') . '" class="button" /></center>
                </fieldset>
            </form>
            <br class="clear" />
        ';

        $modulezUrl = 'https://prestashop.modulez.ru' . (Language::getIsoById($this->context->cookie->id_lang) === 'ru' ? '/ru/' : '/en/');
        $modulePage = $modulezUrl . '43-block-counters.html';
        $output .= '
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
            </div>
            <br class="clear" />
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
}