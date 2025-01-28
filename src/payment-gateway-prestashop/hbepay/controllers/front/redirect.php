<?php
/**
 * HBepay - A Sample Payment Module for PrestaShop 1.7
 *
 * This file is the declaration of the module.
 *
 * @author SprintSquads
 * @license https://opensource.org/licenses/afl-3.0.php
 */

class HbepayRedirectModuleFrontController extends ModuleFrontController
{

    public function paymentGateway(){

    	$cart = $this->context->cart;
    	$total = $cart->getOrderTotal();
    	$link = $this->context->link;

    	$payCurrency = Context::getContext()->currency;

    	$test_url = "https://testoauth.homebank.kz/epay2/oauth2/token";
		$prod_url = "https://epay-oauth.homebank.kz/oauth2/token";
		$test_page = "https://test-epay.homebank.kz/payform/payment-api.js";
        $prod_page = "https://epay.homebank.kz/payform/payment-api.js";

		$token_api_url = "";
		$pay_page = "";
		$err_exist = false;
		$err = "";

		// initiate default variables
		$hbp_account_id = "";
		$hbp_telephone = "";
		$hbp_email = "";
		$hbp_language = "RU";
		$hbp_description = "Оплата в интернет магазине";

		$hbp_currency = $payCurrency->iso_code;
		$hbp_env = Configuration::get('hbepay_TEST_MODE');
		$hbp_client_id = Configuration::get('hbepay_CLIENT_ID');
		$hbp_client_secret = Configuration::get('hbepay_CLIENT_SECRET');
		$hbp_terminal = Configuration::get('hbepay_TERMINAL');
		$hbp_invoice_id ='0000000'. $cart->id;
		$hbp_amount = $total;
		$hbp_back_link = $link->getModuleLink('hbepay', 'validation');
		$hbp_failure_back_link = $link->getModuleLink('hbepay', 'cancel');
		$hbp_post_link = '';
		$hbp_failure_post_link = '';
		
		if($hbp_env){
			$token_api_url = $test_url;
			$pay_page = $test_page;
		}
		else{
			$token_api_url = $prod_url;
			$pay_page = $prod_page;	
		}
		
		$fields = [
				'grant_type'      => 'client_credentials', 
				'scope'           => 'payment usermanagement',
				'client_id'       => $hbp_client_id,
				'client_secret'   => $hbp_client_secret,
				'invoiceID'       => $hbp_invoice_id,
				'amount'          => $hbp_amount,
				'currency'        => $hbp_currency,
				'terminal'        => $hbp_terminal,
				'postLink'        => '',
				'failurePostLink' => ''
			];
		
			$fields_string = http_build_query($fields);
		
			$ch = curl_init();
		
			curl_setopt($ch, CURLOPT_URL, $token_api_url);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
		
			$result = curl_exec($ch);
		
			$json_result = json_decode($result, true);
			if (!curl_errno($ch)) {
				switch ($http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
						case 200:
								$hbp_auth = (object) $json_result;
		
								$hbp_payment_object = (object) [
										"invoiceId" => $hbp_invoice_id,
										"backLink" => $hbp_back_link,
										"failureBackLink" => $hbp_failure_back_link,
										"postLink" => $hbp_post_link,
										"failurePostLink" => $hbp_failure_post_link,
										"language" => $hbp_language,
										"description" => $hbp_description,
										"accountId" => $hbp_account_id,
										"terminal" => $hbp_terminal,
										"amount" => $hbp_amount,
										"currency" => $hbp_currency,
										"auth" => $hbp_auth,
										"phone" => $hbp_telephone,
										"email" => $hbp_email
								];
						?>
						<script src="<?=$pay_page?>"></script>
						<script>
								halyk.pay(<?= json_encode($hbp_payment_object) ?>);
						</script>
				<?php
								break;
						default:
								echo 'Неожиданный код HTTP: ', $http_code, "\n";
				}
		}
    }

    public function initContent()
    {
        parent::initContent();
        $fields['action'] = $this->paymentGateway();
        $this->context->smarty->assign($fields);

        $this->setTemplate('module:' . $this->module->name . '/views/templates/front/redirect.tpl');
    }   
}
