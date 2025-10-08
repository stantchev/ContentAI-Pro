<?php
/**
 * SEO Optimizer Class
 * 
 * Analyzes and optimizes content for SEO
 */

if (!defined('ABSPATH')) {
    exit;
}

class AI_CW_SEO_Optimizer {
    
    private $brand_analyzer;
    private $openai_api_key;
    private $openai_model = 'gpt-4';
    
    public function __construct() {
        $this->brand_analyzer = new AI_CW_Brand_Analyzer();
        $this->openai_api_key = get_option('ai_cw_openai_api_key', '');
    }
    
    /**
     * Optimize content for SEO
     */
    public function optimize_content($content, $keyword, $post_id = null) {
        try {
            $brand_profile = $this->brand_analyzer->get_brand_profile();
            
            // Analyze current SEO score
            $current_score = $this->calculate_seo_score($content, $keyword);
            
            if ($current_score >= 8) {
                return array(
                    'success' => true,
                    'message' => __('Content already optimized', 'ai-content-writer'),
                    'score' => $current_score,
                    'optimized_content' => $content
                );
            }
            
            // Optimize content using ChatGPT
            $optimized_content = $this->optimize_with_chatgpt($content, $keyword, $brand_profile);
            
            if ($optimized_content['success']) {
                $new_score = $this->calculate_seo_score($optimized_content['data'], $keyword);
                
                return array(
                    'success' => true,
                    'message' => __('Content optimized successfully', 'ai-content-writer'),
                    'score' => $new_score,
                    'previous_score' => $current_score,
                    'optimized_content' => $optimized_content['data'],
                    'improvements' => $this->get_improvements($content, $optimized_content['data'])
                );
            } else {
                return $optimized_content;
            }
            
        } catch (Exception $e) {
            return array(
                'success' => false,
                'message' => sprintf(__('Error during SEO optimization: %s', 'ai-content-writer'), $e->getMessage())
            );
        }
    }
    
    /**
     * Calculate SEO score for content
     */
    public function calculate_seo_score($content, $keyword) {
        $score = 0;
        $max_score = 10;
        
        // Check keyword in first paragraph (2 points)
        $first_paragraph = $this->get_first_paragraph($content);
        if (stripos($first_paragraph, $keyword) !== false) {
            $score += 2;
        }
        
        // Check keyword density (2 points)
        $keyword_density = $this->calculate_keyword_density($content, $keyword);
        if ($keyword_density >= 0.5 && $keyword_density <= 2.0) {
            $score += 2;
        }
        
        // Check content length (1 point)
        $content_length = strlen(wp_strip_all_tags($content));
        if ($content_length >= 300) {
            $score += 1;
        }
        
        // Check transition words (1 point)
        $transition_ratio = $this->calculate_transition_ratio($content);
        if ($transition_ratio >= 0.3) {
            $score += 1;
        }
        
        // Check passive voice (1 point)
        $passive_ratio = $this->calculate_passive_ratio($content);
        if ($passive_ratio <= 0.1) {
            $score += 1;
        }
        
        // Check headings structure (1 point)
        if ($this->has_proper_headings($content)) {
            $score += 1;
        }
        
        // Check internal links (1 point)
        if ($this->has_internal_links($content)) {
            $score += 1;
        }
        
        // Check readability (1 point)
        if ($this->is_readable($content)) {
            $score += 1;
        }
        
        return $score;
    }
    
