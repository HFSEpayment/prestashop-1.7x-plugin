<?php
/**
 * HBepay - A Sample Payment Module for PrestaShop 1.7
 *
 * This file is the declaration of the module.
 *
 * @author SprintSquads
 * @license https://opensource.org/licenses/afl-3.0.php
 */

class HbepayCancelModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
		$this->redirectWithNotifications('index.php?controller=order');
	}
}