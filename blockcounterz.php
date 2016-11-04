<?php
/**
 * Block counters: module for PrestaShop 1.2-1.6
 *
 * @author    zapalm <zapalm@ya.ru>
 * @copyright (c) 2010-2016, zapalm
 * @link      http://prestashop.modulez.ru/en/frontend-features/43-block-counters.html The module's homepage
 * @license   http://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_'))
	exit;

class BlockCounterz extends Module
{
	const CONF_CONTENT = 'BLOCKCOUNTERZ_CONTENT';

	public function __construct() {
		$this->name = 'blockcounterz';
		$this->tab = 'Blocks';
		$this->version = '1.1.0';
		$this->author = 'zapalm';

		parent::__construct();

		$this->displayName = $this->l('Stat counters block');
		$this->description = $this->l('Adds a block to display stat counters.');
	}

	public function install() {
		$content = base64_encode('<!--noindex-->' . PHP_EOL . '<!--/noindex-->');
		Configuration::updateValue(self::CONF_CONTENT, $content);

		return parent::install()
			&& $this->registerHook('header')
			&& $this->registerHook('footer')
		;
	}

	public function uninstall() {
		Configuration::deleteByName(self::CONF_CONTENT);

		return parent::uninstall();
	}

	public function getContent() {
		$output = '';

		if (Tools::isSubmit('submit_save')) {
			$content = base64_encode(trim(Tools::getValue('counters_text')));
			if (Configuration::updateValue(self::CONF_CONTENT, $content))
				$output .= $this->displayConfirmation($this->l('Settings updated'));
			else
				$output .= $this->displayError($this->l('Some setting not updated'));
		}

		$output .= '
			<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
				<fieldset>
					<legend><img src="'._PS_ADMIN_IMG_.'cog.gif" alt="" title="" />'.$this->l('Settings').'</legend>
					<b>'.$this->l('Stat counters code').':</b><br />
					<textarea style="width:100%; height:300px;" name="counters_text">'.base64_decode(Configuration::get(self::CONF_CONTENT)).'</textarea><br />
					<center><input type="submit" name="submit_save" value="'.$this->l('Save').'" class="button" /></center>
				</fieldset>
			</form>
			<br class="clear" />
		';

		return $output;
	}

	public function hookHeader() {
		return '<link href="' . $this->_path . 'views/css/main.css" rel="stylesheet">';
	}

	public function hookRightColumn($params) {
		global $smarty;

		$smarty->assign(array(
			'stat_counters' => base64_decode(Configuration::get(self::CONF_CONTENT)),
		));

		return $this->display(__FILE__, 'views/templates/hook/column-right.tpl');
	}

	public function hookLeftColumn($params) {
		return $this->hookRightColumn($params);
	}

	public function hookFooter($params) {
		global $smarty;

		$smarty->assign(array(
			'stat_counters' => base64_decode(Configuration::get(self::CONF_CONTENT)),
		));

		return $this->display(__FILE__, 'views/templates/hook/footer.tpl');
	}
}