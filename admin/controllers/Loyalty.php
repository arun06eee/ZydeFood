<?php if ( ! defined('BASEPATH')) exit('No direct access allowed');

class Loyalty extends Admin_Controller {

	public function __construct() {
		parent::__construct(); //  calls the constructor

        $this->user->restrict('Admin.Loyalty');

		$this->load->model('Loyalty_model');

        $this->load->library('pagination');

        $this->lang->load('loyalty_lang');
	}

	public function index() {
		
		$url = '?';
		$filter = array();
		if ($this->input->get('page')) {
			$filter['page'] = (int) $this->input->get('page');
		} else {
			$filter['page'] = '';
		}

		if ($this->config->item('page_limit')) {
			$filter['limit'] = $this->config->item('page_limit');
		}

		if ($this->input->get('filter_search')) {
			$filter['filter_search'] = $data['filter_search'] = $this->input->get('filter_search');
			$url .= 'filter_search='.$filter['filter_search'].'&';
		} else {
			$data['filter_search'] = '';
		}

		if (is_numeric($this->input->get('filter_status'))) {
			$filter['filter_status'] = $data['filter_status'] = $this->input->get('filter_status');
			$url .= 'filter_status='.$filter['filter_status'].'&';
		} else {
			$filter['filter_status'] = $data['filter_status'] = '';
		}

		if ($this->input->get('sort_by')) {
			$filter['sort_by'] = $data['sort_by'] = $this->input->get('sort_by');
		} else {
			$filter['sort_by'] = $data['sort_by'] = 'loyalty_id';
		}

		if ($this->input->get('order_by')) {
			$filter['order_by'] = $data['order_by'] = $this->input->get('order_by');
			$data['order_by_active'] = $this->input->get('order_by') .' active';
		} else {
			$filter['order_by'] = $data['order_by'] = 'DESC';
			$data['order_by_active'] = 'DESC';
		}

		$this->template->setTitle($this->lang->line('text_title'));
		$this->template->setHeading($this->lang->line('text_heading'));
		$this->template->setButton($this->lang->line('button_new'), array('class' => 'btn btn-primary', 'href' => page_url() .'/edit'));
		$this->template->setButton($this->lang->line('button_delete'), array('class' => 'btn btn-danger', 'onclick' => 'confirmDelete();'));
		
		if ($this->input->post('delete') AND $this->_deleteLoyalty() === TRUE) {
			redirect('loyalty');
		}
		
		$order_by = (isset($filter['order_by']) AND $filter['order_by'] == 'ASC') ? 'DESC' : 'ASC';
		$data['sort_name'] 				= site_url('loyalty'.$url.'sort_by=name&order_by='.$order_by);
		$data['sort_min_range'] 		= site_url('loyalty'.$url.'sort_by=min_range&order_by='.$order_by);
		$data['sort_max_range'] 		= site_url('loyalty'.$url.'sort_by=max_range&order_by='.$order_by);
		$data['sort_status'] 			= site_url('loyalty'.$url.'sort_by=status&order_by='.$order_by);
		$data['sort_points']			= site_url('loyalty'.$url.'sort_by=status&order_by='.$order_by);

		$data['loyalties'] = array();
		$results = $this->Loyalty_model->getList($filter);
		
		foreach ($results as $result) {
			$data['loyalties'][] = array(
				'loyalty_id'	=> $result['loyalty_id'],
				'name'			=> $result['name'],
				'min_range'		=> $result['min_range'],
				'max_range'		=> $result['max_range'],
				'points'		=> $result['points'],
				'status'		=> ($result['status'] === '1') ? $this->lang->line('text_enabled') : $this->lang->line('text_disabled'),
				'edit' 			=> site_url('loyalty/edit?id=' . $result['loyalty_id'])
			);
		}

		if ($this->input->get('sort_by') AND $this->input->get('order_by')) {
			$url .= 'sort_by='.$filter['sort_by'].'&';
			$url .= 'order_by='.$filter['order_by'].'&';
		}

		$config['base_url'] 		= site_url('loyalty'.$url);
		$config['total_rows'] 		= $this->Loyalty_model->getCount($filter);
		$config['per_page'] 		= $filter['limit'];

		$this->pagination->initialize($config);

		$data['pagination'] = array(
			'info'		=> $this->pagination->create_infos(),
			'links'		=> $this->pagination->create_links()
		);
		
		$this->template->render('loyalty', $data);
	}

