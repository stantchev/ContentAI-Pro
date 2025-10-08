<?php
/**
 * Content Scheduler Class
 * 
 * Advanced content scheduling and planning system
 */

if (!defined('ABSPATH')) {
    exit;
}

class AI_CW_Content_Scheduler {
    
    private $content_generator;
    private $database;
    
    public function __construct() {
        $this->content_generator = new AI_CW_Content_Generator();
        $this->database = new AI_CW_Database();
    }
    
    /**
     * Schedule content for specific date and time
     */
    public function schedule_content($content_data) {
        try {
            $scheduled_data = array(
                'topic' => sanitize_text_field($content_data['topic']),
                'keyword' => sanitize_text_field($content_data['keyword']),
                'word_count' => intval($content_data['word_count']),
                'tone' => sanitize_text_field($content_data['tone']),
                'category' => intval($content_data['category']),
                'tags' => sanitize_text_field($content_data['tags']),
                'scheduled_for' => sanitize_text_field($content_data['scheduled_for']),
                'status' => 'scheduled',
                'created_at' => current_time('mysql'),
                'created_by' => get_current_user_id(),
                'options' => json_encode($content_data['options'] ?? array())
            );
            
            // Save to database
            $result = $this->database->schedule_content(
                $scheduled_data['topic'],
                $scheduled_data['scheduled_for'],
                $scheduled_data['options']
            );
            
            if ($result) {
                // Schedule WordPress cron job
                $this->schedule_cron_job($scheduled_data);
                
                return array(
                    'success' => true,
                    'message' => __('Content scheduled successfully', 'ai-content-writer'),
                    'scheduled_id' => $result
                );
            } else {
                return array(
                    'success' => false,
                    'message' => __('Failed to schedule content', 'ai-content-writer')
                );
            }
            
        } catch (Exception $e) {
            return array(
                'success' => false,
                'message' => sprintf(__('Error scheduling content: %s', 'ai-content-writer'), $e->getMessage())
            );
        }
    }
    
    /**
     * Schedule WordPress cron job
     */
    private function schedule_cron_job($scheduled_data) {
        $timestamp = strtotime($scheduled_data['scheduled_for']);
        
        wp_schedule_single_event($timestamp, 'ai_cw_scheduled_content_generation', array(
            'topic' => $scheduled_data['topic'],
            'keyword' => $scheduled_data['keyword'],
            'word_count' => $scheduled_data['word_count'],
            'tone' => $scheduled_data['tone'],
            'category' => $scheduled_data['category'],
            'tags' => $scheduled_data['tags'],
            'options' => $scheduled_data['options']
        ));
    }
    
    /**
     * Process scheduled content
     */
    public function process_scheduled_content($content_data) {
        try {
            // Generate content
            $generation_result = $this->content_generator->generate_content(
                $content_data['topic'],
                array(
                    'word_count' => $content_data['word_count'],
                    'tone' => $content_data['tone']
                )
            );
            
            if (!$generation_result['success']) {
                return $generation_result;
            }
            
            // Prepare content for publishing
            $publish_data = $generation_result['data'];
            $publish_data['status'] = 'publish';
            
            if (!empty($content_data['category'])) {
                $publish_data['categories'] = array($content_data['category']);
            }
            
            if (!empty($content_data['tags'])) {
                $publish_data['tags'] = explode(',', $content_data['tags']);
            }
            
            // Publish content
            $publish_result = $this->content_generator->publish_content($publish_data);
            
            if ($publish_result['success']) {
                // Update scheduled content status
                $this->update_scheduled_status($content_data['topic'], 'completed');
                
                return array(
                    'success' => true,
                    'message' => __('Scheduled content published successfully', 'ai-content-writer'),
                    'post_id' => $publish_result['post_id']
                );
            } else {
                return $publish_result;
            }
            
        } catch (Exception $e) {
            return array(
                'success' => false,
                'message' => sprintf(__('Error processing scheduled content: %s', 'ai-content-writer'), $e->getMessage())
            );
        }
    }
    
    /**
     * Get scheduled content
     */
    public function get_scheduled_content($status = 'scheduled', $limit = 20) {
        return $this->database->get_scheduled_content($status, $limit);
    }
    
