<?php
/**
 * HBepay - A Sample Payment Module for PrestaShop 1.7
 *
 * This file is the declaration of the module.
 *
 * @author SprintSquads
 * @license https://opensource.org/licenses/afl-3.0.php
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class HBepay extends PaymentModule
{

    private $postErrors = array();

    public function __construct()
    {
        $this->name                   = 'hbepay';
        $this->tab                    = 'payments_gateways';
        $this->version                = '1.0';
        $this->author                 = 'SprintSquads';
        $this->bootstrap              = true;
        $this->displayName            = 'HBepay';
        $this->description            = 'HB epay payment gateway';
        $this->confirmUninstall       = 'Are you sure you want to uninstall this module?';
        $this->ps_versions_compliancy = array('min' => '1.7.0', 'max' => _PS_VERSION_);

        parent::__construct();
    }

 
    public function install()
    {
        return parent::install()
            && $this->registerHook('paymentOptions');
    }

    public function uninstall()
    {
        return parent::uninstall();
    }

    public function hookPaymentOptions($params)
    {
 
        if (!$this->active) {
            return;
        }
 
        $formAction = $this->context->link->getModuleLink($this->name, 'redirect', array(), true);
        $this->smarty->assign(array('action' => $formAction));

        $paymentForm = $this->fetch('module:hbepay/views/templates/hook/payment_options.tpl');
 
        $newOption = new PrestaShop\PrestaShop\Core\Payment\PaymentOption;
        $newOption->setModuleName($this->displayName)
            ->setCallToActionText($this->displayName)
            ->setAction($formAction)
            ->setForm($paymentForm);
 
        $payment_options = array(
            $newOption
        );
 
        return $payment_options;
    }

     public function getContent()
    {
        $err = '';
        if (((bool)Tools::isSubmit('submit'.$this->name)) == true) {
            $this->postValidation();
            if (!sizeof($this->postErrors)) {
                $this->postProcess();
            } else {
                foreach ($this->postErrors as $error) {
                    $err .= $this->displayError($error);
                }
            }
        }

        return $err.$this->renderForm();
    }

    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submit'.$this->name;
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), 
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Please specify the hbepay account details for customers'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'radio',
                        'label' => $this->l('Test mode'),
                        'name' => 'hbepay_TEST_MODE',
                        'desc' => $this->l(''),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No'),
                            )
                        ),
                    ),
                    array(
                        'col' => 4,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-user"></i>',
                        'desc' => $this->l('Enter a client id'),
                        'name' => 'hbepay_CLIENT_ID',
                        'label' => $this->l('Client ID'),
                        'required' => true
                    ),
                    array(
                        'col' => 4,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-key"></i>',
                        'name' => 'hbepay_CLIENT_SECRET',
                        'desc' => $this->l('Enter a client secret'),
                        'label' => $this->l('Client secret'),
                        'required' => true
                    ),
                    array(
                        'col' => 4,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-key"></i>',
                        'name' => 'hbepay_TERMINAL',
                        'desc' => $this->l('Enter a terminal id'),
                        'label' => $this->l('Terminal'),
                        'required' => true
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default pull-right'
                ),
            ),
        );
    }


    protected function getConfigFormValues()
    {
        return array(
            'hbepay_CLIENT_ID' => Configuration::get('hbepay_CLIENT_ID', null),
            'hbepay_CLIENT_SECRET' => Configuration::get('hbepay_CLIENT_SECRET', null),
            'hbepay_TERMINAL' => Configuration::get('hbepay_TERMINAL', null),
            'hbepay_TEST_MODE' => Configuration::get('hbepay_TEST_MODE', null),
        );
    }

    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();
        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

     private function postValidation()
    {
        if (Tools::isSubmit('submit'.$this->name)) {
            $client_id = Tools::getValue('hbepay_CLIENT_ID');
            $client_secret = Tools::getValue('hbepay_CLIENT_SECRET');
            $terminal = Tools::getValue('hbepay_TERMINAL');
            if (empty($client_id)) {
                $this->postErrors[] = $this->l('Client ID is required.');
            }
            if (empty($client_secret)) {
                $this->postErrors[] = $this->l('Client secret is required.');
            }
            if (empty($terminal)) {
                $this->postErrors[] = $this->l('Terminal is required.');
            }
        }
    }
  
}