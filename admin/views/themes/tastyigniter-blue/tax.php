<?php echo get_header(); ?>
<div class="row content">
	<div class="col-md-12">
		<div class="panel panel-default panel-table">

			<form role="form" id="list-form" accept-charset="utf-8" method="POST" action="<?php echo current_url(); ?>">
				<div class="table-responsive">
					<table class="table table-striped table-border">
						<thead>
							<tr>
								<th class="action"><input type="checkbox" onclick="$('input[name*=\'delete\']').prop('checked', this.checked);"></th>
								<th><a class="sort" href="<?php echo $sort_name; ?>"><?php echo lang('column_name'); ?><i class="fa fa-sort-<?php echo ($sort_by == 'name') ? $order_by_active : $order_by; ?>"></i></a></th>
								<th><a class="sort" href="<?php echo $sort_percentage; ?>"><?php echo lang('column_percentage'); ?><i class="fa fa-sort-<?php echo ($sort_by == 'percentage') ? $order_by_active : $order_by; ?>"></i></a></th>
								<th><a class="sort" href="<?php echo $sort_status; ?>"><?php echo lang('column_status'); ?><i class="fa fa-sort-<?php echo ($sort_by == 'status') ? $order_by_active : $order_by; ?>"></i></a></th>
							</tr>
						</thead>
						<tbody>
							<?php if ($_tax) {?>
							<?php foreach ($_tax as $tax) { ?>
							<tr>
								<td class="action"><input type="checkbox" value="<?php echo $tax['id']; ?>" name="delete[]" />&nbsp;&nbsp;&nbsp;
									<a class="btn btn-edit" title="<?php echo lang('text_edit'); ?>" href="<?php echo $tax['edit']; ?>"><i class="fa fa-pencil"></i></a></td>
								<td><?php echo $tax['name']; ?></td>
								<td><?php echo $tax['percentage']; ?></td>
								<td><?php echo $tax['status']; ?></td>
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
<script type="text/javascript">
</script>
<?php echo get_footer(); ?>