    /**
     * Cancel scheduled content
     */
    public function cancel_scheduled_content($topic) {
        try {
            // Remove from database
            $this->database->update_scheduled_content_status($topic, 'cancelled');
            
            // Clear WordPress cron job
            wp_clear_scheduled_hook('ai_cw_scheduled_content_generation', array(
                'topic' => $topic
            ));
            
            return array(
                'success' => true,
                'message' => __('Scheduled content cancelled', 'ai-content-writer')
            );
            
        } catch (Exception $e) {
            return array(
                'success' => false,
                'message' => sprintf(__('Error cancelling scheduled content: %s', 'ai-content-writer'), $e->getMessage())
            );
        }
    }
    
    /**
     * Update scheduled content status
     */
    private function update_scheduled_status($topic, $status) {
        $this->database->update_scheduled_content_status($topic, $status);
    }
    
    /**
     * Get content calendar
     */
    public function get_content_calendar($month = null, $year = null) {
        if (!$month) $month = date('n');
        if (!$year) $year = date('Y');
        
        $scheduled_content = $this->get_scheduled_content('scheduled');
        $calendar = array();
        
        foreach ($scheduled_content as $content) {
            $scheduled_date = strtotime($content['scheduled_for']);
            $content_month = date('n', $scheduled_date);
            $content_year = date('Y', $scheduled_date);
            
            if ($content_month == $month && $content_year == $year) {
                $day = date('j', $scheduled_date);
                if (!isset($calendar[$day])) {
                    $calendar[$day] = array();
                }
                $calendar[$day][] = $content;
            }
        }
        
        return $calendar;
    }
    
    /**
     * Get content suggestions for scheduling
     */
    public function get_scheduling_suggestions() {
        $suggestions = array();
        
        // Get seasonal suggestions
        $seasonal = $this->get_seasonal_suggestions();
        $suggestions = array_merge($suggestions, $seasonal);
        
        // Get trending topics
        $trending = $this->get_trending_suggestions();
        $suggestions = array_merge($suggestions, $trending);
        
        // Get product-related suggestions (for e-commerce)
        if (class_exists('WooCommerce')) {
            $product_suggestions = $this->get_product_suggestions();
            $suggestions = array_merge($suggestions, $product_suggestions);
        }
        
        return $suggestions;
    }
    
    /**
     * Get seasonal content suggestions
     */
    private function get_seasonal_suggestions() {
        $current_month = date('n');
        $suggestions = array();
        
        $seasonal_topics = array(
            1 => array(
                'New Year Marketing Strategies',
                'New Year Resolutions for Business',
                'Planning Your Year Ahead'
            ),
            2 => array(
                'Valentine\'s Day Marketing',
                'Love and Business',
                'Romantic Business Ideas'
            ),
            3 => array(
                'Spring Marketing Trends',
                'Spring Cleaning for Business',
                'Fresh Start Marketing'
            ),
            4 => array(
                'Easter Marketing Ideas',
                'Spring Business Growth',
                'Renewal and Growth'
            ),
            5 => array(
                'Mother\'s Day Marketing',
                'Women in Business',
                'Family Business Tips'
            ),
            6 => array(
                'Father\'s Day Marketing',
                'Summer Business Planning',
                'Mid-Year Review'
            ),
            7 => array(
                'Summer Marketing Strategies',
                'Vacation Business Tips',
                'Summer Sales Boost'
            ),
            8 => array(
                'Back to School Marketing',
                'Educational Content Ideas',
                'Learning and Development'
            ),
            9 => array(
                'Fall Marketing Trends',
                'Back to Business',
                'Autumn Growth Strategies'
            ),
            10 => array(
                'Halloween Marketing',
                'Spooky Business Tips',
                'Creative Marketing Ideas'
            ),
            11 => array(
                'Thanksgiving Marketing',
                'Gratitude in Business',
                'Thank You Campaigns'
            ),
            12 => array(
                'Christmas Marketing',
                'Holiday Business Tips',
                'Year-End Strategies'
            )
        );
        
        if (isset($seasonal_topics[$current_month])) {
            foreach ($seasonal_topics[$current_month] as $topic) {
                $suggestions[] = array(
                    'topic' => $topic,
                    'type' => 'seasonal',
                    'priority' => 'high',
                    'suggested_date' => $this->get_optimal_publish_date(),
                    'description' => sprintf(__('Seasonal content for %s', 'ai-content-writer'), date('F'))
                );
            }
        }
        
        return $suggestions;
    }
    
