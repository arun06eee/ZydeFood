<?php if ( ! defined('BASEPATH')) exit('No direct access allowed');

class Local extends Main_Controller {

	public function __construct() {
		parent::__construct(); 																	// calls the constructor

		$this->load->model('Locations_model');
		$this->load->model('Pages_model');
		$this->load->model('Reviews_model');
		$this->load->model('Customers_model');
		$this->load->model('Addresses_model');
		$this->load->model('Orders_model');

		$this->load->library('location'); 														// load the location library
		$this->load->library('currency'); 														// load the currency library

		$this->lang->load('local');

		if ($this->input->post('cmd') == 'getStores') {
			$this->getLocationApi();
			exit;
		}
		if ($this->input->post('cmd') == 'getMenu'){
			if(!empty($this->input->post('storeid'))) {
				$this->getMenuApi();
			}else {
				print_r("page not found");
			}
			exit;
		}
		if ($this->input->post('cmd') == 'getLoyalty') {
			$this->getLoyaltyApi();
			exit;
		}
		if ($this->input->post('cmd') == 'getOrderHistory') {
			$this->getOrderHistoryApi();
			exit;
		}
		if ($this->input->post('cmd') == 'getAddress') {
			$this->getAddressApi();
			exit;
		}
		if ($this->input->post('cmd') == 'applyCoupons') {
			$this->applyCouponsApi();
			exit;
		}
		if ($this->input->post('cmd') == 'applyLoyalty') {
			$this->applyLoyaltyApi();
			exit;
		}
	}

	public function index() {

		if (!($location = $this->Locations_model->getLocation($this->input->get('location_id')))) {
			redirect('local/all');
		}

		$this->location->setLocation($location['location_id']);

		$this->template->setBreadcrumb('<i class="fa fa-home"></i>', '/');
		$this->template->setBreadcrumb($this->lang->line('text_heading'), 'local/all');
		$this->template->setBreadcrumb($location['location_name']);

		$text_heading = sprintf($this->lang->line('text_local_heading'), $location['location_name']);
		$this->template->setTitle($text_heading);
		$this->template->setScriptTag('js/jquery.mixitup.js', 'jquery-mixitup-js', '100330');

		$filter = array();

		if ($this->input->get('page')) {
			$filter['page'] = (int) $this->input->get('page');
		} else {
			$filter['page'] = '';
		}

		if ($this->config->item('menus_page_limit')) {
			$filter['limit'] = $this->config->item('menus_page_limit');
		}

		$filter['sort_by'] = 'menus.menu_priority';
		$filter['order_by'] = 'ASC';
		$filter['filter_status'] = '1';
		$filter['filter_category'] = (int) $this->input->get('category_id'); 									// retrieve 3rd uri segment else set FALSE if unavailable.

		$this->load->module('menus');
		$data['menu_list'] = $this->menus->getList($filter);

		$data['menu_total']	= $this->Menus_model->getCount();
		if (is_numeric($data['menu_total']) AND $data['menu_total'] < 150) {
			$filter['category_id'] = 0;
		}

		$data['location_name'] = $this->location->getName();

		$data['local_info'] = $this->info();

		$data['local_reviews'] = $this->reviews();

		$data['local_gallery'] = $this->gallery();

		$this->template->render('local', $data);
	}