	public function edit() {																//edit loyalty method
		$loyalty_info = $this->Loyalty_model->getEditLoyalty((int) $this->input->get('id'));
		if ($loyalty_info) {
			$loyalty_id = $loyalty_info['loyalty_id'];
			$data['_action']	= site_url('loyalty/edit?id='. $loyalty_id);
		} else {
		    $loyalty_id = 0;
			$data['_action']	= site_url('loyalty/edit');
		}
		
		if ($this->input->post('validity')) {
			$validity = $this->input->post('validity');
		} else if (!empty($loyalty_info['validity'])) {
			$validity = $loyalty_info['validity'];
		} else {
			$validity = 'forever';
		}
		
		$title = (isset($loyalty_info['name'])) ? $loyalty_info['name'] : $this->lang->line('text_new');
        $this->template->setTitle(sprintf($this->lang->line('text_edit_heading'), $title));
        $this->template->setHeading(sprintf($this->lang->line('text_edit_heading'), $title));

        $this->template->setButton($this->lang->line('button_save'), array('class' => 'btn btn-primary', 'onclick' => '$(\'#edit-form\').submit();'));
		$this->template->setButton($this->lang->line('button_save_close'), array('class' => 'btn btn-default', 'onclick' => 'saveClose();'));
		$this->template->setButton($this->lang->line('button_icon_back'), array('class' => 'btn btn-default', 'href' => site_url('loyalty')));

		$this->template->setStyleTag(assets_url('js/datepicker/datepicker.css'), 'datepicker-css');
		$this->template->setScriptTag(assets_url("js/datepicker/bootstrap-datepicker.js"), 'bootstrap-datepicker-js');
		$this->template->setStyleTag(assets_url('js/datepicker/bootstrap-timepicker.css'), 'bootstrap-timepicker-css');
		$this->template->setScriptTag(assets_url("js/datepicker/bootstrap-timepicker.js"), 'bootstrap-timepicker-js');

		if ($this->input->post() AND $coupon_id = $this->_saveLoyalty()) {
			if ($this->input->post('save_close') === '1') {
				redirect('loyalty');
			}

			redirect('loyalty/edit?id='. $coupon_id);
		}
		
		//edit loyalty info
		
		$data['loyalty_id'] 		= $loyalty_info['loyalty_id'];
		$data['name'] 				= $loyalty_info['name'];
		$data['min_range'] 			= $loyalty_info['min_range'];
		$data['max_range'] 			= $loyalty_info['max_range'];
		$data['description'] 		= $loyalty_info['description'];
		$data['points']				= $loyalty_info['points'];
		$data['validity'] 			= $validity;
		$data['fixed_date'] 		= (empty($loyalty_info['fixed_date']) OR $loyalty_info['fixed_date'] === '0000-00-00') ? '' : mdate('%d-%m-%Y', strtotime($loyalty_info['fixed_date']));
		$data['fixed_from_time'] 	= (empty($loyalty_info['fixed_from_time']) OR $loyalty_info['fixed_from_time'] === '00:00:00') ? '' : mdate('%h:%i %a', strtotime($loyalty_info['fixed_from_time']));
		$data['fixed_to_time'] 		= (empty($loyalty_info['fixed_to_time']) OR $loyalty_info['fixed_to_time'] === '00:00:00') ? '' : mdate('%h:%i %a', strtotime($loyalty_info['fixed_to_time']));
		$data['period_start_date'] 	= (empty($loyalty_info['period_start_date']) OR $loyalty_info['period_start_date'] === '0000-00-00') ? '' : mdate('%d-%m-%Y', strtotime($loyalty_info['period_start_date']));
		$data['period_end_date'] 	= (empty($loyalty_info['period_end_date']) OR $loyalty_info['period_end_date'] === '0000-00-00') ? '' : mdate('%d-%m-%Y', strtotime($loyalty_info['period_end_date']));
		$data['recurring_every'] 	= (empty($loyalty_info['recurring_every'])) ? array() : explode(', ', $loyalty_info['recurring_every']);
		$data['recurring_from_time'] = (empty($loyalty_info['recurring_from_time']) OR $loyalty_info['recurring_from_time'] === '00:00:00') ? '' : mdate('%h:%i %a', strtotime($loyalty_info['recurring_from_time']));
		$data['recurring_to_time'] 	= (empty($loyalty_info['recurring_to_time']) OR $loyalty_info['recurring_to_time'] === '00:00:00') ? '' : mdate('%h:%i %a', strtotime($loyalty_info['recurring_to_time']));
		$data['date_added'] 		= $loyalty_info['date_added'];
		$data['status'] 			= $loyalty_info['status'];

		$data['weekdays'] = array('Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun');

		$data['fixed_time'] 		= $this->lang->line('text_24_hour');
		if (isset($loyalty_info['fixed_from_time'], $loyalty_info['fixed_to_time']) AND ($loyalty_info['fixed_from_time'] !== '00:00:00' OR $loyalty_info['fixed_to_time'] !== '23:59:00')) {
			$data['fixed_time'] 	= $this->lang->line('text_custom');
		}

		$data['recurring_time'] 		= $this->lang->line('text_24_hour');
		if (isset($loyalty_info['recurring_from_time'], $loyalty_info['recurring_to_time']) AND ($loyalty_info['recurring_from_time'] !== '00:00:00' OR $loyalty_info['recurring_to_time'] !== '23:59:00')) {
			$data['recurring_time'] 	= $this->lang->line('text_custom');
		}
		
		$this->template->render('Loyalty_edit', $data);
	}
	
