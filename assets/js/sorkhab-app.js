(function($) {
    'use strict';

    $(document).ready(function() {
        // 1. Dual Theme Switcher (Dark & Light)
        var savedTheme = localStorage.getItem('arvan_theme') || 'dark';
        $('html').attr('data-theme', savedTheme);
        updateThemeToggleBtn(savedTheme);

        $(document).on('click', '#ar_theme_toggle_btn', function() {
            var currentTheme = $('html').attr('data-theme') || 'dark';
            var nextTheme = (currentTheme === 'dark') ? 'light' : 'dark';
            $('html').attr('data-theme', nextTheme);
            localStorage.setItem('arvan_theme', nextTheme);
            updateThemeToggleBtn(nextTheme);
        });

        function updateThemeToggleBtn(theme) {
            var $btn = $('#ar_theme_toggle_btn');
            if ($btn.length) {
                $btn.html(theme === 'dark' ? '☀️ تم روشن' : '🌙 تم تاریک');
            }
        }

        // Tab Navigation Switching
        $(document).on('click', '.ar-nav-pill-btn, .ar-tab-btn', function() {
            var target = $(this).data('tab');
            if (!target) return;

            $('.ar-nav-pill-btn').removeClass('active');
            $('.ar-nav-pill-btn[data-tab="' + target + '"]').addClass('active');

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

        // Server Power Off/On
        $(document).on('click', '.ar-toggle-power-btn', function() {
            var $btn = $(this);
            var serverId = $btn.data('id');
            var actionType = $btn.data('action');

            var confirmMsg = (actionType === 'power_off') 
                ? 'آیا از خاموش کردن این سرور ابری اطمینان دارید؟' 
                : 'آیا مایل به روشن کردن مجدد سرور هستید؟';

            if (!confirm(confirmMsg)) return;

            $btn.prop('disabled', true).text('⏳ در حال پردازش...');

            $.post(ArvanApp.ajax_url, {
                action: 'arvan_customer_power_action',
                nonce: ArvanApp.nonce,
                server_id: serverId,
                power_action: actionType
            }, function(res) {
                if (res.success) {
                    showToast(res.data.message, 'success');
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    $btn.prop('disabled', false).text('عملیات ناموفق');
                    showToast(res.data, 'error');
                }
            }).fail(function() {
                $btn.prop('disabled', false).text('خطای ارتباط');
                showToast('خطای شبکه.', 'error');
            });
        });

        // Deposit Modal Open/Close
        $('#ar_open_deposit_modal, #ar_hero_deposit_btn').on('click', function() {
            $('#ar_deposit_modal').addClass('active');
        });

        $('#ar_close_deposit_modal').on('click', function() {
            $('#ar_deposit_modal').removeClass('active');
        });

        // Deposit Preset Chips
        $('.ar-deposit-chip').on('click', function() {
            var amount = $(this).data('amount');
            $('#ar_deposit_amount_input').val(amount);
        });

        // Deposit Form Submit (Online Simulated Gateway)
        $('#ar_deposit_form').on('submit', function(e) {
            e.preventDefault();
            var $btn = $(this).find('button[type="submit"]');
            var amount = $('#ar_deposit_amount_input').val();

            if (!amount || amount < 10000) {
                showToast('حداقل مبلغ افزایش اعتبار ۱۰,۰۰۰ تومان می‌باشد.', 'error');
                return;
            }

            $btn.prop('disabled', true).html('⏳ در حال اتصال به درگاه پرداخت...');

            $.post(ArvanApp.ajax_url, {
                action: 'arvan_customer_deposit',
                nonce: ArvanApp.nonce,
                amount: amount
            }, function(res) {
                $btn.prop('disabled', false).html('پرداخت آنلاین و افزایش آنی موجودی');
                if (res.success) {
                    showToast(res.data.message, 'success');
                    $('#ar_deposit_modal').removeClass('active');
                    $('#ar_wallet_balance_display').html(res.data.new_balance.toLocaleString('fa-IR') + ' <small style="font-size: 11px; font-weight: normal; color: var(--ar-text-secondary);">تومان</small>');
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    showToast(res.data, 'error');
                }
            }).fail(function() {
                $btn.prop('disabled', false).html('پرداخت آنلاین و افزایش آنی موجودی');
                showToast('خطا در شارژ کیف پول.', 'error');
            });
        });

        // Toast Alert Function
        function showToast(msg, type) {
            var $toast = $('#ar_toast_notification');
            if ($toast.length === 0) {
                $toast = $('<div id="ar_toast_notification" class="ar-toast" style="position: fixed; bottom: 30px; left: 30px; background: #141c26; border-right: 4px solid var(--ar-primary); color: white; padding: 14px 22px; border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.6); z-index: 1000000; font-size: 13.5px; font-weight: 700; display: none;"></div>').appendTo('body');
            }

            var borderColor = (type === 'success') ? '#10b981' : '#ef4444';
            $toast.css('border-right-color', borderColor).text(msg).fadeIn(200);

            setTimeout(function() {
                $toast.fadeOut(300);
            }, 3500);
        }
    });

})(jQuery);
