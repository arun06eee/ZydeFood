<?php if ( ! defined('BASEPATH')) exit('No direct access allowed');

class Login extends Main_Controller {

	public function __construct() {
		parent::__construct();
		$this->load->model('Pages_model');
		$this->lang->load('account/login_register');
		
		if ($this->input->post('cmd') == 'login') {
			$this->loginApi();
			exit;
		}

		if ($this->input->post('cmd') == 'forgotpassword') {
			$this->forgotpasswordApi();
			exit;
		}
	}

	public function index() {

//		if($this->input->get('cmd') != 'login'){
			if ($this->customer->islogged()) { 														// checks if customer is logged in then redirect to account page.
				redirect('account/account');
			}

			$this->template->setTitle($this->lang->line('text_heading'));

			$data['reset_url'] 				= site_url('account/reset');
			$data['register_url'] 			= site_url('account/register');
//		}

		if ($this->input->post()) {																// checks if $_POST data is set
			if ($this->validateForm() === TRUE) {
				$email = $this->input->post('email');											// retrieves email value from $_POST data if set
				$password = $this->input->post('password');										// retrieves password value from $_POST data if set

				if ($this->customer->login($email, $password) === FALSE) {						// invoke login method in customer library with email and password $_POST data value then check if login was unsuccessful
				//	$this->alert->set('alert', $this->lang->line('alert_invalid_login'));		// display error message and redirect to account login page
					
					if($this->input->get('cmd') == 'login'){
						$apiArrary = [];
						$apiArrary["code"] = 1;
						$apiArrary["status"] = "fail";
						$apiArrary["message"] = $this->lang->line('alert_invalid_login');
						print_r($apiArrary);
					}else {
//						redirect(current_url());
					}
    			} else {																		// else if login was successful redirect to account page
                    log_activity($this->customer->getId(), 'logged in', 'customers', get_activity_message('activity_logged_in',
                        array('{customer}', '{link}'),
                        array($this->customer->getName(), admin_url('customers/edit?id='.$this->customer->getId()))
                    ));

					if($this->input->get('cmd') == 'login'){
						
					
					}else {
						
						if ($redirect_url = $this->input->get('redirect')) {
							redirect($redirect_url);
						}

						redirect('account/account');
					}
  				}
    		}
		}

	//	if($this->input->get('cmd') != 'login'){
			$this->template->render('account/login', $data);
	//	}
	}

	private function validateForm() {
		// START of form validation rules
		$this->form_validation->set_rules('email', 'lang:label_email', 'xss_clean|trim|required|valid_email');
		$this->form_validation->set_rules('password', 'lang:label_password', 'xss_clean|trim|required|min_length[6]|max_length[32]');
		// END of form validation rules

		if ($this->form_validation->run() === TRUE) {										// checks if form validation routines ran successfully
			return TRUE;
		} else {
			return FALSE;
		}
	}

	public function loginApi() {
		$email = $this->input->post('email');
		$password = $this->input->post('password');

		if ($this->customer->login($email, $password) === FALSE) { 
			$data['code'] = 0;
			$data['status'] = 'fail';
			$data['message'] = 'Invalid Email or password';
		} else {
			$data['code'] = 1;
			$data['status'] = 'success';
			$data['message'] = '';
		}
		print_r(json_encode($data));
	}

	public function forgotpasswordApi() {
		$this->load->module('reset');
		$reset = $this->reset->resetPasswordApi();
	}
}

/* End of file login.php */
/* Location: ./main/controllers/login.php */