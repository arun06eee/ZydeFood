<?php
/**
 * TastyIgniter
 *
 * An open source online ordering, reservation and management system for restaurants.
 *
 * @package   TastyIgniter
 * @author    SamPoyigi
 * @copyright TastyIgniter
 * @link      http://tastyigniter.com
 * @license   http://opensource.org/licenses/GPL-3.0 The GNU GENERAL PUBLIC LICENSE
 * @since     File available since Release 1.0
 * @filesource
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Cart_module_lib {

    public function __construct() {
        $this->CI =& get_instance();
    }

    public function cartTotals() {
        $result = array();
        $order_totals = $this->CI->Cart_model->getTotals();
        $cart_totals = $this->CI->cart->totals();

        foreach ($cart_totals as $name => $cart_total) {
            $order_total = ! empty($order_totals[$name]['status']) ? $order_totals[$name] : array('title' => '');

           if (empty($cart_total['amount']) OR empty($order_total['status'])) {
                if (empty($cart_total['0']['amount'])) {
                  continue;
                }
            }

            $cart_total['title'] = empty($cart_total['title']) ? $order_total['title'] : $cart_total['title'];

            if (isset($cart_total['code'])) {
                $cart_total['title'] = str_replace('{coupon}', $cart_total['code'], $cart_total['title']);
            }

            if (isset($cart_total['points'])) {
                $cart_total['title'] = str_replace('{loyalty}', $cart_total['points'], $cart_total['title']);
            }

            if ($name == 'taxes') {
                $i = 0 ;
                foreach ($cart_total as $tax_data){
                    if(is_array($tax_data)){
                        $result[$name.$i] = array(
                            'title' => htmlspecialchars_decode(str_replace('{tax}', $tax_data['title'], $cart_total['title'])),
                            'amount' => $this->CI->currency->format($tax_data['amount']),
                            'priority' => isset($order_total['priority']) ? $order_total['priority'] : $cart_total['priority'],
                        );
                        $i++;
                    }
                }
            }

            if($name !=  'taxes'){
                $result[$name] = array_merge($cart_total, array(
                    'title' => htmlspecialchars_decode($cart_total['title']),
                    'amount' => $this->CI->currency->format($cart_total['amount']),
                    'priority' => isset($order_total['priority']) ? $order_total['priority'] : $cart_total['priority'],
                ));
            }
        }

        return sort_array($result, 'priority');
    }

    public function validateDeliveryCharge($cart_total) {
        $delivery_charge = $this->CI->location->deliveryCharge($cart_total);

        if ($this->CI->location->orderType() === '1') {
            $this->CI->cart->set_delivery($delivery_charge);
        } else {
            $this->CI->cart->set_delivery(0);
        }

        return TRUE;
    }

	public function validateTaxesCharge($cart_total) {
		$gettax  = $this->CI->Cart_model->Get_Taxes();
		$gettaxTotal = $this->CI->Cart_model->Get_Tax_Total();		
		$taxes_charge = $this->CI->cart->calculate_tax($gettax, $gettaxTotal);

        return TRUE;
    }


    public function validateOrderType($order_type = '', $check_min_total = TRUE) {
        $order_type = empty($order_type) ? $this->CI->location->orderType() : $order_type;
        $cart_total = $this->CI->cart->total();

        if ($this->CI->location->isClosed()) {

            return $this->CI->lang->line('alert_order_unavailable');

        } else if ($order_type === '1') {
            if ( ! $this->CI->location->checkOrderType($order_type)) {

                return $this->CI->lang->line('alert_delivery_unavailable');

            } else if ($check_min_total AND $this->CI->cart->contents() AND ! $this->CI->location->checkMinimumOrder($cart_total)) {                            // checks if cart contents is empty

                return sprintf($this->CI->lang->line('alert_min_delivery_order_total'), $this->CI->currency->format($this->CI->location->minimumOrder($cart_total)));
            }

        } else if ($order_type === '2') {
            if ( ! $this->CI->location->checkOrderType($order_type)) {
                return $this->CI->lang->line('alert_collection_unavailable');
            }
        }

        return TRUE;
    }

    public function validateCartMenu($menu_data = array(), $cart_item = array()) {
        // if no menu found in database
        if (empty($menu_data)) {
            return sprintf($this->CI->lang->line('alert_menu_not_found'), $cart_item['name']);
        }

        // if menu mealtime is enable and menu is outside mealtime
        if ($menu_data['mealtime_status'] === '1' AND empty($menu_data['is_mealtime'])) {
            return sprintf($this->CI->lang->line('alert_menu_not_within_mealtime'), $menu_data['menu_name'], $menu_data['mealtime_name'], $menu_data['start_time'], $menu_data['end_time']);
        }

        // if cart quantity is less than minimum quantity
        if ($cart_item['qty'] < $menu_data['minimum_qty']) {
            return sprintf($this->CI->lang->line('alert_qty_is_below_min_qty'), $menu_data['minimum_qty']);
        }

        if ($this->CI->config->item('show_stock_warning') === '1' AND $menu_data['subtract_stock'] === '1') {
            // checks if stock quantity is less than or equal to zero
            if ($menu_data['stock_qty'] <= 0) {
                $stock_warning = sprintf($this->CI->lang->line('alert_out_of_stock'), $menu_data['menu_name']);
            }

            // checks if stock quantity is less than the cart quantity
            if ($menu_data['stock_qty'] < $cart_item['qty']) {
                $stock_warning = sprintf($this->CI->lang->line('alert_low_on_stock'), $menu_data['menu_name'], $menu_data['stock_qty']);
            }

            // Return warning if stock checkout is disabled, else skip
            if (!empty($stock_warning)) {
                return ($this->CI->config->item('stock_checkout') !== '1') ? $stock_warning : TRUE;
            }
        }

        return TRUE;
    }

    public function validateCartMenuOption(&$menu_data, $menu_options) {
        $cart_option_required = FALSE;

        $cart_options = array();
        if ($this->CI->input->post('menu_options') AND is_array($this->CI->input->post('menu_options'))) {
            $option_price = 0;
            foreach ($this->CI->input->post('menu_options') as $menu_option_id => $menu_option) {
                if ($cart_option_required === FALSE AND isset($menu_options[$menu_option_id])) {
                    if ($menu_options[$menu_option_id]['required'] === '1'
                        AND (empty($menu_option['option_values']) OR ! is_array($menu_option['option_values']))
                    ) {
                        $cart_option_required = $menu_options[$menu_option_id]['option_name'];
                        break;
                    } else if ( ! empty($menu_option['option_values'])) {
                        $option_values = $this->CI->Cart_model->getMenuOptionValues($menu_option['menu_option_id'], $menu_option['option_id']);

                        foreach ($menu_option['option_values'] as $key => $value) {
                            if (isset($option_values[$value], $option_values[$value]['menu_option_value_id'], $option_values[$value]['value'], $option_values[$value]['price'])) {
                                $cart_options[$menu_option_id][] = array(
                                    'value_id'    => $option_values[$value]['menu_option_value_id'],
                                    'value_name'  => $option_values[$value]['value'],
                                    'value_price' => $option_values[$value]['price'],
                                );

                                $option_price += $option_values[$value]['price'];
                            }
                        }
                    }
                }
            }

            $menu_data['menu_price'] = ( ! empty($option_price)) ? $option_price + $menu_data['menu_price'] : $menu_data['menu_price'];
        }

        if ($cart_option_required !== FALSE OR ($menu_options AND ! $this->CI->input->post('menu_options'))) {
            return sprintf($this->CI->lang->line('alert_option_required'), $cart_option_required);
        }

        return $cart_options;
    }

    public function validateCoupon($code = '') {
        $error = '';

        if ($code === NULL) {
            return TRUE;
        } else if (empty($code)) {
            $error = $this->CI->lang->line('alert_coupon_invalid');						// display error message
        } else if (!$coupon = $this->CI->Cart_model->checkCoupon($code)) {
            $error = $this->CI->lang->line('alert_coupon_expired');								// display error message
        } else {
            if (!empty($coupon['order_restriction']) AND $coupon['order_restriction'] !== $this->CI->location->orderType()) {
                $order_type = ($coupon['order_restriction'] === '1') ? $this->CI->lang->line('text_delivery') : $this->CI->lang->line('text_collection');
                $error = sprintf($this->CI->lang->line('alert_coupon_order_restriction'), strtolower($order_type));
            }

            if ($coupon['min_total'] > $this->CI->cart->total()) {
                $error = sprintf($this->CI->lang->line('alert_coupon_not_applied'), $this->CI->currency->format($coupon['min_total']));
            }

            $used = $this->CI->Cart_model->checkCouponHistory($coupon['coupon_id']);

            if (!empty($coupon['redemptions']) AND ($coupon['redemptions']) <= ($used)) {
                $error = $this->CI->lang->line('alert_coupon_maximum_reached');
            }

            if ($coupon['customer_redemptions'] === '1' AND $this->CI->customer->getId()) {
                $customer_used = $this->CI->Cart_model->checkCustomerCouponHistory($coupon['coupon_id'], $this->CI->customer->getId());

                if ($coupon['customer_redemptions'] <= $customer_used) {
                    $error = $this->CI->lang->line('alert_coupon_maximum_reached');
                }
            }

            if ($error === '') {
                $this->CI->cart->add_coupon(array('code' => $coupon['code'], 'type' => $coupon['type'], 'discount' => $coupon['discount']));
                return TRUE;
            }
        }

        if (!empty($code)) {
            $this->CI->cart->remove_coupon($code);
        }

        return $error;
    }

    public function validateCouponApi($code='') {
        $error = '';

        if ($code === NULL) {
            return TRUE;
        } else if (empty($code)) {
            $error = 'coupon_invalid';
        } else if (!$coupon = $this->CI->Cart_model->checkCoupon($code)) {
            $error = 'coupon_expired';
        } else {
            if (!empty($coupon['order_restriction']) AND $coupon['order_restriction'] !== $this->CI->location->orderType()) {
                $order_type = ($coupon['order_restriction'] === '1') ? $this->CI->lang->line('text_delivery') : $this->CI->lang->line('text_collection');
                $error = 'Your coupon can ONLY be applied to '.$order_type.' orders';
            }

            if ($coupon['min_total'] > $this->CI->cart->total()) {
                $error = 'Your coupon can not be applied to orders below '.$this->CI->currency->format($coupon['min_total']);
            }

            $used = $this->CI->Cart_model->checkCouponHistory($coupon['coupon_id']);

            if (!empty($coupon['redemptions']) AND ($coupon['redemptions']) <= ($used)) {
                $error = 'Maximum number of redemption for the coupon has been reached.';
            }

            if ($coupon['customer_redemptions'] === '1' AND $this->CI->customer->getId()) {
                $customer_used = $this->CI->Cart_model->checkCustomerCouponHistory($coupon['coupon_id'], $this->CI->customer->getId());

                if ($coupon['customer_redemptions'] <= $customer_used) {
                    $error = 'Maximum number of redemption for the coupon has been reached';
                }
            }

            if ($error === '') {
                $this->CI->cart->add_coupon(array('code' => $coupon['code'], 'type' => $coupon['type'], 'discount' => $coupon['discount']));
                $data = array('type'=>$coupon['type'], 'value'=>$coupon['discount']);
                return $data;
            }
        }

        if (!empty($code)) {
            $this->CI->cart->remove_coupon($code);
        }

        return $error;
    }

	public function validateLoyaltyPoints($Loyalty_points = '') {
        $error = '';
		$loyaltypoints = $this->CI->Cart_model->checkLoyaltyPoints($this->CI->customer->getId());
		$loyaltyPrice = $this->CI->Cart_model->getloyaltyPrice();
        $points_to_apply = $this->customer_loyaltypoints();

		if (empty($Loyalty_points) OR !is_numeric($Loyalty_points)) {
            $error = $this->CI->lang->line('alert_loyaltyPoints_invalid');						// display error message
        } else if ($loyaltypoints['0']['current_points'] === NULL) {
			$error = $this->CI->lang->line('alert_Points');								// display error message
        } else if ( ($loyaltypoints['0']['current_points'] < $Loyalty_points) OR ($points_to_apply['points_to_apply'] < $Loyalty_points)) {
			$error = $this->CI->lang->line('alert_high_points_applied');
		}

        if ($error === '') {
            $this->CI->cart->add_loyaltyPoints(array('applied_points' => $Loyalty_points, 'total_points' => $loyaltypoints[0]['current_points'], 'priceRate' => $loyaltyPrice[0]['discount']));
            return TRUE;
        }

        if (!empty($Loyalty_points)) {
            $this->CI->cart->remove_points($Loyalty_points);
        }

        return $error;
    }

    public function validateLoyaltyApi($loyalty) {
        $error = '';
        $loyaltypoints = $this->CI->Cart_model->checkLoyaltyPointsApi($loyalty['email']);
        $loyaltyPrice = $this->CI->Cart_model->getloyaltyPrice();
        $amount = $loyalty['purchased_amount'] - ($loyalty['loyalty_points']*$loyaltyPrice[0]['discount']);

        if (empty($loyalty['loyalty_points']) OR !is_numeric($loyalty['loyalty_points'])) {
            $error = 'loyaltyPoints_invalid';                      // display error message
        } else 
        if ($loyaltypoints['0']['current_points'] === NULL) {
            $error = 'No Points';                             // display error message
        } else 
        if ( ($loyaltypoints['0']['current_points'] < $loyalty['loyalty_points']) OR ($amount <  0)) {
            $error = 'high points applied';
        }

        if ($error === '') {
            $data = array('used_points'=> $loyalty['loyalty_points'], 'amount'=> $amount);
            return $data;
        }

        if (!empty($loyalty['loyalty_points'])) {
           // $this->CI->cart->remove_points($Loyalty_points);
        }

        return $error;
    }

   public function customer_loyaltypoints() {
        $points_details = array();
        $cart_details = $this->CI->cart->totals();
        $loyaltyPrice = $this->CI->Cart_model->getloyaltyPrice();

        $points_to_apply = round(($cart_details['order_total']['amount']) / $loyaltyPrice[0]['discount']);

        $loyaltypoints = $this->CI->Cart_model->checkLoyaltyPoints($this->CI->customer->getId());
        $max_points = ($loyaltypoints['0']['current_points'] * $loyaltyPrice[0]['discount']);

        if ($max_points < $cart_details['order_total']['amount']){
            $points_details['points_to_apply'] = $loyaltypoints['0']['current_points'];
            $points_details['loyaltypoints_to_show'] = $loyaltypoints['0']['current_points'];
        }else {
            $points_details['points_to_apply'] = $points_to_apply;
            $points_details['loyaltypoints_to_show'] = $loyaltypoints['0']['current_points'];
        }

        return $points_details;
    }
}