<?php if ( ! defined('BASEPATH')) exit('No direct access allowed');

class Loyalty extends Admin_Controller {

	public function __construct() {
		parent::__construct(); //  calls the constructor

        $this->user->restrict('Admin.Loyalty');

      //  $this->load->model('Loyalty_model');

        $this->load->library('pagination');

        $this->lang->load('loyalty');
	}

	public function index() {

		$this->template->setTitle($this->lang->line('text_title'));
		$this->template->setHeading($this->lang->line('text_heading'));
		$this->template->setButton($this->lang->line('button_new'), array('class' => 'btn btn-primary', 'href' => page_url() .'/edit'));
		$this->template->setButton($this->lang->line('button_delete'), array('class' => 'btn btn-danger', 'onclick' => 'confirmDelete();'));
		
		$url = '?';
		$data['loyalty'] = array();
		
		$this->template->render('loyalty', $data);
	}

	public function edit() {
		//$loyalty_info = $this->loyalty_model->getCoupon((int) $this->input->get('id'));
		$loyalty_info  ="";
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

		if ($this->input->post() AND $coupon_id = $this->_saveCoupon()) {
			if ($this->input->post('save_close') === '1') {
				redirect('loyalty');
			}

			redirect('loyalty/edit?id='. $coupon_id);
		}

		$this->template->render('loyalty_edit', $data);
	}
	
}