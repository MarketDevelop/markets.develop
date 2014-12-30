<?php
/**
 * $Id: pager.ctp 40 2014-09-29 06:08:37Z h.ushito@nicosys.jp $
 *
 * @copyright (c) 2013 Nicosys Co. Ltd.
 */

// 検索件数とページ移動リンクを含むページャー

$params = $this->Paginator->params();

?>
<?php if ($params['count'] > 0) : // 1 件以上の結果がある  ?>
				<!-- Pager -->
				<?php echo $this->Paginator->counter('%count% 件のうち %start%〜%end% 件目を表示') // %count%件が該当しました。%start%〜%end%件を表示しています。 ?>

<?php if ($params['pageCount'] > 1) : // 複数ページに渡る ?>
				<ul class="pagination" style="float: right; margin: 0">
					<?= $this->Paginator->prev(
							'«',
							['tag' => 'li'],
							'<span>«</span>',
							['escape' => false, 'tag' => 'li', 'class' => 'disabled']
					) ?>

					<?= preg_replace('#<li class="current">(\d+)</li>#', '<li class="active"><span>$1</span></li>', $this->Paginator->numbers([
							'tag' => 'li',
							'separator' => "\n\t\t\t\t\t\t\t\t",
							'modulus' => 6,
					])) ?>

					<?= $this->Paginator->next(
							'»',
							['tag' => 'li'],
							'<span>»</span>',
							['escape' => false, 'tag' => 'li', 'class' => 'disabled']
					) ?>
				</ul>
<?php endif ?>

<?php endif ?>
