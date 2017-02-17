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
 * @package        TastyIgniter\Models\Tax_model.php
 * @link           http://docs.tastyigniter.com
 */
 
class tax_model extends TI_Model {

	public function getTax() {
		$this->db->select('*');
		$this->db->from('tax');

		$query = $this->db->get();
		$result = array();

		if ($query->num_rows() > 0) {
			$result = $query->result_array();
		}

		return $result;
	}

	public function getTaxApi() {
		$this->db->select('name, percentage');
		$this->db->where('status','1');
		$this->db->from('tax');

		$query = $this->db->get();
		$result = array();

		if ($query->num_rows() > 0) {
			return $query->result_array();
		}
	}
	
	public function getEditTax($tax_id) {
		$this->db->from('tax');
		$this->db->where('id', $tax_id);

		$query = $this->db->get();

		if ($query->num_rows() > 0) {
			return $query->row_array();
		}
	}
	
	public function Save_Tax($tax_id, $new_Tax) {

		if(empty ($new_Tax)) return FALSE;

		if (isset($new_Tax['name'])) {
			$this->db->set('name', $new_Tax['name']);
		}
		
		if (isset($new_Tax['percentage'])) {
			$this->db->set('percentage',$new_Tax['percentage']);
		}
		
		if (isset($new_Tax['status'])) {
			$this->db->set('status',$new_Tax['status']);
		}

		if (is_numeric($tax_id)) {
			$this->db->where('id', $tax_id);
			$query = $this->db->update('tax');
		} else {
			$query = $this->db->insert('tax');
			$tax_id = $this->db->insert_id();
		}
		return $tax_id;
	}
	
	public function deleteTax($tax_id) {

		if (is_numeric($tax_id)) $tax_id = array($tax_id);

		if ( ! empty($tax_id) AND ctype_digit(implode('', $tax_id))) {
			$this->db->where_in('id', $tax_id);
			$this->db->delete('tax');

			return $this->db->affected_rows();
		}
	}
}
?>