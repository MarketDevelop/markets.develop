<?php
/**
 * $Id: lock-modal.ctp 6 2014-09-22 08:17:09Z h.ushito@nicosys.jp $
 *
 * @copyright (c) 2014 Nicosys Co. Ltd.
 */
?>

<?php $this->Html->scriptStart(['inline' => false]) ?>
<?php ob_start() ?><script><?php ob_end_clean() // for IDE support  ?>

	!$(function () {

		// lock confirmation
		$('a[data-confirm-lock]').click(function (e) {
			var href = $(this).attr('href')
			var title = $(this).data('confirm-lock')
			// $('#lock-modal').find('.modal-body').text($(this).attr('data-confirm-delete'))
			if (title) {
				$('#lock-modal-item-title').text(title)
				$('#lock-modal-item-title-area').show()
			} else {
				$('#lock-modal-item-title-area').hide()
			}
			$('#lock-modal-accept').attr('href', href)
			$('#lock-modal').modal({ show: true })
			return false
		})

	})

<?php ob_start() ?></script><?php ob_end_clean() // for IDE support  ?>
<?php $this->Html->scriptEnd() ?>


<!-- Modal -->
<div id="lock-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="lock-modal-label" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title" id="lock-modal-label">ロック確認</h4>
			</div>
			<div class="modal-body">
				<p><span id="lock-modal-item-title-area" class="collapse"><strong id="lock-modal-item-title"></strong>を</span>ロックしますか？</p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">キャンセル</button>
				<a id="lock-modal-accept" href="#" class="btn btn-warning"><span class="glyphicon glyphicon-lock"></span> ロックする</a>
			</div>
		</div>
	</div>
</div>
