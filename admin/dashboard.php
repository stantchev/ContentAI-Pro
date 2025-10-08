<?php
/**
 * Admin Dashboard Page
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get plugin data
$brand_analyzer = new AI_CW_Brand_Analyzer();
$content_scanner = new AI_CW_Content_Scanner();
$content_generator = new AI_CW_Content_Generator();
$learning_system = new AI_CW_Learning_System();
$database = new AI_CW_Database();

// Check if brand analysis is completed
$analysis_completed = $brand_analyzer->is_analysis_completed();
$analysis_date = $brand_analyzer->get_analysis_date();

// Get statistics
$stats = $database->get_statistics();
$content_logs = $content_generator->get_content_logs(10);
$learning_insights = $learning_system->get_learning_insights();
$content_recommendations = $learning_system->get_content_recommendations();

// Get last scan results
$last_scan = $content_scanner->get_last_scan_results();
$last_scan_date = $content_scanner->get_last_scan_date();
?>

<div class="wrap">
    <h1><?php _e('AI Content Writer Dashboard', 'ai-content-writer'); ?></h1>
    
    <?php if (!$analysis_completed): ?>
    <div class="notice notice-warning">
        <p>
            <strong><?php _e('Brand Analysis Required', 'ai-content-writer'); ?></strong><br>
            <?php _e('Please complete the brand analysis to start generating content.', 'ai-content-writer'); ?>
            <a href="<?php echo admin_url('admin.php?page=ai-content-writer-brand'); ?>" class="button button-primary">
                <?php _e('Run Brand Analysis', 'ai-content-writer'); ?>
            </a>
        </p>
    </div>
    <?php endif; ?>
    
    <div class="ai-cw-dashboard">
        <!-- Statistics Cards -->
        <div class="ai-cw-stats-grid">
            <div class="ai-cw-stat-card">
                <h3><?php _e('Content Generated', 'ai-content-writer'); ?></h3>
                <div class="stat-number"><?php echo intval($stats['total_generated']); ?></div>
                <p><?php _e('Total articles created', 'ai-content-writer'); ?></p>
            </div>
            
            <div class="ai-cw-stat-card">
                <h3><?php _e('Average SEO Score', 'ai-content-writer'); ?></h3>
                <div class="stat-number"><?php echo round($stats['avg_seo_score'], 1); ?>/10</div>
                <p><?php _e('SEO optimization quality', 'ai-content-writer'); ?></p>
            </div>
            
            <div class="ai-cw-stat-card">
                <h3><?php _e('Average Word Count', 'ai-content-writer'); ?></h3>
                <div class="stat-number"><?php echo round($stats['avg_word_count']); ?></div>
                <p><?php _e('Words per article', 'ai-content-writer'); ?></p>
            </div>
            
            <div class="ai-cw-stat-card">
                <h3><?php _e('Content Scans', 'ai-content-writer'); ?></h3>
                <div class="stat-number"><?php echo intval($stats['total_scans']); ?></div>
                <p><?php _e('SEO gap analyses', 'ai-content-writer'); ?></p>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="ai-cw-quick-actions">
            <h2><?php _e('Quick Actions', 'ai-content-writer'); ?></h2>
            <div class="ai-cw-action-buttons">
                <button type="button" class="button button-primary" id="ai-cw-generate-content">
                    <?php _e('Generate New Content', 'ai-content-writer'); ?>
                </button>
                
                <button type="button" class="button button-secondary" id="ai-cw-scan-content">
                    <?php _e('Scan Content', 'ai-content-writer'); ?>
                </button>
                
                <button type="button" class="button button-secondary" id="ai-cw-analyze-brand">
                    <?php _e('Update Brand Analysis', 'ai-content-writer'); ?>
                </button>
            </div>
        </div>
        
        <!-- Content Generation Modal -->
        <div id="ai-cw-generate-modal" class="ai-cw-modal" style="display: none;">
            <div class="ai-cw-modal-content">
                <span class="ai-cw-close">&times;</span>
                <h2><?php _e('Generate New Content', 'ai-content-writer'); ?></h2>
                <form id="ai-cw-generate-form">
                    <div class="ai-cw-form-group">
                        <label for="content-topic"><?php _e('Content Topic', 'ai-content-writer'); ?></label>
                        <input type="text" id="content-topic" name="topic" required 
                               placeholder="<?php _e('Enter the topic for your content', 'ai-content-writer'); ?>">
                    </div>
                    
                    <div class="ai-cw-form-group">
                        <label for="content-keyword"><?php _e('Focus Keyword', 'ai-content-writer'); ?></label>
                        <input type="text" id="content-keyword" name="keyword" 
                               placeholder="<?php _e('Optional: Enter focus keyword', 'ai-content-writer'); ?>">
                    </div>
                    
                    <div class="ai-cw-form-group">
                        <label for="content-word-count"><?php _e('Word Count', 'ai-content-writer'); ?></label>
                        <select id="content-word-count" name="word_count">
                            <option value="500"><?php _e('500 words', 'ai-content-writer'); ?></option>
                            <option value="1000" selected><?php _e('1000 words', 'ai-content-writer'); ?></option>
                            <option value="1500"><?php _e('1500 words', 'ai-content-writer'); ?></option>
                            <option value="2000"><?php _e('2000 words', 'ai-content-writer'); ?></option>
                        </select>
                    </div>
                    
                    <div class="ai-cw-form-group">
                        <label for="content-status"><?php _e('Publish Status', 'ai-content-writer'); ?></label>
                        <select id="content-status" name="status">
                            <option value="draft"><?php _e('Save as Draft', 'ai-content-writer'); ?></option>
                            <option value="publish"><?php _e('Publish Immediately', 'ai-content-writer'); ?></option>
                        </select>
                    </div>
                    
                    <div class="ai-cw-form-actions">
                        <button type="submit" class="button button-primary">
                            <?php _e('Generate Content', 'ai-content-writer'); ?>
                        </button>
                        <button type="button" class="button ai-cw-cancel">
                            <?php _e('Cancel', 'ai-content-writer'); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Recent Content -->
        <div class="ai-cw-recent-content">
            <h2><?php _e('Recent Content', 'ai-content-writer'); ?></h2>
            <?php if (!empty($content_logs)): ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Title', 'ai-content-writer'); ?></th>
                        <th><?php _e('Keyword', 'ai-content-writer'); ?></th>
                        <th><?php _e('SEO Score', 'ai-content-writer'); ?></th>
                        <th><?php _e('Word Count', 'ai-content-writer'); ?></th>
                        <th><?php _e('Generated', 'ai-content-writer'); ?></th>
                        <th><?php _e('Actions', 'ai-content-writer'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($content_logs as $log): ?>
                    <tr>
                        <td>
                            <strong><?php echo esc_html($log['title']); ?></strong>
                        </td>
                        <td><?php echo esc_html($log['keyword']); ?></td>
                        <td>
                            <span class="ai-cw-seo-score ai-cw-score-<?php echo $log['seo_score'] >= 8 ? 'good' : ($log['seo_score'] >= 6 ? 'medium' : 'poor'); ?>">
                                <?php echo $log['seo_score']; ?>/10
                            </span>
                        </td>
                        <td><?php echo number_format($log['word_count']); ?></td>
                        <td><?php echo date('M j, Y', strtotime($log['generated_at'])); ?></td>
                        <td>
                            <a href="<?php echo get_edit_post_link($log['post_id']); ?>" class="button button-small">
                                <?php _e('Edit', 'ai-content-writer'); ?>
                            </a>
                            <a href="<?php echo get_permalink($log['post_id']); ?>" class="button button-small" target="_blank">
                                <?php _e('View', 'ai-content-writer'); ?>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p><?php _e('No content generated yet.', 'ai-content-writer'); ?></p>
            <?php endif; ?>
        </div>
        
        <!-- Learning Insights -->
        <?php if (!empty($learning_insights)): ?>
        <div class="ai-cw-learning-insights">
            <h2><?php _e('Learning Insights', 'ai-content-writer'); ?></h2>
            <div class="ai-cw-insights-grid">
                <?php foreach ($learning_insights as $insight): ?>
                <div class="ai-cw-insight-card">
                    <h4><?php echo esc_html($insight['type']); ?></h4>
                    <p><?php echo esc_html($insight['message']); ?></p>
                    <span class="ai-cw-priority ai-cw-priority-<?php echo $insight['priority']; ?>">
                        <?php echo ucfirst($insight['priority']); ?>
                    </span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Content Recommendations -->
        <?php if (!empty($content_recommendations)): ?>
        <div class="ai-cw-recommendations">
            <h2><?php _e('Content Recommendations', 'ai-content-writer'); ?></h2>
            <div class="ai-cw-recommendations-list">
                <?php foreach ($content_recommendations as $recommendation): ?>
                <div class="ai-cw-recommendation-item">
                    <h4><?php echo esc_html($recommendation['topic']); ?></h4>
                    <p><?php echo esc_html($recommendation['description']); ?></p>
                    <span class="ai-cw-priority ai-cw-priority-<?php echo $recommendation['priority']; ?>">
                        <?php echo ucfirst($recommendation['priority']); ?>
                    </span>
                    <button type="button" class="button button-small ai-cw-generate-from-recommendation" 
                            data-topic="<?php echo esc_attr($recommendation['topic']); ?>">
                        <?php _e('Generate Content', 'ai-content-writer'); ?>
                    </button>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Last Scan Results -->
        <?php if (!empty($last_scan) && !empty($last_scan_date)): ?>
        <div class="ai-cw-scan-results">
            <h2><?php _e('Last Content Scan', 'ai-content-writer'); ?> 
                <small>(<?php echo date('M j, Y', strtotime($last_scan_date)); ?>)</small>
            </h2>
            
            <?php if (!empty($last_scan['seo_gaps'])): ?>
            <div class="ai-cw-scan-section">
                <h3><?php _e('SEO Gaps Found', 'ai-content-writer'); ?></h3>
                <p><?php printf(__('%d posts have SEO issues', 'ai-content-writer'), count($last_scan['seo_gaps'])); ?></p>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($last_scan['missing_topics'])): ?>
            <div class="ai-cw-scan-section">
                <h3><?php _e('Missing Topics', 'ai-content-writer'); ?></h3>
                <p><?php printf(__('%d topics need coverage', 'ai-content-writer'), count($last_scan['missing_topics'])); ?></p>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($last_scan['content_opportunities'])): ?>
            <div class="ai-cw-scan-section">
                <h3><?php _e('Content Opportunities', 'ai-content-writer'); ?></h3>
                <p><?php printf(__('%d opportunities identified', 'ai-content-writer'), count($last_scan['content_opportunities'])); ?></p>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Generate content modal
    $('#ai-cw-generate-content').on('click', function() {
        $('#ai-cw-generate-modal').show();
    });
    
    $('.ai-cw-close, .ai-cw-cancel').on('click', function() {
        $('#ai-cw-generate-modal').hide();
    });
    
    // Generate content form
    $('#ai-cw-generate-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = {
            action: 'ai_cw_generate_content',
            topic: $('#content-topic').val(),
            keyword: $('#content-keyword').val(),
            word_count: $('#content-word-count').val(),
            status: $('#content-status').val(),
            nonce: ai_cw_ajax.nonce
        };
        
        $.ajax({
            url: ai_cw_ajax.ajax_url,
            type: 'POST',
            data: formData,
            beforeSend: function() {
                $('#ai-cw-generate-form button[type="submit"]').prop('disabled', true).text(ai_cw_ajax.strings.generating);
            },
            success: function(response) {
                if (response.success) {
                    alert(ai_cw_ajax.strings.success);
                    location.reload();
                } else {
                    alert(response.data.message || ai_cw_ajax.strings.error);
                }
            },
            error: function() {
                alert(ai_cw_ajax.strings.error);
            },
            complete: function() {
                $('#ai-cw-generate-form button[type="submit"]').prop('disabled', false).text('<?php _e('Generate Content', 'ai-content-writer'); ?>');
            }
        });
    });
    
    // Scan content
    $('#ai-cw-scan-content').on('click', function() {
        var button = $(this);
        button.prop('disabled', true).text(ai_cw_ajax.strings.scanning);
        
        $.ajax({
            url: ai_cw_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'ai_cw_scan_content',
                nonce: ai_cw_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert(ai_cw_ajax.strings.success);
                    location.reload();
                } else {
                    alert(response.data.message || ai_cw_ajax.strings.error);
                }
            },
            error: function() {
                alert(ai_cw_ajax.strings.error);
            },
            complete: function() {
                button.prop('disabled', false).text('<?php _e('Scan Content', 'ai-content-writer'); ?>');
            }
        });
    });
    
    // Analyze brand
    $('#ai-cw-analyze-brand').on('click', function() {
        var button = $(this);
        button.prop('disabled', true).text(ai_cw_ajax.strings.analyzing);
        
        $.ajax({
            url: ai_cw_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'ai_cw_analyze_brand',
                nonce: ai_cw_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert(ai_cw_ajax.strings.success);
                    location.reload();
                } else {
                    alert(response.data.message || ai_cw_ajax.strings.error);
                }
            },
            error: function() {
                alert(ai_cw_ajax.strings.error);
            },
            complete: function() {
                button.prop('disabled', false).text('<?php _e('Update Brand Analysis', 'ai-content-writer'); ?>');
            }
        });
    });
    
    // Generate from recommendation
    $('.ai-cw-generate-from-recommendation').on('click', function() {
        var topic = $(this).data('topic');
        $('#content-topic').val(topic);
        $('#ai-cw-generate-modal').show();
    });
});
</script>
