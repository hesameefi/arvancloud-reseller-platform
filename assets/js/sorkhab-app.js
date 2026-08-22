(function($) {
    'use strict';

    $(document).ready(function() {
        // 1. Dual Theme Switcher (Dark & Light)
        var savedTheme = localStorage.getItem('arvan_theme') || 'dark';
        $('html').attr('data-theme', savedTheme);
        updateThemeToggleBtn(savedTheme);

        $(document).on('click', '#ar_theme_toggle_btn, #arvan_admin_theme_toggle', function() {
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

        // 2. Mobile Sidebar Toggle
        $(document).on('click', '#ar_mobile_toggle', function() {
            $('#ar_sidebar').toggleClass('open');
        });

        // 3. Tab Navigation Switching
        var pageTitles = {
            'tab_my_servers': 'سرورهای ابری من',
            'tab_create_server': 'راه‌اندازی ابرک جدید',
            'tab_cdn_storage': 'CDN و آبجکت استوریج S3',
            'tab_ai_advisor': 'مشاور هوش مصنوعی پلن',
            'tab_wallet_history': 'دفتر کل مالی و تراکنش‌ها',
            'tab_rate_limit_health': 'وضعیت ریت‌لیمیت و سلامت API'
        };

        $(document).on('click', '.ar-sidebar-nav-item, .ar-tab-btn', function() {
            var target = $(this).data('tab');
            if (!target) return;

            $('.ar-sidebar-nav-item').removeClass('active');
            $('.ar-sidebar-nav-item[data-tab="' + target + '"]').addClass('active');

            if (pageTitles[target]) {
                $('#ar_active_page_title').text(pageTitles[target]);
            }

            $('.ar-tab-content').hide();
            $('#' + target).fadeIn(200);

            // Close mobile sidebar if open
            $('#ar_sidebar').removeClass('open');
        });

        // 4. Live Server Flavor Price Calculator
        function updateServerPrice() {
            var $sel = $('#ar_flavor_select');
            if (!$sel.length) return;
            var $opt = $sel.find('option:selected');
            var hourly = $opt.data('hourly') || 0;
            var monthly = $opt.data('monthly') || 0;

            $('#ar_create_hourly_price').text(Number(hourly).toLocaleString('fa-IR'));
            $('#ar_create_monthly_price').text(Number(monthly).toLocaleString('fa-IR'));
        }

        $('#ar_flavor_select').on('change', updateServerPrice);
        updateServerPrice();

        // 5. Create Server Form Submit
        $('#ar_create_server_form').on('submit', function(e) {
            e.preventDefault();
            var $btn = $('#ar_btn_submit_create');
            var originalText = $btn.html();
            $btn.prop('disabled', true).html('⏳ در حال راه‌اندازی و اتصال به زیرساخت...');

            var region = $('input[name="region"]:checked').val() || 'ir-thr-at1';
            var flavorId = $('#ar_flavor_select').val();
            var imageId = $('#ar_image_select').val();
            var serverName = $('#ar_server_name_input').val();
            var sshKey = $('#ar_ssh_key_input').val();

            $.post(ArvanApp.ajax_url, {
                action: 'arvan_customer_create_server',
                nonce: ArvanApp.nonce,
                name: serverName,
                flavor_id: flavorId,
                image_id: imageId,
                region: region,
                ssh_key: sshKey
            }, function(res) {
                $btn.prop('disabled', false).html(originalText);
                if (res.success) {
                    showToast(res.data.message || 'سرور ابری با موفقیت ساخته و تحویل گردید.', 'success');
                    setTimeout(function() {
                        location.reload();
                    }, 1200);
                } else {
                    showToast(res.data || 'خطا در ایجاد سرور.', 'error');
                }
            }).fail(function() {
                $btn.prop('disabled', false).html(originalText);
                showToast('خطا در برقراری ارتباط با سرور.', 'error');
            });
        });

        // 6. Server Action Buttons (Power Off/On / Terminate)
        $(document).on('click', '.ar-action-btn', function() {
            var $btn = $(this);
            var serverId = $btn.data('id');
            var serverName = $btn.data('name');
            var actionType = $btn.data('action');

            var confirmMsg = 'آیا از اجرای عملیات روی سرور ' + serverName + ' اطمینان دارید؟';
            if (actionType === 'power_off') confirmMsg = 'آیا از خاموش کردن سرور ابری ' + serverName + ' اطمینان دارید؟';
            if (actionType === 'terminate') confirmMsg = '⚠️ هشدار: آیا از حذف دائمی سرور ابری ' + serverName + ' اطمینان دارید؟ این عملیات غیرقابل بازگشت است.';

            if (!confirm(confirmMsg)) return;

            $btn.prop('disabled', true).text('⏳ در حال پردازش...');

            $.post(ArvanApp.ajax_url, {
                action: 'arvan_customer_power_action',
                nonce: ArvanApp.nonce,
                server_id: serverId,
                power_action: actionType
            }, function(res) {
                if (res.success) {
                    showToast(res.data.message || 'عملیات با موفقیت انجام شد.', 'success');
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    $btn.prop('disabled', false).text('عملیات ناموفق');
                    showToast(res.data || 'خطا در اجرای عملیات.', 'error');
                }
            }).fail(function() {
                $btn.prop('disabled', false).text('خطای ارتباط');
                showToast('خطای شبکه.', 'error');
            });
        });

        // 7. Web Console Modal
        $(document).on('click', '.ar-console-btn', function() {
            var name = $(this).data('name');
            var ip = $(this).data('ip');
            $('#ar_console_server_name').text(name);
            $('#ar_console_ip').text(ip);
            $('#ar_console_modal').addClass('active');
        });

        // 8. Deposit Modal Open/Close
        $(document).on('click', '#ar_open_deposit_modal', function() {
            $('#ar_deposit_modal').addClass('active');
        });

        $(document).on('click', '.ar-close-modal', function() {
            $('.ar-modal-backdrop').removeClass('active');
        });

        // Close on backdrop click
        $('.ar-modal-backdrop').on('click', function(e) {
            if ($(e.target).hasClass('ar-modal-backdrop')) {
                $(this).removeClass('active');
            }
        });

        // 9. Deposit Form Submit
        $('#ar_deposit_form').on('submit', function(e) {
            e.preventDefault();
            var $btn = $(this).find('button[type="submit"]');
            var amount = $('#ar_deposit_amount').val();

            if (!amount || amount < 10000) {
                showToast('حداقل مبلغ افزایش اعتبار ۱۰,۰۰۰ تومان می‌باشد.', 'error');
                return;
            }

            $btn.prop('disabled', true).html('⏳ در حال اتصال به درگاه بانکی...');

            $.post(ArvanApp.ajax_url, {
                action: 'arvan_customer_deposit',
                nonce: ArvanApp.nonce,
                amount: amount
            }, function(res) {
                $btn.prop('disabled', false).html('💳 پرداخت آنلاین و افزایش آنی موجودی');
                if (res.success) {
                    showToast(res.data.message || 'کیف پول با موفقیت شارژ گردید.', 'success');
                    $('#ar_deposit_modal').removeClass('active');
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    showToast(res.data || 'خطا در افزایش اعتبار.', 'error');
                }
            }).fail(function() {
                $btn.prop('disabled', false).html('💳 پرداخت آنلاین و افزایش آنی موجودی');
                showToast('خطا در شارژ کیف پول.', 'error');
            });
        });

        // 10. Toast Alert Function
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

        // ==========================================================================
        // 11. AI Agentic Copilot Chatbot Engine (RAG + Autonomous Deployer)
        // ==========================================================================
        function formatMarkdownText(text) {
            if (!text) return '';
            var html = text
                .replace(/\n\n/g, '<br><br>')
                .replace(/\n/g, '<br>')
                .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                .replace(/\*(.*?)\*/g, '<em>$1</em>')
                .replace(/`(.*?)`/g, '<code style="background: rgba(0,186,186,0.1); color: var(--ar-primary); padding: 2px 6px; border-radius: 4px; font-family: monospace;">$1</code>');
            return html;
        }

        function appendUserMessage(text) {
            var html = '<div class="ar-ai-msg ar-ai-msg-user">' +
                '<div class="ar-ai-msg-avatar">👤</div>' +
                '<div class="ar-ai-msg-body">' +
                    '<div class="ar-ai-msg-content">' + $('<div>').text(text).html() + '</div>' +
                '</div>' +
            '</div>';
            $('#ar_ai_chat_container').append(html);
            scrollToChatBottom();
        }

        function appendBotMessage(data) {
            var replyHtml = formatMarkdownText(data.reply);
            var cardHtml = '';
            var tierHtml = '';

            if (data.tier) {
                tierHtml = '<div style="display:inline-flex;align-items:center;gap:6px;background:rgba(56,189,248,0.12);border:1px solid rgba(56,189,248,0.3);color:#38bdf8;padding:2px 8px;border-radius:12px;font-size:11px;margin-bottom:6px;font-weight:700;">' +
                    '<span>🏷️ ' + data.tier + '</span>' +
                    '<span style="color:#10b981;">💰 +' + (data.cost_saved_toman || 150) + ' تومان صرفه‌جویی توکن</span>' +
                '</div>';
            }

            if (data.type === 'recommendation' && data.action_card) {
                var card = data.action_card;
                cardHtml = '<div class="ar-ai-action-card">' +
                    '<div class="ar-ai-action-card-header">' +
                        '<div>' +
                            '<span style="font-weight: 800; font-size: 15px; color: var(--ar-primary);">' + card.flavor_name + '</span> ' +
                            '<span style="font-size: 12px; color: var(--ar-text-muted);">(' + card.region_flag + ' ' + card.region_name + ')</span>' +
                        '</div>' +
                        '<div style="font-weight: 900; color: #10b981; font-size: 14px;">' + card.hourly_price_formatted + ' تومان/ساعت</div>' +
                    '</div>' +
                    '<div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; font-size: 12px; text-align: center;">' +
                        '<div style="background: var(--ar-bg-surface); padding: 6px; border-radius: 6px;">💻 ' + card.cpu + ' vCPU</div>' +
                        '<div style="background: var(--ar-bg-surface); padding: 6px; border-radius: 6px;">🧠 ' + (card.ram >= 1024 ? (card.ram / 1024) + ' GB RAM' : card.ram + ' MB') + '</div>' +
                        '<div style="background: var(--ar-bg-surface); padding: 6px; border-radius: 6px;">⚡ ' + card.disk + ' GB NVMe</div>' +
                    '</div>' +
                    '<div class="ar-ai-action-btn-row">' +
                        '<button type="button" class="ar-btn ar-btn-primary ar-ai-deploy-btn" style="flex: 1; padding: 10px;" data-flavor="' + card.flavor_id + '" data-region="' + card.region_id + '" data-image="' + card.image_id + '" data-hostname="' + card.hostname + '">' +
                            '⚡ بله، این سرور را فوراً بساز' +
                        '</button>' +
                        '<button type="button" class="ar-btn ar-btn-secondary ar-tab-btn" data-tab="tab_create_server" style="padding: 10px 14px;" onclick="applyAiRecommendation(\'' + card.flavor_id + '\', \'' + card.flavor_name + '\', \'' + card.flavor_name + '\');">' +
                            '⚙️ شخصی‌سازی' +
                        '</button>' +
                    '</div>' +
                '</div>';
            } else if (data.type === 'diagnostic_report' && data.action_card && data.action_card.can_heal) {
                var ac = data.action_card;
                var diff = ac.before_after_diff || {};
                cardHtml = '<div style="background:rgba(15,23,42,0.85);border:1px solid rgba(244,63,94,0.4);border-radius:12px;padding:12px;margin-top:10px;">' +
                    '<div style="font-weight:800;color:#fb7185;font-size:13px;margin-bottom:8px;">🛠️ ' + ac.heal_title + '</div>' +
                    '<div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;font-size:11.5px;margin-bottom:10px;">' +
                        '<div style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);padding:8px;border-radius:8px;">' +
                            '<strong style="color:#ef4444;">🔴 وضعیت قبل (خطا):</strong><br>' +
                            'پورت: ' + (diff.before ? diff.before.port : '80') + '<br>' +
                            'وضعیت: ' + (diff.before ? diff.before.status : '502 Bad Gateway') +
                        '</div>' +
                        '<div style="background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.3);padding:8px;border-radius:8px;">' +
                            '<strong style="color:#10b981;">🟢 اصلاح بعد (تعمیر):</strong><br>' +
                            'پورت: ' + (diff.after ? diff.after.port : '3000') + '<br>' +
                            'وضعیت: ' + (diff.after ? diff.after.status : '200 OK') +
                        '</div>' +
                    '</div>' +
                    '<div style="font-size:11px;color:#94a3b8;margin-bottom:8px;">🔐 امضای امنیتی دیجیتال: <code>' + (ac.signature ? ac.signature.substring(0, 16) : 'HMAC-SHA256') + '...</code> (اعتبار ۵ دقیقه)</div>' +
                    '<button type="button" class="ar-btn ar-btn-primary ar-signed-heal-btn" style="width:100%;padding:9px;font-size:12.5px;font-weight:800;" data-payload="' + ac.signed_token + '" data-sig="' + ac.signature + '">' +
                        '⚡ تایید و اجرای فوری اصلاحیه امن' +
                    '</button>' +
                '</div>';
            } else if (data.type === 'insufficient_balance') {
                cardHtml = '<div style="margin-top: 10px;">' +
                    '<button type="button" class="ar-btn ar-btn-primary" onclick="document.getElementById(\'ar_open_deposit_modal\').click();" style="padding: 9px 18px; font-size: 13px;">' +
                        '💳 شارژ فوری کیف پول' +
                    '</button>' +
                '</div>';
            } else if (data.type === 'server_created') {
                cardHtml = '<div style="margin-top: 10px;">' +
                    '<button type="button" class="ar-btn ar-btn-success ar-tab-btn" data-tab="tab_my_servers" style="padding: 10px 20px; font-size: 13.5px;">' +
                        '🖥️ مشاهده در داشبورد سرورهای من' +
                    '</button>' +
                '</div>';
            }

            var sourcesHtml = '';
            if (data.sources && data.sources.length) {
                sourcesHtml = '<div style="margin-top:8px;padding-top:6px;border-top:1px dashed rgba(255,255,255,0.1);display:flex;flex-wrap:wrap;gap:6px;">' +
                    '<span style="font-size:11px;color:var(--ar-text-muted);">📚 منابع رسمی:</span>';
                data.sources.forEach(function(s) {
                    sourcesHtml += '<a href="' + s.url + '" target="_blank" style="background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.12);padding:2px 8px;border-radius:10px;font-size:11px;color:#38bdf8;text-decoration:none;">' + s.title + ' ↗</a>';
                });
                sourcesHtml += '</div>';
            }

            var html = '<div class="ar-ai-msg ar-ai-msg-bot">' +
                '<div class="ar-ai-msg-avatar">🤖</div>' +
                '<div class="ar-ai-msg-body">' +
                    tierHtml +
                    '<div class="ar-ai-msg-content">' + replyHtml + '</div>' +
                    cardHtml +
                    sourcesHtml +
                '</div>' +
            '</div>';

            $('#ar_ai_chat_container').append(html);
            scrollToChatBottom();
        }

        function scrollToChatBottom() {
            var $box = $('#ar_ai_chat_container');
            if ($box.length && $box[0]) {
                $box.scrollTop($box[0].scrollHeight);
            }
        }

        // Quick Prompt Chips
        $(document).on('click', '.ar-ai-chip', function() {
            var prompt = $(this).data('prompt');
            $('#ar_ai_input_text').val(prompt);
            $('#ar_ai_chat_form').trigger('submit');
        });

        // Chat Form Submit
        $('#ar_ai_chat_form').on('submit', function(e) {
            e.preventDefault();
            var $input = $('#ar_ai_input_text');
            var msg = $.trim($input.val());
            if (!msg) return;

            appendUserMessage(msg);
            $input.val('');

            var $btn = $('#ar_ai_send_btn');
            $btn.prop('disabled', true).html('⏳ ...');

            // Add typing indicator
            var $typing = $('<div class="ar-ai-msg ar-ai-msg-bot" id="ar_ai_typing_temp"><div class="ar-ai-msg-avatar">🤖</div><div class="ar-ai-msg-body"><div class="ar-ai-msg-content" style="color: var(--ar-text-muted); font-style: italic;">در حال تحلیل سناریو با RAG مستندات... 🧠</div></div></div>').appendTo('#ar_ai_chat_container');
            scrollToChatBottom();

            $.post(ArvanApp.ajax_url, {
                action: 'arvan_ai_chat_message',
                nonce: ArvanApp.nonce,
                message: msg
            }, function(res) {
                $('#ar_ai_typing_temp').remove();
                $btn.prop('disabled', false).html('<span>ارسال</span> 🚀');
                if (res.success && res.data) {
                    appendBotMessage(res.data);
                } else {
                    appendBotMessage({
                        type: 'error',
                        reply: 'متاسفانه در پردازش هوش مصنوعی خطایی رخ داد: ' + (res.data || 'خطای سرور')
                    });
                }
            }).fail(function() {
                $('#ar_ai_typing_temp').remove();
                $btn.prop('disabled', false).html('<span>ارسال</span> 🚀');
                appendBotMessage({
                    type: 'error',
                    reply: 'خطا در ارتباط با سرور هوش مصنوعی.'
                });
            });
        });

        // 1-Click Server Autonomous Deploy Click Handler
        $(document).on('click', '.ar-ai-deploy-btn', function() {
            var $btn = $(this);
            var flavor = $btn.data('flavor');
            var region = $btn.data('region');
            var image = $btn.data('image');
            var hostname = $btn.data('hostname');

            $btn.prop('disabled', true).html('⏳ در حال استقرار آنی ابرک در زیرساخت...');

            $.post(ArvanApp.ajax_url, {
                action: 'arvan_ai_deploy_server',
                nonce: ArvanApp.nonce,
                flavor_id: flavor,
                region: region,
                image_id: image,
                hostname: hostname
            }, function(res) {
                if (res.success && res.data) {
                    appendBotMessage(res.data);
                    showToast('🎉 ابرک جدید با موفقیت ساخته شد و در داشبورد فعال گردید!', 'success');
                } else {
                    var errData = res.data || {};
                    if (errData.type === 'insufficient_balance') {
                        appendBotMessage(errData);
                    } else {
                        showToast(errData.reply || 'خطا در استقرار سرور.', 'error');
                        $btn.prop('disabled', false).html('تلاش مجدد');
                    }
                }
            }).fail(function() {
                $btn.prop('disabled', false).html('تلاش مجدد');
                showToast('خطا در برقراری ارتباط با هایپروایزر.', 'error');
            });
        });

        // Clear Chat
        $('#ar_ai_clear_chat').on('click', function() {
            $('#ar_ai_chat_container').html(
                '<div class="ar-ai-msg ar-ai-msg-bot">' +
                    '<div class="ar-ai-msg-avatar">🤖</div>' +
                    '<div class="ar-ai-msg-body">' +
                        '<div class="ar-ai-msg-content">' +
                            'مکالمه بازنشانی شد. نیاز فنی جدید خود را بفرمایید تا بررسی کنم! ☁️' +
                        '</div>' +
                    '</div>' +
                '</div>'
            );
        });

        // ======================================================================
        // Floating Global AI Cloud Architect Widget Handlers (Feature C)
        // ======================================================================
        $(document).on('click', '#ar_floating_ai_btn', function() {
            $('#ar_floating_ai_drawer').toggleClass('active');
            if ($('#ar_floating_ai_drawer').hasClass('active')) {
                $('#ar_drawer_input').focus();
            }
        });

        $(document).on('click', '#ar_drawer_close', function() {
            $('#ar_floating_ai_drawer').removeClass('active');
        });

        $(document).on('click', '.ar-drawer-chip', function() {
            var p = $(this).data('prompt');
            $('#ar_drawer_input').val(p);
            $('#ar_drawer_form').trigger('submit');
        });

        $('#ar_drawer_form').on('submit', function(e) {
            e.preventDefault();
            var $inp = $('#ar_drawer_input');
            var msg = $.trim($inp.val());
            if (!msg) return;

            var $box = $('#ar_drawer_chat_messages');
            $box.append('<div class="ar-ai-msg user" style="display:flex;justify-content:flex-start;margin-bottom:8px;"><div class="ar-ai-bubble" style="background:#e11d48;color:#fff;padding:8px 12px;border-radius:12px;max-width:80%;font-size:12.5px;">' + escapeHtml(msg) + '</div></div>');
            $inp.val('');
            $box.scrollTop($box[0].scrollHeight);

            var $sendBtn = $('#ar_drawer_send_btn');
            $sendBtn.prop('disabled', true).text('⏳');

            $.post(ArvanApp.ajax_url, {
                action: 'arvan_ai_chat_message',
                nonce: ArvanApp.nonce,
                message: msg
            }, function(res) {
                $sendBtn.prop('disabled', false).text('➤');
                var reply = (res.success && res.data) ? res.data.reply : 'خطا در پردازش هوش مصنوعی.';
                var replyHtml = reply.replace(/\n/g, '<br>').replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
                
                var tierHtml = '';
                if (res.data && res.data.tier) {
                    tierHtml = '<div style="display:inline-flex;align-items:center;gap:6px;background:rgba(56,189,248,0.15);border:1px solid rgba(56,189,248,0.3);color:#38bdf8;padding:2px 8px;border-radius:12px;font-size:10.5px;margin-bottom:6px;font-weight:700;">' +
                        '<span>🏷️ ' + res.data.tier + '</span>' +
                        '<span style="color:#10b981;">💰 +' + (res.data.cost_saved_toman || 150) + ' تومان صرفه‌جویی</span>' +
                    '</div><br>';
                }

                var cardHtml = '';
                if (res.data && res.data.action_card && res.data.action_card.can_deploy) {
                    var ac = res.data.action_card;
                    cardHtml = '<div style="background:rgba(0,0,0,0.3);border:1px solid rgba(255,255,255,0.1);border-radius:10px;padding:10px;margin-top:8px;">' +
                        '<div style="font-weight:800;color:#38bdf8;font-size:12px;">🚀 ' + ac.flavor_name + '</div>' +
                        '<div style="font-size:11px;color:#aaa;margin:4px 0;">' + ac.cpu + ' vCPU | ' + ac.ram + 'MB RAM | ' + ac.disk + 'GB NVMe</div>' +
                        '<div style="font-size:12px;color:#10b981;font-weight:700;margin-bottom:6px;">' + ac.hourly_price_formatted + ' تومان/ساعت</div>' +
                        '<button type="button" class="ar-ai-deploy-btn ar-btn ar-btn-primary" style="padding:6px 12px;font-size:11px;width:100%;" data-flavor="' + ac.flavor_id + '" data-region="' + ac.region_id + '" data-image="' + ac.image_id + '" data-hostname="' + ac.hostname + '">⚡ راه‌اندازی فوری</button>' +
                    '</div>';
                } else if (res.data && res.data.action_card && res.data.action_card.can_heal) {
                    var ac = res.data.action_card;
                    var diff = ac.before_after_diff || {};
                    cardHtml = '<div style="background:rgba(15,23,42,0.85);border:1px solid rgba(244,63,94,0.4);border-radius:10px;padding:10px;margin-top:8px;">' +
                        '<div style="font-weight:800;color:#fb7185;font-size:12px;margin-bottom:6px;">🛠️ ' + ac.heal_title + '</div>' +
                        '<div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;font-size:10.5px;margin-bottom:8px;">' +
                            '<div style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);padding:6px;border-radius:6px;">' +
                                '<strong style="color:#ef4444;">🔴 قبل:</strong> پورت ' + (diff.before ? diff.before.port : '80') + ' (خطای ۵۰۲)' +
                            '</div>' +
                            '<div style="background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.3);padding:6px;border-radius:6px;">' +
                                '<strong style="color:#10b981;">🟢 بعد:</strong> پورت ' + (diff.after ? diff.after.port : '3000') + ' (۲۰۰ OK)' +
                            '</div>' +
                        '</div>' +
                        '<button type="button" class="ar-btn ar-btn-primary ar-signed-heal-btn" style="width:100%;padding:7px;font-size:11.5px;font-weight:800;" data-payload="' + ac.signed_token + '" data-sig="' + ac.signature + '">' +
                            '⚡ تایید و اجرای فوری اصلاحیه امن' +
                        '</button>' +
                    '</div>';
                }

                var sourcesHtml = '';
                if (res.data && res.data.sources && res.data.sources.length) {
                    sourcesHtml = '<div style="margin-top:8px;padding-top:6px;border-top:1px dashed rgba(255,255,255,0.1);display:flex;flex-wrap:wrap;gap:4px;">' +
                        '<span style="font-size:10.5px;color:var(--ar-text-muted);">📚 منابع:</span>';
                    res.data.sources.forEach(function(s) {
                        sourcesHtml += '<a href="' + s.url + '" target="_blank" style="background:rgba(255,255,255,0.06);padding:2px 6px;border-radius:8px;font-size:10px;color:#38bdf8;text-decoration:none;">' + s.title + '</a>';
                    });
                    sourcesHtml += '</div>';
                }

                $box.append('<div class="ar-ai-msg bot" style="display:flex;justify-content:flex-end;margin-bottom:8px;"><div class="ar-ai-bubble" style="background:rgba(255,255,255,0.08);color:#fff;padding:8px 12px;border-radius:12px;max-width:85%;font-size:12px;line-height:1.5;">' + tierHtml + replyHtml + cardHtml + sourcesHtml + '</div></div>');
                $box.scrollTop($box[0].scrollHeight);
            }).fail(function() {
                $sendBtn.prop('disabled', false).text('➤');
                $box.append('<div class="ar-ai-msg bot" style="color:#ef4444;font-size:12px;">خطا در اتصال به هوش مصنوعی.</div>');
            });
        });

        // 1-Click Signed Action Execution Handler (Innovation 2)
        $(document).on('click', '.ar-signed-heal-btn', function() {
            var $btn = $(this);
            var payload = $btn.data('payload');
            var sig = $btn.data('sig');

            $btn.prop('disabled', true).text('⏳ در حال اجرای امن اصلاحیه کانتینر...');

            $.post(ArvanApp.ajax_url, {
                action: 'arvan_execute_signed_action',
                nonce: ArvanApp.nonce,
                payload: payload,
                signature: sig
            }, function(res) {
                if (res.success) {
                    showToast(res.data.message || 'اکشن امن با موفقیت اعمال گردید.', 'success');
                    $btn.removeClass('ar-btn-primary').addClass('ar-btn-success').text('✅ با موفقیت ترمیم و لایو شد');
                } else {
                    showToast(res.data || 'خطا در اعتبارسنجی امضای اکشن.', 'error');
                    $btn.prop('disabled', false).text('تلاش مجدد');
                }
            }).fail(function() {
                showToast('خطا در ارتباط با سرور امنیتی.', 'error');
                $btn.prop('disabled', false).text('تلاش مجدد');
            });
        });

        // ======================================================================
        // S3 Object Storage Bucket Handlers (Feature D)
        // ======================================================================
        $(document).on('click', '#ar_btn_create_bucket_submit', function() {
            var name = $.trim($('#ar_s3_bucket_input').val());
            if (!name) {
                showToast('لطفاً نام باکت را وارد کنید.', 'error');
                return;
            }
            var $btn = $(this);
            $btn.prop('disabled', true).text('در حال ایجاد باکت...');

            $.post(ArvanApp.ajax_url, {
                action: 'arvan_customer_create_bucket',
                nonce: ArvanApp.nonce,
                bucket_name: name,
                region: 'ir-thr-at1',
                acl: 'private'
            }, function(res) {
                $btn.prop('disabled', false).text('ایجاد باکت');
                if (res.success) {
                    showToast(res.data.message || 'باکت ابری با موفقیت ایجاد شد.', 'success');
                    $('#ar_s3_bucket_input').val('');
                    loadS3BucketsList();
                } else {
                    showToast(res.data || 'خطا در ایجاد باکت.', 'error');
                }
            }).fail(function() {
                $btn.prop('disabled', false).text('ایجاد باکت');
                showToast('خطا در ارتباط با سرور ذخیره‌سازی ابری.', 'error');
            });
        });

        function loadS3BucketsList() {
            var $list = $('#ar_s3_buckets_list');
            if (!$list.length) return;

            $.post(ArvanApp.ajax_url, {
                action: 'arvan_customer_list_buckets',
                nonce: ArvanApp.nonce
            }, function(res) {
                if (res.success && res.data && res.data.buckets) {
                    $list.empty();
                    if (res.data.buckets.length === 0) {
                        $list.html('<div style="font-size:12px;color:var(--ar-text-muted);padding:8px 0;">هیچ باکت فعالی وجود ندارد.</div>');
                        return;
                    }
                    res.data.buckets.forEach(function(b) {
                        $list.append(
                            '<div style="display:flex;justify-content:space-between;align-items:center;font-size:12.5px;padding:8px 0;border-bottom:1px dashed var(--ar-border-subtle);">' +
                                '<span dir="ltr">🗂️ <strong>' + escapeHtml(b.bucket_name) + '</strong> (' + b.region + ')</span>' +
                                '<span style="color:var(--ar-status-success);font-weight:700;">● فعال (S3 Endpoint)</span>' +
                            '</div>'
                        );
                    });
                }
            });
        }

        // Auto-load S3 buckets on tab click
        $(document).on('click', '[data-tab="tab_cdn_storage"]', function() {
            loadS3BucketsList();
        });
    });

    function escapeHtml(text) {
        return $('<div>').text(text).html();
    }


        // ======================================================================
        // Server Upgrade & Hardware Resize Modal Handlers
        // ======================================================================
        $(document).on('click', '.ar-upgrade-srv-btn', function() {
            var srvId = $(this).data('id');
            var srvName = $(this).data('name');
            var srvFlavor = $(this).data('flavor');

            $('#ar_upgrade_server_id').val(srvId);
            $('#ar_upgrade_server_name_display').text(srvName + ' (' + srvId + ')');
            if (srvFlavor) {
                $('#ar_upgrade_flavor_select').val(srvFlavor);
            }
            $('#ar_upgrade_srv_modal').addClass('active');
        });

        $('#ar_upgrade_srv_form').on('submit', function(e) {
            e.preventDefault();
            var $btn = $('#ar_btn_submit_upgrade');
            var srvId = $('#ar_upgrade_server_id').val();
            var flavorId = $('#ar_upgrade_flavor_select').val();

            $btn.prop('disabled', true).text('⏳ در حال ارسال دستور تغییر اندازه به هایپروایزر...');

            $.post(ArvanApp.ajax_url, {
                action: 'arvan_customer_upgrade_server',
                nonce: ArvanApp.nonce,
                server_id: srvId,
                flavor_id: flavorId
            }, function(res) {
                $btn.prop('disabled', false).text('🚀 تایید و ارتقای آنی سرور');
                if (res.success) {
                    $('#ar_upgrade_srv_modal').removeClass('active');
                    showToast(res.data.message, 'success');
                    setTimeout(function() { location.reload(); }, 1200);
                } else {
                    showToast(res.data || 'خطا در ارتقای سرور.', 'error');
                }
            }).fail(function() {
                $btn.prop('disabled', false).text('🚀 تایید و ارتقای آنی سرور');
                showToast('خطا در ارتباط با هایپروایزر.', 'error');
            });
        });

        // ======================================================================
        // Server Rename & Edit Modal Handlers
        // ======================================================================
        $(document).on('click', '.ar-edit-srv-btn', function() {
            var srvId = $(this).data('id');
            var srvName = $(this).data('name');

            $('#ar_rename_server_id').val(srvId);
            $('#ar_rename_server_input').val(srvName);
            $('#ar_rename_srv_modal').addClass('active');
        });

        $('#ar_rename_srv_form').on('submit', function(e) {
            e.preventDefault();
            var $btn = $('#ar_btn_submit_rename');
            var srvId = $('#ar_rename_server_id').val();
            var newName = $('#ar_rename_server_input').val().trim();

            $btn.prop('disabled', true).text('در حال ذخیره...');

            $.post(ArvanApp.ajax_url, {
                action: 'arvan_customer_edit_server',
                nonce: ArvanApp.nonce,
                server_id: srvId,
                name: newName
            }, function(res) {
                $btn.prop('disabled', false).text('💾 ذخیره نام جدید');
                if (res.success) {
                    $('#ar_rename_srv_modal').removeClass('active');
                    showToast(res.data.message, 'success');
                    setTimeout(function() { location.reload(); }, 1000);
                } else {
                    showToast(res.data || 'خطا در تغییر نام سرور.', 'error');
                }
            }).fail(function() {
                $btn.prop('disabled', false).text('💾 ذخیره نام جدید');
                showToast('خطا در ارتباط با سرور.', 'error');
            });
        });

    })(jQuery);


