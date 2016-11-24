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
 
class Loyalty_model extends TI_Model {
	
	public function getCount($filter = array()) {
		if ( ! empty($filter['filter_search'])) {
			$this->db->like('name', $filter['filter_search']);
			$this->db->or_like('min_range', $filter['filter_search']);
			$this->db->or_like('points', $filter['filter_search']);
		}

		if ( ! empty($filter['filter_search'])) {
			$this->db->where('max_range', $filter['filter_search']);
		}

		if ( ! empty($filter['filter_status']) AND is_numeric($filter['filter_status'])) {
			$this->db->where('status', $filter['filter_status']);
		}

		$this->db->from('loyalty');

		return $this->db->count_all_results();
	}

		public function getList($filter = array()) {
		if ( ! empty($filter['page']) AND $filter['page'] !== 0) {
			$filter['page'] = ($filter['page'] - 1) * $filter['limit'];
		}

		if ($this->db->limit($filter['limit'], $filter['page'])) {
			$this->db->from('loyalty');

			if ( ! empty($filter['sort_by']) AND ! empty($filter['order_by'])) {
				$this->db->order_by($filter['sort_by'], $filter['order_by']);
			}

			if ( ! empty($filter['filter_search'])) {
				$this->db->like('name', $filter['filter_search']);
				$this->db->or_like('min_range', $filter['filter_search']);
				$this->db->or_like('max_range', $filter['filter_search']);
				$this->db->or_like('points', $filter['filter_search']);
			}

			if (isset($filter['filter_status']) AND is_numeric($filter['filter_status'])) {
				$this->db->where('status', $filter['filter_status']);
			}

			$query = $this->db->get();
			$result = array();

			if ($query->num_rows() > 0) {
				$result = $query->result_array();
			}

			return $result;
		}
	}
	
	public function getLoyalty() {
		$this->db->select('*');
		$this->db->from('loyalty');

		$query = $this->db->get();
		$result = array();

		if ($query->num_rows() > 0) {
			$result = $query->result_array();
		}

		return $result;
	}
	
	public function getEditLoyalty($loyalty_id) {
		$this->db->from('loyalty');
		$this->db->where('loyalty_id', $loyalty_id);

		$query = $this->db->get();

		if ($query->num_rows() > 0) {
			return $query->row_array();
		}
	}

	public function Save_Loyalty($loyalty_id, $new_Loyalty) {

		if(empty ($new_Loyalty)) return FALSE;
		
		if (isset($new_Loyalty['name'])) {
			$this->db->set('name', $new_Loyalty['name']);
		}
		
		if (isset($new_Loyalty['min_range'])) {
			$this->db->set('min_range', $new_Loyalty['min_range']);
		}
		
		if (isset($new_Loyalty['max_range'])) {
			$this->db->set('max_range',$new_Loyalty['max_range']);
		}
		
		if (isset($new_Loyalty['points'])) {
			$this->db->set('points',$new_Loyalty['points']);
		}
		
		if (isset($new_Loyalty['description'])) {
			$this->db->set('description', $new_Loyalty['description']);
		}
		
		if (isset($new_Loyalty['status']) AND $new_Loyalty['status'] === '1') {
			$this->db->set('status', $new_Loyalty['status']);
		} else {
			$this->db->set('status', '0');
		}
		
		if ( isset($new_Loyalty['validity']) AND isset($new_Loyalty['validity_times'])) {
			$this->db->set('validity', $new_Loyalty['validity']);

			if ($new_Loyalty['validity'] == 'fixed') {
				if (isset($new_Loyalty['validity_times']['fixed_date'])) {
					$this->db->set('fixed_date', mdate('%Y-%m-%d', strtotime($new_Loyalty['validity_times']['fixed_date'])));
				}

				if (isset($new_Loyalty['validity_times']['fixed_from_time'])) {
					$this->db->set('fixed_from_time',
					               mdate('%H:%i', strtotime($new_Loyalty['validity_times']['fixed_from_time'])));
				} else {
					$this->db->set('fixed_from_time', mdate('%H:%i', strtotime('12:00 AM')));
				}

				if (isset($new_Loyalty['validity_times']['fixed_to_time'])) {
					$this->db->set('fixed_to_time',
					               mdate('%H:%i', strtotime($new_Loyalty['validity_times']['fixed_to_time'])));
				} else {
					$this->db->set('fixed_to_time', mdate('%H:%i', strtotime('11:59 PM')));
				}
			} else if ($new_Loyalty['validity'] == 'period') {
				if (isset($new_Loyalty['validity_times']['period_start_date'])) {
					$this->db->set('period_start_date',
					               mdate('%Y-%m-%d', strtotime($new_Loyalty['validity_times']['period_start_date'])));
				}

				if (isset($new_Loyalty['validity_times']['period_end_date'])) {
					$this->db->set('period_end_date',
					               mdate('%Y-%m-%d', strtotime($new_Loyalty['validity_times']['period_end_date'])));
				}
			} else if ($new_Loyalty['validity'] == 'recurring') {
				if (isset($new_Loyalty['validity_times']['recurring_every'])) {
					$this->db->set('recurring_every', implode(', ', $new_Loyalty['validity_times']['recurring_every']));
				}

				if (isset($new_Loyalty['validity_times']['recurring_from_time'])) {
					$this->db->set('recurring_from_time',
					               mdate('%H:%i', strtotime($new_Loyalty['validity_times']['recurring_from_time'])));
				} else {
					$this->db->set('recurring_from_time', mdate('%H:%i', strtotime('12:00 AM')));
				}

				if (isset($new_Loyalty['validity_times']['recurring_to_time'])) {
					$this->db->set('recurring_to_time',
					               mdate('%H:%i', strtotime($new_Loyalty['validity_times']['recurring_to_time'])));
				} else {
					$this->db->set('recurring_to_time', mdate('%H:%i', strtotime('11:59 PM')));
				}
			}
		}
		if (is_numeric($loyalty_id)) {
			$this->db->where('loyalty_id', $loyalty_id);
			$query = $this->db->update('loyalty');
		} else {
			$this->db->set('date_added', mdate('%Y-%m-%d', time()));
			$query = $this->db->insert('loyalty');
			$coupon_id = $this->db->insert_id();
		}
		return $loyalty_id;
	}
	
	public function deleteLoyalty($loyalty_id) {
		print_r("hi");
		if (is_numeric($loyalty_id)) $loyalty_id = array($loyalty_id);

		if ( ! empty($loyalty_id) AND ctype_digit(implode('', $loyalty_id))) {
			$this->db->where_in('loyalty_id', $loyalty_id);
			$this->db->delete('loyalty');

			return $this->db->affected_rows();
		}
	}
}
?>