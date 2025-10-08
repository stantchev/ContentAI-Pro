<?php
/**
 * Content Generator Class
 * 
 * Generates new content using ChatGPT API
 */

if (!defined('ABSPATH')) {
    exit;
}

class AI_CW_Content_Generator {
    
    private $brand_analyzer;
    private $seo_optimizer;
    private $seo_integration;
    private $openai_api_key;
    private $openai_model = 'gpt-4';
    
    public function __construct() {
        $this->brand_analyzer = new AI_CW_Brand_Analyzer();
        $this->seo_optimizer = new AI_CW_SEO_Optimizer();
        $this->seo_integration = new AI_CW_SEO_Integration();
        $this->openai_api_key = get_option('ai_cw_openai_api_key', '');
    }
    
    /**
     * Generate content for a specific topic
     */
    public function generate_content($topic, $options = array()) {
        try {
            $brand_profile = $this->brand_analyzer->get_brand_profile();
            
            if (empty($brand_profile)) {
                return array(
                    'success' => false,
                    'message' => __('Brand analysis not completed. Please run brand analysis first.', 'ai-content-writer')
                );
            }
            
            // Generate content using ChatGPT
            $content_result = $this->generate_with_chatgpt($topic, $brand_profile, $options);
            
            if (!$content_result['success']) {
                return $content_result;
            }
            
            $content = $content_result['data'];
            
            // Extract keyword from topic or generate one
            $keyword = $this->extract_keyword($topic, $content);
            
            // Optimize content for SEO
            $seo_result = $this->seo_optimizer->optimize_content($content, $keyword);
            
            if ($seo_result['success']) {
                $content = $seo_result['optimized_content'];
            }
            
            // Generate meta data
            $meta_data = $this->seo_optimizer->generate_meta_data($content, $keyword, $topic);
            
            // Add internal links
            $content = $this->add_internal_links($content, $keyword);
            
            return array(
                'success' => true,
                'message' => __('Content generated successfully', 'ai-content-writer'),
                'data' => array(
                    'title' => $topic,
                    'content' => $content,
                    'meta_data' => $meta_data,
                    'keyword' => $keyword,
                    'seo_score' => $seo_result['score'] ?? 0,
                    'word_count' => str_word_count($content),
                    'reading_time' => $this->calculate_reading_time($content)
                )
            );
            
        } catch (Exception $e) {
            return array(
                'success' => false,
                'message' => sprintf(__('Error during content generation: %s', 'ai-content-writer'), $e->getMessage())
            );
        }
    }
    
    /**
     * Generate content using ChatGPT
     */
    private function generate_with_chatgpt($topic, $brand_profile, $options = array()) {
        if (empty($this->openai_api_key)) {
            return array(
                'success' => false,
                'message' => __('OpenAI API key not configured', 'ai-content-writer')
            );
        }
        
        $prompt = $this->build_generation_prompt($topic, $brand_profile, $options);
        
        $response = $this->call_openai_api($prompt);
        
        if ($response['success']) {
            return array(
                'success' => true,
                'data' => $response['data']
            );
        } else {
            return $response;
        }
    }
    
    /**
     * Build generation prompt for ChatGPT
     */
    private function build_generation_prompt($topic, $brand_profile, $options = array()) {
        $word_count = isset($options['word_count']) ? $options['word_count'] : 1000;
        $tone = isset($options['tone']) ? $options['tone'] : '';
        
        $brand_guidelines = '';
        if (!empty($brand_profile['brand_guidelines'])) {
            $brand_guidelines = "Brand Guidelines: " . json_encode($brand_profile['brand_guidelines']);
        }
        
        $tone_voice = '';
        if (!empty($brand_profile['tone_of_voice'])) {
            $tone_voice = "Tone of Voice: " . json_encode($brand_profile['tone_of_voice']);
        }
        
        $content_themes = '';
        if (!empty($brand_profile['content_themes'])) {
            $content_themes = "Content Themes: " . json_encode($brand_profile['content_themes']);
        }
        
        return "Write a comprehensive, SEO-optimized blog post about '{$topic}'. 

Requirements:
- Word count: approximately {$word_count} words
- Include proper heading structure (H2, H3)
- Use engaging, informative content
- Include actionable insights and tips
- Add relevant examples and case studies
- Ensure content is original and valuable
- Write in a professional yet accessible tone
- Include a compelling introduction and conclusion
- Add internal linking opportunities (mark with [INTERNAL_LINK:keyword])

{$brand_guidelines}
{$tone_voice}
{$content_themes}

Please write only the content without any additional commentary or explanations. The content should be ready for publication.";
    }
    
