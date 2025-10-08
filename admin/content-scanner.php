<?php
/**
 * Content Scanner Page
 */

if (!defined('ABSPATH')) {
    exit;
}

$content_scanner = new AI_CW_Content_Scanner();
$last_scan = $content_scanner->get_last_scan_results();
$last_scan_date = $content_scanner->get_last_scan_date();
?>

<div class="wrap">
    <h1><?php _e('Content Scanner', 'ai-content-writer'); ?></h1>
    
    <div class="ai-cw-content-scanner">
        <!-- Scanner Actions -->
        <div class="ai-cw-scanner-actions">
            <h2><?php _e('Content Analysis', 'ai-content-writer'); ?></h2>
            <p><?php _e('Scan your existing content for SEO gaps and opportunities.', 'ai-content-writer'); ?></p>
            
            <div class="ai-cw-action-buttons">
                <button type="button" class="button button-primary" id="ai-cw-scan-all-content">
                    <?php _e('Scan All Content', 'ai-content-writer'); ?>
                </button>
                
                <button type="button" class="button button-secondary" id="ai-cw-scan-seo-gaps">
                    <?php _e('Scan SEO Gaps Only', 'ai-content-writer'); ?>
                </button>
                
                <button type="button" class="button button-secondary" id="ai-cw-scan-opportunities">
                    <?php _e('Find Content Opportunities', 'ai-content-writer'); ?>
                </button>
            </div>
        </div>
        
        <!-- Last Scan Results -->
        <?php if (!empty($last_scan) && !empty($last_scan_date)): ?>
        <div class="ai-cw-last-scan">
            <h2><?php _e('Last Scan Results', 'ai-content-writer'); ?></h2>
            <p class="ai-cw-scan-date">
                <?php printf(__('Last scanned: %s', 'ai-content-writer'), date('F j, Y \a\t g:i A', strtotime($last_scan_date))); ?>
            </p>
            
            <!-- SEO Gaps -->
            <?php if (!empty($last_scan['seo_gaps'])): ?>
            <div class="ai-cw-scan-section">
                <h3><?php _e('SEO Gaps', 'ai-content-writer'); ?> 
                    <span class="ai-cw-count"><?php echo count($last_scan['seo_gaps']); ?></span>
                </h3>
                
                <div class="ai-cw-gaps-list">
                    <?php foreach (array_slice($last_scan['seo_gaps'], 0, 10) as $gap): ?>
                    <div class="ai-cw-gap-item">
                        <h4>
                            <a href="<?php echo esc_url($gap['post_url']); ?>" target="_blank">
                                <?php echo esc_html($gap['post_title']); ?>
                            </a>
                        </h4>
                        <div class="ai-cw-gap-issues">
                            <?php foreach ($gap['gaps'] as $issue): ?>
                            <span class="ai-cw-gap-issue ai-cw-severity-<?php echo $issue['severity']; ?>">
                                <?php echo esc_html($issue['description']); ?>
                            </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <?php if (count($last_scan['seo_gaps']) > 10): ?>
                    <p class="ai-cw-more-items">
                        <?php printf(__('... and %d more posts with SEO issues', 'ai-content-writer'), count($last_scan['seo_gaps']) - 10); ?>
                    </p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Missing Topics -->
            <?php if (!empty($last_scan['missing_topics'])): ?>
            <div class="ai-cw-scan-section">
                <h3><?php _e('Missing Topics', 'ai-content-writer'); ?> 
                    <span class="ai-cw-count"><?php echo count($last_scan['missing_topics']); ?></span>
                </h3>
                
                <div class="ai-cw-topics-list">
                    <?php foreach ($last_scan['missing_topics'] as $topic): ?>
                    <div class="ai-cw-topic-item">
                        <h4><?php echo esc_html($topic['topic']); ?></h4>
                        <p><?php echo esc_html($topic['description']); ?></p>
                        <div class="ai-cw-topic-meta">
                            <span class="ai-cw-priority ai-cw-priority-<?php echo $topic['priority']; ?>">
                                <?php echo ucfirst($topic['priority']); ?>
                            </span>
                            <?php if (!empty($topic['suggested_keywords'])): ?>
                            <div class="ai-cw-suggested-keywords">
                                <strong><?php _e('Suggested Keywords:', 'ai-content-writer'); ?></strong>
                                <?php echo implode(', ', $topic['suggested_keywords']); ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Content Opportunities -->
            <?php if (!empty($last_scan['content_opportunities'])): ?>
            <div class="ai-cw-scan-section">
                <h3><?php _e('Content Opportunities', 'ai-content-writer'); ?> 
                    <span class="ai-cw-count"><?php echo count($last_scan['content_opportunities']); ?></span>
                </h3>
                
                <div class="ai-cw-opportunities-list">
                    <?php foreach ($last_scan['content_opportunities'] as $opportunity): ?>
                    <div class="ai-cw-opportunity-item">
                        <h4><?php echo esc_html($opportunity['type']); ?></h4>
                        <p><?php echo esc_html($opportunity['description']); ?></p>
                        <div class="ai-cw-opportunity-meta">
                            <span class="ai-cw-priority ai-cw-priority-<?php echo $opportunity['priority']; ?>">
                                <?php echo ucfirst($opportunity['priority']); ?>
                            </span>
                            <?php if (isset($opportunity['post_id'])): ?>
                            <a href="<?php echo get_edit_post_link($opportunity['post_id']); ?>" class="button button-small">
                                <?php _e('Edit Post', 'ai-content-writer'); ?>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Internal Linking -->
            <?php if (!empty($last_scan['internal_linking'])): ?>
            <div class="ai-cw-scan-section">
                <h3><?php _e('Internal Linking Analysis', 'ai-content-writer'); ?></h3>
                
                <?php if (!empty($last_scan['internal_linking']['posts_without_internal_links'])): ?>
                <div class="ai-cw-linking-issue">
                    <h4><?php _e('Posts Without Internal Links', 'ai-content-writer'); ?></h4>
                    <p><?php printf(__('%d posts have no internal links', 'ai-content-writer'), count($last_scan['internal_linking']['posts_without_internal_links'])); ?></p>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($last_scan['internal_linking']['orphaned_posts'])): ?>
                <div class="ai-cw-linking-issue">
                    <h4><?php _e('Orphaned Posts', 'ai-content-writer'); ?></h4>
                    <p><?php printf(__('%d posts have no incoming links', 'ai-content-writer'), count($last_scan['internal_linking']['orphaned_posts'])); ?></p>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <!-- Keyword Opportunities -->
            <?php if (!empty($last_scan['keyword_opportunities'])): ?>
            <div class="ai-cw-scan-section">
                <h3><?php _e('Keyword Opportunities', 'ai-content-writer'); ?> 
                    <span class="ai-cw-count"><?php echo count($last_scan['keyword_opportunities']); ?></span>
                </h3>
                
                <div class="ai-cw-keyword-opportunities">
                    <?php foreach ($last_scan['keyword_opportunities'] as $opportunity): ?>
                    <div class="ai-cw-keyword-opportunity">
                        <h4><?php echo esc_html($opportunity['category']); ?></h4>
                        <p><?php printf(__('Only %d posts in this category. Suggested: %d posts', 'ai-content-writer'), $opportunity['post_count'], $opportunity['suggested_posts']); ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="ai-cw-no-scan">
            <h2><?php _e('No Scan Results', 'ai-content-writer'); ?></h2>
            <p><?php _e('Run your first content scan to identify SEO gaps and opportunities.', 'ai-content-writer'); ?></p>
        </div>
        <?php endif; ?>
        
        <!-- Scan Progress -->
        <div class="ai-cw-scan-progress" style="display: none;">
            <h2><?php _e('Scanning Content', 'ai-content-writer'); ?></h2>
            <div class="ai-cw-progress-bar">
                <div class="ai-cw-progress-fill"></div>
            </div>
            <p class="ai-cw-progress-text"><?php _e('Analyzing content...', 'ai-content-writer'); ?></p>
        </div>
        
        <!-- Scan Results -->
        <div class="ai-cw-scan-results" style="display: none;">
            <h2><?php _e('Scan Results', 'ai-content-writer'); ?></h2>
            <div class="ai-cw-results-content"></div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Scan all content
    $('#ai-cw-scan-all-content').on('click', function() {
        runScan('ai_cw_scan_content');
    });
    
    // Scan SEO gaps only
    $('#ai-cw-scan-seo-gaps').on('click', function() {
        runScan('ai_cw_scan_content', {type: 'seo_gaps'});
    });
    
    // Find content opportunities
    $('#ai-cw-scan-opportunities').on('click', function() {
        runScan('ai_cw_scan_content', {type: 'opportunities'});
    });
    
    function runScan(action, data = {}) {
        $('.ai-cw-scan-progress').show();
        
        var scanData = {
            action: action,
            nonce: ai_cw_ajax.nonce,
            ...data
        };
        
        $.ajax({
            url: ai_cw_ajax.ajax_url,
            type: 'POST',
            data: scanData,
            success: function(response) {
                if (response.success) {
                    $('.ai-cw-scan-results .ai-cw-results-content').html(
                        '<div class="notice notice-success"><p>' + response.data.message + '</p></div>'
                    );
                    $('.ai-cw-scan-results').show();
                    location.reload();
                } else {
                    $('.ai-cw-scan-results .ai-cw-results-content').html(
                        '<div class="notice notice-error"><p>' + (response.data.message || ai_cw_ajax.strings.error) + '</p></div>'
                    );
                    $('.ai-cw-scan-results').show();
                }
            },
            error: function() {
                $('.ai-cw-scan-results .ai-cw-results-content').html(
                    '<div class="notice notice-error"><p>' + ai_cw_ajax.strings.error + '</p></div>'
                );
                $('.ai-cw-scan-results').show();
            },
            complete: function() {
                $('.ai-cw-scan-progress').hide();
            }
        });
    }
});
</script>
