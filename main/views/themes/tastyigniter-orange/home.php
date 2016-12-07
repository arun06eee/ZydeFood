<style>
	#main-header {
		position: fixed;
		z-index: 100;
		background:  transparent;
		width: 100%;
	}

	#main-header .navbar-collapse {
		background:  transparent;
	}
  
	.introLoading {
		background-color: #fff;
	}  

	.introLoader, .introLoading {
		position: fixed;
		top: 0;
		right: 0;
		bottom: 0;
		left: 0;
		overflow: hidden;
		z-index: 9999;
	}

	.theme-dark.bubble.gifLoader .gifLoaderInner {
		background-image: url('../images/circle-bub_dark.gif');
		background-color: #333333;
		background-repeat: no-repeat;
		background-position: center center;
	}
  
	.introLoader.gifLoader .gifLoaderInner, .introLoader.gifLoader .gifLoaderInnerCustom {
		position: absolute;
		top: 0;
		right: 0;
		bottom: 0;
		left: 0;
	}
</style>

<div id="introLoader" class="introLoading introLoader gifLoader theme-dark bubble" >
  <div id="introLoaderSpinner" class="gifLoaderInner" style=""></div>
</div>

<?php echo get_header(); ?>
<?php echo get_partial('content_top'); ?>
<?php if ($this->alert->get()) { ?>
    <div id="notification">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <?php echo $this->alert->display(); ?>
                </div>
            </div>
        </div>
    </div>
<?php } ?>
<div id="page-content">
	<div class="container top-spacing-10">
	    <div class="content-wrap">
            <div id="order-steps" class="row">
                <div class="col-md-3 col-sm-3 col-xs-6">
                    <div class="step-item">
                        <div class="icon">
                            <i class="fa fa-search"></i>
                        </div>
                        <h4><?php echo lang('text_step_one'); ?></h4>
                        <p><?php echo lang('text_step_search'); ?></p>
                    </div>
                </div>
                <div class="col-md-3 col-sm-3 col-xs-6">
                    <div class="step-item">
                        <div class="icon">
                            <i class="fa fa-mouse-pointer"></i>
                        </div>
                        <h4><?php echo lang('text_step_two'); ?></h4>
                        <p><?php echo lang('text_step_choose'); ?></p>
                    </div>
                </div>
                <div class="clearfix visible-xs"></div>
                <div class="col-md-3 col-sm-3 col-xs-6">
                    <div class="step-item">
                        <div class="icon">
                            <i class="fa fa-credit-card"></i>
                        </div>
                        <h4><?php echo lang('text_step_three'); ?></h4>
                        <p><?php echo lang('text_step_pay'); ?></p>
                    </div>
                </div>
                <div class="col-md-3 col-sm-3 col-xs-6">
                    <div class="step-item">
                        <div class="icon">
                            <i class="fa fa-heart"></i>
                        </div>
                        <h4><?php echo lang('text_step_four'); ?></h4>
                        <p><?php echo lang('text_step_enjoy'); ?></p>
                    </div>
                </div>
            </div>

            <?php echo get_partial('content_bottom'); ?>
        </div>
	</div>
</div>
<?php echo get_footer(); ?>

<script>

(function ($) {


	"use strict";


	/**
	 * introLoader - Preloader
	 */
	$("#introLoader").introLoader({
		animation: {
			name: 'gifLoader',
			options: {
				style: 'dark bubble',
				delayBefore: 500,
				delayAfter: 0,
				exitTime: 300
			}
		}
	});


})(jQuery);

</script>