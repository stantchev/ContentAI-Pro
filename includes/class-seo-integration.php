<?php
/**
 * SEO Integration Class
 * 
 * Integrates with Yoast, RankMath, and All in One SEO plugins
 */

if (!defined('ABSPATH')) {
    exit;
}

class AI_CW_SEO_Integration {
    
    private $seo_plugin;
    
    public function __construct() {
        $this->seo_plugin = $this->detect_seo_plugin();
    }
    
    /**
     * Detect which SEO plugin is active
     */
    private function detect_seo_plugin() {
        if (class_exists('WPSEO_Frontend')) {
            return 'yoast';
        } elseif (class_exists('RankMath')) {
            return 'rankmath';
        } elseif (class_exists('AIOSEO')) {
            return 'aioseo';
        }
        
        return 'none';
    }
    
    /**
     * Set SEO meta data for a post
     */
    public function set_seo_meta($post_id, $meta_data) {
        switch ($this->seo_plugin) {
            case 'yoast':
                return $this->set_yoast_meta($post_id, $meta_data);
            case 'rankmath':
                return $this->set_rankmath_meta($post_id, $meta_data);
            case 'aioseo':
                return $this->set_aioseo_meta($post_id, $meta_data);
            default:
                return $this->set_default_meta($post_id, $meta_data);
        }
    }
    
    /**
     * Get SEO score for a post
     */
    public function get_seo_score($post_id) {
        switch ($this->seo_plugin) {
            case 'yoast':
                return $this->get_yoast_score($post_id);
            case 'rankmath':
                return $this->get_rankmath_score($post_id);
            case 'aioseo':
                return $this->get_aioseo_score($post_id);
            default:
                return $this->get_default_score($post_id);
        }
    }
    
    /**
     * Check if SEO score is optimal
     */
    public function is_seo_optimal($post_id) {
        $score = $this->get_seo_score($post_id);
        $min_score = get_option('ai_cw_min_seo_score', 8);
        
        return $score >= $min_score;
    }
    
    /**
     * Yoast SEO integration
     */
    private function set_yoast_meta($post_id, $meta_data) {
        $updated = array();
        
        if (!empty($meta_data['meta_title'])) {
            update_post_meta($post_id, '_yoast_wpseo_title', $meta_data['meta_title']);
            $updated['title'] = true;
        }
        
        if (!empty($meta_data['meta_description'])) {
            update_post_meta($post_id, '_yoast_wpseo_metadesc', $meta_data['meta_description']);
            $updated['description'] = true;
        }
        
        if (!empty($meta_data['focus_keyword'])) {
            update_post_meta($post_id, '_yoast_wpseo_focuskw', $meta_data['focus_keyword']);
            $updated['keyword'] = true;
        }
        
        if (!empty($meta_data['canonical'])) {
            update_post_meta($post_id, '_yoast_wpseo_canonical', $meta_data['canonical']);
            $updated['canonical'] = true;
        }
        
        if (!empty($meta_data['noindex'])) {
            update_post_meta($post_id, '_yoast_wpseo_meta-robots-noindex', $meta_data['noindex']);
            $updated['noindex'] = true;
        }
        
        return array(
            'success' => true,
            'updated' => $updated,
            'plugin' => 'yoast'
        );
    }
    
    private function get_yoast_score($post_id) {
        $score = get_post_meta($post_id, '_yoast_wpseo_content_score', true);
        return $score ? intval($score) : 0;
    }
    
    /**
     * RankMath integration
     */
    private function set_rankmath_meta($post_id, $meta_data) {
        $updated = array();
        
        if (!empty($meta_data['meta_title'])) {
            update_post_meta($post_id, 'rank_math_title', $meta_data['meta_title']);
            $updated['title'] = true;
        }
        
        if (!empty($meta_data['meta_description'])) {
            update_post_meta($post_id, 'rank_math_description', $meta_data['meta_description']);
            $updated['description'] = true;
        }
        
        if (!empty($meta_data['focus_keyword'])) {
            update_post_meta($post_id, 'rank_math_focus_keyword', $meta_data['focus_keyword']);
            $updated['keyword'] = true;
        }
        
        if (!empty($meta_data['canonical'])) {
            update_post_meta($post_id, 'rank_math_canonical_url', $meta_data['canonical']);
            $updated['canonical'] = true;
        }
        
        if (!empty($meta_data['noindex'])) {
            update_post_meta($post_id, 'rank_math_robots', $meta_data['noindex'] ? array('noindex') : array());
            $updated['noindex'] = true;
        }
        
        return array(
            'success' => true,
            'updated' => $updated,
            'plugin' => 'rankmath'
        );
    }
    
    private function get_rankmath_score($post_id) {
        $score = get_post_meta($post_id, 'rank_math_seo_score', true);
        return $score ? intval($score) : 0;
    }
    
    /**
     * All in One SEO integration
     */
    private function set_aioseo_meta($post_id, $meta_data) {
        $updated = array();
        
        if (!empty($meta_data['meta_title'])) {
            update_post_meta($post_id, '_aioseo_title', $meta_data['meta_title']);
            $updated['title'] = true;
        }
        
        if (!empty($meta_data['meta_description'])) {
            update_post_meta($post_id, '_aioseo_description', $meta_data['meta_description']);
            $updated['description'] = true;
        }
        
        if (!empty($meta_data['focus_keyword'])) {
            update_post_meta($post_id, '_aioseo_keywords', $meta_data['focus_keyword']);
            $updated['keyword'] = true;
        }
        
        if (!empty($meta_data['canonical'])) {
            update_post_meta($post_id, '_aioseo_canonical_url', $meta_data['canonical']);
            $updated['canonical'] = true;
        }
        
        if (!empty($meta_data['noindex'])) {
            update_post_meta($post_id, '_aioseo_robots_noindex', $meta_data['noindex']);
            $updated['noindex'] = true;
        }
        
        return array(
            'success' => true,
            'updated' => $updated,
            'plugin' => 'aioseo'
        );
    }
    
