<?php echo get_header(); ?>
<div class="row content">
	<div class="col-md-12">
	
	<div class="row mini-statistics">
            <div class="col-xs-12 col-sm-6 col-lg-3">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-xs-4 stat-icon">
                                <span class="bg-blue"><i class="stat-icon fa fa-cart-arrow-down fa-2x"></i></span>
                            </div>
                            <div class="col-xs-8 stat-content">
                                <span class="stat-text text-blue Recived_orders">0</span>
                                <span class="stat-heading text-blue">Recived Orders</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6 col-lg-3">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-xs-4 stat-icon">
                                <span class="bg-primary"><i class="stat-icon fa fa-spoon fa-2x"></i></span>
                            </div>
                            <div class="col-xs-8 stat-content">
                                <span class="stat-text text-primary pre_pen_orders"></span>
                                <span class="stat-heading text-primary">Prepration & Pending Orders</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6 col-lg-3">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-xs-4 stat-icon">
                                <span class="bg-green"><i class="fa fa-truck fa-2x"></i></span>
                            </div>
                            <div class="col-xs-8 stat-content">
                                <span class="stat-text text-green sales comp_delivr_orders"></span>
                                <span class="stat-heading text-green ">Completed & Delivered Orders</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6 col-lg-3">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-xs-4 stat-icon">
                                <span class="bg-red"><i class="stat-icon fa fa-times fa-2x"></i></span>
                            </div>
                            <div class="col-xs-8 stat-content">
                                <span class="stat-text text-red tables_reserved canceled_orders">0</span>
                                <span class="stat-heading text-red">Canceled Orders</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </div>
	
		<div class="panel panel-default panel-table">
			<div class="panel-heading">
				<h3 class="panel-title"><?php echo lang('text_list'); ?></h3>
				<div class="panel-body">
					<div class="col-xs-12 col-sm-3">
						<label for="input-assign-staff" class="control-label"><?php echo lang('label_assign_staff'); ?></label>
						<div class="">
							<input type="hidden" name="old_assignee_id" value="<?php echo $assignee_id; ?>" />
							<input type="hidden" name="old_status_id" value="<?php echo $status_id; ?>" />
							<select name="assignee_id" class="form-control">
								<option value=""><?php echo lang('text_please_select'); ?></option>
								<?php
									if($staffs){
									foreach ($staffs as $staff) { ?>
									<?php if ($staff['staff_id'] === $assignee_id) { ?>
										<option value="<?php echo $staff['staff_id']; ?>" <?php echo set_select('assignee_id', $staff['staff_id'], TRUE); ?> ><?php echo $staff['staff_name']; ?></option>
									<?php } else { ?>
										<option value="<?php echo $staff['staff_id']; ?>" <?php echo set_select('assignee_id', $staff['staff_id']); ?> ><?php echo $staff['staff_name']; ?></option>
									<?php } ?>
								<?php } ?>
								<?php } ?>
							</select>
							<?php echo form_error('assignee_id', '<span class="text-danger">', '</span>'); ?>
						</div>
					</div>
					<div class="col-xs-12 col-sm-2">
						<label for="input-name" class="control-label"><?php echo lang('label_status'); ?></label>
						<div class="">
							<select name="order_status" id="" class="form-control">
								<?php foreach ($statuses as $status) { ?>
									<?php if ($status['status_id'] === $status_id) { ?>
										<option value="<?php echo $status['status_id']; ?>" <?php echo set_select('order_status', $status['status_id'], TRUE); ?> ><?php echo $status['status_name']; ?></option>
									<?php } else { ?>
										<option value="<?php echo $status['status_id']; ?>" <?php echo set_select('order_status', $status['status_id']); ?> ><?php echo $status['status_name']; ?></option>
									<?php } ?>
								<?php } ?>
							</select>
							<?php echo form_error('order_status', '<span class="text-danger">', '</span>'); ?>
						</div>
					</div>
					<div class="col-xs-12 col-sm-5">
						<label for="input-name" class="control-label"><?php echo lang('label_comment'); ?></label>
						<div class="">
							<textarea name="status_comment" id="" class="form-control" rows="3"><?php echo set_value('status_comment'); ?></textarea>
							<?php echo form_error('status_comment', '<span class="text-danger">', '</span>'); ?>
						</div>
					</div>
					<div class="col-xs-12 col-sm-2">
					<a class="btn btn-success" style="background-color:#4cae4c" onclick="Confirmsubmit()"><i class="fa fa-paper-plane"></i> Submit</a>
					</div>
				</div>
				<div id="local-alert" class="alert alert-danger alert-dismissable hidden" role="alert">
					<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button><p>Couldn't Update!! please select Orders.</p>
				</div>
			</div>
		</div>
		<div class="panel panel-default panel-table">
			<div class="panel-heading">
				<h3 class="panel-title"><?php echo lang('text_list'); ?></h3>
				<div class="pull-right">
					<button class="btn btn-filter btn-xs"><i class="fa fa-filter"></i></button>
				</div>
			</div>
			<div class="panel-body panel-filter">
				<form role="form" id="filter-form" accept-charset="utf-8" method="GET" action="<?php echo current_url(); ?>">
					<div class="filter-bar">
						<div class="form-inline">
							<div class="row">
								<div class="col-md-3 pull-right text-right">
									<div class="form-group">
										<input type="text" name="filter_search" class="form-control input-sm" value="<?php echo $filter_search; ?>" placeholder="<?php echo lang('text_filter_search'); ?>" />&nbsp;&nbsp;&nbsp;
									</div>
									<a class="btn btn-grey" onclick="filterList();" title="<?php echo lang('text_search'); ?>"><i class="fa fa-search"></i></a>
								</div>

								<div class="col-md-9 pull-left">
									<?php if (!$user_strict_location) { ?>
										<div class="form-group">
											<select name="filter_location" class="form-control input-sm" class="form-control input-sm">
												<option value=""><?php echo lang('text_filter_location'); ?></option>
												<?php foreach ($locations as $location) { ?>
													<?php if ($location['location_id'] === $filter_location) { ?>
														<option value="<?php echo $location['location_id']; ?>" <?php echo set_select('filter_location', $location['location_id'], TRUE); ?> ><?php echo $location['location_name']; ?></option>
													<?php } else { ?>
														<option value="<?php echo $location['location_id']; ?>" <?php echo set_select('filter_location', $location['location_id']); ?> ><?php echo $location['location_name']; ?></option>
													<?php } ?>
												<?php } ?>
											</select>&nbsp;
										</div>
									<?php } ?>
									<div class="form-group">
										<select name="filter_status" class="form-control input-sm">
											<option value=""><?php echo lang('text_filter_status'); ?></option>
											<?php foreach ($statuses as $status) { ?>
											<?php if ($status['status_id'] === $filter_status) { ?>
												<option value="<?php echo $status['status_id']; ?>" <?php echo set_select('filter_status', $status['status_id'], TRUE); ?> ><?php echo $status['status_name']; ?></option>
											<?php } else { ?>
												<option value="<?php echo $status['status_id']; ?>" <?php echo set_select('filter_status', $status['status_id']); ?> ><?php echo $status['status_name']; ?></option>
											<?php } ?>
											<?php } ?>
											<option value="0" <?php echo ($filter_status === '0') ? 'selected' : ''; ?>><?php echo lang('text_lost_orders'); ?></option>
										</select>&nbsp;
									</div>
									<div class="form-group">
										<select name="filter_type" class="form-control input-sm">
											<option value=""><?php echo lang('text_filter_order_type'); ?></option>
										<?php if ($filter_type === '1') { ?>
											<option value="1" <?php echo set_select('filter_type', '1', TRUE); ?> ><?php echo lang('text_delivery'); ?></option>
											<option value="2" <?php echo set_select('filter_type', '2'); ?> ><?php echo lang('text_collection'); ?></option>
										<?php } else if ($filter_type === '2') { ?>
											<option value="1" <?php echo set_select('filter_type', '1'); ?> ><?php echo lang('text_delivery'); ?></option>
											<option value="2" <?php echo set_select('filter_type', '2', TRUE); ?> ><?php echo lang('text_collection'); ?></option>
										<?php } else { ?>
											<option value="1" <?php echo set_select('filter_type', '1'); ?> ><?php echo lang('text_delivery'); ?></option>
											<option value="2" <?php echo set_select('filter_type', '2'); ?> ><?php echo lang('text_collection'); ?></option>
										<?php } ?>
										</select>&nbsp;
									</div>
									<div class="form-group">
										<select name="filter_payment" class="form-control input-sm">
											<option value=""><?php echo lang('text_filter_payment'); ?></option>
											<?php foreach ($payments as $payment) { ?>
												<?php if ($payment['name'] === $filter_payment) { ?>
													<option value="<?php echo $payment['name']; ?>" <?php echo set_select('filter_payment', $payment['name'], TRUE); ?> ><?php echo $payment['title']; ?></option>
												<?php } else { ?>
													<option value="<?php echo $payment['name']; ?>" <?php echo set_select('filter_payment', $payment['name']); ?> ><?php echo $payment['title']; ?></option>
												<?php } ?>
											<?php } ?>
										</select>&nbsp;
									</div>
									<div class="form-group">
										<select name="filter_date" class="form-control input-sm">
											<option value=""><?php echo lang('text_filter_date'); ?></option>
											<?php foreach ($order_dates as $key => $value) { ?>
											<?php if ($key === $filter_date) { ?>
												<option value="<?php echo $key; ?>" <?php echo set_select('filter_date', $key, TRUE); ?> ><?php echo $value; ?></option>
											<?php } else { ?>
												<option value="<?php echo $key; ?>" <?php echo set_select('filter_date', $key); ?> ><?php echo $value; ?></option>
											<?php } ?>
											<?php } ?>
										</select>
									</div>
									<a class="btn btn-grey" onclick="filterList();" title="<?php echo lang('text_filter'); ?>"><i class="fa fa-filter"></i></a>&nbsp;
									<a class="btn btn-grey" href="<?php echo page_url(); ?>" title="<?php echo lang('text_clear'); ?>"><i class="fa fa-times"></i></a>
								</div>
							</div>
						</div>
					</div>
				</form>
			</div>

			<form role="form" id="list-form" accept-charset="utf-8" method="POST" action="<?php echo current_url(); ?>">
				<div id="local" class="alert alert-warning alert-dismissable hidden" role="alert">
					<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button><p>The order must reach the <span style="color:red"><b>completed</b></span> order status before generating an invoice.</p>
				</div>
				<div class="table-responsive">
				<table id="OrderTable" border="0" class="table table-striped table-border">
					<thead>
						<tr>
							<th class="action"><input type="checkbox" onclick="$('input[name*=\'delete\']').prop('checked', this.checked);"></th>
							<th><a class="sort" href="<?php echo $sort_id; ?>"><?php echo lang('column_id'); ?><i class="fa fa-sort-<?php echo ($sort_by == 'order_id') ? $order_by_active : $order_by; ?>"></i></a></th>
							<th><a class="sort" href="<?php echo $sort_location; ?>"><?php echo lang('column_location'); ?><i class="fa fa-sort-<?php echo ($sort_by == 'location_name') ? $order_by_active : $order_by; ?>"></i></a></th>
							<th><a class="sort" href="<?php echo $sort_customer; ?>"><?php echo lang('column_customer_name'); ?><i class="fa fa-sort-<?php echo ($sort_by == 'first_name') ? $order_by_active : $order_by; ?>"></i></a></th>
							<th><a class="sort" href="<?php echo $sort_status; ?>"><?php echo lang('column_status'); ?><i class="fa fa-sort-<?php echo ($sort_by == 'status_name') ? $order_by_active : $order_by; ?>"></i></a></th>
							<th><a class="sort" href="<?php echo $sort_type; ?>"><?php echo lang('column_type'); ?><i class="fa fa-sort-<?php echo ($sort_by == 'order_type') ? $order_by_active : $order_by; ?>"></i></a></th>
							<th><a class="sort" href="<?php echo $sort_payment; ?>"><?php echo lang('column_payment'); ?><i class="fa fa-sort-<?php echo ($sort_by == 'payment') ? $order_by_active : $order_by; ?>"></i></a></th>
							<th><a class="sort" href="<?php echo $sort_total; ?>"><?php echo lang('column_total'); ?><i class="fa fa-sort-<?php echo ($sort_by == 'order_total') ? $order_by_active : $order_by; ?>"></i></a></th>
							<th class="text-center"><a class="sort" href="<?php echo $sort_date; ?>"><?php echo lang('column_time_date'); ?><i class="fa fa-sort-<?php echo ($sort_by == 'date_added') ? $order_by_active : $order_by; ?>"></i></a></th>
							<th class="download_invoice">Invoice</th>
						</tr>
					</thead>
					<tbody>
						<?php if ($orders) { ?>
						<?php foreach ($orders as $order) { ?>
						<tr>
							<td class="action"><input type="checkbox" value="<?php echo $order['order_id']; ?>" name="delete[]" />&nbsp;&nbsp;&nbsp;
								<a class="btn btn-edit" title="<?php echo lang('text_edit'); ?>" href="<?php echo $order['edit']; ?>"><i class="fa fa-pencil"></i></a></td>
							<td><?php echo $order['order_id']; ?></td>
							<td><?php echo $order['location_name']; ?></td>
							<td><?php echo $order['first_name'] .' '. $order['last_name']; ?></td>
                            <td><span class="label label-default" style="background-color: <?php echo $order['status_color']; ?>;"><?php echo $order['order_status']; ?></span></td>
							<td><?php echo $order['order_type']; ?></td>
							<td><?php echo $order['payment']; ?></td>
							<td><?php echo $order['net_total']; ?></td>
							<td class="text-center"><?php echo $order['order_time']; ?> - <?php echo $order['order_date']; ?></td>
							<td><a onclick="fndownload(<?php echo $order['order_id']?> , '<?php echo $order['order_status'] ?>' )" class="show_invoice btn btn-success btn-xs" title="<?php echo lang('button_download_invoice'); ?>"><i class="fa fa-download"></i></a></td>
						</tr>
						<?php } ?>
						<?php } else { ?>
						<tr>
							<td colspan="10"><?php echo lang('text_empty'); ?></td>
						</tr>
						<?php } ?>
					</tbody>
				</table>
				</div>
			</form>

			<div class="pagination-bar clearfix">
				<div class="links"><?php echo $pagination['links']; ?></div>
				<div class="info"><?php echo $pagination['info']; ?></div>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript"><!--
