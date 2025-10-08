<?php
/**
 * Brand Analyzer Class
 * 
 * Analyzes existing content to build brand profile using ChatGPT API
 */

if (!defined('ABSPATH')) {
    exit;
}

class AI_CW_Brand_Analyzer {
    
    private $openai_api_key;
    private $openai_model = 'gpt-4';
    
    public function __construct() {
        $this->openai_api_key = get_option('ai_cw_openai_api_key', '');
    }
    
    /**
     * Main brand analysis function
     */
    public function analyze_brand() {
        try {
            // Get all published posts and pages
            $content_data = $this->get_all_content();
            
            if (empty($content_data)) {
                return array(
                    'success' => false,
                    'message' => __('No content found to analyze', 'ai-content-writer')
                );
            }
            
            // Analyze content with ChatGPT
            $analysis = $this->analyze_with_chatgpt($content_data);
            
            if ($analysis['success']) {
                // Save brand profile
                $this->save_brand_profile($analysis['data']);
                
                // Mark analysis as completed
                update_option('ai_cw_brand_analysis_completed', true);
                update_option('ai_cw_brand_analysis_date', current_time('mysql'));
                
                return array(
                    'success' => true,
                    'message' => __('Brand analysis completed successfully', 'ai-content-writer'),
                    'data' => $analysis['data']
                );
            } else {
                return $analysis;
            }
            
        } catch (Exception $e) {
            return array(
                'success' => false,
                'message' => sprintf(__('Error during brand analysis: %s', 'ai-content-writer'), $e->getMessage())
            );
        }
    }
    
    /**
     * Get all published content from the site
     */
    private function get_all_content() {
        $posts = get_posts(array(
            'post_type' => array('post', 'page'),
            'post_status' => 'publish',
            'numberposts' => -1,
            'orderby' => 'date',
            'order' => 'DESC'
        ));
        
        $content_data = array();
        
        foreach ($posts as $post) {
            $content_data[] = array(
                'id' => $post->ID,
                'title' => $post->post_title,
                'content' => wp_strip_all_tags($post->post_content),
                'excerpt' => $post->post_excerpt,
                'date' => $post->post_date,
                'categories' => wp_get_post_categories($post->ID, array('fields' => 'names')),
                'tags' => wp_get_post_tags($post->ID, array('fields' => 'names')),
                'url' => get_permalink($post->ID)
            );
        }
        
        return $content_data;
    }
    
    /**
     * Analyze content using ChatGPT API
     */
    private function analyze_with_chatgpt($content_data) {
        if (empty($this->openai_api_key)) {
            return array(
                'success' => false,
                'message' => __('OpenAI API key not configured', 'ai-content-writer')
            );
        }
        
        // Prepare content for analysis (limit to avoid token limits)
        $content_sample = $this->prepare_content_sample($content_data);
        
        $prompt = $this->build_analysis_prompt($content_sample);
        
        $response = $this->call_openai_api($prompt);
        
        if ($response['success']) {
            return array(
                'success' => true,
                'data' => $this->parse_analysis_response($response['data'])
            );
        } else {
            return $response;
        }
    }
    
    /**
     * Prepare content sample for analysis
     */
    private function prepare_content_sample($content_data) {
        $sample = array();
        $max_posts = 20; // Limit to avoid token limits
        $max_content_length = 5000; // Characters per post
        
        $count = 0;
        foreach ($content_data as $post) {
            if ($count >= $max_posts) break;
            
            $content = substr($post['content'], 0, $max_content_length);
            if (strlen($content) < 100) continue; // Skip very short posts
            
            $sample[] = array(
                'title' => $post['title'],
                'content' => $content,
                'categories' => $post['categories'],
                'tags' => $post['tags']
            );
            
            $count++;
        }
        
        return $sample;
    }
    
