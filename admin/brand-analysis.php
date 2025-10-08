<?php
/**
 * Brand Analysis Page
 */

if (!defined('ABSPATH')) {
    exit;
}

$brand_analyzer = new AI_CW_Brand_Analyzer();
$analysis_completed = $brand_analyzer->is_analysis_completed();
$analysis_date = $brand_analyzer->get_analysis_date();
$brand_profile = $brand_analyzer->get_brand_profile();
?>

<div class="wrap">
    <h1><?php _e('Brand Analysis', 'ai-content-writer'); ?></h1>
    
    <?php if ($analysis_completed): ?>
    <div class="notice notice-success">
        <p>
            <strong><?php _e('Brand Analysis Completed', 'ai-content-writer'); ?></strong><br>
            <?php printf(__('Last analyzed: %s', 'ai-content-writer'), date('F j, Y', strtotime($analysis_date))); ?>
        </p>
    </div>
    <?php endif; ?>
    
    <div class="ai-cw-brand-analysis">
        <!-- Analysis Status -->
        <div class="ai-cw-analysis-status">
            <h2><?php _e('Analysis Status', 'ai-content-writer'); ?></h2>
            
            <?php if ($analysis_completed): ?>
            <div class="ai-cw-status-completed">
                <span class="dashicons dashicons-yes-alt"></span>
                <p><?php _e('Brand analysis is complete and up to date.', 'ai-content-writer'); ?></p>
            </div>
            <?php else: ?>
            <div class="ai-cw-status-pending">
                <span class="dashicons dashicons-warning"></span>
                <p><?php _e('Brand analysis is required to start generating content.', 'ai-content-writer'); ?></p>
            </div>
            <?php endif; ?>
            
            <div class="ai-cw-analysis-actions">
                <button type="button" class="button button-primary" id="ai-cw-run-analysis">
                    <?php _e('Run Brand Analysis', 'ai-content-writer'); ?>
                </button>
                
                <button type="button" class="button button-secondary" id="ai-cw-update-analysis">
                    <?php _e('Update Analysis', 'ai-content-writer'); ?>
                </button>
            </div>
        </div>
        
        <!-- Brand Profile Display -->
        <?php if (!empty($brand_profile)): ?>
        <div class="ai-cw-brand-profile">
            <h2><?php _e('Brand Profile', 'ai-content-writer'); ?></h2>
            
            <!-- Tone of Voice -->
            <?php if (!empty($brand_profile['tone_of_voice'])): ?>
            <div class="ai-cw-profile-section">
                <h3><?php _e('Tone of Voice', 'ai-content-writer'); ?></h3>
                <div class="ai-cw-profile-grid">
                    <?php foreach ($brand_profile['tone_of_voice'] as $key => $value): ?>
                    <div class="ai-cw-profile-item">
                        <strong><?php echo esc_html(ucfirst(str_replace('_', ' ', $key))); ?>:</strong>
                        <span><?php echo esc_html($value); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Language Characteristics -->
            <?php if (!empty($brand_profile['language_characteristics'])): ?>
            <div class="ai-cw-profile-section">
                <h3><?php _e('Language Characteristics', 'ai-content-writer'); ?></h3>
                <div class="ai-cw-profile-grid">
                    <?php foreach ($brand_profile['language_characteristics'] as $key => $value): ?>
                    <div class="ai-cw-profile-item">
                        <strong><?php echo esc_html(ucfirst(str_replace('_', ' ', $key))); ?>:</strong>
                        <span><?php echo esc_html($value); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Content Themes -->
            <?php if (!empty($brand_profile['content_themes'])): ?>
            <div class="ai-cw-profile-section">
                <h3><?php _e('Content Themes', 'ai-content-writer'); ?></h3>
                <div class="ai-cw-profile-grid">
                    <?php foreach ($brand_profile['content_themes'] as $key => $value): ?>
                    <div class="ai-cw-profile-item">
                        <strong><?php echo esc_html(ucfirst(str_replace('_', ' ', $key))); ?>:</strong>
                        <?php if (is_array($value)): ?>
                        <ul>
                            <?php foreach ($value as $item): ?>
                            <li><?php echo esc_html($item); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <?php else: ?>
                        <span><?php echo esc_html($value); ?></span>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- SEO Patterns -->
            <?php if (!empty($brand_profile['seo_patterns'])): ?>
            <div class="ai-cw-profile-section">
                <h3><?php _e('SEO Patterns', 'ai-content-writer'); ?></h3>
                <div class="ai-cw-profile-grid">
                    <?php foreach ($brand_profile['seo_patterns'] as $key => $value): ?>
                    <div class="ai-cw-profile-item">
                        <strong><?php echo esc_html(ucfirst(str_replace('_', ' ', $key))); ?>:</strong>
                        <?php if (is_array($value)): ?>
                        <ul>
                            <?php foreach ($value as $item): ?>
                            <li><?php echo esc_html($item); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <?php else: ?>
                        <span><?php echo esc_html($value); ?></span>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Brand Guidelines -->
            <?php if (!empty($brand_profile['brand_guidelines'])): ?>
            <div class="ai-cw-profile-section">
                <h3><?php _e('Brand Guidelines', 'ai-content-writer'); ?></h3>
                <div class="ai-cw-profile-grid">
                    <?php foreach ($brand_profile['brand_guidelines'] as $key => $value): ?>
                    <div class="ai-cw-profile-item">
                        <strong><?php echo esc_html(ucfirst(str_replace('_', ' ', $key))); ?>:</strong>
                        <span><?php echo esc_html($value); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <!-- Analysis Progress -->
        <div class="ai-cw-analysis-progress" style="display: none;">
            <h2><?php _e('Analysis Progress', 'ai-content-writer'); ?></h2>
            <div class="ai-cw-progress-bar">
                <div class="ai-cw-progress-fill"></div>
            </div>
            <p class="ai-cw-progress-text"><?php _e('Analyzing content...', 'ai-content-writer'); ?></p>
        </div>
        
        <!-- Analysis Results -->
        <div class="ai-cw-analysis-results" style="display: none;">
            <h2><?php _e('Analysis Results', 'ai-content-writer'); ?></h2>
            <div class="ai-cw-results-content"></div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Run brand analysis
    $('#ai-cw-run-analysis').on('click', function() {
        var button = $(this);
        button.prop('disabled', true).text('<?php _e('Analyzing...', 'ai-content-writer'); ?>');
        
        $('.ai-cw-analysis-progress').show();
        
        $.ajax({
            url: ai_cw_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'ai_cw_analyze_brand',
                nonce: ai_cw_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('.ai-cw-analysis-results .ai-cw-results-content').html(
                        '<div class="notice notice-success"><p>' + response.data.message + '</p></div>'
                    );
                    $('.ai-cw-analysis-results').show();
                    location.reload();
                } else {
                    $('.ai-cw-analysis-results .ai-cw-results-content').html(
                        '<div class="notice notice-error"><p>' + (response.data.message || ai_cw_ajax.strings.error) + '</p></div>'
                    );
                    $('.ai-cw-analysis-results').show();
                }
            },
            error: function() {
                $('.ai-cw-analysis-results .ai-cw-results-content').html(
                    '<div class="notice notice-error"><p>' + ai_cw_ajax.strings.error + '</p></div>'
                );
                $('.ai-cw-analysis-results').show();
            },
            complete: function() {
                button.prop('disabled', false).text('<?php _e('Run Brand Analysis', 'ai-content-writer'); ?>');
                $('.ai-cw-analysis-progress').hide();
            }
        });
    });
    
    // Update brand analysis
    $('#ai-cw-update-analysis').on('click', function() {
        var button = $(this);
        button.prop('disabled', true).text('<?php _e('Updating...', 'ai-content-writer'); ?>');
        
        $('.ai-cw-analysis-progress').show();
        
        $.ajax({
            url: ai_cw_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'ai_cw_analyze_brand',
                nonce: ai_cw_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('.ai-cw-analysis-results .ai-cw-results-content').html(
                        '<div class="notice notice-success"><p>' + response.data.message + '</p></div>'
                    );
                    $('.ai-cw-analysis-results').show();
                    location.reload();
                } else {
                    $('.ai-cw-analysis-results .ai-cw-results-content').html(
                        '<div class="notice notice-error"><p>' + (response.data.message || ai_cw_ajax.strings.error) + '</p></div>'
                    );
                    $('.ai-cw-analysis-results').show();
                }
            },
            error: function() {
                $('.ai-cw-analysis-results .ai-cw-results-content').html(
                    '<div class="notice notice-error"><p>' + ai_cw_ajax.strings.error + '</p></div>'
                );
                $('.ai-cw-analysis-results').show();
            },
            complete: function() {
                button.prop('disabled', false).text('<?php _e('Update Analysis', 'ai-content-writer'); ?>');
                $('.ai-cw-analysis-progress').hide();
            }
        });
    });
});
</script>
