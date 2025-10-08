<?php
/**
 * Content Scanner Class
 * 
 * Scans existing content for SEO gaps and opportunities
 */

if (!defined('ABSPATH')) {
    exit;
}

class AI_CW_Content_Scanner {
    
    private $brand_analyzer;
    
    public function __construct() {
        $this->brand_analyzer = new AI_CW_Brand_Analyzer();
    }
    
    /**
     * Scan all content for SEO gaps and opportunities
     */
    public function scan_all_content() {
        try {
            $scan_results = array(
                'seo_gaps' => $this->find_seo_gaps(),
                'missing_topics' => $this->find_missing_topics(),
                'content_opportunities' => $this->find_content_opportunities(),
                'competitor_analysis' => $this->analyze_competitors(),
                'internal_linking' => $this->analyze_internal_linking(),
                'keyword_opportunities' => $this->find_keyword_opportunities()
            );
            
            // Save scan results
            update_option('ai_cw_last_scan', $scan_results);
            update_option('ai_cw_last_scan_date', current_time('mysql'));
            
            return array(
                'success' => true,
                'message' => __('Content scan completed successfully', 'ai-content-writer'),
                'data' => $scan_results
            );
            
        } catch (Exception $e) {
            return array(
                'success' => false,
                'message' => sprintf(__('Error during content scan: %s', 'ai-content-writer'), $e->getMessage())
            );
        }
    }
    
    /**
     * Find SEO gaps in existing content
     */
    private function find_seo_gaps() {
        $posts = get_posts(array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'numberposts' => -1
        ));
        
        $seo_gaps = array();
        
        foreach ($posts as $post) {
            $gaps = array();
            
            // Check meta title
            $meta_title = get_post_meta($post->ID, '_yoast_wpseo_title', true);
            if (empty($meta_title)) {
                $gaps[] = array(
                    'type' => 'missing_meta_title',
                    'severity' => 'high',
                    'description' => __('Missing meta title', 'ai-content-writer')
                );
            } elseif (strlen($meta_title) < 30 || strlen($meta_title) > 60) {
                $gaps[] = array(
                    'type' => 'meta_title_length',
                    'severity' => 'medium',
                    'description' => __('Meta title length not optimal', 'ai-content-writer')
                );
            }
            
            // Check meta description
            $meta_description = get_post_meta($post->ID, '_yoast_wpseo_metadesc', true);
            if (empty($meta_description)) {
                $gaps[] = array(
                    'type' => 'missing_meta_description',
                    'severity' => 'high',
                    'description' => __('Missing meta description', 'ai-content-writer')
                );
            } elseif (strlen($meta_description) < 120 || strlen($meta_description) > 160) {
                $gaps[] = array(
                    'type' => 'meta_description_length',
                    'severity' => 'medium',
                    'description' => __('Meta description length not optimal', 'ai-content-writer')
                );
            }
            
            // Check focus keyword
            $focus_keyword = get_post_meta($post->ID, '_yoast_wpseo_focuskw', true);
            if (empty($focus_keyword)) {
                $gaps[] = array(
                    'type' => 'missing_focus_keyword',
                    'severity' => 'high',
                    'description' => __('Missing focus keyword', 'ai-content-writer')
                );
            }
            
            // Check content length
            $content_length = strlen(wp_strip_all_tags($post->post_content));
            if ($content_length < 300) {
                $gaps[] = array(
                    'type' => 'content_too_short',
                    'severity' => 'medium',
                    'description' => __('Content too short for good SEO', 'ai-content-writer')
                );
            }
            
            // Check for images with alt text
            $images = $this->get_post_images($post->ID);
            $images_without_alt = 0;
            foreach ($images as $image) {
                if (empty($image['alt'])) {
                    $images_without_alt++;
                }
            }
            
            if ($images_without_alt > 0) {
                $gaps[] = array(
                    'type' => 'missing_alt_text',
                    'severity' => 'medium',
                    'description' => sprintf(__('%d images missing alt text', 'ai-content-writer'), $images_without_alt)
                );
            }
            
            if (!empty($gaps)) {
                $seo_gaps[] = array(
                    'post_id' => $post->ID,
                    'post_title' => $post->post_title,
                    'post_url' => get_permalink($post->ID),
                    'gaps' => $gaps
                );
            }
        }
        
