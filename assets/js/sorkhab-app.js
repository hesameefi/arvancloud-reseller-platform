(function($) {
    'use strict';

    $(document).ready(function() {
        // Tab Switching
        $('.ar-tab-btn').on('click', function() {
            var target = $(this).data('tab');
            $('.ar-tab-btn').removeClass('active');
            $(this).addClass('active');

            $('.ar-tab-content').hide();
            $('#' + target).fadeIn(200);
        });

        // Price calculator for server creation
        function updateServerPrice() {
            var selectedFlavorId = $('#ar_flavor_select').val();
            if (!selectedFlavorId) return;

            var chosen = null;
            if (ArvanApp.flavors) {
                for (var i = 0; i < ArvanApp.flavors.length; i++) {
                    if (ArvanApp.flavors[i].id === selectedFlavorId) {
                        chosen = ArvanApp.flavors[i];
                        break;
                    }
                }
            }

            if (chosen) {
                var hourlyBase = parseFloat(chosen.hourly_price);
                var margin = parseFloat(ArvanApp.margin || 20);
                var hourlyCustomer = Math.round(hourlyBase * (1 + (margin / 100)));
                var monthlyCustomer = hourlyCustomer * 24 * 30;

                $('#ar_hourly_calc').text(hourlyCustomer.toLocaleString('fa-IR') + ' تومان / ساعت');
                $('#ar_monthly_calc').text(monthlyCustomer.toLocaleString('fa-IR') + ' تومان / ماه');
            }
        }

        $('#ar_flavor_select').on('change', updateServerPrice);
        updateServerPrice();

        // Create Server Form Submit
        $('#ar_create_server_form').on('submit', function(e) {
            e.preventDefault();
            var $btn = $(this).find('button[type="submit"]');
            var originalText = $btn.html();
            $btn.prop('disabled', true).html('⏳ در حال ساخت و اتصال به زیرساخت ابری...');

            var formData = {
                action: 'arvan_customer_create_server',
                nonce: ArvanApp.nonce,
                name: $('#ar_server_name').val(),
                flavor_id: $('#ar_flavor_select').val(),
                image_id: $('#ar_image_select').val(),
                region: $('#ar_region_select').val()
            };

            $.post(ArvanApp.ajax_url, formData, function(res) {
                $btn.prop('disabled', false).html(originalText);
                if (res.success) {
                    showToast(res.data.message, 'success');
                    setTimeout(function() {
                        location.reload();
                    }, 1200);
                } else {
                    showToast(res.data, 'error');
                }
            }).fail(function() {
                $btn.prop('disabled', false).html(originalText);
                showToast('خطا در برقراری ارتباط با سرور.', 'error');
            });
        });

        // Power Toggle (Power Off / Power On)
        $(document).on('click', '.ar-toggle-power-btn', function() {
            var $btn = $(this);
            var resourceId = $btn.data('resource-id');
            var action = $btn.data('action');

            var originalText = $btn.html();
            $btn.prop('disabled', true).html('⏳ ...');

            $.post(ArvanApp.ajax_url, {
                action: 'arvan_customer_toggle_power',
                nonce: ArvanApp.nonce,
                resource_id: resourceId,
                power_action: action
            }, function(res) {
                if (res.success) {
                    showToast(res.data, 'success');
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    $btn.prop('disabled', false).html(originalText);
                    showToast(res.data, 'error');
                }
            }).fail(function() {
                $btn.prop('disabled', false).html(originalText);
                showToast('خطا در ارسال دستور مدیریت سرور.', 'error');
            });
        });

        // Web Console Modal
        $(document).on('click', '.ar-open-console-btn', function() {
            var srvName = $(this).data('name');
            var srvIp = $(this).data('ip');

            $('#ar_console_server_name').text(srvName);
            $('#ar_console_ip').text(srvIp);
            $('#ar_console_modal').addClass('active');
        });

        // Deposit Modal Open / Close
        $('#ar_open_deposit_modal').on('click', function() {
            $('#ar_deposit_modal').addClass('active');
        });

        $('.ar-close-modal').on('click', function() {
            $('.ar-modal-backdrop').removeClass('active');
        });

        // Deposit Form Submit
        $('#ar_deposit_form').on('submit', function(e) {
            e.preventDefault();
            var $btn = $(this).find('button[type="submit"]');
            var amount = $('#ar_deposit_amount').val();

            $btn.prop('disabled', true).html('در حال شارژ...');

            $.post(ArvanApp.ajax_url, {
                action: 'arvan_customer_deposit',
                nonce: ArvanApp.nonce,
                amount: amount
            }, function(res) {
                $btn.prop('disabled', false).html('پرداخت آنلاین و شارژ آنی');
                if (res.success) {
                    showToast(res.data.message, 'success');
                    $('#ar_deposit_modal').removeClass('active');
                    $('#ar_wallet_balance_display').text(res.data.new_balance.toLocaleString('fa-IR') + ' تومان');
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    showToast(res.data, 'error');
                }
            }).fail(function() {
                $btn.prop('disabled', false).html('پرداخت آنلاین و شارژ آنی');
                showToast('خطا در شارژ کیف پول.', 'error');
            });
        });

        // Toast Alert Function
        function showToast(msg, type) {
            var $toast = $('#ar_toast_notification');
            if ($toast.length === 0) {
                $toast = $('<div id="ar_toast_notification" class="ar-toast"></div>').appendTo('body');
            }

            var borderColor = (type === 'success') ? 'var(--ar-status-active)' : 'var(--ar-status-danger)';
            $toast.css('border-right-color', borderColor).text(msg).addClass('show');

            setTimeout(function() {
                $toast.removeClass('show');
            }, 4000);
        }
    });

})(jQuery);