	public function info($data = array()) {

		$time_format = ($this->config->item('time_format')) ? $this->config->item('time_format') : '%h:%i %a';

		if ($this->config->item('maps_api_key')) {
			$map_key = '&key=' . $this->config->item('maps_api_key');
		} else {
			$map_key = '';
		}

		$this->template->setScriptTag('https://maps.googleapis.com/maps/api/js?v=3' . $map_key .'&sensor=false&region=GB&libraries=geometry,places', 'google-maps-js', '104330');

		$data['has_delivery']       = $this->location->hasDelivery();
		$data['has_collection']     = $this->location->hasCollection();
		$data['opening_status']		= $this->location->workingStatus('opening');
		$data['delivery_status']	= $this->location->workingStatus('delivery');
		$data['collection_status']	= $this->location->workingStatus('collection');
		$data['holiday_status']		= $this->location->workingStatus('close');
		$data['last_order_time']    = mdate($time_format, strtotime($this->location->lastOrderTime()));
		$data['local_description']  = $this->location->getDescription();
		$data['map_address']        = $this->location->getAddress();                                        // retrieve local location data
		$data['location_telephone'] = $this->location->getTelephone();                                        // retrieve local location data

		$data['working_hours'] 		= $this->location->workingHours();                                //retrieve local restaurant opening hours from location library
		$data['working_type']      = $this->location->workingType();

		if (!$this->location->hasDelivery() OR empty($data['working_type']['delivery'])) {
			unset($data['working_hours']['delivery']);
		}

		if (!$this->location->hasCollection() OR empty($data['working_type']['collection'])) {
			unset($data['working_hours']['collection']);
		}

		$data['delivery_time'] = $this->location->deliveryTime();
		if ($data['delivery_status'] === 'closed') {
			$data['delivery_time'] = 'closed';
		} else if ($data['delivery_status'] === 'opening') {
			$data['delivery_time'] = $this->location->workingTime('delivery', 'open');
		}

		$data['collection_time'] = $this->location->collectionTime();
		if ($data['collection_status'] === 'closed') {
			$data['collection_time'] = 'closed';
		} else if ($data['collection_status'] === 'opening') {
			$data['collection_time'] = $this->location->workingTime('collection', 'open');
		}

		$local_payments = $this->location->payments();
		$payments = $this->extension->getAvailablePayments(FALSE);

		$payment_list = '';
		foreach ($payments as $code => $payment) {
			if ( empty($local_payments) OR in_array($code, $local_payments)) {
				$payment_list[] = $payment['name'];
			}
		}

		$data['payments'] = implode(', ', $payment_list);

		$area_colors = array('#F16745', '#FFC65D', '#7BC8A4', '#4CC3D9', '#93648D', '#404040', '#F16745', '#FFC65D', '#7BC8A4', '#4CC3D9', '#93648D', '#404040', '#F16745', '#FFC65D', '#7BC8A4', '#4CC3D9', '#93648D', '#404040', '#F16745', '#FFC65D');
		$data['area_colors'] = $area_colors;

		$conditions = array(
			'all'   => $this->lang->line('text_delivery_all_orders'),
			'above' => $this->lang->line('text_delivery_above_total'),
			'below' => $this->lang->line('text_delivery_below_total'),
		);

		$data['delivery_areas'] = array();
		$delivery_areas = $this->location->deliveryAreas();
		foreach ($delivery_areas as $area_id => $area) {
			if (isset($area['charge']) AND is_string($area['charge'])) {
				$area['charge'] = array(array(
					'amount' => $area['charge'],
					'condition' => 'above',
					'total' => (isset($area['min_amount'])) ? $area['min_amount'] : '0',
				));
			}

			$text_condition = '';
			foreach ($area['condition'] as $condition) {
				$condition = explode('|', $condition);

				$delivery = (isset($condition[0]) AND $condition[0] > 0) ? $this->currency->format($condition[0]) : $this->lang->line('text_free_delivery');
				$con = (isset($condition[1])) ? $condition[1] : 'above';
				$total = (isset($condition[2]) AND $condition[2] > 0) ? $this->currency->format($condition[2]) : $this->lang->line('text_no_min_total');

				if ($con === 'all') {
					$text_condition .= sprintf($conditions['all'], $delivery);
				} else if ($con === 'above') {
					$text_condition .= sprintf($conditions[$con], $delivery, $total) . ', ';
				} else if ($con === 'below') {
					$text_condition .= sprintf($conditions[$con], $total) . ', ';
				}
			}

			$data['delivery_areas'][] = array(
				'area_id'       => $area['area_id'],
				'name'          => $area['name'],
				'type'			=> $area['type'],
				'color'			=> $area_colors[(int) $area_id - 1],
				'shape'			=> $area['shape'],
				'circle'		=> $area['circle'],
				'condition'     => trim($text_condition, ', '),
			);
		}

		$data['location_lat'] = $data['location_lng'] = '';
		if ($local_info = $this->location->local()) {                                                            //if local restaurant data is available
			$data['location_lat'] = $local_info['location_lat'];
			$data['location_lng'] = $local_info['location_lng'];
		}

		return $data;
	}

