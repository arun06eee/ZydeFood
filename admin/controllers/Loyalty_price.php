<?php if ( ! defined('BASEPATH')) exit('No direct access allowed');

class loyalty_price extends Admin_Controller {

	public function __construct() {
		parent::__construct(); //  calls the constructor

        $this->user->restrict('Admin.Loyalty_price');

		$this->load->model('Loyalty_price_model');

        $this->load->library('pagination');

       $this->lang->load('loyalty_price_lang');
	}
	
	public function index() {

		$url = '?';

		$this->template->setTitle($this->lang->line('text_title'));
		$this->template->setHeading($this->lang->line('text_heading'));
		$this->template->setButton($this->lang->line('button_delete'), array('class' => 'btn btn-danger', 'onclick' => 'confirmDelete();'));
		
		if ($this->input->post('delete') AND $this->_deleteLoyaltyPrice() === TRUE) {
			redirect('loyalty_price');
		}

		$data['loyalty_prices'] = array();
		$results = $this->Loyalty_price_model->getPriceList();
		
		foreach ($results as $result) {
			$data['loyalty_prices'][] = array(
				'id'				=> $result['id'],
				'name'				=> $result['Name'],
				'discount'			=> $result['discount'],
				'edit' 				=> site_url('loyalty_price/edit?id=' . $result['id'])
			);
		}

		$config['base_url'] 		= site_url('loyalty_price'.$url);

		$this->pagination->initialize($config);

		$data['pagination'] = array(
			'info'		=> $this->pagination->create_infos(),
			'links'		=> $this->pagination->create_links()
		);

		$this->template->render('loyalty_price', $data);
	}

	public function edit() {
		$loyalty_price_info = $this->Loyalty_price_model->getEditLoyaltyPrice((int) $this->input->get('id'));

		if ($loyalty_price_info) {
			$loyalty_price_id = $loyalty_price_info['id'];
			$data['_action']	= site_url('loyalty_price/edit?id='. $loyalty_price_id);
		} else {
		    $loyalty_price_id = 0;
			$data['_action']	= site_url('loyalty_price/edit');
		}

		$title = (isset($loyalty_price_info['Name'])) ? $loyalty_price_info['Name'] : $this->lang->line('text_new');
        $this->template->setTitle(sprintf($this->lang->line('text_edit_heading'), $title));
        $this->template->setHeading(sprintf($this->lang->line('text_edit_heading'), $title));

        $this->template->setButton($this->lang->line('button_save'), array('class' => 'btn btn-primary', 'onclick' => '$(\'#edit-form\').submit();'));
		$this->template->setButton($this->lang->line('button_save_close'), array('class' => 'btn btn-default', 'onclick' => 'saveClose();'));
		$this->template->setButton($this->lang->line('button_icon_back'), array('class' => 'btn btn-default', 'href' => site_url('loyalty_price')));

		$this->template->setStyleTag(assets_url('js/datepicker/datepicker.css'), 'datepicker-css');
		$this->template->setScriptTag(assets_url("js/datepicker/bootstrap-datepicker.js"), 'bootstrap-datepicker-js');
		$this->template->setStyleTag(assets_url('js/datepicker/bootstrap-timepicker.css'), 'bootstrap-timepicker-css');
		$this->template->setScriptTag(assets_url("js/datepicker/bootstrap-timepicker.js"), 'bootstrap-timepicker-js');

		if ($this->input->post() AND $loyalty_price_id = $this->_saveLoyaltyPrice()) {
			if ($this->input->post('save_close') === '1') {
				redirect('loyalty_price');
			} 

			redirect('loyalty_price/edit?id='. $loyalty_price_id);
		}
		
		$data['id'] 				= $loyalty_price_info['id'];
		$data['name'] 				= $loyalty_price_info['Name'];
		$data['discount'] 			= $loyalty_price_info['discount'];
		
		$this->template->render('loyalty_price_edit', $data);
	}

	public function _saveLoyaltyPrice() {
		//if ($this->validateForm() === TRUE) {
			print_r($this->input->post());
            
			$save_type = ( ! is_numeric($this->input->get('id'))) ? $this->lang->line('text_added') : $this->lang->line('text_updated');

			if ($new_Loyalty = $this->Loyalty_price_model->Save_LoyaltyPrice($this->input->get('id'), $this->input->post())) {
                $this->alert->set('success', sprintf($this->lang->line('alert_success'), 'loyalty Price' .$save_type));
            } else {
			  redirect('loyalty_price');
            }
			return $new_Loyalty;
		//}
	}
	
	public function _deleteLoyaltyPrice() {
		if ($this->input->post('delete')) {
            $deleted_rows = $this->Loyalty_price_model->deleteLoyaltyPrice($this->input->post('delete'));

            if ($deleted_rows > 0) {
				$prefix = ($deleted_rows > 1) ? '['.$deleted_rows.'] loyalty_prices': 'loyalty_price';
				$this->alert->set('success', sprintf($this->lang->line('alert_success'), $prefix.' '.$this->lang->line('text_deleted')));
            } else {
				$this->alert->set('warning', sprintf($this->lang->line('alert_error_nothing'), $this->lang->line('text_deleted')));
            }

            return TRUE;
        }
    }
}