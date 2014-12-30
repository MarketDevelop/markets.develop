<?php
/**
 * $Id: alert-success.ctp 6 2014-09-22 08:17:09Z h.ushito@nicosys.jp $
 *
 * @copyright (c) 2013 Nicosys Co. Ltd.
 */

// setFlash 用要素
// alert-success

?>
<div class="alert alert-success alert-dismissible" role="alert">
	<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
	<?= $message ?>
</div>
