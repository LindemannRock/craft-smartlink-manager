(function(window) {
    'use strict';

    window.lrSmartlinkAnalyticsInit = function(initConfig) {
        const config = initConfig || {};

        if (window.lrSmartlinkAnalyticsBound) {
            if (window.lrAnalyticsInit) {
                window.lrAnalyticsInit(config);
            }
            return;
        }
        window.lrSmartlinkAnalyticsBound = true;

        if (window.lrAnalyticsInit) {
            window.lrAnalyticsInit(config);
        }

        const chartColors = window.lrChartColors || [
            '#0d78f2', '#10b981', '#ef4444', '#f59e0b', '#8b5cf6', '#06b6d4',
            '#ec4899', '#84cc16', '#f97316', '#6366f1', '#14b8a6', '#f43f5e'
        ];

        const strings = config.strings || {};
        const geoEnabled = config.geoEnabled || false;
        const recentClicksColSpan = config.recentClicksColSpan || 11;
        const dataEndpoint = (window.Craft && Craft.getActionUrl && config.dataEndpoint)
            ? Craft.getActionUrl(config.dataEndpoint)
            : config.dataEndpoint;

        // Guard flags for lazy-loaded tabs
        var geoLoaded = false;
        var recentClicksLoaded = false;

        function esc(str) {
            if (typeof Craft !== 'undefined' && Craft.escapeHtml) {
                return Craft.escapeHtml(str);
            }
            var div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }

        function fmtNum(n) {
            return Number(n).toLocaleString();
        }

        function destroyChart(canvasId, prefix) {
            var chartKey = canvasId.replace(/-/g, '_');
            if (window.lrChartInstances && window.lrChartInstances[prefix] && window.lrChartInstances[prefix][chartKey]) {
                window.lrChartInstances[prefix][chartKey].destroy();
                delete window.lrChartInstances[prefix][chartKey];
            }
        }

        function resetChartState(canvas) {
            if (!canvas) return;
            canvas.style.display = '';
            var parent = canvas.parentElement || canvas.parentNode;
            if (!parent) return;
            parent.querySelectorAll('.zilch').forEach(function(el) { el.remove(); });
        }

        function renderEmptyState(canvasId, message, prefix) {
            var ctx = document.getElementById(canvasId);
            if (!ctx) return;
            resetChartState(ctx);
            destroyChart(canvasId, prefix);
            ctx.style.display = 'none';
            var parent = ctx.parentElement || ctx.parentNode;
            if (!parent) return;
            var emptyMsg = document.createElement('div');
            emptyMsg.className = 'zilch';
            emptyMsg.style.padding = '48px 24px';
            emptyMsg.style.textAlign = 'center';
            var p = document.createElement('p');
            p.textContent = message;
            emptyMsg.appendChild(p);
            parent.appendChild(emptyMsg);
        }

        function renderEmptyRow(tbodyId, colSpan, message) {
            var tbody = document.getElementById(tbodyId);
            if (!tbody) return;
            tbody.innerHTML = '<tr><td colspan="' + colSpan + '" class="thin light lr-text-center">' + esc(message) + '</td></tr>';
        }

        function setPeakInfo(text) {
            var el = document.getElementById('peak-hour-info');
            if (el) {
                el.textContent = text || '';
            }
        }

        function requestData(type, params, onSuccess, onError) {
            if (!dataEndpoint) {
                if (onError) onError();
                return;
            }

            var data = Object.assign({ type: type }, params || {});

            if (config.csrfName && config.csrfToken) {
                data[config.csrfName] = config.csrfToken;
            }

            if (typeof $ !== 'undefined' && $.ajax) {
                $.ajax({
                    url: dataEndpoint,
                    type: 'POST',
                    dataType: 'json',
                    data: data,
                    success: function(response) {
                        if (response && response.success) {
                            onSuccess(response.data || {});
                        } else if (onError) {
                            onError();
                        }
                    },
                    error: function() {
                        if (onError) onError();
                    }
                });
                return;
            }

            var formData = new FormData();
            Object.keys(data).forEach(function(key) {
                formData.append(key, data[key]);
            });

            fetch(dataEndpoint, {
                method: 'POST',
                body: formData
            })
            .then(function(response) { return response.json(); })
            .then(function(response) {
                if (response && response.success) {
                    onSuccess(response.data || {});
                } else if (onError) {
                    onError();
                }
            })
            .catch(function() {
                if (onError) onError();
            });
        }

        function getActiveTabId() {
            var hash = window.location.hash ? window.location.hash.substring(1) : '';
            if (hash && document.getElementById(hash)) {
                return hash;
            }
            var visible = document.querySelector('.lr-tab-content:not(.hidden)');
            return visible ? visible.id : 'overview';
        }

        document.addEventListener('lr:analyticsInit', function(e) {
            var eventConfig = e.detail && e.detail.config ? e.detail.config : (window.lrAnalyticsConfig || {});
            var prefix = eventConfig.prefix || 'analytics';
            var dateRange = eventConfig.dateRange || config.dateRange || 'last7days';
            var siteId = eventConfig.siteId || config.siteId || '';

            // Reset guard flags on re-init (date/site change)
            geoLoaded = false;
            recentClicksLoaded = false;

            loadAllCharts(dateRange, siteId, prefix);

            // Reload the currently active tab (e.g. geographic) if not overview
            var activeTab = getActiveTabId();
            if (activeTab === 'geographic' && geoEnabled) {
                geoLoaded = true;
                loadGeographic({ dateRange: dateRange, siteId: siteId });
            }
        });

        document.addEventListener('lr:tabChanged', function(e) {
            var tabId = e.detail && e.detail.tabId ? e.detail.tabId : '';
            var currentConfig = window.lrAnalyticsConfig || {};
            var dateRange = currentConfig.dateRange || config.dateRange || 'last7days';
            var siteId = currentConfig.siteId || config.siteId || '';
            var baseParams = { dateRange: dateRange, siteId: siteId };

            if (tabId === 'geographic' && !geoLoaded && geoEnabled) {
                geoLoaded = true;
                loadGeographic(baseParams);
            }
        });

        function loadAllCharts(dateRange, siteId, prefix) {
            var baseParams = { dateRange: dateRange, siteId: siteId };

            // Load recent clicks for overview tab
            recentClicksLoaded = true;
            loadRecentClicks(baseParams);

            requestData('clicks', baseParams, function(data) {
                var hasClicks = Array.isArray(data.values) && data.values.some(function(value) { return Number(value) > 0; });
                if (data.labels && data.labels.length > 0 && hasClicks) {
                    renderClicksChart(data);
                } else {
                    renderEmptyState('clicks-chart', strings.noInteraction || 'No interaction data available.', prefix);
                }
            }, function() {
                renderEmptyState('clicks-chart', strings.noInteraction || 'No interaction data available.', prefix);
            });

            requestData('device-types', baseParams, function(data) {
                var hasData = Array.isArray(data.values) && data.values.some(function(value) { return Number(value) > 0; });
                if (data.labels && data.labels.length > 0 && hasData) {
                    renderDeviceChart(data);
                } else {
                    renderEmptyState('device-chart', strings.noDevice || 'No device data available.', prefix);
                }
            }, function() {
                renderEmptyState('device-chart', strings.noDevice || 'No device data available.', prefix);
            });

            requestData('device-brands', baseParams, function(data) {
                var hasData = Array.isArray(data.values) && data.values.some(function(value) { return Number(value) > 0; });
                if (data.labels && data.labels.length > 0 && hasData) {
                    renderBrandChart(data);
                } else {
                    renderEmptyState('brand-chart', strings.noBrand || 'No device brand data available.', prefix);
                }
            }, function() {
                renderEmptyState('brand-chart', strings.noBrand || 'No device brand data available.', prefix);
            });

            requestData('os-breakdown', baseParams, function(data) {
                var hasData = Array.isArray(data.values) && data.values.some(function(value) { return Number(value) > 0; });
                if (data.labels && data.labels.length > 0 && hasData) {
                    renderOsChart(data);
                } else {
                    renderEmptyState('os-chart', strings.noOs || 'No OS data available.', prefix);
                }
            }, function() {
                renderEmptyState('os-chart', strings.noOs || 'No OS data available.', prefix);
            });

            requestData('browsers', baseParams, function(data) {
                var hasData = Array.isArray(data.values) && data.values.some(function(value) { return Number(value) > 0; });
                if (data.labels && data.labels.length > 0 && hasData) {
                    renderBrowserChart(data);
                } else {
                    renderEmptyState('browser-chart', strings.noBrowser || 'No browser data available.', prefix);
                }
            }, function() {
                renderEmptyState('browser-chart', strings.noBrowser || 'No browser data available.', prefix);
            });

            requestData('hourly', baseParams, function(data) {
                var hasHourly = Array.isArray(data.data) && data.data.some(function(value) { return Number(value) > 0; });
                if (data.data && data.data.length > 0 && hasHourly) {
                    renderHourlyChart(data);
                } else {
                    renderEmptyState('hourly-chart', strings.noHourly || 'No hourly data available.', prefix);
                    setPeakInfo('');
                }
            }, function() {
                renderEmptyState('hourly-chart', strings.noHourly || 'No hourly data available.', prefix);
                setPeakInfo('');
            });
        }

        function loadRecentClicks(baseParams) {
            requestData('recent-clicks', baseParams, function(data) {
                renderRecentClicksTable(Array.isArray(data) ? data : []);
            }, function() {
                renderEmptyRow('recent-clicks-body', recentClicksColSpan, strings.noRecentClicks || 'No interactions recorded yet');
            });
        }

        function loadGeographic(baseParams) {
            requestData('countries', baseParams, function(data) {
                renderCountriesTable(Array.isArray(data) ? data : []);
            }, function() {
                renderEmptyRow('geo-countries-body', 3, strings.noCountryData || 'No country data available');
            });

            requestData('all-cities', baseParams, function(data) {
                renderCitiesTable(Array.isArray(data) ? data : []);
            }, function() {
                renderEmptyRow('geo-cities-body', 4, strings.noCityData || 'No city data available');
            });
        }

        function renderCountriesTable(countries) {
            var tbody = document.getElementById('geo-countries-body');
            if (!tbody) return;

            if (!countries.length) {
                renderEmptyRow('geo-countries-body', 3, strings.noCountryData || 'No country data available');
                return;
            }

            var html = '';
            for (var i = 0; i < countries.length; i++) {
                var c = countries[i];
                html += '<tr>' +
                    '<td>' + esc(c.name || '') + '</td>' +
                    '<td>' + fmtNum(c.clicks || 0) + '</td>' +
                    '<td>' + esc(String(c.percentage || 0)) + '%</td>' +
                    '</tr>';
            }
            tbody.innerHTML = html;
        }

        function renderCitiesTable(cities) {
            var tbody = document.getElementById('geo-cities-body');
            if (!tbody) return;

            if (!cities.length) {
                renderEmptyRow('geo-cities-body', 4, strings.noCityData || 'No city data available');
                return;
            }

            var html = '';
            for (var i = 0; i < cities.length; i++) {
                var c = cities[i];
                html += '<tr>' +
                    '<td>' + esc(c.city || '') + '</td>' +
                    '<td>' + esc(c.countryName || '') + '</td>' +
                    '<td>' + fmtNum(c.clicks || 0) + '</td>' +
                    '<td>' + esc(String(c.percentage || 0)) + '%</td>' +
                    '</tr>';
            }
            tbody.innerHTML = html;
        }

        function renderRecentClicksTable(clicks) {
            var tbody = document.getElementById('recent-clicks-body');
            if (!tbody) return;

            if (!clicks.length) {
                renderEmptyRow('recent-clicks-body', recentClicksColSpan, strings.noRecentClicks || 'No interactions recorded yet');
                return;
            }

            var html = '';
            for (var i = 0; i < clicks.length; i++) {
                var click = clicks[i];
                var destUrl = click.destinationUrl || '';
                var destDisplay = destUrl.length > 25 ? destUrl.substring(0, 25) + '...' : destUrl;
                var linkUrl = (typeof Craft !== 'undefined' && Craft.getCpUrl)
                    ? Craft.getCpUrl('smartlink-manager/' + click.linkId)
                    : 'smartlink-manager/' + click.linkId;

                html += '<tr>' +
                    '<td style="white-space:nowrap">' + esc(click.dateFormatted || '') + '</td>' +
                    '<td style="white-space:nowrap">' + esc(click.timeFormatted || '') + '</td>' +
                    '<td><a href="' + esc(linkUrl) + '">' + esc(click.smartLinkTitle || '') + '</a></td>' +
                    '<td>' + esc(click.siteName || '\u2014') + '</td>' +
                    '<td>' + esc(click.clickTypeLabel || '') + '</td>' +
                    '<td>' + esc(click.platformLabel || '\u2014') + '</td>' +
                    '<td>' + esc(click.sourceLabel || '') + '</td>' +
                    '<td>' + (destUrl ? '<span title="' + esc(destUrl) + '">' + esc(destDisplay) + '</span>' : '\u2014') + '</td>' +
                    '<td>' + esc(click.deviceType || '\u2014') + '</td>' +
                    '<td>' + esc(click.browser || '\u2014') + '</td>' +
                    '<td>' + esc(click.osName || '\u2014') + '</td>';

                if (click.geoEnabled) {
                    html += '<td style="white-space:nowrap">' + esc(click.location || '\u2014') + '</td>';
                }

                html += '</tr>';
            }
            tbody.innerHTML = html;
        }

        function renderClicksChart(data) {
            var ctx = document.getElementById('clicks-chart');
            if (!ctx) return;
            resetChartState(ctx);
            window.lrCreateChart('clicks-chart', 'line', {
                labels: data.labels,
                datasets: [{
                    label: strings.clicksLabel || 'Clicks',
                    data: data.values,
                    borderColor: chartColors[0],
                    backgroundColor: chartColors[0] + '20',
                    tension: 0.1,
                    fill: true
                }]
            }, {
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1, precision: 0 } } },
                plugins: { legend: { display: false } }
            });
        }

        function renderDeviceChart(data) {
            var ctx = document.getElementById('device-chart');
            if (!ctx) return;
            resetChartState(ctx);
            window.lrCreateChart('device-chart', 'doughnut', {
                labels: data.labels,
                datasets: [{ data: data.values, backgroundColor: chartColors.slice(0, 6) }]
            }, {
                plugins: { legend: { position: 'bottom' } }
            });
        }

        function renderBrandChart(data) {
            var ctx = document.getElementById('brand-chart');
            if (!ctx) return;
            resetChartState(ctx);

            window.lrCreateChart('brand-chart', 'bar', {
                labels: data.labels,
                datasets: [{
                    label: strings.clicksLabel || 'Clicks',
                    data: data.values,
                    backgroundColor: chartColors[0],
                    borderColor: chartColors[0],
                    borderWidth: 1
                }]
            }, {
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1, precision: 0 } } },
                plugins: { legend: { display: false } }
            });
        }

        function renderOsChart(data) {
            var ctx = document.getElementById('os-chart');
            if (!ctx) return;
            resetChartState(ctx);
            window.lrCreateChart('os-chart', 'doughnut', {
                labels: data.labels,
                datasets: [{ data: data.values, backgroundColor: chartColors }]
            }, {
                plugins: { legend: { position: 'bottom' } }
            });
        }

        function renderBrowserChart(data) {
            var ctx = document.getElementById('browser-chart');
            if (!ctx) return;
            resetChartState(ctx);
            window.lrCreateChart('browser-chart', 'doughnut', {
                labels: data.labels,
                datasets: [{ data: data.values, backgroundColor: chartColors }]
            }, {
                plugins: { legend: { position: 'bottom' } }
            });
        }

        function renderHourlyChart(data) {
            var ctx = document.getElementById('hourly-chart');
            if (!ctx) return;
            resetChartState(ctx);

            window.lrCreateChart('hourly-chart', 'bar', {
                labels: Array.from({ length: 24 }, function(_, i) { return i + ':00'; }),
                datasets: [{
                    label: strings.clicksLabel || 'Clicks',
                    data: data.data,
                    backgroundColor: chartColors[0],
                    borderColor: chartColors[0],
                    borderWidth: 1
                }]
            }, {
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1, precision: 0 } } },
                plugins: { legend: { display: false } }
            });

            if (data.peakHourFormatted) {
                setPeakInfo((strings.peakUsageAt || 'Peak usage at') + ' <strong>' + data.peakHourFormatted + '</strong>');
            } else {
                setPeakInfo('');
            }
        }
    };
})(window);
