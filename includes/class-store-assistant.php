<?php
/**
 * Store Assistant Class
 * 
 * AI assistant for product search and recommendations
 */

if (!defined('ABSPATH')) {
    exit;
}

class AI_CW_Store_Assistant {
    
    private $openai_api_key;
    private $openai_model = 'gpt-4';
    private $max_results = 5;
    
    public function __construct() {
        $this->openai_api_key = get_option('ai_cw_openai_api_key', '');
    }
    
    /**
     * Process user query and find matching products
     */
    public function process_query($query, $user_id = null) {
        try {
            if (empty($this->openai_api_key)) {
                return array(
                    'success' => false,
                    'message' => __('AI Assistant is not configured', 'contentai-pro')
                );
            }
            
            if (!class_exists('WooCommerce')) {
                return array(
                    'success' => false,
                    'message' => __('WooCommerce is not installed', 'contentai-pro')
                );
            }
            
            // Get all products for analysis
            $products = $this->get_all_products();
            
            if (empty($products)) {
                return array(
                    'success' => false,
                    'message' => __('No products found in store', 'contentai-pro')
                );
            }
            
            // Use ChatGPT to understand the query and find best matches
            $matches = $this->find_product_matches($query, $products);
            
            if (empty($matches)) {
                return array(
                    'success' => true,
                    'message' => __('No matching products found', 'contentai-pro'),
                    'products' => array(),
                    'suggestions' => $this->get_general_suggestions()
                );
            }
            
            // Log the search for analytics
            $this->log_search($query, $matches, $user_id);
            
            return array(
                'success' => true,
                'message' => __('Found matching products', 'contentai-pro'),
                'products' => $matches,
                'query' => $query,
                'total_found' => count($matches)
            );
            
        } catch (Exception $e) {
            return array(
                'success' => false,
                'message' => sprintf(__('Error processing query: %s', 'contentai-pro'), $e->getMessage())
            );
        }
    }
    
    /**
     * Get all products with their details
     */
    private function get_all_products() {
        $products = wc_get_products(array(
            'limit' => -1,
            'status' => 'publish',
            'visibility' => 'visible'
        ));
        
        $product_data = array();
        
        foreach ($products as $product) {
            $product_data[] = array(
                'id' => $product->get_id(),
                'name' => $product->get_name(),
                'description' => $product->get_description(),
                'short_description' => $product->get_short_description(),
                'price' => $product->get_price(),
                'regular_price' => $product->get_regular_price(),
                'sale_price' => $product->get_sale_price(),
                'sku' => $product->get_sku(),
                'categories' => wp_get_post_terms($product->get_id(), 'product_cat', array('fields' => 'names')),
                'tags' => wp_get_post_terms($product->get_id(), 'product_tag', array('fields' => 'names')),
                'attributes' => $this->get_product_attributes($product),
                'url' => $product->get_permalink(),
                'image' => wp_get_attachment_image_url($product->get_image_id(), 'medium'),
                'stock_status' => $product->get_stock_status(),
                'in_stock' => $product->is_in_stock(),
                'meta_title' => get_post_meta($product->get_id(), '_yoast_wpseo_title', true),
                'meta_description' => get_post_meta($product->get_id(), '_yoast_wpseo_metadesc', true)
            );
        }
        
        return $product_data;
    }
    
    /**
     * Get product attributes
     */
    private function get_product_attributes($product) {
        $attributes = array();
        
        foreach ($product->get_attributes() as $attribute) {
            if ($attribute->is_taxonomy()) {
                $terms = wp_get_post_terms($product->get_id(), $attribute->get_name(), array('fields' => 'names'));
                $attributes[$attribute->get_name()] = $terms;
            } else {
                $attributes[$attribute->get_name()] = $attribute->get_options();
            }
        }
        
        return $attributes;
    }
    