	public function gallery($data = array()) {
		$gallery = $this->location->getGallery();

		if (empty($gallery) OR empty($gallery['images'])) {
			return $data;
		}

		$this->template->setScriptTag('js/jquery.bsPhotoGallery.js', 'jquery-bsPhotoGallery-js', '99330');

		$data['title'] = isset($gallery['title']) ? $gallery['title'] : '';
		$data['description'] = isset($gallery['description']) ? $gallery['description'] : '';

		foreach ($gallery['images'] as $key => $image) {
			if (isset($image['status']) AND $image['status'] !== '1') {
				$data['images'][$key] = array(
					'name'     => isset($image['name']) ? $image['name'] : '',
					'path'     => isset($image['path']) ? $image['path'] : '',
					'thumb'    => isset($image['path']) ? $this->Image_tool_model->resize($image['path']) : '',
					'alt_text' => isset($image['alt_text']) ? $image['alt_text'] : '',
					'status'   => $image['status'],
				);
			}
		}

		return $data;
	}

	public function reviews($data = array()) {
		$date_format = ($this->config->item('date_format')) ? $this->config->item('date_format') : '%d %M %y';

		$url = '&';
		$filter = array();
		$filter['location_id'] = (int) $this->location->getId();

		if ($this->input->get('page')) {
			$filter['page'] = (int) $this->input->get('page');
		} else {
			$filter['page'] = '';
		}

		if ($this->config->item('page_limit')) {
			$filter['limit'] = $this->config->item('page_limit');
		}

		$filter['filter_status'] = '1';

		$ratings = $this->config->item('ratings');
		$data['ratings'] = $ratings['ratings'];

		$data['reviews'] = array();
		$results = $this->Reviews_model->getList($filter);                                    // retrieve all customer reviews from getMainList method in Reviews model
		foreach ($results as $result) {
			$data['reviews'][] = array(                                                            // create array of customer reviews to pass to view
				'author'   => $result['author'],
				'city'     => $result['location_city'],
				'quality'  => $result['quality'],
				'delivery' => $result['delivery'],
				'service'  => $result['service'],
				'date'     => mdate($date_format, strtotime($result['date_added'])),
				'text'     => $result['review_text']
			);
		}

		$prefs['base_url'] = site_url('local?location_id='.$this->location->getId() . $url);
		$prefs['total_rows'] = $this->Reviews_model->getCount($filter);
		$prefs['per_page'] = $filter['limit'];

		$this->load->library('pagination');
		$this->pagination->initialize($prefs);

		$data['pagination'] = array(
			'info'  => $this->pagination->create_infos(),
			'links' => $this->pagination->create_links()
		);
		return $data;
	}

