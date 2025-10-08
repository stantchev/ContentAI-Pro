<?php
/**
 * Settings Page
 */

if (!defined('ABSPATH')) {
    exit;
}

// Handle form submission
if (isset($_POST['submit']) && wp_verify_nonce($_POST['ai_cw_settings_nonce'], 'ai_cw_settings')) {
    $settings = array(
        'openai_api_key' => sanitize_text_field($_POST['openai_api_key']),
        'auto_publish' => isset($_POST['auto_publish']),
        'content_frequency' => sanitize_text_field($_POST['content_frequency']),
        'seo_plugin' => sanitize_text_field($_POST['seo_plugin']),
        'learning_enabled' => isset($_POST['learning_enabled']),
        'min_seo_score' => intval($_POST['min_seo_score'])
    );
    
    foreach ($settings as $key => $value) {
        update_option('ai_cw_' . $key, $value);
    }
    
    echo '<div class="notice notice-success"><p>' . __('Settings saved successfully.', 'ai-content-writer') . '</p></div>';
}

// Get current settings
$openai_api_key = get_option('ai_cw_openai_api_key', '');
$auto_publish = get_option('ai_cw_auto_publish', false);
$content_frequency = get_option('ai_cw_content_frequency', 'weekly');
$seo_plugin = get_option('ai_cw_seo_plugin', 'yoast');
$learning_enabled = get_option('ai_cw_learning_enabled', true);
$min_seo_score = get_option('ai_cw_min_seo_score', 8);
?>