    /**
     * Use ChatGPT to find product matches
     */
    private function find_product_matches($query, $products) {
        // Prepare product data for ChatGPT
        $product_summaries = array();
        
        foreach ($products as $product) {
            $summary = $product['name'];
            if (!empty($product['short_description'])) {
                $summary .= ' - ' . $product['short_description'];
            }
            if (!empty($product['categories'])) {
                $summary .= ' (Categories: ' . implode(', ', $product['categories']) . ')';
            }
            if (!empty($product['tags'])) {
                $summary .= ' (Tags: ' . implode(', ', $product['tags']) . ')';
            }
            
            $product_summaries[] = array(
                'id' => $product['id'],
                'summary' => $summary,
                'price' => $product['price'],
                'in_stock' => $product['in_stock']
            );
        }
        
        $prompt = $this->build_search_prompt($query, $product_summaries);
        
        $response = $this->call_openai_api($prompt);
        
        if ($response['success']) {
            return $this->parse_search_results($response['data'], $products);
        } else {
            // Fallback to simple text search
            return $this->fallback_search($query, $products);
        }
    }
    
    /**
     * Build search prompt for ChatGPT
     */
    private function build_search_prompt($query, $product_summaries) {
        $products_text = '';
        foreach ($product_summaries as $product) {
            $products_text .= "ID: {$product['id']} - {$product['summary']} - Price: {$product['price']} - In Stock: " . ($product['in_stock'] ? 'Yes' : 'No') . "\n";
        }
        
        return "You are an AI shopping assistant for an online store. A customer is searching for: \"{$query}\"

Available products:
{$products_text}

Please analyze the customer's query and find the best matching products. Consider:
1. Product names and descriptions
2. Categories and tags
3. Price range if mentioned
4. Specific features or attributes
5. Stock availability

Return ONLY a JSON array of product IDs in order of relevance (most relevant first). Maximum 5 products.

Example format: [123, 456, 789]

If no products match, return an empty array: []";
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
            'max_tokens' => 500,
            'temperature' => 0.3
        );
        
        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', array(
            'headers' => $headers,
            'body' => json_encode($data),
            'timeout' => 30
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
     * Parse search results from ChatGPT
     */
    private function parse_search_results($response, $products) {
        // Try to extract JSON from response
        $json_start = strpos($response, '[');
        $json_end = strrpos($response, ']') + 1;
        
        if ($json_start !== false && $json_end !== false) {
            $json_string = substr($response, $json_start, $json_end - $json_start);
            $product_ids = json_decode($json_string, true);
            
            if (is_array($product_ids)) {
                $matches = array();
                foreach ($product_ids as $product_id) {
                    $product = $this->find_product_by_id($product_id, $products);
                    if ($product) {
                        $matches[] = $product;
                    }
                }
                return $matches;
            }
        }
        
        // Fallback to simple search
        return $this->fallback_search($response, $products);
    }
    
    /**
     * Find product by ID
     */
    private function find_product_by_id($product_id, $products) {
        foreach ($products as $product) {
            if ($product['id'] == $product_id) {
                return $product;
            }
        }
        return null;
    }
    
    /**
     * Fallback search using WordPress search
     */
    private function fallback_search($query, $products) {
        $matches = array();
        $query_lower = strtolower($query);
        $query_words = explode(' ', $query_lower);
        
        foreach ($products as $product) {
            $score = 0;
            
            // Check product name
            $name_lower = strtolower($product['name']);
            foreach ($query_words as $word) {
                if (strpos($name_lower, $word) !== false) {
                    $score += 3;
                }
            }
            
            // Check description
            $desc_lower = strtolower($product['description'] . ' ' . $product['short_description']);
            foreach ($query_words as $word) {
                if (strpos($desc_lower, $word) !== false) {
                    $score += 2;
                }
            }
            
            // Check categories
            foreach ($product['categories'] as $category) {
                $cat_lower = strtolower($category);
                foreach ($query_words as $word) {
                    if (strpos($cat_lower, $word) !== false) {
                        $score += 2;
                    }
                }
            }
            
            // Check tags
            foreach ($product['tags'] as $tag) {
                $tag_lower = strtolower($tag);
                foreach ($query_words as $word) {
                    if (strpos($tag_lower, $word) !== false) {
                        $score += 1;
                    }
                }
            }
            
            if ($score > 0) {
                $product['relevance_score'] = $score;
                $matches[] = $product;
            }
        }
        
        // Sort by relevance score
        usort($matches, function($a, $b) {
            return $b['relevance_score'] - $a['relevance_score'];
        });
        
        return array_slice($matches, 0, $this->max_results);
    }
    