	public function all() {
		$this->load->library('country');
		$this->load->library('pagination');
		$this->load->library('cart'); 															// load the cart library
		$this->load->model('Image_tool_model');

		$url = '?';

		$filter = array();
		if ($this->input->get('page')) {
			$filter['page'] = (int) $this->input->get('page');
		} else {
			$filter['page'] = '';
		}

		if ($this->config->item('menus_page_limit')) {
			$filter['limit'] = $this->config->item('menus_page_limit');
		}

		$filter['filter_status'] = '1';
		$filter['order_by'] = 'ASC';

		if ($this->input->get('search')) {
			$filter['filter_search'] = $this->input->get('search');
			$url .= 'search='.$filter['filter_search'].'&';
		}

		if ($this->input->get('sort_by')) {
			$sort_by = $this->input->get('sort_by');

			if ($sort_by === 'newest') {
				$filter['sort_by'] = 'location_id';
				$filter['order_by'] = 'DESC';
			} else if ($sort_by === 'name') {
				$filter['sort_by'] = 'location_name';
			}

			$url .= 'sort_by=' . $sort_by . '&';
		}

		$this->template->setBreadcrumb('<i class="fa fa-home"></i>', '/');
		$this->template->setBreadcrumb($this->lang->line('text_heading'), 'local/all');

		$this->template->setTitle($this->lang->line('text_heading'));
		$this->template->setHeading($this->lang->line('text_heading'));

		$review_totals = $this->Reviews_model->getTotalsbyId();                                    // retrieve all customer reviews from getMainList method in Reviews model

		$data['locations'] = array();
		$locations = $this->Locations_model->getList($filter);

		if ($locations) {
			foreach ($locations as $location) {
				$this->location->setLocation($location['location_id'], FALSE);

				$opening_status = $this->location->workingStatus('opening');
				$delivery_status = $this->location->workingStatus('delivery');
				$collection_status = $this->location->workingStatus('collection');
				$holiday_status = $this->location->workingStatus('close');

				$delivery_time = $this->location->deliveryTime();
				if ($delivery_status === 'closed') {
					$delivery_time = 'closed';
				} else if ($delivery_status === 'opening') {
					$delivery_time = $this->location->workingTime('delivery', 'open');
				}

				$collection_time = $this->location->collectionTime();
				if ($collection_status === 'closed') {
					$collection_time = 'closed';
				} else if ($collection_status === 'opening') {
					$collection_time = $this->location->workingTime('collection', 'open');
				}
				
				if(!empty($location['holiday'])){					
					$holidays = unserialize($location['holiday']);
					foreach($holidays as $holiday) {
						if ($holiday['holiday_date'] == date("Y-m-d") AND $holiday['holiday_status'] == '1') {
							$delivery_status = 'closed';
							$collection_status = 'closed';
							$opening_status = 'closed';
							$data['holiday']['reason'] = $holiday['reason'];
						}
					}
				}

				$review_totals = isset($review_totals[$location['location_id']]) ? $review_totals[$location['location_id']] : 0;

				$data['locations'][] = array(                                                            // create array of menu data to be sent to view
					'location_id'       => $location['location_id'],
					'location_name'     => $location['location_name'],
					'description'       => (strlen($location['description']) > 120) ? substr($location['description'], 0, 120) . '...' : $location['description'],
					'address'           => $this->location->getAddress(TRUE),
					'total_reviews'     => $review_totals,
					'location_image'    => $this->location->getImage(),
					'is_opened'         => $this->location->isOpened(),
					'is_closed'         => $this->location->isClosed(),
					'opening_status'    => $opening_status,
					'delivery_status'   => $delivery_status,
					'collection_status' => $collection_status,
					'holiday_status'	=> $holiday_status,
					'delivery_time'     => $delivery_time,
					'collection_time'   => $collection_time,
					'opening_time'      => $this->location->openingTime(),
					'closing_time'      => $this->location->closingTime(),
					'min_total'         => $this->location->minimumOrder($this->cart->total()),
					'delivery_charge'   => $this->location->deliveryCharge($this->cart->total()),
					'has_delivery'      => $this->location->hasDelivery(),
					'has_collection'    => $this->location->hasCollection(),
					'last_order_time'   => $this->location->lastOrderTime(),
					'distance'   		=> round($this->location->checkDistance()),
					'distance_unit'   	=> $this->config->item('distance_unit') === 'km' ? $this->lang->line('text_kilometers') : $this->lang->line('text_miles'),
					'href'              => site_url('local?location_id=' . $location['location_id']),
				);
			}
		}

		if (!empty($sort_by) AND $sort_by === 'distance') {
			$data['locations'] = sort_array($data['locations'], 'distance');
		} else if (!empty($sort_by) AND $sort_by === 'rating') {
			$data['locations'] = sort_array($data['locations'], 'total_reviews');
		}

		$config['base_url'] 		= site_url('local/all'.$url);
		$config['total_rows'] 		= $this->Locations_model->getCount($filter);
		$config['per_page'] 		= $filter['limit'];

		$this->pagination->initialize($config);

		$data['pagination'] = array(
			'info'		=> $this->pagination->create_infos(),
			'links'		=> $this->pagination->create_links()
		);

		$this->location->initialize();

		$data['locations_filter'] = $this->filter($url);

		$this->template->render('local_all', $data);
	}

