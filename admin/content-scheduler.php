<?php
/**
 * Content Scheduler Page
 */

if (!defined('ABSPATH')) {
    exit;
}

$content_scheduler = new AI_CW_Content_Scheduler();
$scheduled_content = $content_scheduler->get_scheduled_content('scheduled');
$scheduling_stats = $content_scheduler->get_scheduling_stats();
$scheduling_suggestions = $content_scheduler->get_scheduling_suggestions();
?>

<div class="wrap">
    <h1><?php _e('Content Scheduler', 'ai-content-writer'); ?></h1>
    
    <div class="ai-cw-content-scheduler">
        <!-- Scheduling Statistics -->
        <div class="ai-cw-scheduler-stats">
            <h2><?php _e('Scheduling Statistics', 'ai-content-writer'); ?></h2>
            <div class="ai-cw-stats-grid">
                <div class="ai-cw-stat-card">
                    <h3><?php _e('Total Scheduled', 'ai-content-writer'); ?></h3>
                    <div class="stat-number"><?php echo $scheduling_stats['total_scheduled']; ?></div>
                    <p><?php _e('Content pieces scheduled', 'ai-content-writer'); ?></p>
                </div>
                
                <div class="ai-cw-stat-card">
                    <h3><?php _e('Completed This Month', 'ai-content-writer'); ?></h3>
                    <div class="stat-number"><?php echo $scheduling_stats['completed_this_month']; ?></div>
                    <p><?php _e('Content published this month', 'ai-content-writer'); ?></p>
                </div>
                
                <div class="ai-cw-stat-card">
                    <h3><?php _e('Upcoming This Week', 'ai-content-writer'); ?></h3>
                    <div class="stat-number"><?php echo $scheduling_stats['upcoming_this_week']; ?></div>
                    <p><?php _e('Content scheduled this week', 'ai-content-writer'); ?></p>
                </div>
            </div>
        </div>
        
        <!-- Schedule New Content -->
        <div class="ai-cw-schedule-form">
            <h2><?php _e('Schedule New Content', 'ai-content-writer'); ?></h2>
            
            <form id="ai-cw-schedule-form">
                <div class="ai-cw-form-row">
                    <div class="ai-cw-form-group">
                        <label for="schedule-topic"><?php _e('Content Topic', 'ai-content-writer'); ?> *</label>
                        <input type="text" id="schedule-topic" name="topic" required 
                               placeholder="<?php _e('Enter the topic for your content', 'ai-content-writer'); ?>">
                    </div>
                    
                    <div class="ai-cw-form-group">
                        <label for="schedule-keyword"><?php _e('Focus Keyword', 'ai-content-writer'); ?></label>
                        <input type="text" id="schedule-keyword" name="keyword" 
                               placeholder="<?php _e('Optional: Enter focus keyword', 'ai-content-writer'); ?>">
                    </div>
                </div>
                
                <div class="ai-cw-form-row">
                    <div class="ai-cw-form-group">
                        <label for="schedule-word-count"><?php _e('Word Count', 'ai-content-writer'); ?></label>
                        <select id="schedule-word-count" name="word_count">
                            <option value="500"><?php _e('500 words', 'ai-content-writer'); ?></option>
                            <option value="1000" selected><?php _e('1000 words', 'ai-content-writer'); ?></option>
                            <option value="1500"><?php _e('1500 words', 'ai-content-writer'); ?></option>
                            <option value="2000"><?php _e('2000 words', 'ai-content-writer'); ?></option>
                        </select>
                    </div>
                    
                    <div class="ai-cw-form-group">
                        <label for="schedule-tone"><?php _e('Content Tone', 'ai-content-writer'); ?></label>
                        <select id="schedule-tone" name="tone">
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
                        <label for="schedule-date"><?php _e('Schedule Date', 'ai-content-writer'); ?> *</label>
                        <input type="datetime-local" id="schedule-date" name="scheduled_for" required>
                    </div>
                    
                    <div class="ai-cw-form-group">
                        <label for="schedule-category"><?php _e('Category', 'ai-content-writer'); ?></label>
                        <select id="schedule-category" name="category">
                            <option value=""><?php _e('Select Category', 'ai-content-writer'); ?></option>
                            <?php
                            $categories = get_categories();
                            foreach ($categories as $category) {
                                echo '<option value="' . $category->term_id . '">' . $category->name . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
                
                <div class="ai-cw-form-group">
                    <label for="schedule-tags"><?php _e('Tags', 'ai-content-writer'); ?></label>
                    <input type="text" id="schedule-tags" name="tags" 
                           placeholder="<?php _e('Enter tags separated by commas', 'ai-content-writer'); ?>">
                </div>
                
                <div class="ai-cw-form-actions">
                    <button type="submit" class="button button-primary">
                        <?php _e('Schedule Content', 'ai-content-writer'); ?>
                    </button>
                    <button type="button" class="button button-secondary" id="ai-cw-bulk-schedule">
                        <?php _e('Bulk Schedule', 'ai-content-writer'); ?>
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Content Calendar -->
        <div class="ai-cw-content-calendar">
            <h2><?php _e('Content Calendar', 'ai-content-writer'); ?></h2>
            
            <div class="ai-cw-calendar-navigation">
                <button type="button" class="button" id="ai-cw-prev-month">
                    <?php _e('Previous Month', 'ai-content-writer'); ?>
                </button>
                <h3 id="ai-cw-current-month"><?php echo date('F Y'); ?></h3>
                <button type="button" class="button" id="ai-cw-next-month">
                    <?php _e('Next Month', 'ai-content-writer'); ?>
                </button>
            </div>
            
            <div class="ai-cw-calendar-grid" id="ai-cw-calendar-grid">
                <!-- Calendar will be populated by JavaScript -->
            </div>
        </div>
        
        <!-- Scheduled Content -->
        <div class="ai-cw-scheduled-content">
            <h2><?php _e('Scheduled Content', 'ai-content-writer'); ?></h2>
            
            <?php if (!empty($scheduled_content)): ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Topic', 'ai-content-writer'); ?></th>
                        <th><?php _e('Scheduled For', 'ai-content-writer'); ?></th>
                        <th><?php _e('Status', 'ai-content-writer'); ?></th>
                        <th><?php _e('Created', 'ai-content-writer'); ?></th>
                        <th><?php _e('Actions', 'ai-content-writer'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($scheduled_content as $scheduled): ?>
                    <tr>
                        <td>
                            <strong><?php echo esc_html($scheduled['topic']); ?></strong>
                        </td>
                        <td>
                            <?php echo date('M j, Y \a\t g:i A', strtotime($scheduled['scheduled_for'])); ?>
                        </td>
                        <td>
                            <span class="ai-cw-status ai-cw-status-<?php echo $scheduled['status']; ?>">
                                <?php echo ucfirst($scheduled['status']); ?>
                            </span>
                        </td>
                        <td>
                            <?php echo date('M j, Y', strtotime($scheduled['created_at'])); ?>
                        </td>
                        <td>
                            <button type="button" class="button button-small ai-cw-cancel-scheduled" 
                                    data-topic="<?php echo esc_attr($scheduled['topic']); ?>">
                                <?php _e('Cancel', 'ai-content-writer'); ?>
                            </button>
                            <button type="button" class="button button-small ai-cw-edit-scheduled" 
                                    data-scheduled-id="<?php echo $scheduled['id']; ?>">
                                <?php _e('Edit', 'ai-content-writer'); ?>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p><?php _e('No content scheduled yet.', 'ai-content-writer'); ?></p>
            <?php endif; ?>
        </div>
        
        <!-- Scheduling Suggestions -->
        <?php if (!empty($scheduling_suggestions)): ?>
        <div class="ai-cw-scheduling-suggestions">
            <h2><?php _e('Content Suggestions', 'ai-content-writer'); ?></h2>
            <p><?php _e('Based on seasonal trends and your content patterns.', 'ai-content-writer'); ?></p>
            
            <div class="ai-cw-suggestions-grid">
                <?php foreach ($scheduling_suggestions as $suggestion): ?>
                <div class="ai-cw-suggestion-card">
                    <h3><?php echo esc_html($suggestion['topic']); ?></h3>
                    <p><?php echo esc_html($suggestion['description']); ?></p>
                    <div class="ai-cw-suggestion-meta">
                        <span class="ai-cw-priority ai-cw-priority-<?php echo $suggestion['priority']; ?>">
                            <?php echo ucfirst($suggestion['priority']); ?>
                        </span>
                        <span class="ai-cw-type"><?php echo esc_html($suggestion['type']); ?></span>
                        <?php if (isset($suggestion['suggested_date'])): ?>
                        <span class="ai-cw-suggested-date">
                            <?php echo date('M j, Y', strtotime($suggestion['suggested_date'])); ?>
                        </span>
                        <?php endif; ?>
                    </div>
                    <button type="button" class="button button-small ai-cw-use-suggestion" 
                            data-topic="<?php echo esc_attr($suggestion['topic']); ?>"
                            data-suggested-date="<?php echo esc_attr($suggestion['suggested_date'] ?? ''); ?>">
                        <?php _e('Use This Topic', 'ai-content-writer'); ?>
                    </button>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Bulk Schedule Modal -->
        <div id="ai-cw-bulk-schedule-modal" class="ai-cw-modal" style="display: none;">
            <div class="ai-cw-modal-content">
                <span class="ai-cw-close">&times;</span>
                <h2><?php _e('Bulk Schedule Content', 'ai-content-writer'); ?></h2>
                <form id="ai-cw-bulk-schedule-form">
                    <div class="ai-cw-form-group">
                        <label for="bulk-topics"><?php _e('Content Topics', 'ai-content-writer'); ?></label>
                        <textarea id="bulk-topics" name="topics" rows="10" 
                                  placeholder="<?php _e('Enter one topic per line', 'ai-content-writer'); ?>"></textarea>
                    </div>
                    
                    <div class="ai-cw-form-row">
                        <div class="ai-cw-form-group">
                            <label for="bulk-start-date"><?php _e('Start Date', 'ai-content-writer'); ?></label>
                            <input type="date" id="bulk-start-date" name="start_date" required>
                        </div>
                        
                        <div class="ai-cw-form-group">
                            <label for="bulk-frequency"><?php _e('Frequency', 'ai-content-writer'); ?></label>
                            <select id="bulk-frequency" name="frequency">
                                <option value="daily"><?php _e('Daily', 'ai-content-writer'); ?></option>
                                <option value="weekly"><?php _e('Weekly', 'ai-content-writer'); ?></option>
                                <option value="bi-weekly"><?php _e('Bi-weekly', 'ai-content-writer'); ?></option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="ai-cw-form-actions">
                        <button type="submit" class="button button-primary">
                            <?php _e('Schedule All', 'ai-content-writer'); ?>
                        </button>
                        <button type="button" class="button ai-cw-cancel">
                            <?php _e('Cancel', 'ai-content-writer'); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Schedule content form
    $('#ai-cw-schedule-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = {
            action: 'ai_cw_schedule_content',
            topic: $('#schedule-topic').val(),
            keyword: $('#schedule-keyword').val(),
            word_count: $('#schedule-word-count').val(),
            tone: $('#schedule-tone').val(),
            scheduled_for: $('#schedule-date').val(),
            category: $('#schedule-category').val(),
            tags: $('#schedule-tags').val(),
            nonce: ai_cw_ajax.nonce
        };
        
        if (!formData.topic || !formData.scheduled_for) {
            alert('<?php _e('Please fill in all required fields.', 'ai-content-writer'); ?>');
            return;
        }
        
        $.ajax({
            url: ai_cw_ajax.ajax_url,
            type: 'POST',
            data: formData,
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
    
    // Use suggestion
    $('.ai-cw-use-suggestion').on('click', function() {
        var topic = $(this).data('topic');
        var suggestedDate = $(this).data('suggested-date');
        
        $('#schedule-topic').val(topic);
        if (suggestedDate) {
            $('#schedule-date').val(suggestedDate);
        }
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
    
    // Bulk schedule
    $('#ai-cw-bulk-schedule').on('click', function() {
        $('#ai-cw-bulk-schedule-modal').show();
    });
    
    // Bulk schedule form
    $('#ai-cw-bulk-schedule-form').on('submit', function(e) {
        e.preventDefault();
        
        var topics = $('#bulk-topics').val().split('\n').filter(function(topic) {
            return topic.trim() !== '';
        });
        
        if (topics.length === 0) {
            alert('<?php _e('Please enter at least one topic.', 'ai-content-writer'); ?>');
            return;
        }
        
        var formData = {
            action: 'ai_cw_bulk_schedule',
            topics: topics,
            start_date: $('#bulk-start-date').val(),
            frequency: $('#bulk-frequency').val(),
            nonce: ai_cw_ajax.nonce
        };
        
        $.ajax({
            url: ai_cw_ajax.ajax_url,
            type: 'POST',
            data: formData,
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
    
    // Calendar navigation
    $('#ai-cw-prev-month, #ai-cw-next-month').on('click', function() {
        // Calendar navigation logic would go here
        console.log('Calendar navigation clicked');
    });
    
    // Set default date to tomorrow
    var tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    tomorrow.setHours(9, 0, 0, 0);
    $('#schedule-date').val(tomorrow.toISOString().slice(0, 16));
    
    // Set default bulk start date to tomorrow
    var tomorrowDate = tomorrow.toISOString().slice(0, 10);
    $('#bulk-start-date').val(tomorrowDate);
});
</script>