    /**
     * Call OpenAI API
     */
    private function call_openai_api($prompt) {
        $headers = array(
            'Authorization' => 'Bearer ' . $this->openai_api_key,
            'Content-Type' => 'application/json'
        );
        
        $data = array(
            'model' => $this->openai_model,
            'messages' => array(
                array(
                    'role' => 'user',
                    'content' => $prompt
                )
            ),
            'max_tokens' => 3000,
            'temperature' => 0.7
        );
        
        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', array(
            'headers' => $headers,
            'body' => json_encode($data),
            'timeout' => 120
        ));
        
        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => $response->get_error_message()
            );
        }
        
        $body = wp_remote_retrieve_body($response);
        $decoded = json_decode($body, true);
        
        if (isset($decoded['error'])) {
            return array(
                'success' => false,
                'message' => $decoded['error']['message']
            );
        }
        
        if (!isset($decoded['choices'][0]['message']['content'])) {
            return array(
                'success' => false,
                'message' => __('Invalid response from OpenAI API', 'ai-content-writer')
            );
        }
        
        return array(
            'success' => true,
            'data' => $decoded['choices'][0]['message']['content']
        );
    }
    
    /**
     * Extract keyword from topic or content
     */
    private function extract_keyword($topic, $content) {
        // Try to extract keyword from topic
        $topic_words = explode(' ', strtolower($topic));
        $keyword = implode(' ', array_slice($topic_words, 0, 3)); // Take first 3 words
        
        // If topic is too short, try to extract from content
        if (strlen($keyword) < 5) {
            $content_words = str_word_count(strtolower($content), 1);
            $word_freq = array_count_values($content_words);
            arsort($word_freq);
            
            $common_words = array('the', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by', 'is', 'are', 'was', 'were', 'be', 'been', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'could', 'should', 'may', 'might', 'can', 'this', 'that', 'these', 'those', 'a', 'an');
            
            foreach ($word_freq as $word => $freq) {
                if (!in_array($word, $common_words) && strlen($word) > 3) {
                    $keyword = $word;
                    break;
                }
            }
        }
        
        return $keyword;
    }
    
    /**
     * Add internal links to content
     */
    private function add_internal_links($content, $keyword) {
        // Find posts related to the keyword
        $related_posts = $this->get_related_posts($keyword);
        
        if (empty($related_posts)) {
            return $content;
        }
        
        // Replace [INTERNAL_LINK:keyword] placeholders with actual links
        foreach ($related_posts as $post) {
            $link_text = $post['title'];
            $link_url = $post['url'];
            $link = '<a href="' . $link_url . '" title="' . esc_attr($post['title']) . '">' . $link_text . '</a>';
            
            // Replace first occurrence of the keyword with a link
            $content = preg_replace('/\b' . preg_quote($keyword, '/') . '\b/i', $link, $content, 1);
        }
        
        return $content;
    }
    
    /**
     * Get related posts for internal linking
     */
    private function get_related_posts($keyword, $limit = 3) {
        $posts = get_posts(array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'numberposts' => $limit,
            's' => $keyword,
            'orderby' => 'relevance'
        ));
        
        $related_posts = array();
        
        foreach ($posts as $post) {
            $related_posts[] = array(
                'id' => $post->ID,
                'title' => $post->post_title,
                'url' => get_permalink($post->ID)
            );
        }
        
        return $related_posts;
    }
    
    /**
     * Calculate reading time
     */
    private function calculate_reading_time($content) {
        $word_count = str_word_count($content);
        $reading_time = ceil($word_count / 200); // Average reading speed: 200 words per minute
        return $reading_time;
    }
    
    /**
     * Publish generated content
     */
    public function publish_content($content_data) {
        try {
            $post_data = array(
                'post_title' => $content_data['title'],
                'post_content' => $content_data['content'],
                'post_status' => $content_data['status'] ?? 'draft',
                'post_type' => 'post',
                'post_author' => get_current_user_id(),
                'post_date' => current_time('mysql')
            );
            
            // Create the post
            $post_id = wp_insert_post($post_data);
            
            if (is_wp_error($post_id)) {
                return array(
                    'success' => false,
                    'message' => $post_id->get_error_message()
                );
            }
            
            // Set SEO meta data
            if (!empty($content_data['meta_data'])) {
                $this->seo_integration->set_seo_meta($post_id, $content_data['meta_data']);
            }
            
            // Set focus keyword
            if (!empty($content_data['keyword'])) {
                update_post_meta($post_id, '_ai_cw_focus_keyword', $content_data['keyword']);
            }
            
            // Set SEO score
            if (!empty($content_data['seo_score'])) {
                $this->seo_integration->update_seo_score($post_id, $content_data['seo_score']);
            }
            
            // Set categories if provided
            if (!empty($content_data['categories'])) {
                wp_set_post_categories($post_id, $content_data['categories']);
            }
            
            // Set tags if provided
            if (!empty($content_data['tags'])) {
                wp_set_post_tags($post_id, $content_data['tags']);
            }
            
            // Log the generation
            $this->log_content_generation($post_id, $content_data);
            
            return array(
                'success' => true,
                'message' => __('Content published successfully', 'ai-content-writer'),
                'post_id' => $post_id,
                'post_url' => get_permalink($post_id)
            );
            
        } catch (Exception $e) {
            return array(
                'success' => false,
                'message' => sprintf(__('Error publishing content: %s', 'ai-content-writer'), $e->getMessage())
            );
        }
    }
    
    /**
     * Log content generation
     */
    private function log_content_generation($post_id, $content_data) {
        $log_entry = array(
            'post_id' => $post_id,
            'title' => $content_data['title'],
            'keyword' => $content_data['keyword'] ?? '',
            'seo_score' => $content_data['seo_score'] ?? 0,
            'word_count' => $content_data['word_count'] ?? 0,
            'generated_at' => current_time('mysql'),
            'generated_by' => get_current_user_id()
        );
        
        $logs = get_option('ai_cw_content_logs', array());
        $logs[] = $log_entry;
        
        // Keep only last 100 logs
        if (count($logs) > 100) {
            $logs = array_slice($logs, -100);
        }
        
        update_option('ai_cw_content_logs', $logs);
    }
    
    /**
     * Get content generation logs
     */
    public function get_content_logs($limit = 20) {
        $logs = get_option('ai_cw_content_logs', array());
        return array_slice(array_reverse($logs), 0, $limit);
    }
    
    /**
     * Generate content suggestions based on brand analysis
     */
    public function generate_content_suggestions() {
        $brand_profile = $this->brand_analyzer->get_brand_profile();
        
        if (empty($brand_profile)) {
            return array();
        }
        
        $suggestions = array();
        
        // Get main topics from brand profile
        if (!empty($brand_profile['content_themes']['main_topics'])) {
            foreach ($brand_profile['content_themes']['main_topics'] as $topic) {
                $suggestions[] = array(
                    'topic' => $topic,
                    'priority' => 'high',
                    'type' => 'brand_topic',
                    'description' => sprintf(__('Content about %s based on brand analysis', 'ai-content-writer'), $topic)
                );
            }
        }
        
        // Get popular keywords
        $popular_keywords = $this->get_popular_keywords();
        foreach ($popular_keywords as $keyword) {
            $suggestions[] = array(
                'topic' => $keyword,
                'priority' => 'medium',
                'type' => 'trending_keyword',
                'description' => sprintf(__('Content about trending keyword: %s', 'ai-content-writer'), $keyword)
            );
        }
        
        // Get seasonal suggestions
        $seasonal_suggestions = $this->get_seasonal_suggestions();
        foreach ($seasonal_suggestions as $suggestion) {
            $suggestions[] = $suggestion;
        }
        
        return $suggestions;
    }
    
    /**
     * Get popular keywords (placeholder)
     */
    private function get_popular_keywords() {
        // In production, this would integrate with Google Trends or similar
        return array('digital marketing', 'SEO tips', 'content strategy');
    }
    
    /**
     * Get seasonal content suggestions
     */
    private function get_seasonal_suggestions() {
        $current_month = date('n');
        $suggestions = array();
        
        switch ($current_month) {
            case 1:
                $suggestions[] = array(
                    'topic' => 'New Year Marketing Strategies',
                    'priority' => 'high',
                    'type' => 'seasonal',
                    'description' => __('New Year marketing content', 'ai-content-writer')
                );
                break;
            case 12:
                $suggestions[] = array(
                    'topic' => 'Holiday Marketing Campaigns',
                    'priority' => 'high',
                    'type' => 'seasonal',
                    'description' => __('Holiday marketing content', 'ai-content-writer')
                );
                break;
        }
        
        return $suggestions;
    }
}
