<div class="large_header_text">500 <div class="large_header_text_gray" style="display: inline;">- SERVER ERROR</div></div>
<div class="medium_text_block">
	<p>A server error has occured. Please try again.</p>
</div>
<?php
if (Configure::read('debug') > 0 ):
	echo $this->element('exception_stack_trace');
endif;
?>