	public function filter() {
		$url = '';

		$data['search'] = '';
		if ($this->input->get('search')) {
			$data['search'] = $this->input->get('search');
			$url .= 'search='.$this->input->get('search').'&';
		}

		$filters['distance']['name'] = lang('text_filter_distance');
		$filters['distance']['href'] = site_url('local/all?'.$url.'sort_by=distance');

		$filters['newest']['name'] = lang('text_filter_newest');
		$filters['newest']['href'] = site_url('local/all?'.$url.'sort_by=newest');

		$filters['rating']['name'] = lang('text_filter_rating');
		$filters['rating']['href'] = site_url('local/all?'.$url.'sort_by=rating');

		$filters['name']['name'] = lang('text_filter_name');
		$filters['name']['href'] = site_url('local/all?'.$url.'sort_by=name');

		$data['sort_by'] = '';
		if ($this->input->get('sort_by')) {
			$data['sort_by'] = $this->input->get('sort_by');
			$url .= 'sort_by=' . $data['sort_by'];
		}

		$data['filters'] = $filters;

		$url = (!empty($url)) ? '?'.$url : '';
		$data['search_action'] = site_url('local/all'.$url);

		return $data;
	}

	public function getLocationApi(){
		$customer_detail = $this->Customers_model->getCustomerByEmail($this->input->post('useremail'));			//get customers details
		$data['loyalty'] = $customer_detail['current_points'];													//get loyalty points of customer

		$data['tax'] = array();
		$this->load->model('Tax_model');																		//get available tax details
		$tax = $this->Tax_model->getTaxApi();
		foreach ($tax as $taxes) {
			$data['tax'][] = array(
				'name' 		 => $taxes['name'],
				'percentage' => $taxes['percentage']
			);
		}
		
		$address = json_decode($this->input->post('address'),true);												//get address from request
		
		$getaddress = $this->location->searchRestaurant($address['postcode']);										//search restaurant by zipcode

		if (is_array($getaddress)) {
			if ($this->input->post('newaddress') == 'y') {
				$this->Addresses_model->saveAddress($customer_detail['customer_id'], $address_id=FALSE,$address);		//if it's new address save to DB
			}
			$ordertype = $this->input->post('ordertype');
			$timestamp = $this->input->post('timestamp')/1000;
			if (!empty($timestamp)) {
				$date = date("y-m-d", $timestamp);															//get time and date from timestamp
				$time = date('G:i', $timestamp);
			}

			$review_totals = $this->Reviews_model->getTotalsbyId();                                    // retrieve all customer reviews from getMainList method in Reviews model

			$data['locations'] = array();
			$locations = $this->Locations_model->getListApi($getaddress['location_id']);				//get available locations details
			if ($locations) {
				foreach ($locations as $location) {
					$this->location->setLocation($location['location_id'], FALSE);
					$lastordertime = date("G:i", strtotime($this->location->lastOrderTime()));
					$closingtime = date("G:i", strtotime($this->location->closingTime()));

					$opening_status = $this->location->workingStatus('opening');
					$delivery_status = $this->location->workingStatus('delivery');
					$collection_status = $this->location->workingStatus('collection');
					$holiday = unserialize($location['holiday']);
					if (! empty($holiday)) {
						if ($location['offer_delivery'] == '1' OR $location['offer_collection'] == '1' ) {				//check delivery or pickup available in restaurant
							foreach ($holiday as $holidays) {													//check for holidays to the restaurant
								if ($holidays['holiday_date'] == $date AND $holidays['holiday_status'] == 1){
									$opening_status = 'closed';
									$delivery_status = 'closed';
									$collection_status = 'closed';
									$reason = $holidays['reason'];
								}else if ($ordertype == 'delivery' AND $lastordertime <= $time) {
									$delivery_status = 'closed';
								} else if ($ordertype == 'pickup'){
									if($lastordertime <= $time AND $closingtime <= $time) {
										$collection_status = 'closed';
										$delivery_status = 'closed';
										$opening_status = 'closed';
									} else if ($closingtime >= $time AND $lastordertime <= $time) {
										$collection_status = 'open';
										$opening_status = 'open';
										$delivery_status = 'closed';
									}
								}
							}
						}
					}

					$delivery_time = $this->location->deliveryTime();
					if ($delivery_status === 'closed') {
						$delivery_time = 'closed';
					} else if ($delivery_status === 'open') {
						$delivery_time = $this->location->workingTime('delivery', 'open');
					}

					$collection_time = $this->location->collectionTime();
					if ($collection_status === 'closed') {
						$collection_time = 'closed';
					} else if ($collection_status === 'open') {
						$collection_time = $this->location->workingTime('collection', 'open');
					}
					
					if($opening_status == 'opening') $opening_status = 'closed';
					if($delivery_status == 'opening') $delivery_status = 'closed';
					if($collection_status == 'opening') $collection_status = 'closed';

					$review_totals = isset($review_totals[$location['location_id']]) ? $review_totals[$location['location_id']] : 0;

					$data['locations'][] = array(                                                            // create array of menu data to be sent to view
						'location_id'       => $location['location_id'],
						'location_name'     => $location['location_name'],
						'description'       => (strlen($location['description']) > 120) ? substr($location['description'], 0, 120) . '...' : $location['description'],
						'address'           => $this->location->AddressApi(TRUE),
						'total_reviews'     => $review_totals,
						'location_image'    => utf8_encode($this->location->getImage()),
						'is_opened'         => $this->location->isOpened(),
						'is_closed'         => $this->location->isClosed(),
						'opening_status'    => utf8_encode($opening_status),
						'delivery_status'   => utf8_encode($delivery_status),
						'collection_status' => utf8_encode($collection_status),
						'delivery_time'     => $delivery_time,
						'collection_time'   => $collection_time,
						'reason'			=> utf8_encode($reason),
						'opening_time'      => $this->location->openingTime(),
						'closing_time'      => $this->location->closingTime(),
						'min_total'         => utf8_encode($this->location->minimumOrder($this->cart->total())),
						'delivery_charge'   => utf8_encode($this->location->deliveryCharge($this->cart->total())),
						'has_delivery'      => $this->location->hasDelivery(),
						'has_collection'    => $this->location->hasCollection(),
						'last_order_time'   => $this->location->lastOrderTime(),
						'distance'   		=> round($this->location->checkDistance()),
						'distance_unit'   	=> $this->config->item('distance_unit') === 'km' ? $this->lang->line('text_kilometers') : $this->lang->line('text_miles'),
						'href'              => site_url('local?location_id=' . $location['location_id']),
					);
				}
			}

			if (!empty($sort_by) AND $sort_by === 'distance') {
				$data['locations'] = sort_array($data['locations'], 'distance');
			} else if (!empty($sort_by) AND $sort_by === 'rating') {
				$data['locations'] = sort_array($data['locations'], 'total_reviews');
			}

			$data['status'] = 1;
		} else {
			$data['status'] = 0;
			$data['err_msg'] = utf8_encode($getaddress);
		}
		$this->location->initialize();

		print_r(json_encode($data));
	}

