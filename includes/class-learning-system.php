<?php
/**
 * Learning System Class
 * 
 * Handles continuous learning and adaptation
 */

if (!defined('ABSPATH')) {
    exit;
}

class AI_CW_Learning_System {
    
    private $brand_analyzer;
    private $content_generator;
    
    public function __construct() {
        $this->brand_analyzer = new AI_CW_Brand_Analyzer();
        $this->content_generator = new AI_CW_Content_Generator();
    }
    
    /**
     * Learn from new content
     */
    public function learn_from_content($post_id) {
        try {
            $post = get_post($post_id);
            
            if (!$post || $post->post_status !== 'publish') {
                return array(
                    'success' => false,
                    'message' => __('Post not found or not published', 'ai-content-writer')
                );
            }
            
            // Analyze the new content
            $content_analysis = $this->analyze_content($post);
            
            // Update brand profile
            $this->update_brand_profile($content_analysis);
            
            // Update content patterns
            $this->update_content_patterns($content_analysis);
            
            // Update SEO patterns
            $this->update_seo_patterns($content_analysis);
            
            // Log learning
            $this->log_learning($post_id, $content_analysis);
            
            return array(
                'success' => true,
                'message' => __('Learning completed successfully', 'ai-content-writer')
            );
            
        } catch (Exception $e) {
            return array(
                'success' => false,
                'message' => sprintf(__('Error during learning: %s', 'ai-content-writer'), $e->getMessage())
            );
        }
    }
    
    /**
     * Analyze content for learning
     */
    private function analyze_content($post) {
        $content = wp_strip_all_tags($post->post_content);
        
        return array(
            'post_id' => $post->ID,
            'title' => $post->post_title,
            'content' => $content,
            'word_count' => str_word_count($content),
            'reading_time' => ceil(str_word_count($content) / 200),
            'categories' => wp_get_post_categories($post->ID, array('fields' => 'names')),
            'tags' => wp_get_post_tags($post->ID, array('fields' => 'names')),
            'meta_title' => get_post_meta($post->ID, '_yoast_wpseo_title', true),
            'meta_description' => get_post_meta($post->ID, '_yoast_wpseo_metadesc', true),
            'focus_keyword' => get_post_meta($post->ID, '_yoast_wpseo_focuskw', true),
            'seo_score' => get_post_meta($post->ID, '_yoast_wpseo_content_score', true),
            'published_date' => $post->post_date,
            'author_id' => $post->post_author
        );
    }
    
    /**
     * Update brand profile based on new content
     */
    private function update_brand_profile($content_analysis) {
        $current_profile = $this->brand_analyzer->get_brand_profile();
        
        if (empty($current_profile)) {
            return;
        }
        
        // Update content themes
        if (!empty($content_analysis['categories'])) {
            $current_themes = $current_profile['content_themes']['main_topics'] ?? array();
            $new_themes = array_merge($current_themes, $content_analysis['categories']);
            $current_profile['content_themes']['main_topics'] = array_unique($new_themes);
        }
        
        // Update language characteristics
        $this->update_language_characteristics($current_profile, $content_analysis);
        
        // Update tone of voice
        $this->update_tone_of_voice($current_profile, $content_analysis);
        
        // Save updated profile
        update_option('ai_cw_brand_profile', $current_profile);
        update_option('ai_cw_brand_profile_updated', current_time('mysql'));
    }
    
    /**
     * Update language characteristics
     */
    private function update_language_characteristics($profile, $content_analysis) {
        $content = $content_analysis['content'];
        
        // Analyze vocabulary level
        $vocabulary_level = $this->analyze_vocabulary_level($content);
        
        // Analyze sentence structure
        $sentence_structure = $this->analyze_sentence_structure($content);
        
        // Update profile
        if (!isset($profile['language_characteristics'])) {
            $profile['language_characteristics'] = array();
        }
        
        $profile['language_characteristics']['vocabulary_level'] = $vocabulary_level;
        $profile['language_characteristics']['sentence_structure'] = $sentence_structure;
    }
    
    /**
     * Update tone of voice
     */
    private function update_tone_of_voice($profile, $content_analysis) {
        $content = $content_analysis['content'];
        
        // Analyze formality
        $formality = $this->analyze_formality($content);
        
        // Analyze emotional tone
        $emotional_tone = $this->analyze_emotional_tone($content);
        
        // Update profile
        if (!isset($profile['tone_of_voice'])) {
            $profile['tone_of_voice'] = array();
        }
        
        $profile['tone_of_voice']['formality'] = $formality;
        $profile['tone_of_voice']['emotional_tone'] = $emotional_tone;
    }
    
