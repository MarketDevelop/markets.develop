<?php
/**
 * $Id: _input_form.ctp 96 2014-12-21 14:33:39Z h.ushito@nicosys.jp $
 *
 * @copyright (c) 2014 Nicosys Co. Ltd.
 */

$NEW = $this->request->action === 'add';

?>

<?php $this->Html->scriptStart(['inline' => false]) ?>
<?php ob_start() ?><script><?php ob_end_clean() // for IDE support  ?>

	$(function () {

		$.fn.extend({
			enableLink: function (enabled) {
				return this
					.css('opacity', enabled ? 1 : 0.2)
					.css('cursor', enabled ? 'auto' : 'default')
					.css('pointer-events', enabled ? 'auto' : 'none')
					.attr('tabindex', enabled ? '' : -1)
			}
		})

		function h(text) {
			return $('<div />').text(text).html()
		}

		$('#CustomerAddress, #CustomerEmail, #CustomerUrl')
			.on('change keyup', function () {
				$(this)
					.parent()
					.find('a')
					.enableLink($(this).val().length > 0)
			}).trigger('change')

		function updateStaffLinkage() {

			var enabled = $('#CustomerStaffId').val().length > 0

			// $('#staff-linkage-indicator').enableLink(enabled)

			if (enabled) {
				$('#CustomerStaffName') .attr('readonly', true)
			} else {
				$('#CustomerStaffName') .removeAttr('readonly')
			}

		}

        function formatCurrentTime() {
            var now = new Date()
            return String(now.getHours()) + ':' + String(now.getMinutes() + 100).substr(1)
        }

        function formatCurrentDate() {
            var now = new Date()
            return now.getFullYear() + '/' + String(now.getMonth() + 101).substr(1) + '/' + String(now.getDate() + 100).substr(1)
        }

        function formatCurrentDateTime() {
            var now = new Date()
            return now.getFullYear() + '/' + String(now.getMonth() + 1) + '/' + String(now.getDate()) + ' ' + String(now.getHours()) + ':' + String(now.getMinutes() + 100).substr(1)
        }

        function formatCurrentDateTimeShort() {
            var now = new Date()
            return String(now.getMonth() + 1) + '/' + String(now.getDate()) + ' ' + String(now.getHours()) + ':' + String(now.getMinutes() + 100).substr(1)
        }

		function inputCurrentDateTime() {
			$('#CustomerContactDate').val(formatCurrentDate())
			$('#CustomerContactTime').val(formatCurrentTime())
		}

		function changeStaff() {
			inputCurrentDateTime()
			updateStaffLinkage()
		}

        $.getJSON('<?= h($this->Html->url(['controller' => 'staffs', 'action' => 'json'])) ?>', function (data) {

            $('#CustomerStaffCode').combobox({

                //source: '<?= h($this->Html->url(['controller' => 'staffs', 'action' => 'json'])) ?>',
                source: function (request, response) {
                    var matcher = new RegExp($.ui.autocomplete.escapeRegex(request.term), 'i')
                    response($.grep(data, function (item) {
                        return matcher.test(item.code)
                    }))
                },
                delay: 0,

                format: function (item, text) {
                    return '<span class="glyphicon glyphicon-' + (item.locked ? "lock" : "user") + '"></span> ' + text + ' ' + h(item.name)
                },

                response: function (e, ui) {

                    $.each(ui.content, function (index, item) {
                        item.value = item.code
                        item.label = item.code
                    })

                    if (ui.content.length == 1 && ui.content[0].code === $(this).val()) {
                        ui.item = ui.content[0]
                        var _ = $(this)
                        _.combobox('instance')._trigger('select', 'comboboxselect', ui)
                        _.combobox('close')
                        // _.element[0].blur()
                        // _.element[0].focus()
                        _.combobox('instance').selectedItem = ui.item
                    }

                },
                select: function (e, ui) {
                    var item = ui.item
                    // $('#staff-linkage-indicator').enableLink(item)
                    $('#CustomerStaffId').val(item && item.id)
                    $('#CustomerStaffName').val(item && item.name)
                    changeStaff()
                },

                change: function (e, ui) {
                    var item = ui.item
                    // $('#staff-linkage-indicator').enableLink(item)
                    $('#CustomerStaffId').val(item && item.id)
                    $('#CustomerStaffName').val(item && item.name)
                    changeStaff()
                }

            })

            updateStaffLinkage()

        })

        $('#assign-to-me-button').on('click', function () {
            $('#CustomerStaffId').val('<?= str_replace("'", "\\'", AuthComponent::user('id')) ?>')
            $('#CustomerStaffCode').val('<?= str_replace("'", "\\'", AuthComponent::user('code')) ?>')
            $('#CustomerStaffName').val('<?= str_replace("'", "\\'", AuthComponent::user('name')) ?>')
            changeStaff()
            return false
        })

//		$('#staff-linkage-indicator').on('click', function () {
//            $('#CustomerStaffId').val('')
//            $('#CustomerStaffCode').val('')
//            $('#CustomerStaffName').val('')
//            changeStaff()
//            return false
//        })

		$('#input-current-datetime').on('click', inputCurrentDateTime)

		$('input[type=text], input[type=number], input[type=date], input[type=time], input[type=password]')
			.on('keypress', function (e) { return e.which !== 13 })

        function appendWord(element, text) {

            var t = element.value
            if (t !== '') {
                t += ' '
            }
            element.value = t + text + ' '
            element.focus()

            if (element.createTextRange) {
                var r = element.createTextRange()
                r.move('character', element.value.length)
                r.select()
            } else if (element.setSelectionRange) {
                element.setSelectionRange(element.value.length, element.value.length)
            }

        }

        $('.append-current-datetime').on('click', function () {
            appendWord(this.nextElementSibling.nextElementSibling.nextElementSibling, formatCurrentDateTime())
        })
        $('.append-current-datetime-short').on('click', function () {
            appendWord(this.nextElementSibling.nextElementSibling, formatCurrentDateTimeShort())
        })
        $('.append-current-time').on('click', function () {
            appendWord(this.nextElementSibling, formatCurrentTime())
        })

	})