    /**
     * Get trending topics suggestions
     */
    private function get_trending_suggestions() {
        // In production, this would integrate with Google Trends API
        $trending_topics = array(
            'AI and Machine Learning',
            'Digital Marketing Trends',
            'Remote Work Strategies',
            'Sustainability in Business',
            'Customer Experience',
            'Data Privacy',
            'E-commerce Growth',
            'Social Media Marketing',
            'Content Marketing',
            'SEO Best Practices'
        );
        
        $suggestions = array();
        foreach ($trending_topics as $topic) {
            $suggestions[] = array(
                'topic' => $topic,
                'type' => 'trending',
                'priority' => 'medium',
                'suggested_date' => $this->get_optimal_publish_date(),
                'description' => __('Trending topic with high search volume', 'ai-content-writer')
            );
        }
        
        return $suggestions;
    }
    
    /**
     * Get product-related suggestions for e-commerce
     */
    private function get_product_suggestions() {
        if (!class_exists('WooCommerce')) {
            return array();
        }
        
        $suggestions = array();
        
        // Get popular products
        $popular_products = wc_get_products(array(
            'limit' => 10,
            'orderby' => 'popularity',
            'order' => 'DESC'
        ));
        
        foreach ($popular_products as $product) {
            $suggestions[] = array(
                'topic' => sprintf(__('Complete Guide to %s', 'ai-content-writer'), $product->get_name()),
                'type' => 'product_guide',
                'priority' => 'high',
                'product_id' => $product->get_id(),
                'suggested_date' => $this->get_optimal_publish_date(),
                'description' => sprintf(__('Comprehensive guide for %s', 'ai-content-writer'), $product->get_name())
            );
        }
        
        // Get product categories
        $categories = get_terms(array(
            'taxonomy' => 'product_cat',
            'hide_empty' => true,
            'number' => 10
        ));
        
        foreach ($categories as $category) {
            $suggestions[] = array(
                'topic' => sprintf(__('Best %s Products 2024', 'ai-content-writer'), $category->name),
                'type' => 'category_guide',
                'priority' => 'medium',
                'category_id' => $category->term_id,
                'suggested_date' => $this->get_optimal_publish_date(),
                'description' => sprintf(__('Product recommendations for %s', 'ai-content-writer'), $category->name)
            );
        }
        
        return $suggestions;
    }
    
    /**
     * Get optimal publish date
     */
    private function get_optimal_publish_date() {
        // Get current scheduled content to avoid conflicts
        $scheduled = $this->get_scheduled_content('scheduled');
        $scheduled_dates = array();
        
        foreach ($scheduled as $content) {
            $scheduled_dates[] = date('Y-m-d', strtotime($content['scheduled_for']));
        }
        
        // Find next available date
        $date = date('Y-m-d', strtotime('+1 day'));
        while (in_array($date, $scheduled_dates)) {
            $date = date('Y-m-d', strtotime($date . ' +1 day'));
        }
        
        // Add optimal time (9 AM)
        return $date . ' 09:00:00';
    }
    
    /**
     * Bulk schedule content
     */
    public function bulk_schedule_content($content_list) {
        $results = array();
        
        foreach ($content_list as $content_data) {
            $result = $this->schedule_content($content_data);
            $results[] = array(
                'topic' => $content_data['topic'],
                'result' => $result
            );
        }
        
        return array(
            'success' => true,
            'message' => __('Bulk scheduling completed', 'ai-content-writer'),
            'results' => $results
        );
    }
    
    /**
     * Get scheduling statistics
     */
    public function get_scheduling_stats() {
        $stats = array();
        
        // Total scheduled
        $scheduled = $this->get_scheduled_content('scheduled');
        $stats['total_scheduled'] = count($scheduled);
        
        // Completed this month
        $completed = $this->get_scheduled_content('completed');
        $this_month = date('Y-m');
        $completed_this_month = 0;
        
        foreach ($completed as $content) {
            if (strpos($content['scheduled_for'], $this_month) === 0) {
                $completed_this_month++;
            }
        }
        
        $stats['completed_this_month'] = $completed_this_month;
        
        // Upcoming this week
        $upcoming_week = 0;
        $week_start = date('Y-m-d', strtotime('monday this week'));
        $week_end = date('Y-m-d', strtotime('sunday this week'));
        
        foreach ($scheduled as $content) {
            $content_date = date('Y-m-d', strtotime($content['scheduled_for']));
            if ($content_date >= $week_start && $content_date <= $week_end) {
                $upcoming_week++;
            }
        }
        
        $stats['upcoming_this_week'] = $upcoming_week;
        
        return $stats;
    }
}

