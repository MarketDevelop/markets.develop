<?php
/**
 * $Id: remove-modal.ctp 6 2014-09-22 08:17:09Z h.ushito@nicosys.jp $
 *
 * @copyright (c) 2014 Nicosys Co. Ltd.
 */
?>

<?php $this->Html->scriptStart(['inline' => false]) ?>
<?php ob_start() ?><script><?php ob_end_clean() // for IDE support  ?>

	!$(function () {

		// remove confirmation
		$('a[data-confirm-remove]').click(function (e) {
			var href = $(this).attr('href')
			var title = $(this).data('confirm-remove')
			// $('#remove-modal').find('.modal-body').text($(this).attr('data-confirm-delete'))
			if (title) {
				$('#remove-modal-item-title').text(title)
				$('#remove-modal-item-title-area').show()
			} else {
				$('#remove-modal-item-title-area').hide()
			}
			$('#remove-modal-accept').attr('href', href)
			$('#remove-modal').modal({ show: true })
			return false
		})

	})

<?php ob_start() ?></script><?php ob_end_clean() // for IDE support  ?>
<?php $this->Html->scriptEnd() ?>


<!-- Modal -->
<div id="remove-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="remove-modal-label" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title" id="remove-modal-label">削除確認</h4>
			</div>
			<div class="modal-body">
				<p><span id="remove-modal-item-title-area" class="collapse"><strong id="remove-modal-item-title"></strong>を</span>削除しますか？</p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">キャンセル</button>
				<a id="remove-modal-accept" href="#" class="btn btn-danger"><span class="glyphicon glyphicon-trash"></span> 削除する</a>
			</div>
		</div>
	</div>
</div>