<?php ob_start() ?></script><?php ob_end_clean() // for IDE support  ?>
<?php $this->Html->scriptEnd() ?>

<?= $this->fetch('content') //============================================================ ?>

<div class="panel panel-default">
	<div class="panel-body">

		<?= $this->Form->create() ?>

			<div class="row">
				<div class="col-xs-6 col-sm-4 col-md-3 col-lg-2">
					<?= $this->Form->input('prefecture') ?>

				</div>
				<div class="col-xs-6 col-sm-4 col-md-3 col-lg-2">
					<?= $this->Form->input('business') ?>

				</div>
				<div class="col-xs-6 col-sm-4 col-md-3 col-lg-2">
					<?= $this->Form->input('personality') ?>

				</div>
				<div class="col-xs-6 col-sm-4 col-md-3 col-lg-2">
					<?= $this->Form->input('zipcode') ?>

				</div>
			</div>

			<div class="row">
				<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
					<?= $this->Form->input('name', ['append' => '<span class="input-group-addon"><a href="#" title="ブラウザで検索" target="_blank" onclick="this.href = \'https://www.google.co.jp/search?q=\' + encodeURIComponent($(\'#CustomerName\').val())"><span class="glyphicon glyphicon-search"></span></a></span>']) ?>

				</div>
				<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
					<?= $this->Form->input('address', ['append' => '<span class="input-group-addon"><a href="#" title="地図を表示" target="_blank" onclick="this.href = \'https://www.google.co.jp/maps?q=\' + encodeURIComponent($(\'#CustomerAddress\').val())"><span class="glyphicon glyphicon-map-marker"></span></a></span>']) ?>

				</div>
			</div>

			<div class="row">
				<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
					<?= $this->Form->input('phone', ['class' => 'input-xlg', 'append' => '<span class="input-group-addon"><a href="#" title="ブラウザで検索" target="_blank" onclick="this.href = \'https://www.google.co.jp/search?q=\' + encodeURIComponent($(\'#CustomerPhone\').val())"><span class="glyphicon glyphicon-search"></span></a></span>']) ?>

				</div>
				<div class="col-xs-6 col-sm-4 col-md-3 col-lg-2">
					<?= $this->Form->input('email', ['append' => '<span class="input-group-addon"><a href="#" title="メールを作成" onclick="this.href = \'mailto:\' + $(\'#CustomerEmail\').val()"><span class="glyphicon glyphicon-envelope"></span></a></span>']) ?>

				</div>
				<div class="col-xs-6 col-sm-4 col-md-3 col-lg-2">
					<?= $this->Form->input('fax') ?>

				</div>
				<div class="col-xs-12 col-sm-8 col-md-6 col-lg-4">
					<?= $this->Form->input('url', ['append' => '<span class="input-group-addon"><a href="#" title="Web ページへ" target="_blank" onclick="this.href = $(\'#CustomerUrl\').val()"><span class="glyphicon glyphicon-globe"></span></a></span>']) ?>

				</div>
			</div>
			<div class="row">
				<div class="col-xs-6 col-sm-4 col-md-3 col-lg-2">

					<?= $this->Form->hidden('staff_id') ?>

					<?= $this->Form->input('staff_code', ['append' => '<span class="input-group-btn"><button class="btn btn-default" type="button" tabindex="-1" title="自分" id="assign-to-me-button"><span class="glyphicon glyphicon-user"></span></button></span>']) ?>

				</div>
				<div class="col-xs-6 col-sm-4 col-md-3 col-lg-2">
					<?= $this->Form->input('staff_name') ?>

				</div>
			</div>
			<div class="row">
				<div class="col-xs-6 col-sm-4 col-md-3 col-lg-2">
					<?= $this->Form->input('contact_name') ?>

				</div>
				<div class="col-xs-6 col-sm-4 col-md-3 col-lg-2">
					<?= $this->Form->input('contact_gender') ?>

				</div>
				<div class="col-xs-6 col-sm-4 col-md-3 col-lg-2">
					<?= $this->Form->input('responsible_name') ?>

				</div>
				<div class="col-xs-6 col-sm-4 col-md-3 col-lg-2">
					<?= $this->Form->input('responsible_gender') ?>

				</div>
				<div class="col-xs-6 col-sm-4 col-md-3 col-lg-2">
					<?= $this->Form->input('responsible_title') ?>

				</div>
                <div class="col-xs-6 col-sm-4 col-md-3 col-lg-2">
                    <?= $this->Form->input('dealer') ?>

                </div>
			</div>
			<div class="row">
				<div class="col-xs-6 col-sm-4 col-md-3 col-lg-2">
					<?= $this->Form->input('charge') ?>

				</div>
				<div class="col-xs-6 col-sm-4 col-md-3 col-lg-2">
					<?= $this->Form->input('carrier') ?>

				</div>
				<div class="col-xs-6 col-sm-4 col-md-3 col-lg-2">
					<?= $this->Form->input('quantity') ?>

				</div>
				<div class="col-xs-6 col-sm-4 col-md-3 col-lg-2">
					<?= $this->Form->input('contract') ?>

				</div>
			</div>
			<div class="row">
				<div class="col-xs-6 col-sm-4 col-md-3 col-lg-2">
					<?= $this->Form->input('contact_date', ['append' => '<span class="input-group-btn"><button class="btn btn-default" type="button" tabindex="-1" title="現在日時" id="input-current-datetime"><span class="glyphicon glyphicon-time"></span></button></span>']) ?>

				</div>
				<div class="col-xs-6 col-sm-4 col-md-3 col-lg-2">
					<?= $this->Form->input('contact_time') ?>

				</div>
				<div class="col-xs-6 col-sm-4 col-md-3 col-lg-2">
					<?= $this->Form->input('result') ?>

				</div>
				<div class="col-xs-6 col-sm-4 col-md-3 col-lg-2">
					<?= $this->Form->input('level') ?>

				</div>
				<div class="col-xs-12">
					<?= $this->Form->input('note', ['rows' => 10, 'between' => ' <button class="btn btn-default btn-sm append-current-datetime" type="button" tabindex="-1" title="現在日時の追記"><span class="glyphicon glyphicon-time"></span></button> <button class="btn btn-default btn-sm append-current-datetime-short" type="button" tabindex="-1" title="現在日時の追記（短い表記）"><span class="glyphicon glyphicon-time"></span></button> <button class="btn btn-default btn-sm append-current-time" type="button" tabindex="-1" title="現在時の追記"><span class="glyphicon glyphicon-time"></span></button>']) ?>

				</div>
			</div>
			<div class="row">
				<div class="col-xs-6 col-sm-4 col-md-3 col-lg-2">
					<?= $this->Form->input('appointment_date') ?>

				</div>
				<div class="col-xs-6 col-sm-4 col-md-3 col-lg-2">
					<?= $this->Form->input('appointment_time') ?>

			</div>
				<div class="col-xs-6 col-sm-4 col-md-3 col-lg-2">
					<?= $this->Form->input('appointment_staff_name') ?>

				</div>
				<div class="col-xs-12">
					<?= $this->Form->input('appointment_note', ['rows' => 10, 'between' => ' <button class="btn btn-default btn-sm append-current-datetime" type="button" tabindex="-1" title="現在日時の追記"><span class="glyphicon glyphicon-time"></span></button> <button class="btn btn-default btn-sm append-current-datetime-short" type="button" tabindex="-1" title="現在日時の追記（短い表記）"><span class="glyphicon glyphicon-time"></span></button> <button class="btn btn-default btn-sm append-current-time" type="button" tabindex="-1" title="現在時の追記"><span class="glyphicon glyphicon-time"></span></button>']) ?>

				</div>
			</div>
			<div class="row">
				<div class="col-xs-6 col-sm-4 col-md-3 col-lg-2">
					<?= $this->Form->input('visit_date') ?>

				</div>
				<div class="col-xs-6 col-sm-4 col-md-3 col-lg-2">
					<?= $this->Form->input('visit_time') ?>

				</div>
				<div class="col-xs-6 col-sm-4 col-md-3 col-lg-2">
					<?= $this->Form->input('visit_result') ?>

				</div>
				<div class="col-xs-6 col-sm-4 col-md-3 col-lg-2">
					<?= $this->Form->input('visit_staff_name') ?>

				</div>
			</div>

			<div class="form-group form-actions" style="position: fixed; right: 0; bottom: 0; padding: 4px; background-color: #eee; border-radius: 4px; border: 1px solid silver;">
				<?= $this->fetch('form-actions') //============================================================ ?>

			</div>

		<?= $this->Form->end() ?>

	</div>

</div>
