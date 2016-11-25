<?php if ( ! defined('BASEPATH')) exit('No direct access allowed');

class tax extends Admin_Controller {

	public function __construct() {
		parent::__construct(); //  calls the constructor

        $this->user->restrict('Admin.Tax');

		$this->load->model('Tax_model');

        $this->load->library('pagination');

       $this->lang->load('tax_lang');
	}

	public function index() {

		$url = '?';

		$this->template->setTitle($this->lang->line('text_title'));
		$this->template->setHeading($this->lang->line('text_heading'));
		$this->template->setButton($this->lang->line('button_new'), array('class' => 'btn btn-primary', 'href' => page_url() .'/edit'));
		$this->template->setButton($this->lang->line('button_delete'), array('class' => 'btn btn-danger', 'onclick' => 'confirmDelete();'));
		
		if ($this->input->post('delete') AND $this->_deleteTax() === TRUE) {
			redirect('tax');
		}

		$data['_tax'] = array();
		$results = $this->Tax_model->getTax();
		
		foreach ($results as $result) {
			$data['_tax'][] = array(
				'id'				=> $result['id'],
				'name'				=> $result['name'],
				'percentage'		=> $result['percentage'],
				'status'			=> ($result['status'] === '1') ? $this->lang->line('text_enabled') : $this->lang->line('text_disabled'),
				'edit' 				=> site_url('tax/edit?id=' . $result['id'])
			);
		}

		$config['base_url'] 		= site_url('tax'.$url);

		$this->pagination->initialize($config);

		$data['pagination'] = array(
			'info'		=> $this->pagination->create_infos(),
			'links'		=> $this->pagination->create_links()
		);

		$this->template->render('tax', $data);
	}

	public function edit() {
		$tax_info = $this->Tax_model->getEditTax((int) $this->input->get('id'));

		if ($tax_info) {
			$tax_id = $tax_info['id'];
			$data['_action']	= site_url('tax/edit?id='. $tax_id);
		} else {
		    $tax_id = 0;
			$data['_action']	= site_url('tax/edit');
		}

		$title = (isset($tax_info['name'])) ? $tax_info['name'] : $this->lang->line('text_new');
        $this->template->setTitle(sprintf($this->lang->line('text_edit_heading'), $title));
        $this->template->setHeading(sprintf($this->lang->line('text_edit_heading'), $title));

        $this->template->setButton($this->lang->line('button_save'), array('class' => 'btn btn-primary', 'onclick' => '$(\'#edit-form\').submit();'));
		$this->template->setButton($this->lang->line('button_save_close'), array('class' => 'btn btn-default', 'onclick' => 'saveClose();'));
		$this->template->setButton($this->lang->line('button_icon_back'), array('class' => 'btn btn-default', 'href' => site_url('tax')));

		$this->template->setStyleTag(assets_url('js/datepicker/datepicker.css'), 'datepicker-css');
		$this->template->setScriptTag(assets_url("js/datepicker/bootstrap-datepicker.js"), 'bootstrap-datepicker-js');
		$this->template->setStyleTag(assets_url('js/datepicker/bootstrap-timepicker.css'), 'bootstrap-timepicker-css');
		$this->template->setScriptTag(assets_url("js/datepicker/bootstrap-timepicker.js"), 'bootstrap-timepicker-js');

		if ($this->input->post() AND $tax_id = $this->_saveTax()) {
			if ($this->input->post('save_close') === '1') {
				redirect('tax');
			} 

			redirect('tax/edit?id='. $tax_id);
		}
		
		$data['id'] 				= $tax_info['id'];
		$data['name'] 				= $tax_info['name'];
		$data['percentage'] 		= $tax_info['percentage'];
		$data['status'] 			= $tax_info['status'];
		
		$this->template->render('tax_edit', $data);
	}

	public function _saveTax() {
		//if ($this->validateForm() === TRUE) {
			print_r($this->input->post());
            
			$save_type = ( ! is_numeric($this->input->get('id'))) ? $this->lang->line('text_added') : $this->lang->line('text_updated');

			if ($new_Tax = $this->Tax_model->Save_Tax($this->input->get('id'), $this->input->post())) {
                $this->alert->set('success', sprintf($this->lang->line('alert_success'), 'tax '.$save_type));
            } else {
			  redirect('tax');
            }
			return $new_Tax;
		//}
	}
	
	public function _deleteTax() {
		if ($this->input->post('delete')) {
            $deleted_rows = $this->Tax_model->deleteTax($this->input->post('delete'));

            if ($deleted_rows > 0) {
				$prefix = ($deleted_rows > 1) ? '['.$deleted_rows.'] _tax': 'tax';
				$this->alert->set('success', sprintf($this->lang->line('alert_success'), $prefix.' '.$this->lang->line('text_deleted')));
            } else {
				$this->alert->set('warning', sprintf($this->lang->line('alert_error_nothing'), $this->lang->line('text_deleted')));
            }

            return TRUE;
        }
    }
}