$(document).ready(function () {
	
	requestURL("orders/Status_count", dataUpdateOrder);
	//setInterval(function(){
		requestURL("orders/Status_count", dataUpdateOrder);
	//},1000);
	
	function dataUpdateOrder(data) {
		$(".pre_pen_orders").empty();
		$(".comp_delivr_orders").empty();
		$(".pre_pen_orders").append("0");
		$(".comp_delivr_orders").append("0");
		for(var i=0; i<data.length; i++){
			if(data[i].status_id == 11){
				$(".Recived_orders").empty();
				$(".Recived_orders").append(data[i].count);
			}
			else 
				if(data[i].status_id == 12 || data[i].status_id == 13){
				var y = parseInt(data[i].count,10);
				x = parseInt($(".pre_pen_orders").html()) || 0;
				$(".pre_pen_orders").empty();
				$(".pre_pen_orders").append(x+y);
			}
			else 
				if(data[i].status_id == 14 || data[i].status_id == 15){
				var p = parseInt(data[i].count,10);
				q = parseInt($(".comp_delivr_orders").html()) || 0;
				$(".comp_delivr_orders").empty();
				$(".comp_delivr_orders").append(p+q);
			}
			else 
				if(data[i].status_id == 19){
				$(".canceled_orders").empty();
				$(".canceled_orders").append(data[i].count);
			}
		}
	}
	
	function requestURL(url, callback){
		$.ajax ({
			type: "GET",
			url: "orders/Status_count",
			dataType: "json",
			success: function(data) {
				callback(data);
			}
		})
	}
});

function filterList() {
	$('#filter-form').submit();
}

function fndownload(id, status) {
		$("#local").addClass('hidden');
	if (status == "Completed") {
		$(".show_invoice").attr('href','orders/invoice/view/'+id);
		$(".show_invoice").attr('target','_blank');
	}else{
		$("#local").removeClass('hidden');
	}
}

function Confirmsubmit() {
	$('#local-alert').addClass('hidden');
	var checkbox_status = [];
	$('input[name*=\'delete\']:checked').each(function() {
		checkbox_status.push($(this).val());
	});
	var data = {
		"checkbox_status"	: checkbox_status,
		"status_id"			: $('select[name="order_status"]').val(),
		"assignee_id"		: $('select[name="assignee_id"]').val(),
		"status_comment"	: $('textarea[name="status_comment"]').val()
	};
	if  (checkbox_status != '') {
		$.ajax({
			type: "POST",
			url: "Orders/orderStatusChange?",
			data: {"selected_order":data},
			success: function() {
				window.location.href = ['orders'];
			}
		});
	}else{
		$('#local-alert').removeClass('hidden');
	}
}

//--></script>
<?php echo get_footer(); ?>