<?php
/**
 * Scheduler Class
 * 
 * Handles content scheduling and automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class AI_CW_Scheduler {
    
    private $content_generator;
    
    public function __construct() {
        $this->content_generator = new AI_CW_Content_Generator();
    }
    
    /**
     * Schedule cron jobs
     */
    public function schedule_cron_jobs() {
        // Schedule daily content generation
        if (!wp_next_scheduled('ai_cw_daily_content_generation')) {
            wp_schedule_event(time(), 'daily', 'ai_cw_daily_content_generation');
        }
        
        // Schedule weekly content scan
        if (!wp_next_scheduled('ai_cw_weekly_content_scan')) {
            wp_schedule_event(time(), 'weekly', 'ai_cw_weekly_content_scan');
        }
        
        // Schedule monthly brand analysis update
        if (!wp_next_scheduled('ai_cw_monthly_brand_update')) {
            wp_schedule_event(time(), 'monthly', 'ai_cw_monthly_brand_update');
        }
        
        // Add action hooks
        add_action('ai_cw_daily_content_generation', array($this, 'daily_content_generation'));
        add_action('ai_cw_weekly_content_scan', array($this, 'weekly_content_scan'));
        add_action('ai_cw_monthly_brand_update', array($this, 'monthly_brand_update'));
    }
    
    /**
     * Clear cron jobs
     */
    public function clear_cron_jobs() {
        wp_clear_scheduled_hook('ai_cw_daily_content_generation');
        wp_clear_scheduled_hook('ai_cw_weekly_content_scan');
        wp_clear_scheduled_hook('ai_cw_monthly_brand_update');
    }
    
    /**
     * Daily content generation
     */
    public function daily_content_generation() {
        $auto_publish = get_option('ai_cw_auto_publish', false);
        
        if (!$auto_publish) {
            return;
        }
        
        $content_frequency = get_option('ai_cw_content_frequency', 'weekly');
        
        // Check if it's time to generate content
        if (!$this->should_generate_content($content_frequency)) {
            return;
        }
        
        // Get content suggestions
        $suggestions = $this->content_generator->generate_content_suggestions();
        
        if (empty($suggestions)) {
            return;
        }
        
        // Select highest priority suggestion
        $selected_suggestion = $this->select_best_suggestion($suggestions);
        
        if (!$selected_suggestion) {
            return;
        }
        
        // Generate content
        $content_result = $this->content_generator->generate_content($selected_suggestion['topic']);
        
        if ($content_result['success']) {
            // Publish content
            $publish_result = $this->content_generator->publish_content($content_result['data']);
            
            if ($publish_result['success']) {
                // Log successful generation
                $this->log_automated_generation($publish_result['post_id'], $selected_suggestion);
            }
        }
    }
    
    /**
     * Weekly content scan
     */
    public function weekly_content_scan() {
        $content_scanner = new AI_CW_Content_Scanner();
        $scan_result = $content_scanner->scan_all_content();
        
        if ($scan_result['success']) {
            // Log scan results
            $this->log_scan_results($scan_result['data']);
        }
    }
    
    /**
     * Monthly brand update
     */
    public function monthly_brand_update() {
        $brand_analyzer = new AI_CW_Brand_Analyzer();
        
        // Get recent content for analysis
        $recent_posts = get_posts(array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'numberposts' => 10,
            'date_query' => array(
                'after' => '1 month ago'
            )
        ));
        
        if (!empty($recent_posts)) {
            $brand_analyzer->update_brand_profile($recent_posts);
        }
    }
    
    /**
     * Check if content should be generated
     */
    private function should_generate_content($frequency) {
        $last_generation = get_option('ai_cw_last_automated_generation', 0);
        $current_time = time();
        
        switch ($frequency) {
            case 'daily':
                return ($current_time - $last_generation) >= DAY_IN_SECONDS;
            case 'weekly':
                return ($current_time - $last_generation) >= WEEK_IN_SECONDS;
            case 'monthly':
                return ($current_time - $last_generation) >= MONTH_IN_SECONDS;
            default:
                return false;
        }
    }
    
    /**
     * Select best content suggestion
     */
    private function select_best_suggestion($suggestions) {
        // Sort by priority
        $priority_order = array('high' => 3, 'medium' => 2, 'low' => 1);
        
        usort($suggestions, function($a, $b) use ($priority_order) {
            $a_priority = $priority_order[$a['priority']] ?? 0;
            $b_priority = $priority_order[$b['priority']] ?? 0;
            
            if ($a_priority === $b_priority) {
                return 0;
            }
            
            return $a_priority > $b_priority ? -1 : 1;
        });
        
        return !empty($suggestions) ? $suggestions[0] : null;
    }
    
    /**
     * Log automated generation
     */
    private function log_automated_generation($post_id, $suggestion) {
        $log_entry = array(
            'post_id' => $post_id,
            'suggestion' => $suggestion,
            'generated_at' => current_time('mysql'),
            'type' => 'automated'
        );
        
        $logs = get_option('ai_cw_automated_logs', array());
        $logs[] = $log_entry;
        
        // Keep only last 50 logs
        if (count($logs) > 50) {
            $logs = array_slice($logs, -50);
        }
        
        update_option('ai_cw_automated_logs', $logs);
        update_option('ai_cw_last_automated_generation', time());
    }
    
    /**
     * Log scan results
     */
    private function log_scan_results($scan_data) {
        $log_entry = array(
            'scan_data' => $scan_data,
            'scanned_at' => current_time('mysql'),
            'type' => 'content_scan'
        );
        
        $logs = get_option('ai_cw_scan_logs', array());
        $logs[] = $log_entry;
        
        // Keep only last 20 logs
        if (count($logs) > 20) {
            $logs = array_slice($logs, -20);
        }
        
        update_option('ai_cw_scan_logs', $logs);
    }
    
    /**
     * Get automated generation logs
     */
    public function get_automated_logs($limit = 20) {
        $logs = get_option('ai_cw_automated_logs', array());
        return array_slice(array_reverse($logs), 0, $limit);
    }
    
    /**
     * Get scan logs
     */
    public function get_scan_logs($limit = 10) {
        $logs = get_option('ai_cw_scan_logs', array());
        return array_slice(array_reverse($logs), 0, $limit);
    }
    
    /**
     * Schedule specific content generation
     */
    public function schedule_content_generation($topic, $publish_date, $options = array()) {
        $scheduled_content = array(
            'topic' => $topic,
            'publish_date' => $publish_date,
            'options' => $options,
            'created_at' => current_time('mysql'),
            'status' => 'scheduled'
        );
        
        $scheduled = get_option('ai_cw_scheduled_content', array());
        $scheduled[] = $scheduled_content;
        
        update_option('ai_cw_scheduled_content', $scheduled);
        
        // Schedule the actual generation
        wp_schedule_single_event(strtotime($publish_date), 'ai_cw_scheduled_generation', array($topic, $options));
        
        return array(
            'success' => true,
            'message' => __('Content scheduled successfully', 'ai-content-writer')
        );
    }
    
    /**
     * Process scheduled content
     */
    public function process_scheduled_content($topic, $options = array()) {
        $content_result = $this->content_generator->generate_content($topic, $options);
        
        if ($content_result['success']) {
            $publish_result = $this->content_generator->publish_content($content_result['data']);
            
            if ($publish_result['success']) {
                // Remove from scheduled list
                $this->remove_scheduled_content($topic);
                
                return array(
                    'success' => true,
                    'message' => __('Scheduled content published successfully', 'ai-content-writer'),
                    'post_id' => $publish_result['post_id']
                );
            }
        }
        
        return $content_result;
    }
    
    /**
     * Remove scheduled content
     */
    private function remove_scheduled_content($topic) {
        $scheduled = get_option('ai_cw_scheduled_content', array());
        
        foreach ($scheduled as $key => $content) {
            if ($content['topic'] === $topic) {
                unset($scheduled[$key]);
                break;
            }
        }
        
        update_option('ai_cw_scheduled_content', array_values($scheduled));
    }
    
    /**
     * Get scheduled content
     */
    public function get_scheduled_content() {
        return get_option('ai_cw_scheduled_content', array());
    }
    
    /**
     * Cancel scheduled content
     */
    public function cancel_scheduled_content($topic) {
        $this->remove_scheduled_content($topic);
        
        // Cancel the scheduled event
        wp_clear_scheduled_hook('ai_cw_scheduled_generation', array($topic, array()));
        
        return array(
            'success' => true,
            'message' => __('Scheduled content cancelled', 'ai-content-writer')
        );
    }
}
