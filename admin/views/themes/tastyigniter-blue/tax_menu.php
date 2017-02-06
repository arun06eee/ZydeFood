<?php echo get_header(); ?>
<div class="row content">
	<div class="col-md-12">
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
								<div class="col-md-9 pull-left">
									<?php if (isset($tax_name)) { ?>
										<div class="form-group">
											<select name="filter_by_titles" class="form-control input-sm" class="form-control input-sm">
												<option value=""><?php echo lang('text_filter_taxTitles'); ?></option>
												<?php foreach ($tax_name as $tax_names) { ?>
													<?php if ($tax_names['tax_titles'] === $filter_by_titles) { ?>
														<option value="<?php echo $tax_names['tax_titles']; ?>" <?php echo set_select('filter_by_titles', $tax_names['tax_titles'], TRUE); ?> ><?php echo $tax_names['tax_titles']; ?></option>
													<?php } else { ?>
														<option value="<?php echo $tax_names['tax_titles']; ?>" <?php echo set_select('filter_by_titles', $tax_names['tax_titles']); ?> ><?php echo $tax_names['tax_titles']; ?></option>
													<?php } ?>
												<?php } ?>
											</select>&nbsp;
										</div>
									<?php } ?>
									<div class="input-group">
										<?php if (isset($filter_start_date)) { ?>
											<input type="text" name="filter_start_date" class="form-control input-sm date" value="<?php echo $filter_start_date; ?>" placeholder="start date" />
											<span class="input-group-addon"><i class="fa fa-calendar" style="color:black"></i></span>
										<?php } else { ?>
											<input type="text" name="filter_start_date" class="form-control input-sm date" value="" placeholder="Select date From.." />
											<span class="input-group-addon"><i class="fa fa-calendar" style="color:black"></i></span>
										<?php } ?>
									</div>
									<div class="input-group">
										<?php if (isset($filter_end_date)) { ?>
											<input type="text" name="filter_end_date" class="form-control input-sm date" value="<?php  echo $filter_end_date; ?>" placeholder="end date" />
											<span class="input-group-addon"><i class="fa fa-calendar" style="color:black"></i></span>
										<?php } else { ?>
											<input type="text" name="filter_end_date" class="form-control input-sm date" value="" placeholder="Select End date" />
											<span class="input-group-addon"><i class="fa fa-calendar" style="color:black"></i></span>
										<?php } ?>
									</div>
								</div>
								<div class="col-md-3 pull-right text-right">
									<a class="btn btn-grey" onclick="filterList();" title="<?php echo lang('text_search'); ?>"><i class="fa fa-search"></i></a>
									<a class="btn btn-grey" href="<?php echo page_url(); ?>" title="<?php echo lang('text_clear'); ?>"><i class="fa fa-times"></i></a>
								</div>
							</div>
						</div>
					</div>
				</form>
			</div>

			<form role="form" id="list-form" accept-charset="utf-8" method="POST" action="<?php echo current_url(); ?>">
				<div class="table-responsive">
					<table class="table table-striped table-border">
						<thead>
							<tr>
								<th><a class="sort" href="<?php echo $order_id; ?>"><?php echo lang('column_order_id'); ?><i class="fa fa-sort-<?php echo ($sort_by == 'order_id') ? $order_by_active : $order_by; ?>"></i></a></th>
								<th><a class="sort" href="<?php echo $tax_title; ?>"><?php echo lang('column_tax_title'); ?><i class="fa fa-sort-<?php echo ($sort_by == 'tax_title') ? $order_by_active : $order_by; ?>"></i></a></th>
								<th><a class="sort" href="<?php echo $tax_amount; ?>"><?php echo lang('column_tax_amount'); ?><i class="fa fa-sort-<?php echo ($sort_by == 'tax_amount') ? $order_by_active : $order_by; ?>"></i></a></th>
								<th><a class="sort" href="<?php echo $tax_date;  ?>"><?php echo lang('date_and_time'); ?><i class="fa fa-sort-<?php echo ($sort_by == 'date') ? $order_by_active : $order_by; ?>"></i></a></th>
							</tr>
						</thead>
						<tbody>
						<?php if(isset($tax_menus)) {?>
							<?php foreach ($tax_menus as $tax_menu) { ?>
								<tr>
									<td><?php echo $tax_menu['order_id']; ?></td>
									<td><?php echo $tax_menu['tax_title']; ?></td>
									<td><?php echo $tax_menu['tax_amount']; ?></td>
									<td><?php echo $tax_menu['date']; ?>-<?php echo  $tax_menu['time']; ?></td>
								</tr>
							<?php } ?>
						<?php } else { ?>
							<tr>
								<td colspan="6"><?php echo lang('text_empty'); ?></td>
							</tr>
						<?php } ?>
						</tbody>
					</table>
				</div>
			</form>

			<div class="pagination-bar clearfix">
				<div class="pull-right"><b>Total Tax amount: <?php echo $total; ?></b></div>
				<div class="links"><?php echo $pagination['linfks']; ?></div>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript"><!--
function filterList() {
	$('#filter-form').submit();
}
$(document).ready(function () {

	$('.date').datepicker({
		format: 'yyyy-mm-dd'
	});
});
//--></script>
<?php echo get_footer(); ?>