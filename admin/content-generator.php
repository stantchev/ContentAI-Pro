<?php
/**
 * Content Generator Page
 */

if (!defined('ABSPATH')) {
    exit;
}

$content_generator = new AI_CW_Content_Generator();
$scheduler = new AI_CW_Scheduler();
$brand_analyzer = new AI_CW_Brand_Analyzer();

// Check if brand analysis is completed
$analysis_completed = $brand_analyzer->is_analysis_completed();

// Get content suggestions
$content_suggestions = $content_generator->generate_content_suggestions();

// Get scheduled content
$scheduled_content = $scheduler->get_scheduled_content();

// Get content logs
$content_logs = $content_generator->get_content_logs(20);
?>

<div class="wrap">
    <h1><?php _e('Content Generator', 'ai-content-writer'); ?></h1>
    
    <?php if (!$analysis_completed): ?>
    <div class="notice notice-warning">
        <p>
            <strong><?php _e('Brand Analysis Required', 'ai-content-writer'); ?></strong><br>
            <?php _e('Please complete the brand analysis before generating content.', 'ai-content-writer'); ?>
            <a href="<?php echo admin_url('admin.php?page=ai-content-writer-brand'); ?>" class="button button-primary">
                <?php _e('Run Brand Analysis', 'ai-content-writer'); ?>
            </a>
        </p>
    </div>
    <?php endif; ?>
    
    <div class="ai-cw-content-generator">
        <!-- Content Generation Form -->
        <div class="ai-cw-generation-form">
            <h2><?php _e('Generate New Content', 'ai-content-writer'); ?></h2>
            
            <form id="ai-cw-content-form">
                <div class="ai-cw-form-row">
                    <div class="ai-cw-form-group">
                        <label for="content-topic"><?php _e('Content Topic', 'ai-content-writer'); ?> *</label>
                        <input type="text" id="content-topic" name="topic" required 
                               placeholder="<?php _e('Enter the topic for your content', 'ai-content-writer'); ?>">
                    </div>
                    
                    <div class="ai-cw-form-group">
                        <label for="content-keyword"><?php _e('Focus Keyword', 'ai-content-writer'); ?></label>
                        <input type="text" id="content-keyword" name="keyword" 
                               placeholder="<?php _e('Optional: Enter focus keyword', 'ai-content-writer'); ?>">
                    </div>
                </div>
                
                <div class="ai-cw-form-row">
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
                        <label for="content-tone"><?php _e('Content Tone', 'ai-content-writer'); ?></label>
                        <select id="content-tone" name="tone">
                            <option value=""><?php _e('Use Brand Tone', 'ai-content-writer'); ?></option>
                            <option value="professional"><?php _e('Professional', 'ai-content-writer'); ?></option>
                            <option value="casual"><?php _e('Casual', 'ai-content-writer'); ?></option>
                            <option value="friendly"><?php _e('Friendly', 'ai-content-writer'); ?></option>
                            <option value="authoritative"><?php _e('Authoritative', 'ai-content-writer'); ?></option>
                        </select>
                    </div>
                </div>
                
                <div class="ai-cw-form-row">
                    <div class="ai-cw-form-group">
                        <label for="content-status"><?php _e('Publish Status', 'ai-content-writer'); ?></label>
                        <select id="content-status" name="status">
                            <option value="draft"><?php _e('Save as Draft', 'ai-content-writer'); ?></option>
                            <option value="publish"><?php _e('Publish Immediately', 'ai-content-writer'); ?></option>
                        </select>
                    </div>
                    
                    <div class="ai-cw-form-group">
                        <label for="content-schedule"><?php _e('Schedule for Later', 'ai-content-writer'); ?></label>
                        <input type="datetime-local" id="content-schedule" name="schedule_date">
                    </div>
                </div>
                
                <div class="ai-cw-form-actions">
                    <button type="submit" class="button button-primary" id="ai-cw-generate-button">
                        <?php _e('Generate Content', 'ai-content-writer'); ?>
                    </button>
                    <button type="button" class="button button-secondary" id="ai-cw-preview-button">
                        <?php _e('Preview Only', 'ai-content-writer'); ?>
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Content Suggestions -->
        <?php if (!empty($content_suggestions)): ?>
        <div class="ai-cw-content-suggestions">
            <h2><?php _e('Content Suggestions', 'ai-content-writer'); ?></h2>
            <p><?php _e('Based on your brand analysis and content patterns.', 'ai-content-writer'); ?></p>
            
            <div class="ai-cw-suggestions-grid">
                <?php foreach ($content_suggestions as $suggestion): ?>
                <div class="ai-cw-suggestion-card">
                    <h3><?php echo esc_html($suggestion['topic']); ?></h3>
                    <p><?php echo esc_html($suggestion['description']); ?></p>
                    <div class="ai-cw-suggestion-meta">
                        <span class="ai-cw-priority ai-cw-priority-<?php echo $suggestion['priority']; ?>">
                            <?php echo ucfirst($suggestion['priority']); ?>
                        </span>
                        <span class="ai-cw-type"><?php echo esc_html($suggestion['type']); ?></span>
                    </div>
                    <button type="button" class="button button-small ai-cw-use-suggestion" 
                            data-topic="<?php echo esc_attr($suggestion['topic']); ?>">
                        <?php _e('Use This Topic', 'ai-content-writer'); ?>
                    </button>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Scheduled Content -->
        <?php if (!empty($scheduled_content)): ?>
        <div class="ai-cw-scheduled-content">
            <h2><?php _e('Scheduled Content', 'ai-content-writer'); ?></h2>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Topic', 'ai-content-writer'); ?></th>
                        <th><?php _e('Scheduled For', 'ai-content-writer'); ?></th>
                        <th><?php _e('Status', 'ai-content-writer'); ?></th>
                        <th><?php _e('Actions', 'ai-content-writer'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($scheduled_content as $scheduled): ?>
                    <tr>
                        <td><?php echo esc_html($scheduled['topic']); ?></td>
                        <td><?php echo date('M j, Y \a\t g:i A', strtotime($scheduled['scheduled_for'])); ?></td>
                        <td>
                            <span class="ai-cw-status ai-cw-status-<?php echo $scheduled['status']; ?>">
                                <?php echo ucfirst($scheduled['status']); ?>
                            </span>
                        </td>
                        <td>
                            <button type="button" class="button button-small ai-cw-cancel-scheduled" 
                                    data-topic="<?php echo esc_attr($scheduled['topic']); ?>">
                                <?php _e('Cancel', 'ai-content-writer'); ?>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
        
        <!-- Recent Generated Content -->
        <div class="ai-cw-recent-content">
            <h2><?php _e('Recent Generated Content', 'ai-content-writer'); ?></h2>
            
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
        
        <!-- Generation Progress -->
        <div class="ai-cw-generation-progress" style="display: none;">
            <h2><?php _e('Generating Content', 'ai-content-writer'); ?></h2>
            <div class="ai-cw-progress-bar">
                <div class="ai-cw-progress-fill"></div>
            </div>
            <p class="ai-cw-progress-text"><?php _e('Creating your content...', 'ai-content-writer'); ?></p>
        </div>
        
        <!-- Generation Results -->
        <div class="ai-cw-generation-results" style="display: none;">
            <h2><?php _e('Generated Content', 'ai-content-writer'); ?></h2>
            <div class="ai-cw-results-content"></div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Use suggestion
    $('.ai-cw-use-suggestion').on('click', function() {
        var topic = $(this).data('topic');
        $('#content-topic').val(topic);
    });
    
    // Generate content
    $('#ai-cw-content-form').on('submit', function(e) {
        e.preventDefault();
        generateContent(false);
    });
    
    // Preview content
    $('#ai-cw-preview-button').on('click', function() {
        generateContent(true);
    });
    
    // Cancel scheduled content
    $('.ai-cw-cancel-scheduled').on('click', function() {
        var topic = $(this).data('topic');
        var button = $(this);
        
        if (confirm('<?php _e('Are you sure you want to cancel this scheduled content?', 'ai-content-writer'); ?>')) {
            button.prop('disabled', true).text('<?php _e('Cancelling...', 'ai-content-writer'); ?>');
            
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
                        location.reload();
                    } else {
                        alert(response.data.message || ai_cw_ajax.strings.error);
                    }
                },
                error: function() {
                    alert(ai_cw_ajax.strings.error);
                },
                complete: function() {
                    button.prop('disabled', false).text('<?php _e('Cancel', 'ai-content-writer'); ?>');
                }
            });
        }
    });
    
    function generateContent(preview = false) {
        var formData = {
            action: 'ai_cw_generate_content',
            topic: $('#content-topic').val(),
            keyword: $('#content-keyword').val(),
            word_count: $('#content-word-count').val(),
            tone: $('#content-tone').val(),
            status: $('#content-status').val(),
            schedule_date: $('#content-schedule').val(),
            preview: preview,
            nonce: ai_cw_ajax.nonce
        };
        
        if (!formData.topic) {
            alert('<?php _e('Please enter a content topic.', 'ai-content-writer'); ?>');
            return;
        }
        
        $('.ai-cw-generation-progress').show();
        $('#ai-cw-generate-button').prop('disabled', true).text('<?php _e('Generating...', 'ai-content-writer'); ?>');
        
        $.ajax({
            url: ai_cw_ajax.ajax_url,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    if (preview) {
                        showPreview(response.data);
                    } else {
                        $('.ai-cw-generation-results .ai-cw-results-content').html(
                            '<div class="notice notice-success"><p>' + response.data.message + '</p></div>'
                        );
                        $('.ai-cw-generation-results').show();
                        location.reload();
                    }
                } else {
                    $('.ai-cw-generation-results .ai-cw-results-content').html(
                        '<div class="notice notice-error"><p>' + (response.data.message || ai_cw_ajax.strings.error) + '</p></div>'
                    );
                    $('.ai-cw-generation-results').show();
                }
            },
            error: function() {
                $('.ai-cw-generation-results .ai-cw-results-content').html(
                    '<div class="notice notice-error"><p>' + ai_cw_ajax.strings.error + '</p></div>'
                );
                $('.ai-cw-generation-results').show();
            },
            complete: function() {
                $('.ai-cw-generation-progress').hide();
                $('#ai-cw-generate-button').prop('disabled', false).text('<?php _e('Generate Content', 'ai-content-writer'); ?>');
            }
        });
    }
    
    function showPreview(data) {
        var previewHtml = '<div class="ai-cw-preview-content">';
        previewHtml += '<h3>' + data.title + '</h3>';
        previewHtml += '<div class="ai-cw-preview-meta">';
        previewHtml += '<p><strong><?php _e('SEO Score:', 'ai-content-writer'); ?></strong> ' + data.seo_score + '/10</p>';
        previewHtml += '<p><strong><?php _e('Word Count:', 'ai-content-writer'); ?></strong> ' + data.word_count + '</p>';
        previewHtml += '<p><strong><?php _e('Reading Time:', 'ai-content-writer'); ?></strong> ' + data.reading_time + ' <?php _e('minutes', 'ai-content-writer'); ?></p>';
        previewHtml += '</div>';
        previewHtml += '<div class="ai-cw-preview-text">' + data.content + '</div>';
        previewHtml += '<div class="ai-cw-preview-actions">';
        previewHtml += '<button type="button" class="button button-primary ai-cw-publish-preview"><?php _e('Publish This Content', 'ai-content-writer'); ?></button>';
        previewHtml += '<button type="button" class="button ai-cw-close-preview"><?php _e('Close Preview', 'ai-content-writer'); ?></button>';
        previewHtml += '</div>';
        previewHtml += '</div>';
        
        $('.ai-cw-generation-results .ai-cw-results-content').html(previewHtml);
        $('.ai-cw-generation-results').show();
        
        // Publish from preview
        $('.ai-cw-publish-preview').on('click', function() {
            var publishData = {
                action: 'ai_cw_publish_content',
                content_data: data,
                nonce: ai_cw_ajax.nonce
            };
            
            $.ajax({
                url: ai_cw_ajax.ajax_url,
                type: 'POST',
                data: publishData,
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
                }
            });
        });
        
        // Close preview
        $('.ai-cw-close-preview').on('click', function() {
            $('.ai-cw-generation-results').hide();
        });
    }
});
</script>