	public function _saveLoyalty() {																					//save loyalty method
		if ($this->validateForm() === TRUE) {
            
			$save_type = ( ! is_numeric($this->input->get('id'))) ? $this->lang->line('text_added') : $this->lang->line('text_updated');

			if ($new_Loyalty = $this->Loyalty_model->Save_Loyalty($this->input->get('id'), $this->input->post())) {		//goes to loyalty model Save_Loyalty
                $this->alert->set('success', sprintf($this->lang->line('alert_success'), 'loyalty'.$save_type));
            } else {
			  redirect('loyalty');
            }
			return $new_Loyalty;
		}
	}
	
	public function _deleteLoyalty() {																//loyalty delete method
		if ($this->input->post('delete')) {
            $deleted_rows = $this->Loyalty_model->deleteLoyalty($this->input->post('delete'));		//goes to delete loyalty model method deleteLoyalty

            if ($deleted_rows > 0) {
				$prefix = ($deleted_rows > 1) ? '['.$deleted_rows.'] Loyalties': 'loyalty';
				$this->alert->set('success', sprintf($this->lang->line('alert_success'), $prefix.' '.$this->lang->line('text_deleted')));
            } else {
				$this->alert->set('warning', sprintf($this->lang->line('alert_error_nothing'), $this->lang->line('text_deleted')));
            }

            return TRUE;
        }
    }
	
	private function validateForm() {						//loyalty form validation

		$this->form_validation->set_rules('name', 'lang:label_name', 'xss_clean|trim|required|min_length[2]|max_length[128]');

		$this->form_validation->set_rules('description', 'lang:label_description', 'xss_clean|trim|min_length[2]|max_length[1028]');
		$this->form_validation->set_rules('validity', 'lang:label_validity', 'xss_clean|trim|required');

		if ($this->input->post('validity') === 'fixed') {
			$this->form_validation->set_rules('validity_times[fixed_date]', 'lang:label_fixed_date', 'xss_clean|trim|required|valid_date');
			$this->form_validation->set_rules('fixed_time', 'lang:label_fixed_time', 'xss_clean|trim|required');

			if ($this->input->post('fixed_time') !== '24hours') {
				$this->form_validation->set_rules('validity_times[fixed_from_time]', 'lang:label_fixed_from_time', 'xss_clean|trim|required|valid_time');
				$this->form_validation->set_rules('validity_times[fixed_to_time]', 'lang:label_fixed_to_time', 'xss_clean|trim|required|valid_time');
			}
		} else if ($this->input->post('validity') === 'period') {
			$this->form_validation->set_rules('validity_times[period_start_date]', 'lang:label_period_start_date', 'xss_clean|trim|required|valid_date');
			$this->form_validation->set_rules('validity_times[period_end_date]', 'lang:label_period_end_date', 'xss_clean|trim|required|valid_date');
		} else if ($this->input->post('validity') === 'recurring') {
			$this->form_validation->set_rules('validity_times[recurring_every]', 'lang:label_recurring_every', 'xss_clean|trim|required');
			if (isset($_POST['validity_times']['recurring_every'])) {
				foreach ($_POST['validity_times']['recurring_every'] as $key => $value) {
					$this->form_validation->set_rules('validity_times[recurring_every]['.$key.']', 'lang:label_recurring_every', 'xss_clean|required');
				}
			}

			$this->form_validation->set_rules('recurring_time', 'lang:label_recurring_time', 'xss_clean|trim|required');
			if ($this->input->post('recurring_time') !== '24hours') {
				$this->form_validation->set_rules('validity_times[recurring_from_time]', 'lang:label_recurring_from_time', 'xss_clean|trim|required|valid_time');
				$this->form_validation->set_rules('validity_times[recurring_to_time]', 'lang:label_recurring_to_time', 'xss_clean|trim|required|valid_time');
			}
		}

		$this->form_validation->set_rules('status', 'lang:label_status', 'xss_clean|trim|required|integer');

		if ($this->form_validation->run() === TRUE) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
}