    private function get_aioseo_score($post_id) {
        $score = get_post_meta($post_id, '_aioseo_score', true);
        return $score ? intval($score) : 0;
    }
    
    /**
     * Default meta handling (when no SEO plugin is active)
     */
    private function set_default_meta($post_id, $meta_data) {
        $updated = array();
        
        if (!empty($meta_data['meta_title'])) {
            update_post_meta($post_id, '_ai_cw_meta_title', $meta_data['meta_title']);
            $updated['title'] = true;
        }
        
        if (!empty($meta_data['meta_description'])) {
            update_post_meta($post_id, '_ai_cw_meta_description', $meta_data['meta_description']);
            $updated['description'] = true;
        }
        
        if (!empty($meta_data['focus_keyword'])) {
            update_post_meta($post_id, '_ai_cw_focus_keyword', $meta_data['focus_keyword']);
            $updated['keyword'] = true;
        }
        
        return array(
            'success' => true,
            'updated' => $updated,
            'plugin' => 'default'
        );
    }
    
    private function get_default_score($post_id) {
        $score = get_post_meta($post_id, '_ai_cw_seo_score', true);
        return $score ? intval($score) : 0;
    }
    
    /**
     * Get SEO plugin name
     */
    public function get_seo_plugin_name() {
        switch ($this->seo_plugin) {
            case 'yoast':
                return 'Yoast SEO';
            case 'rankmath':
                return 'RankMath';
            case 'aioseo':
                return 'All in One SEO';
            default:
                return 'None';
        }
    }
    
    /**
     * Check if SEO plugin is active
     */
    public function is_seo_plugin_active() {
        return $this->seo_plugin !== 'none';
    }
    
    /**
     * Get SEO recommendations for a post
     */
    public function get_seo_recommendations($post_id) {
        $recommendations = array();
        
        // Check meta title
        $meta_title = $this->get_meta_title($post_id);
        if (empty($meta_title)) {
            $recommendations[] = __('Add meta title', 'ai-content-writer');
        } elseif (strlen($meta_title) < 30 || strlen($meta_title) > 60) {
            $recommendations[] = __('Optimize meta title length (30-60 characters)', 'ai-content-writer');
        }
        
        // Check meta description
        $meta_description = $this->get_meta_description($post_id);
        if (empty($meta_description)) {
            $recommendations[] = __('Add meta description', 'ai-content-writer');
        } elseif (strlen($meta_description) < 120 || strlen($meta_description) > 160) {
            $recommendations[] = __('Optimize meta description length (120-160 characters)', 'ai-content-writer');
        }
        
        // Check focus keyword
        $focus_keyword = $this->get_focus_keyword($post_id);
        if (empty($focus_keyword)) {
            $recommendations[] = __('Add focus keyword', 'ai-content-writer');
        }
        
        return $recommendations;
    }
    
    /**
     * Get meta data for a post
     */
    public function get_meta_title($post_id) {
        switch ($this->seo_plugin) {
            case 'yoast':
                return get_post_meta($post_id, '_yoast_wpseo_title', true);
            case 'rankmath':
                return get_post_meta($post_id, 'rank_math_title', true);
            case 'aioseo':
                return get_post_meta($post_id, '_aioseo_title', true);
            default:
                return get_post_meta($post_id, '_ai_cw_meta_title', true);
        }
    }
    
    public function get_meta_description($post_id) {
        switch ($this->seo_plugin) {
            case 'yoast':
                return get_post_meta($post_id, '_yoast_wpseo_metadesc', true);
            case 'rankmath':
                return get_post_meta($post_id, 'rank_math_description', true);
            case 'aioseo':
                return get_post_meta($post_id, '_aioseo_description', true);
            default:
                return get_post_meta($post_id, '_ai_cw_meta_description', true);
        }
    }
    
    public function get_focus_keyword($post_id) {
        switch ($this->seo_plugin) {
            case 'yoast':
                return get_post_meta($post_id, '_yoast_wpseo_focuskw', true);
            case 'rankmath':
                return get_post_meta($post_id, 'rank_math_focus_keyword', true);
            case 'aioseo':
                return get_post_meta($post_id, '_aioseo_keywords', true);
            default:
                return get_post_meta($post_id, '_ai_cw_focus_keyword', true);
        }
    }
    
    /**
     * Update SEO score for a post
     */
    public function update_seo_score($post_id, $score) {
        switch ($this->seo_plugin) {
            case 'yoast':
                update_post_meta($post_id, '_yoast_wpseo_content_score', $score);
                break;
            case 'rankmath':
                update_post_meta($post_id, 'rank_math_seo_score', $score);
                break;
            case 'aioseo':
                update_post_meta($post_id, '_aioseo_score', $score);
                break;
            default:
                update_post_meta($post_id, '_ai_cw_seo_score', $score);
                break;
        }
    }
}
