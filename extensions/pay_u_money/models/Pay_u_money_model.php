<?php if ( ! defined('BASEPATH')) exit('No direct access allowed');

class Pay_u_money_model extends TI_Model {

    public function __construct() {
        parent::__construct();

        $this->load->library('cart');
        $this->load->library('currency');
    }

	public function getPayUMoneyDetails($order_id, $customer_id) {
		$this->db->from('pp_payments');
		$this->db->where('order_id', $order_id);
		$this->db->where('customer_id', $customer_id);

		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			$row = $query->row_array();

			return unserialize($row['serialized']);
		}
	}

	public function setExpressCheckout($order_info, $cart_items) {
		//log_message('error', 'PayUMoney::setExpressCheckout Error --> Testing' );
		if ($cart_items) {

			if ($order_info['order_type'] === '1' AND (!empty($order_info['address_id']) OR !empty($order_info['customer_id']))) {
				$firstname = urlencode($order_info['first_name'] .' '. $order_info['last_name']);
				$email = urlencode($order_info['email']);
				$phone = urlencode($order_info['telephone']);
			}

			if ($this->cart->order_total() > 0) {
				$amount  = urlencode($this->cart->order_total());
			}

 			$response = $this->callPayUMoney('SetExpressCheckout', $firstname, $amount, $email, $phone);

			if (isset($response['ACK'])) {
				if (strtoupper($response['ACK']) !== 'SUCCESS' OR strtoupper($response['ACK']) !== 'SUCCESSWITHWARNING') {
					if (isset($response['L_ERRORCODE0'], $response['L_LONGMESSAGE0'], $order_info['order_id'])) {
						log_message('error', 'PayUMoney::setExpressCheckout Error -->' . $order_info['order_id'] . ': ' . $response['L_ERRORCODE0'] . ': ' . $response['L_LONGMESSAGE0']);
					}
				}
			}

			return $response;
		}
	}

	public function doExpressCheckout($token, $payer_id, $order_info = array()) {

		if ($order_info['order_type'] === '1' AND (!empty($order_info['address_id']) OR !empty($order_info['customer_id']))) {
			$firstname = urlencode($order_info['first_name'] .' '. $order_info['last_name']);
			$email = urlencode($order_info['email']);
			$phone = urlencode($order_info['telephone']);
		}

		if ($this->cart->order_total() > 0) {
			$amount  = urlencode($this->cart->order_total());
		}

		
		log_message('error', 'PayUMoney::token Error -->' . $token);

//		$response = $this->callPayUMoney('SetExpressCheckout', $firstname, $amount, $email, $phone);

		$response = $this->callPayUMoneyData('DoExpressCheckoutPayment', $token);
/* 
		if (isset($response['ACK'])) {
			if (strtoupper($response['ACK']) === 'SUCCESS' OR strtoupper($response['ACK']) === 'SUCCESSWITHWARNING') {
				return $response['PAYMENTINFO_0_TRANSACTIONID'];
			} else {
				if (isset($response['L_ERRORCODE0'], $response['L_LONGMESSAGE0'], $order_info['order_id'])) {
					log_message('error', 'PayUMoney::doExpressCheckout Error -->' . $order_info['order_id'] . ': ' . $response['L_ERRORCODE0'] . ': ' . $response['L_LONGMESSAGE0']);
				}
			}
		}
*/
		return FALSE;
	}

	public function getTransactionDetails($transaction_id, $order_info = array()) {

		$nvp_data = '&TRANSACTIONID='. urlencode($transaction_id);

		$response = $this->callPayUMoney('GetTransactionDetails', $nvp_data);

		if (isset($response['ACK'])) {
			if (strtoupper($response['ACK']) !== 'SUCCESS' OR strtoupper($response['ACK']) !== 'SUCCESSWITHWARNING') {
				if (isset($response['L_ERRORCODE0'], $response['L_LONGMESSAGE0'], $order_info['order_id'])) {
					log_message('error', 'PayUMoney::getTransactionDetails Error -->' . $order_info['order_id'] . ': ' . $response['L_ERRORCODE0'] . ': ' . $response['L_LONGMESSAGE0']);
				}
			}
		}

		return $response;
	}

	public function addPayUMoneyOrder($transaction_id, $order_id, $customer_id, $response_data) {
		$query = FALSE;

		if (!empty($order_id)) {
			$this->db->set('order_id', $order_id);
		}

		if (!empty($customer_id)) {
			$this->db->set('customer_id', $customer_id);
		}

		if (!empty($response_data)) {
			$this->db->set('serialized', serialize($response_data));
		}

		if (!empty($transaction_id)) {
			$this->db->set('transaction_id', $transaction_id);

			if ($this->db->insert('pp_payments')) {
				$query = $this->db->insert_id();
			}
		}

		return $query;
	}

	public function callPayUMoney($method, $firstname, $amount, $email, $phone) {
		$payment = $this->extension->getPayment('pay_u_money');
		$settings = $payment['ext_data'];
		$amount = 1;

		if (isset($settings['api_mode']) AND $settings['api_mode'] === 'sandbox') {
			$api_mode = 'https://test.payu.in/_payment';
			$merchant_key = "rjQUPktU"; 		 // sandbox
			$merchant_salt = "e5iIg1jwi8";		// sandbox
			$productinfo = "Demoproductinfo";
		} else {
			$api_mode = 'https://secure.payu.in/_payment';
			$merchant_id = (isset($settings['merchant_id'])) ? $settings['merchant_id'] : '';
			$merchant_key = (isset($settings['merchant_key'])) ? $settings['merchant_key'] : '';
			$merchant_salt = (isset($settings['merchant_salt'])) ? $settings['merchant_salt'] : '';
			$productinfo = "productinfo";
		}

		$api_end_point = $api_mode ;

		$surl = 'http://localhost/ZydeFood/pay_u_money/authorize';
		$furl = 'http://localhost/ZydeFood/pay_u_money/cancel';
		$txnid = substr(hash('sha256', mt_rand() . microtime()), 0, 20);
 
		$hashSequence = "$merchant_key|$txnid|$amount|$productinfo|$firstname|$email|||||||||||$merchant_salt";

		$hash = strtolower(hash("sha512", $hashSequence));

		$url = $api_end_point;
		$html = "<html><body>Loading, please Wait..  <form id='formpayumoney' name='payuForm' action='$url' method='post'>";
		$html .= "<input type='hidden' name='key' value='$merchant_key'>";
		$html .= "<input type='hidden' name='txnid' value='$txnid'>";
		$html .= "<input type='hidden' name='amount' value='$amount'>";
		$html .= "<input type='hidden' name='productinfo' value='$productinfo'>";
		$html .= "<input type='hidden' name='firstname' value='$firstname'>";
		$html .= "<input type='hidden' name='email' value='$email'>";
		$html .= "<input type='hidden' name='phone' value='$phone'>";
		$html .= "<input type='hidden' name='surl' value='$surl'>";
		$html .= "<input type='hidden' name='furl' value='$furl'>";
		$html .= "<input type='hidden' name='hash' value='$hash'>";
		$html .= "<input type='hidden' name='service_provider' value='payu_paisa'>";
		$html .= "</form><script>document.getElementById('formpayumoney').submit();</script>";
		$html .= "</body></html>";

		print($html);
	}
	
	public function callPayUMoneyData($method, $token) {
		$payment = $this->extension->getPayment('pay_u_money');
		$settings = $payment['ext_data'];
		
		if (isset($settings['api_mode']) AND $settings['api_mode'] === 'sandbox') {
			$api_mode = 'https://test.payumoney.com/payment/op/getPaymentResponse?';
			$merchant_key = "rjQUPktU"; 		 // sandbox
			$merchant_salt = "e5iIg1jwi8";		// sandbox
			$productinfo = "Demoproductinfo";
		} else {
			$api_mode = 'https://www.payumoney.com/payment/op/getPaymentResponse?';
			$merchant_id = (isset($settings['merchant_id'])) ? $settings['merchant_id'] : '';
			$merchant_key = (isset($settings['merchant_key'])) ? $settings['merchant_key'] : '';
			$merchant_salt = (isset($settings['merchant_salt'])) ? $settings['merchant_salt'] : '';
			$productinfo = "productinfo";
		}

		$api_end_point = $api_mode ;
		$url = $api_end_point.'merchantKey='.$merchant_key.'&merchantTransactionIds='.$token; 

		log_message('error', 'PayUMoney::url Error -->' . $url);

		$data =array('merchantKey'=> $merchant_key, 'merchantTransactionIds '=>$token);
		$options = array(
			'http' => array(
				'header' => "Authorization: 0SC8FamYqWnwFzVgYKmiCfSsT96xerU8E+WBUh/KDXc=", 
				'method' => 'POST', 
				'Authorization'=> '0SC8FamYqWnwFzVgYKmiCfSsT96xerU8E+WBUh/KDXc=', 
				'content' => http_build_query($data) 
			),
		); 
		$context = stream_context_create($options); 
		$result = file_get_contents($url, false, $context); 
		
		log_message('error', 'PayUMoney::doExpressCheckout Error -->' . json_encode($result));

		return $result;
	}
}

/* End of file pay_u_money_model.php */
/* Location: ./extensions/pay_u_money_model/models/pay_u_money_model.php */