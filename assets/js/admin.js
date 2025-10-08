/**
 * AI Content Writer Admin JavaScript
 */

jQuery(document).ready(function($) {
    'use strict';
    
    // Global variables
    var $body = $('body');
    var $window = $(window);
    
    // Initialize
    init();
    
    function init() {
        setupEventHandlers();
        setupModals();
        setupProgressBars();
        setupTooltips();
    }
    
    // Event Handlers
    function setupEventHandlers() {
        // Generate content button
        $(document).on('click', '#ai-cw-generate-content, .ai-cw-generate-from-recommendation', function(e) {
            e.preventDefault();
            var topic = $(this).data('topic') || '';
            if (topic) {
                $('#content-topic').val(topic);
            }
            $('#ai-cw-generate-modal').show();
        });
        
        // Close modal
        $(document).on('click', '.ai-cw-close, .ai-cw-cancel', function(e) {
            e.preventDefault();
            $('.ai-cw-modal').hide();
        });
        
        // Close modal on outside click
        $(document).on('click', '.ai-cw-modal', function(e) {
            if (e.target === this) {
                $(this).hide();
            }
        });
        
        // Generate content form
        $(document).on('submit', '#ai-cw-generate-form', function(e) {
            e.preventDefault();
            generateContent();
        });
        
        // Scan content
        $(document).on('click', '#ai-cw-scan-content', function(e) {
            e.preventDefault();
            scanContent();
        });
        
        // Analyze brand
        $(document).on('click', '#ai-cw-analyze-brand', function(e) {
            e.preventDefault();
            analyzeBrand();
        });
        
        // Use suggestion
        $(document).on('click', '.ai-cw-use-suggestion', function(e) {
            e.preventDefault();
            var topic = $(this).data('topic');
            $('#content-topic').val(topic);
            $('#ai-cw-generate-modal').show();
        });
        
        // Cancel scheduled content
        $(document).on('click', '.ai-cw-cancel-scheduled', function(e) {
            e.preventDefault();
            var topic = $(this).data('topic');
            cancelScheduledContent(topic);
        });
        
        // Clear cache
        $(document).on('click', '#ai-cw-clear-cache', function(e) {
            e.preventDefault();
            clearCache();
        });
        
        // Reset settings
        $(document).on('click', '#ai-cw-reset-settings', function(e) {
            e.preventDefault();
            resetSettings();
        });
        
        // Export settings
        $(document).on('click', '#ai-cw-export-settings', function(e) {
            e.preventDefault();
            exportSettings();
        });
        
        // Import settings
        $(document).on('click', '#ai-cw-import-settings', function(e) {
            e.preventDefault();
            importSettings();
        });
        
        // Publish from preview
        $(document).on('click', '.ai-cw-publish-preview', function(e) {
            e.preventDefault();
            publishFromPreview();
        });
        
        // Close preview
        $(document).on('click', '.ai-cw-close-preview', function(e) {
            e.preventDefault();
            $('.ai-cw-generation-results').hide();
        });
    }
    
    // Modal Functions
    function setupModals() {
        // Close modal on escape key
        $(document).on('keydown', function(e) {
            if (e.keyCode === 27) { // Escape key
                $('.ai-cw-modal').hide();
            }
        });
    }
    
    // Progress Bar Functions
    function setupProgressBars() {
        // Animate progress bars
        $('.ai-cw-progress-fill').each(function() {
            var $this = $(this);
            var width = $this.data('width') || 0;
            $this.css('width', width + '%');
        });
    }
    
    // Tooltip Functions
    function setupTooltips() {
        // Add tooltips to elements with data-tooltip attribute
        $('[data-tooltip]').each(function() {
            var $this = $(this);
            var tooltip = $this.data('tooltip');
            $this.attr('title', tooltip);
        });
    }
    
    // AJAX Functions
    function generateContent() {
        var formData = {
            action: 'ai_cw_generate_content',
            topic: $('#content-topic').val(),
            keyword: $('#content-keyword').val(),
            word_count: $('#content-word-count').val(),
            status: $('#content-status').val(),
            nonce: ai_cw_ajax.nonce
        };
        
        if (!formData.topic) {
            showNotice('error', '<?php _e('Please enter a content topic.', 'ai-content-writer'); ?>');
            return;
        }
        
        showProgress('<?php _e('Generating content...', 'ai-content-writer'); ?>');
        
        $.ajax({
            url: ai_cw_ajax.ajax_url,
            type: 'POST',
            data: formData,
            success: function(response) {
                hideProgress();
                if (response.success) {
                    showNotice('success', response.data.message);
                    $('#ai-cw-generate-modal').hide();
                    location.reload();
                } else {
                    showNotice('error', response.data.message || ai_cw_ajax.strings.error);
                }
            },
            error: function() {
                hideProgress();
                showNotice('error', ai_cw_ajax.strings.error);
            }
        });
    }
    
    function scanContent() {
        var button = $('#ai-cw-scan-content');
        button.prop('disabled', true).text(ai_cw_ajax.strings.scanning);
        
        showProgress('<?php _e('Scanning content...', 'ai-content-writer'); ?>');
        
        $.ajax({
            url: ai_cw_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'ai_cw_scan_content',
                nonce: ai_cw_ajax.nonce
            },
            success: function(response) {
                hideProgress();
                if (response.success) {
                    showNotice('success', response.data.message);
                    location.reload();
                } else {
                    showNotice('error', response.data.message || ai_cw_ajax.strings.error);
                }
            },
            error: function() {
                hideProgress();
                showNotice('error', ai_cw_ajax.strings.error);
            },
            complete: function() {
                button.prop('disabled', false).text('<?php _e('Scan Content', 'ai-content-writer'); ?>');
            }
        });
    }
    
    function analyzeBrand() {
        var button = $('#ai-cw-analyze-brand');
        button.prop('disabled', true).text(ai_cw_ajax.strings.analyzing);
        
        showProgress('<?php _e('Analyzing brand...', 'ai-content-writer'); ?>');
        
        $.ajax({
            url: ai_cw_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'ai_cw_analyze_brand',
                nonce: ai_cw_ajax.nonce
            },
            success: function(response) {
                hideProgress();
                if (response.success) {
                    showNotice('success', response.data.message);
                    location.reload();
                } else {
                    showNotice('error', response.data.message || ai_cw_ajax.strings.error);
                }
            },
            error: function() {
                hideProgress();
                showNotice('error', ai_cw_ajax.strings.error);
            },
            complete: function() {
                button.prop('disabled', false).text('<?php _e('Analyze Brand', 'ai-content-writer'); ?>');
            }
        });
    }
    
    function cancelScheduledContent(topic) {
        if (!confirm('<?php _e('Are you sure you want to cancel this scheduled content?', 'ai-content-writer'); ?>')) {
            return;
        }
        
        $.ajax({
            url: ai_cw_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'ai_cw_cancel_scheduled',
                topic: topic,
                nonce: ai_cw_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    showNotice('success', response.data.message);
                    location.reload();
                } else {
                    showNotice('error', response.data.message || ai_cw_ajax.strings.error);
                }
            },
            error: function() {
                showNotice('error', ai_cw_ajax.strings.error);
            }
        });
    }
    
    function clearCache() {
        if (!confirm('<?php _e('Are you sure you want to clear the cache?', 'ai-content-writer'); ?>')) {
            return;
        }
        
        $.ajax({
            url: ai_cw_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'ai_cw_clear_cache',
                nonce: ai_cw_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    showNotice('success', '<?php _e('Cache cleared successfully.', 'ai-content-writer'); ?>');
                } else {
                    showNotice('error', response.data.message || ai_cw_ajax.strings.error);
                }
            },
            error: function() {
                showNotice('error', ai_cw_ajax.strings.error);
            }
        });
    }
    
    function resetSettings() {
        if (!confirm('<?php _e('Are you sure you want to reset all settings to default?', 'ai-content-writer'); ?>')) {
            return;
        }
        
        $.ajax({
            url: ai_cw_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'ai_cw_reset_settings',
                nonce: ai_cw_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    showNotice('success', '<?php _e('Settings reset successfully.', 'ai-content-writer'); ?>');
                    location.reload();
                } else {
                    showNotice('error', response.data.message || ai_cw_ajax.strings.error);
                }
            },
            error: function() {
                showNotice('error', ai_cw_ajax.strings.error);
            }
        });
    }
    
    function exportSettings() {
        $.ajax({
            url: ai_cw_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'ai_cw_export_settings',
                nonce: ai_cw_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    var blob = new Blob([response.data], {type: 'application/json'});
                    var url = window.URL.createObjectURL(blob);
                    var a = document.createElement('a');
                    a.href = url;
                    a.download = 'ai-content-writer-settings.json';
                    a.click();
                    window.URL.revokeObjectURL(url);
                } else {
                    showNotice('error', response.data.message || ai_cw_ajax.strings.error);
                }
            },
            error: function() {
                showNotice('error', ai_cw_ajax.strings.error);
            }
        });
    }
    
    function importSettings() {
        var input = document.createElement('input');
        input.type = 'file';
        input.accept = '.json';
        input.onchange = function(e) {
            var file = e.target.files[0];
            if (file) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    var settings = JSON.parse(e.target.result);
                    $.ajax({
                        url: ai_cw_ajax.ajax_url,
                        type: 'POST',
                        data: {
                            action: 'ai_cw_import_settings',
                            settings: settings,
                            nonce: ai_cw_ajax.nonce
                        },
                        success: function(response) {
                            if (response.success) {
                                showNotice('success', '<?php _e('Settings imported successfully.', 'ai-content-writer'); ?>');
                                location.reload();
                            } else {
                                showNotice('error', response.data.message || ai_cw_ajax.strings.error);
                            }
                        },
                        error: function() {
                            showNotice('error', ai_cw_ajax.strings.error);
                        }
                    });
                };
                reader.readAsText(file);
            }
        };
        input.click();
    }
    
    function publishFromPreview() {
        var contentData = window.previewContentData;
        if (!contentData) {
            showNotice('error', '<?php _e('No content data available.', 'ai-content-writer'); ?>');
            return;
        }
        
        $.ajax({
            url: ai_cw_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'ai_cw_publish_content',
                content_data: contentData,
                nonce: ai_cw_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    showNotice('success', response.data.message);
                    location.reload();
                } else {
                    showNotice('error', response.data.message || ai_cw_ajax.strings.error);
                }
            },
            error: function() {
                showNotice('error', ai_cw_ajax.strings.error);
            }
        });
    }
    
    // Utility Functions
    function showNotice(type, message) {
        var noticeClass = 'notice-' + type;
        var notice = $('<div class="notice ' + noticeClass + ' is-dismissible"><p>' + message + '</p></div>');
        
        $('.wrap h1').after(notice);
        
        // Auto-dismiss after 5 seconds
        setTimeout(function() {
            notice.fadeOut(function() {
                notice.remove();
            });
        }, 5000);
    }
    
    function showProgress(message) {
        var progressHtml = '<div class="ai-cw-progress-overlay">' +
            '<div class="ai-cw-progress-content">' +
            '<div class="ai-cw-progress-bar">' +
            '<div class="ai-cw-progress-fill"></div>' +
            '</div>' +
            '<p class="ai-cw-progress-text">' + message + '</p>' +
            '</div>' +
            '</div>';
        
        $body.append(progressHtml);
    }
    
    function hideProgress() {
        $('.ai-cw-progress-overlay').remove();
    }
    
    function showModal(modalId) {
        $('#' + modalId).show();
    }
    
    function hideModal(modalId) {
        $('#' + modalId).hide();
    }
    
    function updateProgressBar(percentage) {
        $('.ai-cw-progress-fill').css('width', percentage + '%');
    }
    
    function animateProgressBar() {
        var $progressFill = $('.ai-cw-progress-fill');
        var width = 0;
        var interval = setInterval(function() {
            width += Math.random() * 10;
            if (width >= 100) {
                width = 100;
                clearInterval(interval);
            }
            $progressFill.css('width', width + '%');
        }, 200);
    }
    
    // Form Validation
    function validateForm(formId) {
        var $form = $('#' + formId);
        var isValid = true;
        
        $form.find('[required]').each(function() {
            var $field = $(this);
            if (!$field.val()) {
                $field.addClass('error');
                isValid = false;
            } else {
                $field.removeClass('error');
            }
        });
        
        return isValid;
    }
    
    // Data Table Functions
    function initializeDataTables() {
        if ($.fn.DataTable) {
            $('.ai-cw-data-table').DataTable({
                pageLength: 25,
                responsive: true,
                language: {
                    search: '<?php _e('Search:', 'ai-content-writer'); ?>',
                    lengthMenu: '<?php _e('Show _MENU_ entries', 'ai-content-writer'); ?>',
                    info: '<?php _e('Showing _START_ to _END_ of _TOTAL_ entries', 'ai-content-writer'); ?>',
                    paginate: {
                        first: '<?php _e('First', 'ai-content-writer'); ?>',
                        last: '<?php _e('Last', 'ai-content-writer'); ?>',
                        next: '<?php _e('Next', 'ai-content-writer'); ?>',
                        previous: '<?php _e('Previous', 'ai-content-writer'); ?>'
                    }
                }
            });
        }
    }
    
    // Chart Functions
    function initializeCharts() {
        if (typeof Chart !== 'undefined') {
            // SEO Score Chart
            var seoScoreCtx = document.getElementById('ai-cw-seo-score-chart');
            if (seoScoreCtx) {
                new Chart(seoScoreCtx, {
                    type: 'line',
                    data: {
                        labels: ['<?php _e('Jan', 'ai-content-writer'); ?>', '<?php _e('Feb', 'ai-content-writer'); ?>', '<?php _e('Mar', 'ai-content-writer'); ?>', '<?php _e('Apr', 'ai-content-writer'); ?>', '<?php _e('May', 'ai-content-writer'); ?>', '<?php _e('Jun', 'ai-content-writer'); ?>'],
                        datasets: [{
                            label: '<?php _e('SEO Score', 'ai-content-writer'); ?>',
                            data: [6, 7, 8, 8, 9, 9],
                            borderColor: '#0073aa',
                            backgroundColor: 'rgba(0, 115, 170, 0.1)',
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 10
                            }
                        }
                    }
                });
            }
            
            // Content Generation Chart
            var contentGenCtx = document.getElementById('ai-cw-content-generation-chart');
            if (contentGenCtx) {
                new Chart(contentGenCtx, {
                    type: 'bar',
                    data: {
                        labels: ['<?php _e('Jan', 'ai-content-writer'); ?>', '<?php _e('Feb', 'ai-content-writer'); ?>', '<?php _e('Mar', 'ai-content-writer'); ?>', '<?php _e('Apr', 'ai-content-writer'); ?>', '<?php _e('May', 'ai-content-writer'); ?>', '<?php _e('Jun', 'ai-content-writer'); ?>'],
                        datasets: [{
                            label: '<?php _e('Content Generated', 'ai-content-writer'); ?>',
                            data: [5, 8, 12, 10, 15, 18],
                            backgroundColor: '#0073aa'
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
        }
    }
    
    // Initialize charts on page load
    initializeCharts();
    
    // Initialize data tables on page load
    initializeDataTables();
    
    // Auto-refresh functionality
    function setupAutoRefresh() {
        var refreshInterval = 30000; // 30 seconds
        
        setInterval(function() {
            // Only refresh if no modals are open
            if ($('.ai-cw-modal:visible').length === 0) {
                // Refresh specific sections
                refreshDashboardStats();
            }
        }, refreshInterval);
    }
    
    function refreshDashboardStats() {
        $.ajax({
            url: ai_cw_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'ai_cw_get_stats',
                nonce: ai_cw_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    updateStatsDisplay(response.data);
                }
            }
        });
    }
    
    function updateStatsDisplay(stats) {
        $('.ai-cw-stat-card .stat-number').each(function() {
            var $this = $(this);
            var statType = $this.closest('.ai-cw-stat-card').find('h3').text();
            
            switch(statType) {
                case '<?php _e('Content Generated', 'ai-content-writer'); ?>':
                    $this.text(stats.total_generated);
                    break;
                case '<?php _e('Average SEO Score', 'ai-content-writer'); ?>':
                    $this.text(stats.avg_seo_score + '/10');
                    break;
                case '<?php _e('Average Word Count', 'ai-content-writer'); ?>':
                    $this.text(stats.avg_word_count);
                    break;
                case '<?php _e('Content Scans', 'ai-content-writer'); ?>':
                    $this.text(stats.total_scans);
                    break;
            }
        });
    }
    
    // Setup auto-refresh
    setupAutoRefresh();
    
    // Keyboard shortcuts
    $(document).on('keydown', function(e) {
        // Ctrl/Cmd + G to generate content
        if ((e.ctrlKey || e.metaKey) && e.keyCode === 71) {
            e.preventDefault();
            $('#ai-cw-generate-content').click();
        }
        
        // Ctrl/Cmd + S to scan content
        if ((e.ctrlKey || e.metaKey) && e.keyCode === 83) {
            e.preventDefault();
            $('#ai-cw-scan-content').click();
        }
    });
    
    // Smooth scrolling for anchor links
    $('a[href^="#"]').on('click', function(e) {
        e.preventDefault();
        var target = $(this.getAttribute('href'));
        if (target.length) {
            $('html, body').animate({
                scrollTop: target.offset().top - 100
            }, 500);
        }
    });
    
    // Loading states for buttons
    $(document).on('click', 'button[data-loading-text]', function() {
        var $button = $(this);
        var originalText = $button.text();
        var loadingText = $button.data('loading-text');
        
        $button.prop('disabled', true).text(loadingText);
        
        // Re-enable button after 5 seconds as fallback
        setTimeout(function() {
            $button.prop('disabled', false).text(originalText);
        }, 5000);
    });
    
    // Form field validation
    $(document).on('blur', 'input[required], select[required], textarea[required]', function() {
        var $field = $(this);
        if (!$field.val()) {
            $field.addClass('error');
        } else {
            $field.removeClass('error');
        }
    });
    
    // Real-time character count
    $(document).on('input', 'textarea[data-max-length]', function() {
        var $textarea = $(this);
        var maxLength = $textarea.data('max-length');
        var currentLength = $textarea.val().length;
        var remaining = maxLength - currentLength;
        
        var $counter = $textarea.siblings('.ai-cw-char-counter');
        if ($counter.length === 0) {
            $counter = $('<div class="ai-cw-char-counter"></div>');
            $textarea.after($counter);
        }
        
        $counter.text(remaining + ' <?php _e('characters remaining', 'ai-content-writer'); ?>');
        
        if (remaining < 0) {
            $counter.addClass('over-limit');
        } else {
            $counter.removeClass('over-limit');
        }
    });
    
    // Initialize tooltips
    if ($.fn.tooltip) {
        $('[data-tooltip]').tooltip();
    }
    
    // Initialize date pickers
    if ($.fn.datepicker) {
        $('.ai-cw-datepicker').datepicker({
            dateFormat: 'yy-mm-dd',
            changeMonth: true,
            changeYear: true
        });
    }
    
    // Initialize time pickers
    if ($.fn.timepicker) {
        $('.ai-cw-timepicker').timepicker({
            timeFormat: 'HH:mm',
            interval: 15,
            minTime: '00:00',
            maxTime: '23:59',
            defaultTime: '09:00',
            startTime: '00:00',
            dynamic: false,
            dropdown: true,
            scrollbar: true
        });
    }
});