    /**
     * Optimize content using ChatGPT
     */
    private function optimize_with_chatgpt($content, $keyword, $brand_profile) {
        if (empty($this->openai_api_key)) {
            return array(
                'success' => false,
                'message' => __('OpenAI API key not configured', 'ai-content-writer')
            );
        }
        
        $prompt = $this->build_optimization_prompt($content, $keyword, $brand_profile);
        
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
     * Build optimization prompt for ChatGPT
     */
    private function build_optimization_prompt($content, $keyword, $brand_profile) {
        $brand_guidelines = '';
        if (!empty($brand_profile['brand_guidelines'])) {
            $brand_guidelines = "Brand Guidelines: " . json_encode($brand_profile['brand_guidelines']);
        }
        
        return "Optimize the following content for SEO while maintaining the brand voice and style. The content should achieve a 10/10 SEO score.

SEO Requirements:
1. Include the focus keyword '{$keyword}' in the first paragraph
2. Maintain keyword density between 0.5% and 2%
3. Use at least 30% transition words
4. Keep passive voice under 10%
5. Include proper heading structure (H2, H3)
6. Add internal links where relevant
7. Ensure content is readable and engaging
8. Maintain content length of at least 300 words

{$brand_guidelines}

Content to optimize:

{$content}

Please return only the optimized content without any additional commentary or explanations.";
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
            'max_tokens' => 2000,
            'temperature' => 0.3
        );
        
        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', array(
            'headers' => $headers,
            'body' => json_encode($data),
            'timeout' => 60
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
     * Helper methods for SEO analysis
     */
    private function get_first_paragraph($content) {
        $paragraphs = explode("\n\n", $content);
        return isset($paragraphs[0]) ? $paragraphs[0] : '';
    }
    
    private function calculate_keyword_density($content, $keyword) {
        $content_lower = strtolower($content);
        $keyword_lower = strtolower($keyword);
        $word_count = str_word_count($content_lower);
        $keyword_count = substr_count($content_lower, $keyword_lower);
        
        return $word_count > 0 ? ($keyword_count / $word_count) * 100 : 0;
    }
    
    private function calculate_transition_ratio($content) {
        $transition_words = array(
            'however', 'therefore', 'furthermore', 'moreover', 'additionally',
            'consequently', 'meanwhile', 'nevertheless', 'nonetheless',
            'similarly', 'likewise', 'conversely', 'alternatively',
            'firstly', 'secondly', 'finally', 'initially', 'ultimately',
            'specifically', 'particularly', 'especially', 'notably',
            'indeed', 'certainly', 'obviously', 'clearly', 'evidently'
        );
        
        $content_lower = strtolower($content);
        $word_count = str_word_count($content_lower);
        $transition_count = 0;
        
        foreach ($transition_words as $word) {
            $transition_count += substr_count($content_lower, ' ' . $word . ' ');
        }
        
        return $word_count > 0 ? $transition_count / $word_count : 0;
    }
    
    private function calculate_passive_ratio($content) {
        $passive_indicators = array(
            'was', 'were', 'been', 'being', 'is', 'are', 'am',
            'have been', 'has been', 'had been', 'will be',
            'can be', 'could be', 'should be', 'would be'
        );
        
        $content_lower = strtolower($content);
        $word_count = str_word_count($content_lower);
        $passive_count = 0;
        
        foreach ($passive_indicators as $indicator) {
            $passive_count += substr_count($content_lower, $indicator);
        }
        
        return $word_count > 0 ? $passive_count / $word_count : 0;
    }
    
    private function has_proper_headings($content) {
        return preg_match('/<h[2-6][^>]*>/i', $content) > 0;
    }
    
    private function has_internal_links($content) {
        preg_match_all('/<a[^>]+href=["\']([^"\']*)["\'][^>]*>/i', $content, $matches);
        
        foreach ($matches[1] as $url) {
            if (strpos($url, home_url()) === 0 || strpos($url, '/') === 0) {
                return true;
            }
        }
        
        return false;
    }
    
    private function is_readable($content) {
        $content_clean = wp_strip_all_tags($content);
        $sentences = preg_split('/[.!?]+/', $content_clean);
        $words = str_word_count($content_clean);
        $sentence_count = count(array_filter($sentences));
        
        if ($sentence_count === 0) return false;
        
        $avg_sentence_length = $words / $sentence_count;
        
        // Simple readability check - average sentence length between 10-20 words
        return $avg_sentence_length >= 10 && $avg_sentence_length <= 20;
    }
    
    private function get_improvements($original, $optimized) {
        $improvements = array();
        
        $original_score = $this->calculate_seo_score($original, '');
        $optimized_score = $this->calculate_seo_score($optimized, '');
        
        if ($optimized_score > $original_score) {
            $improvements[] = __('SEO score improved', 'ai-content-writer');
        }
        
        if (strlen($optimized) > strlen($original)) {
            $improvements[] = __('Content length increased', 'ai-content-writer');
        }
        
        if ($this->has_proper_headings($optimized) && !$this->has_proper_headings($original)) {
            $improvements[] = __('Headings structure added', 'ai-content-writer');
        }
        
        if ($this->has_internal_links($optimized) && !$this->has_internal_links($original)) {
            $improvements[] = __('Internal links added', 'ai-content-writer');
        }
        
        return $improvements;
    }
    
    /**
     * Generate SEO meta data
     */
    public function generate_meta_data($content, $keyword, $title = '') {
        $meta_title = $this->generate_meta_title($content, $keyword, $title);
        $meta_description = $this->generate_meta_description($content, $keyword);
        
        return array(
            'meta_title' => $meta_title,
            'meta_description' => $meta_description,
            'focus_keyword' => $keyword
        );
    }
    
    private function generate_meta_title($content, $keyword, $title = '') {
        if (!empty($title)) {
            $title = $title . ' - ' . get_bloginfo('name');
        } else {
            $title = $keyword . ' - ' . get_bloginfo('name');
        }
        
        // Ensure title is between 30-60 characters
        if (strlen($title) > 60) {
            $title = substr($title, 0, 57) . '...';
        }
        
        return $title;
    }
    
    private function generate_meta_description($content, $keyword) {
        $description = wp_trim_words($content, 25, '...');
        
        // Ensure description is between 120-160 characters
        if (strlen($description) < 120) {
            $description = $description . ' Learn more about ' . $keyword . ' and discover expert insights.';
        } elseif (strlen($description) > 160) {
            $description = substr($description, 0, 157) . '...';
        }
        
        return $description;
    }
    
    /**
     * Get SEO recommendations
     */
    public function get_seo_recommendations($content, $keyword) {
        $recommendations = array();
        
        $score = $this->calculate_seo_score($content, $keyword);
        
        if ($score < 8) {
            $recommendations[] = __('Content needs SEO optimization', 'ai-content-writer');
        }
        
        if (!$this->has_proper_headings($content)) {
            $recommendations[] = __('Add proper heading structure (H2, H3)', 'ai-content-writer');
        }
        
        if (!$this->has_internal_links($content)) {
            $recommendations[] = __('Add internal links to related content', 'ai-content-writer');
        }
        
        $transition_ratio = $this->calculate_transition_ratio($content);
        if ($transition_ratio < 0.3) {
            $recommendations[] = __('Increase use of transition words', 'ai-content-writer');
        }
        
        $passive_ratio = $this->calculate_passive_ratio($content);
        if ($passive_ratio > 0.1) {
            $recommendations[] = __('Reduce passive voice usage', 'ai-content-writer');
        }
        
        return $recommendations;
    }
}