    /**
     * Get general suggestions when no matches found
     */
    private function get_general_suggestions() {
        $suggestions = array();
        
        // Get popular categories
        $categories = get_terms(array(
            'taxonomy' => 'product_cat',
            'hide_empty' => true,
            'number' => 5,
            'orderby' => 'count',
            'order' => 'DESC'
        ));
        
        foreach ($categories as $category) {
            $suggestions[] = array(
                'type' => 'category',
                'name' => $category->name,
                'url' => get_term_link($category),
                'count' => $category->count
            );
        }
        
        // Get featured products
        $featured_products = wc_get_products(array(
            'limit' => 3,
            'featured' => true,
            'status' => 'publish'
        ));
        
        foreach ($featured_products as $product) {
            $suggestions[] = array(
                'type' => 'product',
                'name' => $product->get_name(),
                'url' => $product->get_permalink(),
                'price' => $product->get_price_html(),
                'image' => wp_get_attachment_image_url($product->get_image_id(), 'thumbnail')
            );
        }
        
        return $suggestions;
    }
    
    /**
     * Log search for analytics
     */
    private function log_search($query, $results, $user_id) {
        $log_entry = array(
            'query' => $query,
            'results_count' => count($results),
            'user_id' => $user_id,
            'timestamp' => current_time('mysql'),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        );
        
        $logs = get_option('ai_cw_store_assistant_logs', array());
        $logs[] = $log_entry;
        
        // Keep only last 1000 logs
        if (count($logs) > 1000) {
            $logs = array_slice($logs, -1000);
        }
        
        update_option('ai_cw_store_assistant_logs', $logs);
    }
    
    /**
     * Get search analytics
     */
    public function get_search_analytics($days = 30) {
        $logs = get_option('ai_cw_store_assistant_logs', array());
        $cutoff_date = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        $recent_logs = array_filter($logs, function($log) use ($cutoff_date) {
            return $log['timestamp'] >= $cutoff_date;
        });
        
        $analytics = array(
            'total_searches' => count($recent_logs),
            'unique_queries' => count(array_unique(array_column($recent_logs, 'query'))),
            'avg_results_per_search' => 0,
            'popular_queries' => array(),
            'no_results_queries' => array()
        );
        
        if (!empty($recent_logs)) {
            $total_results = array_sum(array_column($recent_logs, 'results_count'));
            $analytics['avg_results_per_search'] = round($total_results / count($recent_logs), 2);
            
            // Get popular queries
            $query_counts = array_count_values(array_column($recent_logs, 'query'));
            arsort($query_counts);
            $analytics['popular_queries'] = array_slice($query_counts, 0, 10, true);
            
            // Get no results queries
            $no_results = array_filter($recent_logs, function($log) {
                return $log['results_count'] == 0;
            });
            $analytics['no_results_queries'] = array_slice(array_column($no_results, 'query'), 0, 10);
        }
        
        return $analytics;
    }
    
    /**
     * Get search suggestions based on popular queries
     */
    public function get_search_suggestions() {
        $analytics = $this->get_search_analytics(7); // Last 7 days
        
        $suggestions = array();
        
        // Add popular queries as suggestions
        foreach ($analytics['popular_queries'] as $query => $count) {
            $suggestions[] = array(
                'text' => $query,
                'type' => 'popular',
                'count' => $count
            );
        }
        
        // Add category suggestions
        $categories = get_terms(array(
            'taxonomy' => 'product_cat',
            'hide_empty' => true,
            'number' => 5,
            'orderby' => 'count',
            'order' => 'DESC'
        ));
        
        foreach ($categories as $category) {
            $suggestions[] = array(
                'text' => $category->name,
                'type' => 'category',
                'count' => $category->count
            );
        }
        
        return array_slice($suggestions, 0, 10);
    }
}

