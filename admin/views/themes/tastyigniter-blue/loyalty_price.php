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
								<th><a class="sort" href="<?php echo $sort_id; ?>"><?php echo lang('text_id'); ?><i class="fa fa-sort-<?php echo ($sort_by == 'id') ? $order_by_active : $order_by; ?>"></i></a></th>
								<th><a class="sort" href="<?php echo $sort_name; ?>"><?php echo lang('label_name'); ?><i class="fa fa-sort-<?php echo ($sort_by == 'name') ? $order_by_active : $order_by; ?>"></i></a></th>
								<th><a class="sort" href="<?php echo $sort_discount; ?>"><?php echo lang('column_discount'); ?><i class="fa fa-sort-<?php echo ($sort_by == 'discount') ? $order_by_active : $order_by; ?>"></i></a></th>
							</tr>
						</thead>
						<tbody>
							<?php if ($loyalty_prices) {?>
							<?php foreach ($loyalty_prices as $loyalty_price) { ?>
							<tr>
								<td class="action"><input type="checkbox" value="<?php echo $loyalty_price['id']; ?>" name="delete[]" />&nbsp;&nbsp;&nbsp;
									<a class="btn btn-edit" title="<?php echo lang('text_edit'); ?>" href="<?php echo $loyalty_price['edit']; ?>"><i class="fa fa-pencil"></i></a></td>
								<td><?php echo $loyalty_price['id']; ?></td>
								<td><?php echo $loyalty_price['name']; ?></td>
								<td><?php echo $loyalty_price['discount']; ?></td>
							</tr>
							<?php } ?>
							<?php } else { ?>
									<?php redirect ("loyalty_price/edit") ?>
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