        return $seo_gaps;
    }
    
    /**
     * Find missing topics based on brand analysis
     */
    private function find_missing_topics() {
        $brand_profile = $this->brand_analyzer->get_brand_profile();
        
        if (empty($brand_profile['content_themes']['main_topics'])) {
            return array();
        }
        
        $existing_topics = $this->get_existing_topics();
        $brand_topics = $brand_profile['content_themes']['main_topics'];
        
        $missing_topics = array();
        
        foreach ($brand_topics as $topic) {
            if (!$this->topic_is_covered($topic, $existing_topics)) {
                $missing_topics[] = array(
                    'topic' => $topic,
                    'priority' => $this->calculate_topic_priority($topic),
                    'suggested_keywords' => $this->get_topic_keywords($topic)
                );
            }
        }
        
        return $missing_topics;
    }
    
    /**
     * Find content opportunities
     */
    private function find_content_opportunities() {
        $opportunities = array();
        
        // Find popular posts that could be expanded
        $popular_posts = $this->get_popular_posts();
        foreach ($popular_posts as $post) {
            $content_length = strlen(wp_strip_all_tags($post->post_content));
            if ($content_length < 1000) {
                $opportunities[] = array(
                    'type' => 'expand_popular_content',
                    'post_id' => $post->ID,
                    'post_title' => $post->post_title,
                    'current_length' => $content_length,
                    'suggested_length' => 1500,
                    'priority' => 'high'
                );
            }
        }
        
        // Find posts with high bounce rate potential
        $short_posts = get_posts(array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'numberposts' => 10,
            'meta_query' => array(
                array(
                    'key' => 'ai_cw_content_length',
                    'value' => 500,
                    'compare' => '<'
                )
            )
        ));
        
        foreach ($short_posts as $post) {
            $opportunities[] = array(
                'type' => 'improve_short_content',
                'post_id' => $post->ID,
                'post_title' => $post->post_title,
                'priority' => 'medium'
            );
        }
        
        return $opportunities;
    }
    
    /**
     * Analyze competitors (placeholder for future integration)
     */
    private function analyze_competitors() {
        // This would integrate with Google Search Console API or Ahrefs API
        // For now, return placeholder data
        return array(
            'status' => 'not_implemented',
            'message' => __('Competitor analysis requires external API integration', 'ai-content-writer')
        );
    }
    
    /**
     * Analyze internal linking patterns
     */
    private function analyze_internal_linking() {
        $posts = get_posts(array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'numberposts' => -1
        ));
        
        $linking_analysis = array(
            'posts_without_internal_links' => array(),
            'orphaned_posts' => array(),
            'link_opportunities' => array()
        );
        
        foreach ($posts as $post) {
            $internal_links = $this->count_internal_links($post->post_content);
            $incoming_links = $this->count_incoming_links($post->ID);
            
            if ($internal_links === 0) {
                $linking_analysis['posts_without_internal_links'][] = array(
                    'post_id' => $post->ID,
                    'post_title' => $post->post_title,
                    'post_url' => get_permalink($post->ID)
                );
            }
            
            if ($incoming_links === 0) {
                $linking_analysis['orphaned_posts'][] = array(
                    'post_id' => $post->ID,
                    'post_title' => $post->post_title,
                    'post_url' => get_permalink($post->ID)
                );
            }
        }
        
        return $linking_analysis;
    }
    
    /**
     * Find keyword opportunities
     */
    private function find_keyword_opportunities() {
        $opportunities = array();
        
        // Get all categories and tags
        $categories = get_categories();
        $tags = get_tags();
        
        foreach ($categories as $category) {
            $posts_in_category = get_posts(array(
                'category' => $category->term_id,
                'numberposts' => -1,
                'post_status' => 'publish'
            ));
            
            if (count($posts_in_category) < 3) {
                $opportunities[] = array(
                    'type' => 'underutilized_category',
                    'category' => $category->name,
                    'category_id' => $category->term_id,
                    'post_count' => count($posts_in_category),
                    'suggested_posts' => 5 - count($posts_in_category)
                );
            }
        }
        
        return $opportunities;
    }
    
    /**
     * Helper methods
     */
    private function get_post_images($post_id) {
        $images = array();
        $content = get_post_field('post_content', $post_id);
        
        preg_match_all('/<img[^>]+>/i', $content, $matches);
        
        foreach ($matches[0] as $img_tag) {
            preg_match('/alt=["\']([^"\']*)["\']/', $img_tag, $alt_match);
            $images[] = array(
                'alt' => isset($alt_match[1]) ? $alt_match[1] : ''
            );
        }
        
        return $images;
    }
    
    private function get_existing_topics() {
        $topics = array();
        $posts = get_posts(array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'numberposts' => -1
        ));
        
        foreach ($posts as $post) {
            $categories = wp_get_post_categories($post->ID, array('fields' => 'names'));
            $topics = array_merge($topics, $categories);
        }
        
        return array_unique($topics);
    }
    
    private function topic_is_covered($topic, $existing_topics) {
        foreach ($existing_topics as $existing_topic) {
            if (stripos($existing_topic, $topic) !== false || stripos($topic, $existing_topic) !== false) {
                return true;
            }
        }
        return false;
    }
    
    private function calculate_topic_priority($topic) {
        // Simple priority calculation - in production, use more sophisticated logic
        return 'medium';
    }
    
    private function get_topic_keywords($topic) {
        // Placeholder for keyword research
        return array($topic, $topic . ' guide', 'how to ' . $topic);
    }
    
    private function get_popular_posts() {
        // Placeholder for popular posts logic
        return get_posts(array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'numberposts' => 5,
            'orderby' => 'comment_count',
            'order' => 'DESC'
        ));
    }
    
    private function count_internal_links($content) {
        preg_match_all('/<a[^>]+href=["\']([^"\']*)["\'][^>]*>/i', $content, $matches);
        $internal_links = 0;
        
        foreach ($matches[1] as $url) {
            if (strpos($url, home_url()) === 0 || strpos($url, '/') === 0) {
                $internal_links++;
            }
        }
        
        return $internal_links;
    }
    
    private function count_incoming_links($post_id) {
        global $wpdb;
        
        $post_url = get_permalink($post_id);
        $post_url_short = str_replace(home_url(), '', $post_url);
        
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->posts} 
             WHERE post_content LIKE %s 
             AND post_status = 'publish'",
            '%' . $wpdb->esc_like($post_url) . '%'
        ));
        
        $count += $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->posts} 
             WHERE post_content LIKE %s 
             AND post_status = 'publish'",
            '%' . $wpdb->esc_like($post_url_short) . '%'
        ));
        
        return $count;
    }
    
    /**
     * Get last scan results
     */
    public function get_last_scan_results() {
        return get_option('ai_cw_last_scan', array());
    }
    
    /**
     * Get scan date
     */
    public function get_last_scan_date() {
        return get_option('ai_cw_last_scan_date', '');
    }
}
