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
								<div class="col-md-3 pull-right text-right">
									<div class="form-group">
										<input type="text" name="filter_search" class="form-control input-sm" value="<?php echo $filter_search; ?>" placeholder="<?php echo lang('text_filter_search'); ?>" />&nbsp;&nbsp;&nbsp;
									</div>
									<a class="btn btn-grey" onclick="filterList();" title="<?php echo lang('text_search'); ?>"><i class="fa fa-search"></i></a>
								</div>

								<div class="col-md-8 pull-left">
									<div class="form-group">
										<select name="filter_type" class="form-control input-sm">
												<option value=""><?php echo lang('text_filter_type'); ?></option>
											<?php if ($filter_type === 'min_range') { ?>
												<option <?php echo set_select('filter_type', 'min_range', TRUE); ?> ><?php echo lang('column_min_range'); ?></option>
												<option <?php echo set_select('filter_type', 'max_range'); ?> ><?php echo lang('column_max_range'); ?></option>
												<option <?php echo set_select('filter_type', 'points'); ?> ><?php echo lang('label_points'); ?></option>
											<?php } else if ($filter_type === 'max_range') { ?>
												<option <?php echo set_select('filter_type', 'min_range'); ?> ><?php echo lang('column_min_range'); ?></option>
												<option <?php echo set_select('filter_type', 'max_range', TRUE); ?> ><?php echo lang('column_max_range'); ?></option>
												<option <?php echo set_select('filter_type', 'points', TRUE); ?> ><?php echo lang('label_points'); ?></option>
											<?php } else { ?>
												<option <?php echo set_select('filter_type', 'min_range'); ?> ><?php echo lang('column_min_range'); ?></option>
												<option <?php echo set_select('filter_type', 'max_range'); ?> ><?php echo lang('column_max_range'); ?></option>
												<option <?php echo set_select('filter_type', 'points'); ?> ><?php echo lang('label_points'); ?></option>
											<?php } ?>
										</select>&nbsp;
									</div>
									<div class="form-group">
										<select name="filter_status" class="form-control input-sm">
											<option value=""><?php echo lang('text_filter_status'); ?></option>
										<?php if ($filter_status === '1') { ?>
											<option value="1" <?php echo set_select('filter_status', '1', TRUE); ?> ><?php echo lang('text_enabled'); ?></option>
											<option value="0" <?php echo set_select('filter_status', '0'); ?> ><?php echo lang('text_disabled'); ?></option>
										<?php } else if ($filter_status === '0') { ?>
											<option value="1" <?php echo set_select('filter_status', '1'); ?> ><?php echo lang('text_enabled'); ?></option>
											<option value="0" <?php echo set_select('filter_status', '0', TRUE); ?> ><?php echo lang('text_disabled'); ?></option>
										<?php } else { ?>
											<option value="1" <?php echo set_select('filter_status', '1'); ?> ><?php echo lang('text_enabled'); ?></option>
											<option value="0" <?php echo set_select('filter_status', '0'); ?> ><?php echo lang('text_disabled'); ?></option>
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
				<div class="table-responsive">
					<table class="table table-striped table-border">
						<thead>
							<tr>
								<th class="action"><input type="checkbox" onclick="$('input[name*=\'delete\']').prop('checked', this.checked);"></th>
								<th><a class="sort" href="<?php echo $sort_name; ?>"><?php echo lang('column_name'); ?><i class="fa fa-sort-<?php echo ($sort_by == 'name') ? $order_by_active : $order_by; ?>"></i></a></th>
								<th><a class="sort" href="<?php echo $sort_min_range; ?>"><?php echo lang('column_min_range'); ?><i class="fa fa-sort-<?php echo ($sort_by == 'min_range') ? $order_by_active : $order_by; ?>"></i></a></th>
								<th><a class="sort" href="<?php echo $sort_max_range; ?>"><?php echo lang('column_max_range'); ?><i class="fa fa-sort-<?php echo ($sort_by == 'max_range') ? $order_by_active : $order_by; ?>"></i></a></th>
								<th><a class="sort" href="<?php echo $sort_points; ?>"><?php echo lang('label_points'); ?><i class="fa fa-sort-<?php echo ($sort_by == 'points') ? $order_by_active : $order_by; ?>"></i></a></th>
								<th class="text-center"><?php echo lang('column_status'); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php if ($loyalties) {?>
							<?php foreach ($loyalties as $loyalty) { ?>
							<tr>
								<td class="action"><input type="checkbox" value="<?php echo $loyalty['loyalty_id']; ?>" name="delete[]" />&nbsp;&nbsp;&nbsp;
									<a class="btn btn-edit" title="<?php echo lang('text_edit'); ?>" href="<?php echo $loyalty['edit']; ?>"><i class="fa fa-pencil"></i></a></td>
								<td><?php echo $loyalty['name']; ?></td>
								<td><?php echo $loyalty['min_range']; ?></td>
								<td><?php echo $loyalty['max_range']; ?></td>
								<td><?php echo $loyalty['points']; ?></td>
								<td class="text-center"><?php echo $loyalty['status']; ?></td>
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
				<div class="links"><?php echo $pagination['links']; ?></div>
				<div class="info"><?php echo $pagination['info']; ?></div>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript"><!--
function filterList() {
	$('#filter-form').submit();
}
//--></script>
<?php echo get_footer(); ?>