    /**
     * Update content patterns
     */
    private function update_content_patterns($content_analysis) {
        $patterns = get_option('ai_cw_content_patterns', array());
        
        // Update word count patterns
        $word_count = $content_analysis['word_count'];
        if (!isset($patterns['word_count'])) {
            $patterns['word_count'] = array();
        }
        $patterns['word_count'][] = $word_count;
        
        // Update reading time patterns
        $reading_time = $content_analysis['reading_time'];
        if (!isset($patterns['reading_time'])) {
            $patterns['reading_time'] = array();
        }
        $patterns['reading_time'][] = $reading_time;
        
        // Update category patterns
        if (!empty($content_analysis['categories'])) {
            if (!isset($patterns['categories'])) {
                $patterns['categories'] = array();
            }
            foreach ($content_analysis['categories'] as $category) {
                $patterns['categories'][$category] = ($patterns['categories'][$category] ?? 0) + 1;
            }
        }
        
        // Keep only recent patterns (last 100 posts)
        if (count($patterns['word_count']) > 100) {
            $patterns['word_count'] = array_slice($patterns['word_count'], -100);
        }
        if (count($patterns['reading_time']) > 100) {
            $patterns['reading_time'] = array_slice($patterns['reading_time'], -100);
        }
        
        update_option('ai_cw_content_patterns', $patterns);
    }
    
    /**
     * Update SEO patterns
     */
    private function update_seo_patterns($content_analysis) {
        $seo_patterns = get_option('ai_cw_seo_patterns', array());
        
        // Update title length patterns
        if (!empty($content_analysis['meta_title'])) {
            $title_length = strlen($content_analysis['meta_title']);
            if (!isset($seo_patterns['title_length'])) {
                $seo_patterns['title_length'] = array();
            }
            $seo_patterns['title_length'][] = $title_length;
        }
        
        // Update description length patterns
        if (!empty($content_analysis['meta_description'])) {
            $desc_length = strlen($content_analysis['meta_description']);
            if (!isset($seo_patterns['description_length'])) {
                $seo_patterns['description_length'] = array();
            }
            $seo_patterns['description_length'][] = $desc_length;
        }
        
        // Update SEO score patterns
        if (!empty($content_analysis['seo_score'])) {
            if (!isset($seo_patterns['seo_score'])) {
                $seo_patterns['seo_score'] = array();
            }
            $seo_patterns['seo_score'][] = intval($content_analysis['seo_score']);
        }
        
        // Keep only recent patterns
        foreach ($seo_patterns as $key => $values) {
            if (count($values) > 100) {
                $seo_patterns[$key] = array_slice($values, -100);
            }
        }
        
        update_option('ai_cw_seo_patterns', $seo_patterns);
    }
    
    /**
     * Log learning activity
     */
    private function log_learning($post_id, $content_analysis) {
        $log_entry = array(
            'post_id' => $post_id,
            'analysis' => $content_analysis,
            'learned_at' => current_time('mysql'),
            'type' => 'content_learning'
        );
        
        $logs = get_option('ai_cw_learning_logs', array());
        $logs[] = $log_entry;
        
        // Keep only last 50 logs
        if (count($logs) > 50) {
            $logs = array_slice($logs, -50);
        }
        
        update_option('ai_cw_learning_logs', $logs);
    }
    
    /**
     * Get learning insights
     */
    public function get_learning_insights() {
        $content_patterns = get_option('ai_cw_content_patterns', array());
        $seo_patterns = get_option('ai_cw_seo_patterns', array());
        
        $insights = array();
        
        // Word count insights
        if (!empty($content_patterns['word_count'])) {
            $avg_word_count = array_sum($content_patterns['word_count']) / count($content_patterns['word_count']);
            $insights['avg_word_count'] = round($avg_word_count);
        }
        
        // Reading time insights
        if (!empty($content_patterns['reading_time'])) {
            $avg_reading_time = array_sum($content_patterns['reading_time']) / count($content_patterns['reading_time']);
            $insights['avg_reading_time'] = round($avg_reading_time);
        }
        
        // Category insights
        if (!empty($content_patterns['categories'])) {
            arsort($content_patterns['categories']);
            $insights['popular_categories'] = array_slice($content_patterns['categories'], 0, 5, true);
        }
        
        // SEO insights
        if (!empty($seo_patterns['seo_score'])) {
            $avg_seo_score = array_sum($seo_patterns['seo_score']) / count($seo_patterns['seo_score']);
            $insights['avg_seo_score'] = round($avg_seo_score, 1);
        }
        
        return $insights;
    }
    
