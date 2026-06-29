(function() {
    'use strict';

    const configElement = document.querySelector('script[data-smartlink-edit-config]');
    if (!configElement) {
        return;
    }

    let config = {};
    try {
        config = JSON.parse(configElement.textContent || '{}');
    } catch (e) {
        config = {};
    }

    const messages = config.messages || {};
    const urls = config.urls || {};
    const defaults = config.defaults || {};

    function initSaveShortcut() {
        const saveShortcut = document.getElementById('save-shortcut');
        if (saveShortcut) {
            saveShortcut.textContent = navigator.platform.toUpperCase().indexOf('MAC') >= 0 ? '⌘S' : 'Ctrl+S';
        }
    }

    function initSlugBehavior() {
        if (typeof $ === 'undefined') {
            return;
        }

        if (config.generateSlug && typeof Craft !== 'undefined' && Craft.SlugGenerator) {
            new Craft.SlugGenerator('#title', '#slug');
        }

        $('#slug').on('blur', function() {
            const slug = $(this).val();
            if (slug) {
                $(this).val(window.lrIdentifiers?.normalizeSlug(slug, '') ?? slug);
            }
        });
    }

    function initQrDownload() {
        if (typeof $ === 'undefined' || !urls.qrPublicBaseUrl) {
            return;
        }

        $('.download-qr').on('click', function(e) {
            e.preventDefault();

            let size = $(this).data('size');

            if (size === 'custom') {
                const customSize = prompt(messages.enterCustomSize || 'Enter custom size (100-4096 pixels):', '1024');
                if (!customSize) {
                    return;
                }

                size = parseInt(customSize, 10);
                if (isNaN(size) || size < 100 || size > 4096) {
                    alert(messages.invalidCustomSize || 'Please enter a valid size between 100 and 4096 pixels');
                    return;
                }
            }

            const color = ($('#qrCodeColor').val() || '000000').replace(/^#/, '');
            const bgColor = ($('#qrCodeBgColor').val() || 'FFFFFF').replace(/^#/, '');
            const eyeColor = $('#qrCodeEyeColor').val() ? $('#qrCodeEyeColor').val().replace(/^#/, '') : '';
            const format = $('#qrCodeFormat').val() || defaults.qrFormat || 'png';

            let downloadUrl = urls.qrPublicBaseUrl +
                '?size=' + encodeURIComponent(size) +
                '&color=' + encodeURIComponent(color) +
                '&bg=' + encodeURIComponent(bgColor) +
                '&format=' + encodeURIComponent(format) +
                '&download=1';

            if (eyeColor) {
                downloadUrl += '&eyeColor=' + encodeURIComponent(eyeColor);
            }

            const logoField = document.querySelector('#qrLogoId-field .elements .element');
            if (logoField) {
                downloadUrl += '&logo=' + encodeURIComponent(logoField.dataset.id);
            }

            let downloadFrame = document.getElementById('smartlink-qr-download-frame');
            if (!downloadFrame) {
                downloadFrame = document.createElement('iframe');
                downloadFrame.id = 'smartlink-qr-download-frame';
                downloadFrame.style.display = 'none';
                document.body.appendChild(downloadFrame);
            }

            downloadFrame.src = downloadUrl;
        });
    }

    function initQrDefaultsReset() {
        if (typeof $ === 'undefined') {
            return;
        }

        $('#reset-qr-defaults').on('click', function(e) {
            e.preventDefault();
            if (!confirm(messages.resetQrDefaultsConfirm || 'Reset QR code settings to plugin defaults?')) {
                return;
            }

            $('#qrCodeSize').val(defaults.qrSize || 256);
            $('#qrCodeColor').val(defaults.qrColor || '#000000');
            $('#qrCodeBgColor').val(defaults.qrBgColor || '#FFFFFF');
            $('#qrCodeEyeColor').val(defaults.qrEyeColor || '');
            $('#qrCodeFormat').val(defaults.qrFormat || '');

            $('#qrCodeSize').trigger('change');
            $('#qrCodeColor').trigger('change').trigger('input');
            $('#qrCodeBgColor').trigger('change').trigger('input');
            $('#qrCodeEyeColor').trigger('change').trigger('input');
            $('#qrCodeFormat').trigger('change');

            Craft.cp.displayNotice(messages.qrDefaultsReset || 'QR code settings reset to defaults');
        });
    }

    function initAnalyticsToggleConfirm() {
        if (typeof $ === 'undefined' || !config.confirmAnalyticsDisable) {
            return;
        }

        $('#trackAnalytics').on('change', function() {
            const $toggle = $(this);
            const isEnabled = $toggle.hasClass('on');

            if (!isEnabled && !confirm(messages.disableAnalyticsConfirm || 'Disable analytics tracking?')) {
                $toggle.addClass('on');
                $toggle.find('input[type="hidden"]').val('1');
                $toggle.attr('aria-checked', 'true');
                return false;
            }

            return true;
        });
    }

    function initDisclosureMenu() {
        if (typeof Craft !== 'undefined' && Craft.ui && Craft.ui.createDisclosureMenu) {
            const actionBtn = document.getElementById('action-btn');
            if (actionBtn) {
                new Craft.ui.createDisclosureMenu(actionBtn);
            }
        }
    }

    function init() {
        initSaveShortcut();
        initSlugBehavior();
        initQrDownload();
        initQrDefaultsReset();
        initAnalyticsToggleConfirm();
        initDisclosureMenu();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