	public function getMenuApi() {
		$data['status'] = 1;
		$this->load->module('menus');
		$data['menu_count']	= $this->Menus_model->getCount();
		$data['categories_count'] = $this->Categories_model->getCount();
		$data['menu_list'] = $this->menus->getListAPI();
		$data['menu_option_count'] = count($data['menu_list']['option_values']);
		
		if (! empty($data['menu_list']['menus'])){
			$data['err_msg'] = "";
		}else {
			$data['err_msg'] = "No menus available";
		}
		print_r(json_encode($data));
	}

	public function getLoyaltyApi() {
		$email = $this->input->post('useremail');
		$customer_detail = $this->Customers_model->getCustomerByEmail($email);
		if (! empty($customer_detail)) {
			$data['status'] = 1;
			$data['err_msg'] = '';
			$data['total_points'] = $customer_detail['current_points'];
			$data['user_name'] = $customer_detail['first_name'].' '.$customer_detail['last_name'];

			$data['history'] = '';
			$filter = array('email' => $email);
			$history = $this->Orders_model->getList($filter);

			foreach ($history as $histories) {
				if (! empty ($histories['redeem_points_provide'])) {
					$data['history'][] = array(
						'location'  => $histories['location_name'],
						'date'		=> $histories['order_date'],
						'points'	=> $histories['redeem_points_provide'],
						'operation' => 'add'
					);
				}
				if (! empty($histories['redeem_points'])) {
					$data['history'][] = array(
						'location'  => $histories['location_name'],
						'date'		=> $histories['order_date'],
						'points'	=> $histories['redeem_points'],
						'operation' => 'redeem'
					);
				}
			}
		} else {
			$data['status'] = 0;
			$data['err_msg'] = 'FAILED';
		}
		print_r(json_encode($data));
	}

