<?php
/**
 * Block Counterz: module for PrestaShop 1.2-1.6
 *
 * @author zapalm <zapalm@ya.ru>
 * @copyright (c) 2010-2015, zapalm
 * @link http://prestashop.modulez.ru/en/ The module's homepage
 * @license http://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_'))
	exit;

class BlockCounterz extends Module
{
	private $counters_filepath;

	public function __construct()
	{
		$this->name = 'blockcounterz';
		$this->tab = 'Blocks';
		$this->version = '1.0.0';
		$this->author = 'zapalm';

		parent::__construct();

		$this->displayName = $this->l('Stat counters block');
		$this->description = $this->l('Adds a block to display stat counters.');

		$this->counters_filepath = dirname(__FILE__).'/counters.html';
	}

	private function getCountersCode()
	{
		if (file_exists($this->counters_filepath))
			return file_get_contents($this->counters_filepath);
	}

	public function install()
	{
		if (!file_exists($this->counters_filepath))
			file_put_contents($this->counters_filepath, '<!--noindex-->'."\r\n".'<!--/noindex-->');

		return parent::install() && $this->registerHook('rightColumn');
	}

	public function getContent()
	{
		global $cookie;

		$output = '';

		if (Tools::isSubmit('submit_save'))
		{
			if (file_put_contents($this->counters_filepath, Tools::getValue('counters_text')))
				$output .= $this->displayConfirmation($this->l('Settings updated'));
			else
				$output .= $this->displayError($this->l('Some setting not updated'));
		}

		$output .= '
			<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
				<fieldset>
					<legend><img src="'._PS_ADMIN_IMG_.'cog.gif" alt="" title="" />'.$this->l('Settings').'</legend>
					<b>'.$this->l('Stat counters code').':</b><br />
					<textarea style="width:100%; height:300px;" name="counters_text">'.$this->getCountersCode().'</textarea><br />
					<center><input type="submit" name="submit_save" value="'.$this->l('Save').'" class="button" /></center>
				</fieldset>
			</form>
			<br class="clear" />
		';

		return $output;
	}

	public function hookRightColumn($params)
	{
		global $smarty;

		$smarty->assign('stat_counters', $this->getCountersCode());

		return $this->display(__FILE__, 'blockcounterz.tpl');
	}

	public function hookLeftColumn($params)
	{
		return $this->hookRightColumn($params);
	}

	public function hookFooter($params)
	{
		return $this->hookRightColumn($params);
	}
}