<div class="wrap">
    <h1><?php _e('AI Content Writer Settings', 'ai-content-writer'); ?></h1>
    
    <form method="post" action="">
        <?php wp_nonce_field('ai_cw_settings', 'ai_cw_settings_nonce'); ?>
        
        <table class="form-table">
            <tbody>
                <!-- OpenAI API Key -->
                <tr>
                    <th scope="row">
                        <label for="openai_api_key"><?php _e('OpenAI API Key', 'ai-content-writer'); ?></label>
                    </th>
                    <td>
                        <input type="password" id="openai_api_key" name="openai_api_key" 
                               value="<?php echo esc_attr($openai_api_key); ?>" class="regular-text" required>
                        <p class="description">
                            <?php _e('Enter your OpenAI API key to enable content generation.', 'ai-content-writer'); ?>
                            <a href="https://platform.openai.com/api-keys" target="_blank">
                                <?php _e('Get your API key', 'ai-content-writer'); ?>
                            </a>
                        </p>
                    </td>
                </tr>
                
                <!-- Auto Publish -->
                <tr>
                    <th scope="row">
                        <label for="auto_publish"><?php _e('Auto Publish', 'ai-content-writer'); ?></label>
                    </th>
                    <td>
                        <input type="checkbox" id="auto_publish" name="auto_publish" value="1" 
                               <?php checked($auto_publish); ?>>
                        <label for="auto_publish">
                            <?php _e('Automatically publish generated content', 'ai-content-writer'); ?>
                        </label>
                        <p class="description">
                            <?php _e('When enabled, content will be published immediately after generation.', 'ai-content-writer'); ?>
                        </p>
                    </td>
                </tr>
                
                <!-- Content Frequency -->
                <tr>
                    <th scope="row">
                        <label for="content_frequency"><?php _e('Content Generation Frequency', 'ai-content-writer'); ?></label>
                    </th>
                    <td>
                        <select id="content_frequency" name="content_frequency">
                            <option value="daily" <?php selected($content_frequency, 'daily'); ?>>
                                <?php _e('Daily', 'ai-content-writer'); ?>
                            </option>
                            <option value="weekly" <?php selected($content_frequency, 'weekly'); ?>>
                                <?php _e('Weekly', 'ai-content-writer'); ?>
                            </option>
                            <option value="monthly" <?php selected($content_frequency, 'monthly'); ?>>
                                <?php _e('Monthly', 'ai-content-writer'); ?>
                            </option>
                        </select>
                        <p class="description">
                            <?php _e('How often to automatically generate new content.', 'ai-content-writer'); ?>
                        </p>
                    </td>
                </tr>
                
                <!-- SEO Plugin -->
                <tr>
                    <th scope="row">
                        <label for="seo_plugin"><?php _e('SEO Plugin', 'ai-content-writer'); ?></label>
                    </th>
                    <td>
                        <select id="seo_plugin" name="seo_plugin">
                            <option value="yoast" <?php selected($seo_plugin, 'yoast'); ?>>
                                <?php _e('Yoast SEO', 'ai-content-writer'); ?>
                            </option>
                            <option value="rankmath" <?php selected($seo_plugin, 'rankmath'); ?>>
                                <?php _e('RankMath', 'ai-content-writer'); ?>
                            </option>
                            <option value="aioseo" <?php selected($seo_plugin, 'aioseo'); ?>>
                                <?php _e('All in One SEO', 'ai-content-writer'); ?>
                            </option>
                        </select>
                        <p class="description">
                            <?php _e('Select the SEO plugin you are using for better integration.', 'ai-content-writer'); ?>
                        </p>
                    </td>
                </tr>
                
                <!-- Learning Enabled -->
                <tr>
                    <th scope="row">
                        <label for="learning_enabled"><?php _e('Continuous Learning', 'ai-content-writer'); ?></label>
                    </th>
                    <td>
                        <input type="checkbox" id="learning_enabled" name="learning_enabled" value="1" 
                               <?php checked($learning_enabled); ?>>
                        <label for="learning_enabled">
                            <?php _e('Enable continuous learning from new content', 'ai-content-writer'); ?>
                        </label>
                        <p class="description">
                            <?php _e('The plugin will learn from your new content to improve future generations.', 'ai-content-writer'); ?>
                        </p>
                    </td>
                </tr>
                
                <!-- Minimum SEO Score -->
                <tr>
                    <th scope="row">
                        <label for="min_seo_score"><?php _e('Minimum SEO Score', 'ai-content-writer'); ?></label>
                    </th>
                    <td>
                        <input type="number" id="min_seo_score" name="min_seo_score" 
                               value="<?php echo esc_attr($min_seo_score); ?>" min="1" max="10" class="small-text">
                        <p class="description">
                            <?php _e('Minimum SEO score required for content to be considered optimized (1-10).', 'ai-content-writer'); ?>
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>
        
        <?php submit_button(); ?>
    </form>
    
    <!-- Plugin Information -->
    <div class="ai-cw-plugin-info">
        <h2><?php _e('Plugin Information', 'ai-content-writer'); ?></h2>
        
        <div class="ai-cw-info-grid">
            <div class="ai-cw-info-card">
                <h3><?php _e('Version', 'ai-content-writer'); ?></h3>
                <p><?php echo AI_CONTENT_WRITER_VERSION; ?></p>
            </div>
            
            <div class="ai-cw-info-card">
                <h3><?php _e('Database Tables', 'ai-content-writer'); ?></h3>
                <p><?php _e('All database tables are properly created and maintained.', 'ai-content-writer'); ?></p>
            </div>
            
            <div class="ai-cw-info-card">
                <h3><?php _e('Cron Jobs', 'ai-content-writer'); ?></h3>
                <p><?php _e('Scheduled tasks are running automatically.', 'ai-content-writer'); ?></p>
            </div>
            
            <div class="ai-cw-info-card">
                <h3><?php _e('API Status', 'ai-content-writer'); ?></h3>
                <p>
                    <?php if (!empty($openai_api_key)): ?>
                        <span class="ai-cw-status-good"><?php _e('OpenAI API configured', 'ai-content-writer'); ?></span>
                    <?php else: ?>
                        <span class="ai-cw-status-warning"><?php _e('OpenAI API key required', 'ai-content-writer'); ?></span>
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>
    
    <!-- System Status -->
    <div class="ai-cw-system-status">
        <h2><?php _e('System Status', 'ai-content-writer'); ?></h2>
        
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('Component', 'ai-content-writer'); ?></th>
                    <th><?php _e('Status', 'ai-content-writer'); ?></th>
                    <th><?php _e('Description', 'ai-content-writer'); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?php _e('Brand Analysis', 'ai-content-writer'); ?></td>
                    <td>
                        <?php
                        $brand_analyzer = new AI_CW_Brand_Analyzer();
                        if ($brand_analyzer->is_analysis_completed()):
                        ?>
                            <span class="ai-cw-status-good"><?php _e('Completed', 'ai-content-writer'); ?></span>
                        <?php else: ?>
                            <span class="ai-cw-status-warning"><?php _e('Required', 'ai-content-writer'); ?></span>
                        <?php endif; ?>
                    </td>
                    <td><?php _e('Brand profile analysis for content generation', 'ai-content-writer'); ?></td>
                </tr>
                
                <tr>
                    <td><?php _e('Content Scanner', 'ai-content-writer'); ?></td>
                    <td>
                        <?php
                        $content_scanner = new AI_CW_Content_Scanner();
                        $last_scan_date = $content_scanner->get_last_scan_date();
                        if (!empty($last_scan_date)):
                        ?>
                            <span class="ai-cw-status-good"><?php _e('Active', 'ai-content-writer'); ?></span>
                        <?php else: ?>
                            <span class="ai-cw-status-neutral"><?php _e('Not Run', 'ai-content-writer'); ?></span>
                        <?php endif; ?>
                    </td>
                    <td><?php _e('SEO gap analysis and content opportunities', 'ai-content-writer'); ?></td>
                </tr>
                
                <tr>
                    <td><?php _e('Learning System', 'ai-content-writer'); ?></td>
                    <td>
                        <?php if ($learning_enabled): ?>
                            <span class="ai-cw-status-good"><?php _e('Enabled', 'ai-content-writer'); ?></span>
                        <?php else: ?>
                            <span class="ai-cw-status-neutral"><?php _e('Disabled', 'ai-content-writer'); ?></span>
                        <?php endif; ?>
                    </td>
                    <td><?php _e('Continuous learning from new content', 'ai-content-writer'); ?></td>
                </tr>
                
                <tr>
                    <td><?php _e('SEO Integration', 'ai-content-writer'); ?></td>
                    <td>
                        <?php
                        $seo_integration = new AI_CW_SEO_Integration();
                        if ($seo_integration->is_seo_plugin_active()):
                        ?>
                            <span class="ai-cw-status-good"><?php echo $seo_integration->get_seo_plugin_name(); ?></span>
                        <?php else: ?>
                            <span class="ai-cw-status-warning"><?php _e('No SEO Plugin', 'ai-content-writer'); ?></span>
                        <?php endif; ?>
                    </td>
                    <td><?php _e('Integration with SEO plugins for optimization', 'ai-content-writer'); ?></td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <!-- Advanced Settings -->
    <div class="ai-cw-advanced-settings">
        <h2><?php _e('Advanced Settings', 'ai-content-writer'); ?></h2>
        
        <div class="ai-cw-advanced-actions">
            <button type="button" class="button button-secondary" id="ai-cw-clear-cache">
                <?php _e('Clear Cache', 'ai-content-writer'); ?>
            </button>
            
            <button type="button" class="button button-secondary" id="ai-cw-reset-settings">
                <?php _e('Reset Settings', 'ai-content-writer'); ?>
            </button>
            
            <button type="button" class="button button-secondary" id="ai-cw-export-settings">
                <?php _e('Export Settings', 'ai-content-writer'); ?>
            </button>
            
            <button type="button" class="button button-secondary" id="ai-cw-import-settings">
                <?php _e('Import Settings', 'ai-content-writer'); ?>
            </button>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Clear cache
    $('#ai-cw-clear-cache').on('click', function() {
        if (confirm('<?php _e('Are you sure you want to clear the cache?', 'ai-content-writer'); ?>')) {
            $.ajax({
                url: ai_cw_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'ai_cw_clear_cache',
                    nonce: ai_cw_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        alert('<?php _e('Cache cleared successfully.', 'ai-content-writer'); ?>');
                    } else {
                        alert(response.data.message || ai_cw_ajax.strings.error);
                    }
                },
                error: function() {
                    alert(ai_cw_ajax.strings.error);
                }
            });
        }
    });
    
    // Reset settings
    $('#ai-cw-reset-settings').on('click', function() {
        if (confirm('<?php _e('Are you sure you want to reset all settings to default?', 'ai-content-writer'); ?>')) {
            $.ajax({
                url: ai_cw_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'ai_cw_reset_settings',
                    nonce: ai_cw_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        alert('<?php _e('Settings reset successfully.', 'ai-content-writer'); ?>');
                        location.reload();
                    } else {
                        alert(response.data.message || ai_cw_ajax.strings.error);
                    }
                },
                error: function() {
                    alert(ai_cw_ajax.strings.error);
                }
            });
        }
    });
    
    // Export settings
    $('#ai-cw-export-settings').on('click', function() {
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
                    alert(response.data.message || ai_cw_ajax.strings.error);
                }
            },
            error: function() {
                alert(ai_cw_ajax.strings.error);
            }
        });
    });
    
    // Import settings
    $('#ai-cw-import-settings').on('click', function() {
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
                                alert('<?php _e('Settings imported successfully.', 'ai-content-writer'); ?>');
                                location.reload();
                            } else {
                                alert(response.data.message || ai_cw_ajax.strings.error);
                            }
                        },
                        error: function() {
                            alert(ai_cw_ajax.strings.error);
                        }
                    });
                };
                reader.readAsText(file);
            }
        };
        input.click();
    });
});
</script>
