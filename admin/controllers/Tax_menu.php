<?php if ( ! defined('BASEPATH')) exit('No direct access allowed');

class Tax_menu extends Admin_Controller {

	public function __construct() {
		parent::__construct(); //  calls the constructor

        $this->user->restrict('Admin.Tax_menu');

		$this->load->model('Orders_model');
		$this->load->model('Tax_model');

        $this->load->library('pagination');

        $this->lang->load('tax_menu_lang');

	}

	public function index() {

		$filter = array();
		$url = '?';
		if ($this->input->get('page')) {
			$filter['page'] = (int) $this->input->get('page');
		} else {
			$filter['page'] = '';
		}

		if ($this->config->item('page_limit')) {
			$filter['limit'] = $this->config->item('page_limit');
		}

		if ($this->input->get('filter_by_titles')) {
			$filter['filter_by_titles'] = $data['filter_by_titles'] = $this->input->get('filter_by_titles');
			$url .= 'filter_by_titles'.$filter['filter_by_titles'].'&';
		} else {
			$filter['filter_by_titles'] = $data['filter_by_titles'] = '';
		}

		if($this->input->get('filter_start_date')) {
			$filter['filter_start_date'] = $data['filter_start_date'] = $this->input->get('filter_start_date');
			$url .= 'filter_start_date'.$filter['filter_start_date'].'&';
		} else {
			$filter['filter_start_date'] = $data['filter_start_date'] = '';
		}

		if($this->input->get('filter_end_date')) {
			$filter['filter_end_date'] = $data['filter_end_date'] = $this->input->get('filter_end_date');
			$url .= 'filter_end_date'.$filter['filter_end_date'].'&';
		} else {
			$filter['filter_end_date'] = $data['filter_end_date'] = '';
		}

		$this->template->setTitle($this->lang->line('text_title'));
		$this->template->setHeading($this->lang->line('text_heading'));

		$this->template->setStyleTag(assets_url('js/datepicker/datepicker.css'), 'datepicker-css');
		$this->template->setScriptTag(assets_url("js/datepicker/bootstrap-datepicker.js"), 'bootstrap-datepicker-js');

		$data['tax_name'] = array();
		$tax_title = $this->Tax_model->getTax();

		foreach ($tax_title as $tax_titles) {
			$data['tax_name'][] = array(
				'tax_titles' => $tax_titles['name'],
			);
		}

		$data['tax_menus'] = array();
		$results = $this->Orders_model->getList($filter);

		foreach ($results as $result) {
			$tax_data = unserialize( $result['tax_details'] );
			foreach($tax_data as $tax_datas) {
				$title = strstr($tax_datas['title'], '-', true);
				if(trim($title) == $this->input->get('filter_by_titles')){
					$data['tax_menus'][] = array(
						'order_id'		=> $result['order_id'],
						'tax_title'		=> $title,
						'tax_amount'	=> round($tax_datas['amount']),
						'time'			=> mdate('%H:%i', strtotime($result['order_time'])),
						'date'			=> day_elapsed($result['order_date']),
					);
					$data['total'] += round($tax_datas['amount']);
				} else
					if (!($this->input->get('filter_by_titles'))) {
					$data['tax_menus'][] = array(
						'order_id'		=> $result['order_id'],
						'tax_title'		=> $title,
						'tax_amount'	=> round($tax_datas['amount']),
						'time'			=> mdate('%H:%i', strtotime($result['order_time'])),
						'date'			=> day_elapsed($result['order_date']),
					);
					$data['total'] += round($tax_datas['amount']);
				}
			}
		}

		$config['base_url'] 	= site_url('tax_menu'.$url);
		$config['per_page'] 	= $filter['limit'];

		$this->pagination->initialize($config);

		$data['pagination'] = array(
			'info'		=> $this->pagination->create_infos(),
			'links'		=> $this->pagination->create_links()
		);
		
		$this->template->render('tax_menu', $data);
	}
}
	