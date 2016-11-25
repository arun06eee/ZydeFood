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
 */
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Coupons Model Class
 *
 * @category       Models
 * @package        TastyIgniter\Models\Loyalty_model.php
 * @link           http://docs.tastyigniter.com
 */
 
class loyalty_price_model extends TI_Model {

	public function getPriceList() {
		$this->db->select('*');
		$this->db->from('loyalty_price');

		$query = $this->db->get();
		$result = array();

		if ($query->num_rows() > 0) {
			$result = $query->result_array();
		}

		return $result;
	}
	
	public function getEditLoyaltyPrice($loyalty_price_id) {
		$this->db->from('loyalty_price');
		$this->db->where('id', $loyalty_price_id);

		$query = $this->db->get();

		if ($query->num_rows() > 0) {
			return $query->row_array();
		}
	}
	
	public function Save_LoyaltyPrice($loyalty_price_id, $new_Loyalty) {

		if(empty ($new_Loyalty)) return FALSE;
		
		if (isset($new_Loyalty['id'])) {
			$this->db->set('id', $new_Loyalty['id']);
		}
		
		if (isset($new_Loyalty['name'])) {
			$this->db->set('Name', $new_Loyalty['name']);
		}
		
		if (isset($new_Loyalty['discount'])) {
			$this->db->set('discount',$new_Loyalty['discount']);
		}
		if (is_numeric($loyalty_price_id)) {
			$this->db->where('id', $loyalty_price_id);
			$query = $this->db->update('loyalty_price');
		} else {
			$query = $this->db->insert('loyalty_price');
			$loyalty_price_id = $this->db->insert_id();
		}
		return $loyalty_price_id;
	}
	
	public function deleteLoyaltyPrice($loyalty_price_id) {

		if (is_numeric($loyalty_price_id)) $loyalty_price_id = array($loyalty_price_id);

		if ( ! empty($loyalty_price_id) AND ctype_digit(implode('', $loyalty_price_id))) {
			$this->db->where_in('id', $loyalty_price_id);
			$this->db->delete('loyalty_price');

			return $this->db->affected_rows();
		}
	}
}
?>