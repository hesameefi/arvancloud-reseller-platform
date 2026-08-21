(function($) {
    'use strict';

    $(document).ready(function() {
        // Admin Dual Theme Switcher
        function applyAdminTheme(theme) {
            if (theme === 'dark') {
                $('body').addClass('arvan-admin-dark-theme');
                $('.arvan-admin-wrap').attr('data-theme', 'dark');
                $('#arvan_admin_theme_toggle').html('☀️ تم روشن');
            } else {
                $('body').removeClass('arvan-admin-dark-theme');
                $('.arvan-admin-wrap').attr('data-theme', 'light');
                $('#arvan_admin_theme_toggle').html('🌙 تم تاریک');
            }
            localStorage.setItem('arvan_admin_theme', theme);
        }

        var savedAdminTheme = localStorage.getItem('arvan_admin_theme') || 'light';
        applyAdminTheme(savedAdminTheme);

        $(document).on('click', '#arvan_admin_theme_toggle', function() {
            var current = $('body').hasClass('arvan-admin-dark-theme') ? 'dark' : 'light';
            var next = (current === 'light') ? 'dark' : 'light';
            applyAdminTheme(next);
        });

        // Save Settings AJAX
        $('#arvan_admin_settings_form').on('submit', function(e) {
            e.preventDefault();
            var $btn = $('#arvan_btn_save_settings');
            var $msg = $('#arvan_save_msg');

            $btn.prop('disabled', true).text('در حال ذخیره...');
            $msg.text('');

            $.post(ArvanAdmin.ajax_url, {
                action: 'arvan_save_settings',
                nonce: ArvanAdmin.nonce,
                api_key: $('#arvan_setting_api_key').val(),
                mode: $('#arvan_setting_mode').val(),
                margin: $('#arvan_setting_margin').val(),
                rate_limit_rpm: $('#arvan_setting_rpm').val()
            }, function(res) {
                $btn.prop('disabled', false).text('💾 ذخیره تنظیمات ریسلری');
                if (res.success) {
                    $msg.css('color', '#10b981').text(res.data);
                } else {
                    $msg.css('color', '#ef4444').text(res.data);
                }
            }).fail(function() {
                $btn.prop('disabled', false).text('💾 ذخیره تنظیمات ریسلری');
                $msg.css('color', '#ef4444').text('خطا در ارسال اطلاعات.');
            });
        });

        // Run Hourly Cron Manually
        $('#arvan_btn_run_cron').on('click', function() {
            var $btn = $(this);
            var original = $btn.text();
            $btn.prop('disabled', true).text('⏳ در حال اجرای کران‌جاب ساعتی...');

            $.post(ArvanAdmin.ajax_url, {
                action: 'arvan_run_cron_now',
                nonce: ArvanAdmin.nonce
            }, function(res) {
                $btn.prop('disabled', false).text(original);
                if (res.success) {
                    alert(res.data);
                    location.reload();
                } else {
                    alert('خطا: ' + res.data);
                }
            }).fail(function() {
                $btn.prop('disabled', false).text(original);
                alert('خطا در برقراری ارتباط.');
            });
        });

        // Seed Demo Data
        $('#arvan_btn_seed_demo').on('click', function() {
            var $btn = $(this);
            var original = $btn.text();
            $btn.prop('disabled', true).text('⏳ در حال بارگذاری داده‌های دمو...');

            $.post(ArvanAdmin.ajax_url, {
                action: 'arvan_seed_demo_data',
                nonce: ArvanAdmin.nonce
            }, function(res) {
                $btn.prop('disabled', false).text(original);
                if (res.success) {
                    alert(res.data);
                    location.reload();
                } else {
                    alert('خطا: ' + res.data);
                }
            }).fail(function() {
                $btn.prop('disabled', false).text(original);
                alert('خطا در برقراری ارتباط.');
            });
        });

        // Reset Demo Data
        $('#arvan_btn_reset_demo').on('click', function() {
            if (!confirm('آیا از ریست کامل داده‌های دمو اطمینان دارید؟ تمام لاگ‌ها، سرورها و کیف پول‌ها صفر خواهند شد.')) {
                return;
            }

            var $btn = $(this);
            var original = $btn.text();
            $btn.prop('disabled', true).text('⏳ در حال ریست داده‌ها...');

            $.post(ArvanAdmin.ajax_url, {
                action: 'arvan_reset_demo_data',
                nonce: ArvanAdmin.nonce
            }, function(res) {
                $btn.prop('disabled', false).text(original);
                if (res.success) {
                    alert(res.data);
                    location.reload();
                } else {
                    alert('خطا: ' + res.data);
                }
            }).fail(function() {
                $btn.prop('disabled', false).text(original);
                alert('خطا در برقراری ارتباط.');
            });
        });

        // Delete Server
        $(document).on('click', '.arvan-delete-server-btn', function() {
            var id = $(this).data('id');
            if (!confirm('آیا از حذف این سرور از پنل مدیریت اطمینان دارید؟')) {
                return;
            }

            $.post(ArvanAdmin.ajax_url, {
                action: 'arvan_admin_delete_server',
                nonce: ArvanAdmin.nonce,
                id: id
            }, function(res) {
                if (res.success) {
                    $('#arvan_server_row_' + id).fadeOut(300, function() { $(this).remove(); });
                } else {
                    alert(res.data);
                }
            });
        });

        // Quick Edit Server
        $(document).on('click', '.arvan-edit-server-btn', function() {
            var id = $(this).data('id');
            var currentName = $(this).data('name');
            var currentStatus = $(this).data('status');

            var newName = prompt('نام جدید سرور را وارد نمایید:', currentName);
            if (newName === null || newName.trim() === '') return;

            var newStatus = prompt('وضعیت سرور را وارد نمایید (ACTIVE یا SUSPENDED):', currentStatus);
            if (newStatus === null) return;
            newStatus = (newStatus.toUpperCase() === 'ACTIVE') ? 'ACTIVE' : 'SUSPENDED';

            $.post(ArvanAdmin.ajax_url, {
                action: 'arvan_admin_edit_server',
                nonce: ArvanAdmin.nonce,
                id: id,
                name: newName,
                status: newStatus
            }, function(res) {
                if (res.success) {
                    alert(res.data);
                    location.reload();
                } else {
                    alert(res.data);
                }
            });
        });
    });

})(jQuery);