    /**
     * Get learning logs
     */
    public function get_learning_logs($limit = 20) {
        $logs = get_option('ai_cw_learning_logs', array());
        return array_slice(array_reverse($logs), 0, $limit);
    }
    
    /**
     * Analyze vocabulary level
     */
    private function analyze_vocabulary_level($content) {
        $words = str_word_count(strtolower($content), 1);
        $word_count = count($words);
        
        $complex_words = 0;
        foreach ($words as $word) {
            if (strlen($word) > 6) {
                $complex_words++;
            }
        }
        
        $complexity_ratio = $complex_words / $word_count;
        
        if ($complexity_ratio > 0.3) {
            return 'advanced';
        } elseif ($complexity_ratio > 0.15) {
            return 'intermediate';
        } else {
            return 'basic';
        }
    }
    
    /**
     * Analyze sentence structure
     */
    private function analyze_sentence_structure($content) {
        $sentences = preg_split('/[.!?]+/', $content);
        $sentence_count = count(array_filter($sentences));
        
        if ($sentence_count === 0) {
            return 'simple';
        }
        
        $total_words = str_word_count($content);
        $avg_sentence_length = $total_words / $sentence_count;
        
        if ($avg_sentence_length > 20) {
            return 'complex';
        } elseif ($avg_sentence_length > 15) {
            return 'mixed';
        } else {
            return 'simple';
        }
    }
    
    /**
     * Analyze formality
     */
    private function analyze_formality($content) {
        $formal_indicators = array('therefore', 'however', 'furthermore', 'moreover', 'consequently');
        $informal_indicators = array('hey', 'wow', 'awesome', 'cool', 'yeah', 'gonna', 'wanna');
        
        $content_lower = strtolower($content);
        
        $formal_count = 0;
        foreach ($formal_indicators as $indicator) {
            $formal_count += substr_count($content_lower, $indicator);
        }
        
        $informal_count = 0;
        foreach ($informal_indicators as $indicator) {
            $informal_count += substr_count($content_lower, $indicator);
        }
        
        if ($formal_count > $informal_count) {
            return 'formal';
        } elseif ($informal_count > $formal_count) {
            return 'informal';
        } else {
            return 'mixed';
        }
    }
    
    /**
     * Analyze emotional tone
     */
    private function analyze_emotional_tone($content) {
        $positive_words = array('great', 'excellent', 'amazing', 'wonderful', 'fantastic', 'awesome', 'brilliant');
        $negative_words = array('terrible', 'awful', 'horrible', 'bad', 'worst', 'disappointing', 'frustrating');
        $neutral_words = array('good', 'okay', 'fine', 'average', 'standard', 'normal');
        
        $content_lower = strtolower($content);
        
        $positive_count = 0;
        foreach ($positive_words as $word) {
            $positive_count += substr_count($content_lower, $word);
        }
        
        $negative_count = 0;
        foreach ($negative_words as $word) {
            $negative_count += substr_count($content_lower, $word);
        }
        
        $neutral_count = 0;
        foreach ($neutral_words as $word) {
            $neutral_count += substr_count($content_lower, $word);
        }
        
        if ($positive_count > $negative_count && $positive_count > $neutral_count) {
            return 'positive';
        } elseif ($negative_count > $positive_count && $negative_count > $neutral_count) {
            return 'negative';
        } else {
            return 'neutral';
        }
    }
    
    /**
     * Get content recommendations based on learning
     */
    public function get_content_recommendations() {
        $insights = $this->get_learning_insights();
        $recommendations = array();
        
        // Word count recommendations
        if (isset($insights['avg_word_count'])) {
            if ($insights['avg_word_count'] < 500) {
                $recommendations[] = array(
                    'type' => 'word_count',
                    'message' => __('Consider increasing content length for better SEO', 'ai-content-writer'),
                    'priority' => 'medium'
                );
            }
        }
        
        // SEO score recommendations
        if (isset($insights['avg_seo_score'])) {
            if ($insights['avg_seo_score'] < 7) {
                $recommendations[] = array(
                    'type' => 'seo_score',
                    'message' => __('Focus on improving SEO scores for better rankings', 'ai-content-writer'),
                    'priority' => 'high'
                );
            }
        }
        
        // Category diversity recommendations
        if (isset($insights['popular_categories'])) {
            $category_count = count($insights['popular_categories']);
            if ($category_count < 3) {
                $recommendations[] = array(
                    'type' => 'category_diversity',
                    'message' => __('Consider diversifying content across more categories', 'ai-content-writer'),
                    'priority' => 'low'
                );
            }
        }
        
        return $recommendations;
    }
}
