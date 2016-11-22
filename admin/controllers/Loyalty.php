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

		$this->template->setTitle($this->lang->line('text_title'));
		$this->template->setHeading($this->lang->line('text_heading'));
		$this->template->setButton($this->lang->line('button_new'), array('class' => 'btn btn-primary', 'href' => page_url() .'/edit'));
		$this->template->setButton($this->lang->line('button_delete'), array('class' => 'btn btn-danger', 'onclick' => 'confirmDelete();'));
		
		$url = '?';
		
		$data['loyalties'] = array();
		$results = $this->Loyalty_model->getLoyalty();
		foreach ($results as $result) {
			$data['loyalties'][] = array(
				'loyalty_id'	=> $result['loyalty_id'],
				'name'			=> $result['name'],
				'min_range'		=> $result['min_range'],
				'max_range'		=> $result['max_range'],
				'status'		=> ($result['status'] === '1') ? $this->lang->line('text_enabled') : $this->lang->line('text_disabled'),
				'edit' 			=> site_url('coupons/edit?id=' . $result['loyalty_id'])
			);
		}
		
		$this->template->render('loyalty', $data);
	}

	public function edit() {
		//$loyalty_info = $this->loyalty_model->getEditLoyalty((int) $this->input->get('id'));
		
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
		
		/* $data['loyalty_histories'] = array();
		$loyalty_histories = $this->Coupons_model->getCouponHistories($coupon_id);
		foreach ($loyalty_histories as $loyalty_history) {
			$data['loyalty_histories'][] = array(
				'coupon_history_id'	=> $loyalty_history['loyalty_history_id'],
				'order_id'			=> $loyalty_history['order_id'],
				'customer_name'		=> $loyalty_history['first_name'] .' '. $loyalty_history['last_name'],
				'date_used'			=> mdate('%d %M %y', strtotime($loyalty_history['date_used'])),
				'view'				=> site_url('orders/edit?id='. $loyalty_history['order_id'])
			);
		} */

		$this->template->render('Loyalty_edit', $data);
		
	}
	
	public function _saveLoyalty() {
		$new_Loyalty = $this->Loyalty_model->Save_Loyalty($this->input->post());
	}
	
}