    /**
     * Build analysis prompt for ChatGPT
     */
    private function build_analysis_prompt($content_sample) {
        $content_text = '';
        foreach ($content_sample as $post) {
            $content_text .= "Title: " . $post['title'] . "\n";
            $content_text .= "Content: " . $post['content'] . "\n";
            $content_text .= "Categories: " . implode(', ', $post['categories']) . "\n";
            $content_text .= "Tags: " . implode(', ', $post['tags']) . "\n\n";
        }
        
        return "Analyze the following blog content and provide a comprehensive brand analysis. Please respond in JSON format with the following structure:

{
  \"tone_of_voice\": {
    \"formality\": \"formal/informal/mixed\",
    \"personality\": \"professional/friendly/authoritative/casual\",
    \"emotional_tone\": \"neutral/positive/enthusiastic/serious\",
    \"writing_style\": \"conversational/technical/educational/persuasive\"
  },
  \"language_characteristics\": {
    \"primary_language\": \"language_code\",
    \"vocabulary_level\": \"basic/intermediate/advanced\",
    \"sentence_structure\": \"simple/complex/mixed\",
    \"common_phrases\": [\"phrase1\", \"phrase2\", \"phrase3\"],
    \"technical_terms\": [\"term1\", \"term2\", \"term3\"]
  },
  \"content_themes\": {
    \"main_topics\": [\"topic1\", \"topic2\", \"topic3\"],
    \"content_categories\": [\"category1\", \"category2\", \"category3\"],
    \"target_audience\": \"description of target audience\",
    \"content_goals\": [\"goal1\", \"goal2\", \"goal3\"]
  },
  \"seo_patterns\": {
    \"common_keywords\": [\"keyword1\", \"keyword2\", \"keyword3\"],
    \"title_patterns\": \"description of title patterns\",
    \"content_structure\": \"description of content structure\",
    \"internal_linking_patterns\": \"description of linking patterns\"
  },
  \"brand_guidelines\": {
    \"voice_guidelines\": \"specific voice guidelines\",
    \"style_preferences\": \"style preferences\",
    \"content_standards\": \"content quality standards\",
    \"seo_requirements\": \"SEO requirements and patterns\"
  }
}

Content to analyze:

" . $content_text;
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
     * Parse analysis response from ChatGPT
     */
    private function parse_analysis_response($response) {
        // Try to extract JSON from response
        $json_start = strpos($response, '{');
        $json_end = strrpos($response, '}') + 1;
        
        if ($json_start !== false && $json_end !== false) {
            $json_string = substr($response, $json_start, $json_end - $json_start);
            $parsed = json_decode($json_string, true);
            
            if (json_last_error() === JSON_ERROR_NONE) {
                return $parsed;
            }
        }
        
        // Fallback: return raw response
        return array(
            'raw_response' => $response,
            'parsed' => false
        );
    }
    
    /**
     * Save brand profile to database
     */
    private function save_brand_profile($profile_data) {
        update_option('ai_cw_brand_profile', $profile_data);
        update_option('ai_cw_brand_profile_updated', current_time('mysql'));
    }
    
    /**
     * Get saved brand profile
     */
    public function get_brand_profile() {
        return get_option('ai_cw_brand_profile', array());
    }
    
    /**
     * Check if brand analysis is completed
     */
    public function is_analysis_completed() {
        return get_option('ai_cw_brand_analysis_completed', false);
    }
    
    /**
     * Get analysis date
     */
    public function get_analysis_date() {
        return get_option('ai_cw_brand_analysis_date', '');
    }
    
    /**
     * Update brand profile with new content analysis
     */
    public function update_brand_profile($new_content) {
        $current_profile = $this->get_brand_profile();
        
        if (empty($current_profile)) {
            return $this->analyze_brand();
        }
        
        // Analyze new content and merge with existing profile
        $new_analysis = $this->analyze_with_chatgpt(array($new_content));
        
        if ($new_analysis['success']) {
            $updated_profile = $this->merge_brand_profiles($current_profile, $new_analysis['data']);
            $this->save_brand_profile($updated_profile);
            
            return array(
                'success' => true,
                'message' => __('Brand profile updated successfully', 'ai-content-writer')
            );
        }
        
        return $new_analysis;
    }
    
    /**
     * Merge brand profiles
     */
    private function merge_brand_profiles($current, $new) {
        // Simple merge strategy - in production, implement more sophisticated merging
        return array_merge_recursive($current, $new);
    }
}