	public function getOrderHistoryApi() {
		$data['status'] = 1;
		$data['err_msg'] = '';
		$email = $this->input->post('useremail');
		$customer_detail = $this->Customers_model->getCustomerByEmail($email);
		if (! empty($customer_detail)){
			$filter = array('email' =>$email);
			$data['orderhistory'] = array();
			$result = $this->Orders_model->getList($filter);
			foreach ($result as $results) {
				if ($results['order_type'] == 1) $ordertype = 'delivery';
				if ($results['order_type'] == 2) $ordertype = 'pickup';

				$data['orderhistory'][] = array(
					'location'		=> $results['location_name'],
					'date'			=> $results['order_date'].' '.$results['order_time'],
					'order_number'	=> $results['order_id'],
					'order_type'	=> $ordertype,
					'order_status'	=> $results['status_name'],
					'price'			=> $results['net_total']
				);
			}
		}else {
			$data['status'] = 0;
			$data['err_msg'] = 'Failure while getting Order History.';
		}
	print_r(json_encode($data));
	}

	public function getAddressApi() {
		$email = $this->input->post('useremail');
		$customer_detail = $this->Customers_model->getCustomerByEmail($email);
		
		if (! empty($customer_detail)) {
			$data['status'] = 1;
			$data['err_msg'] = '';
			$data['address'] = array();
			$result = $this->Addresses_model->getList($filter = array('customer_id' => $customer_detail['customer_id']));
			foreach ($result as $results) {
				$data['address'][] = array(
					'Nickname'		=> '',
					'addr'			=> $results['address_1'],
					'suite'			=> '',
					'city'			=> $results['city'],
					'state'			=> $results['state'],
					'zipcode'		=> $results['postcode']
				);
			}
		} else {
			$data['status'] = 0;
			$data['err_msg'] = 'The userEmail sent is not available or invalid.';
		}
	print_r(json_encode($data));
	}

	public function applyCouponsApi() {
		$this->load->module('cart_module');
		$apply = $this->cart_module->couponApi();

		if ($apply['type'] == 'P' OR $apply['type'] == 'F') {
			if ($apply['type'] == 'P') $apply['type'] = 'percentage';
			if ($apply['type'] == 'F') $apply['type'] = 'Fixedvalue';

			$data['status'] = 1;
			$data['err_msg'] = '';
			$data['type'] = $apply['type'];
			$data['value'] = $apply['value'];
		} else if ($apply == 'removed') {
			$data['status'] = 1;
			$data['err_msg'] = '';
		}else {
			$data['status'] = 0;
			$data['err_msg'] = $apply;
		}
	print_r(json_encode($data));
	}

	public function applyLoyaltyApi() {
		$this->load->module('cart_module');
		$apply = $this->cart_module->loyaltyApi();
		if (is_array ($apply)) {
			$data['status'] = 1;
			$data['err_msg'] = '';
			$data['used_points'] = $apply['used_points'];
			$data['amount'] = round($apply['amount']);
		}else {
			$data['status'] = 0;
			$data['err_msg'] = $apply;
		}
	print_r(json_encode($data));
	}
}
/* End of file local.php */
/* Location: ./main/controllers/local.php */