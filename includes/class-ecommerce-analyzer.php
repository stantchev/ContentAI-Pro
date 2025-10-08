<?php
/**
 * E-commerce Analyzer Class
 * 
 * Deep analysis for online stores with product integration
 */

if (!defined('ABSPATH')) {
    exit;
}

class AI_CW_Ecommerce_Analyzer {
    
    private $brand_analyzer;
    private $content_generator;
    private $openai_api_key;
    private $openai_model = 'gpt-4';
    
    public function __construct() {
        $this->brand_analyzer = new AI_CW_Brand_Analyzer();
        $this->content_generator = new AI_CW_Content_Generator();
        $this->openai_api_key = get_option('ai_cw_openai_api_key', '');
    }
    
    /**
     * Analyze e-commerce store for content opportunities
     */
    public function analyze_ecommerce_store() {
        try {
            if (!class_exists('WooCommerce')) {
                return array(
                    'success' => false,
                    'message' => __('WooCommerce is not installed or activated', 'ai-content-writer')
                );
            }
            
            $analysis = array(
                'products' => $this->analyze_products(),
                'categories' => $this->analyze_categories(),
                'customers' => $this->analyze_customers(),
                'sales_data' => $this->analyze_sales_data(),
                'content_opportunities' => $this->find_content_opportunities(),
                'product_content_suggestions' => $this->get_product_content_suggestions(),
                'seasonal_opportunities' => $this->get_seasonal_opportunities(),
                'competitor_analysis' => $this->analyze_competitors(),
                'seo_opportunities' => $this->find_seo_opportunities()
            );
            
            // Save analysis results
            update_option('ai_cw_ecommerce_analysis', $analysis);
            update_option('ai_cw_ecommerce_analysis_date', current_time('mysql'));
            
            return array(
                'success' => true,
                'message' => __('E-commerce analysis completed successfully', 'ai-content-writer'),
                'data' => $analysis
            );
            
        } catch (Exception $e) {
            return array(
                'success' => false,
                'message' => sprintf(__('Error during e-commerce analysis: %s', 'ai-content-writer'), $e->getMessage())
            );
        }
    }
    
    /**
     * Analyze products for content opportunities
     */
    private function analyze_products() {
        $products = wc_get_products(array(
            'limit' => -1,
            'status' => 'publish'
        ));
        
        $analysis = array(
            'total_products' => count($products),
            'products_without_content' => array(),
            'popular_products' => array(),
            'low_performing_products' => array(),
            'new_products' => array(),
            'seasonal_products' => array()
        );
        
        foreach ($products as $product) {
            $product_id = $product->get_id();
            
            // Check if product has content
            $content = $product->get_description();
            if (empty($content) || strlen($content) < 100) {
                $analysis['products_without_content'][] = array(
                    'id' => $product_id,
                    'name' => $product->get_name(),
                    'url' => $product->get_permalink(),
                    'content_length' => strlen($content)
                );
            }
            
            // Get product performance data
            $sales = $this->get_product_sales($product_id);
            $views = $this->get_product_views($product_id);
            
            if ($sales > 10) {
                $analysis['popular_products'][] = array(
                    'id' => $product_id,
                    'name' => $product->get_name(),
                    'sales' => $sales,
                    'views' => $views,
                    'conversion_rate' => $views > 0 ? ($sales / $views) * 100 : 0
                );
            }
            
            if ($sales < 2 && $views > 50) {
                $analysis['low_performing_products'][] = array(
                    'id' => $product_id,
                    'name' => $product->get_name(),
                    'sales' => $sales,
                    'views' => $views,
                    'potential' => 'high'
                );
            }
            
            // Check if product is new (less than 30 days)
            $created_date = $product->get_date_created();
            if ($created_date && $created_date->diff(new DateTime())->days < 30) {
                $analysis['new_products'][] = array(
                    'id' => $product_id,
                    'name' => $product->get_name(),
                    'created_date' => $created_date->format('Y-m-d'),
                    'days_old' => $created_date->diff(new DateTime())->days
                );
            }
            
            // Check for seasonal products
            $tags = wp_get_post_terms($product_id, 'product_tag', array('fields' => 'names'));
            $seasonal_tags = array('summer', 'winter', 'spring', 'autumn', 'christmas', 'halloween', 'valentine', 'easter');
            
            foreach ($tags as $tag) {
                if (in_array(strtolower($tag), $seasonal_tags)) {
                    $analysis['seasonal_products'][] = array(
                        'id' => $product_id,
                        'name' => $product->get_name(),
                        'seasonal_tag' => $tag
                    );
                    break;
                }
            }
        }
        
        return $analysis;
    }
    
    /**
     * Analyze product categories
     */
    private function analyze_categories() {
        $categories = get_terms(array(
            'taxonomy' => 'product_cat',
            'hide_empty' => true
        ));
        
        $analysis = array(
            'total_categories' => count($categories),
            'categories_without_content' => array(),
            'popular_categories' => array(),
            'underperforming_categories' => array()
        );
        
        foreach ($categories as $category) {
            $category_id = $category->term_id;
            $products_in_category = get_posts(array(
                'post_type' => 'product',
                'tax_query' => array(
                    array(
                        'taxonomy' => 'product_cat',
                        'field' => 'term_id',
                        'terms' => $category_id
                    )
                ),
                'posts_per_page' => -1
            ));
            
            $product_count = count($products_in_category);
            $total_sales = 0;
            
            foreach ($products_in_category as $product_post) {
                $product = wc_get_product($product_post->ID);
                $total_sales += $this->get_product_sales($product_post->ID);
            }
            
            // Check if category has content
            $category_description = $category->description;
            if (empty($category_description) || strlen($category_description) < 50) {
                $analysis['categories_without_content'][] = array(
                    'id' => $category_id,
                    'name' => $category->name,
                    'product_count' => $product_count,
                    'description_length' => strlen($category_description)
                );
            }
            
            if ($total_sales > 50) {
                $analysis['popular_categories'][] = array(
                    'id' => $category_id,
                    'name' => $category->name,
                    'product_count' => $product_count,
                    'total_sales' => $total_sales,
                    'avg_sales_per_product' => $product_count > 0 ? $total_sales / $product_count : 0
                );
            }
            
            if ($total_sales < 10 && $product_count > 5) {
                $analysis['underperforming_categories'][] = array(
                    'id' => $category_id,
                    'name' => $category->name,
                    'product_count' => $product_count,
                    'total_sales' => $total_sales,
                    'potential' => 'high'
                );
            }
        }
        
        return $analysis;
    }
    
    /**
     * Analyze customer data
     */
    private function analyze_customers() {
        $customers = get_users(array(
            'role' => 'customer',
            'number' => 100
        ));
        
        $analysis = array(
            'total_customers' => count($customers),
            'customer_segments' => array(),
            'purchase_patterns' => array(),
            'content_preferences' => array()
        );
        
        // Analyze customer segments
        $segments = array(
            'new_customers' => 0,
            'returning_customers' => 0,
            'high_value_customers' => 0,
            'inactive_customers' => 0
        );
        
        foreach ($customers as $customer) {
            $user_id = $customer->ID;
            $orders = wc_get_orders(array(
                'customer_id' => $user_id,
                'limit' => -1
            ));
            
            $order_count = count($orders);
            $total_spent = 0;
            
            foreach ($orders as $order) {
                $total_spent += $order->get_total();
            }
            
            if ($order_count == 1) {
                $segments['new_customers']++;
            } elseif ($order_count > 1) {
                $segments['returning_customers']++;
            }
            
            if ($total_spent > 500) {
                $segments['high_value_customers']++;
            }
            
            if ($order_count > 0) {
                $last_order = $orders[0];
                $last_order_date = $last_order->get_date_created();
                if ($last_order_date->diff(new DateTime())->days > 90) {
                    $segments['inactive_customers']++;
                }
            }
        }
        
        $analysis['customer_segments'] = $segments;
        
        return $analysis;
    }
    
    /**
     * Analyze sales data
     */
    private function analyze_sales_data() {
        $orders = wc_get_orders(array(
            'limit' => -1,
            'status' => 'completed',
            'date_created' => date('Y-m-d', strtotime('-1 year')) . '...' . date('Y-m-d')
        ));
        
        $analysis = array(
            'total_orders' => count($orders),
            'total_revenue' => 0,
            'average_order_value' => 0,
            'monthly_sales' => array(),
            'top_selling_products' => array(),
            'seasonal_trends' => array()
        );
        
        $monthly_sales = array();
        $product_sales = array();
        
        foreach ($orders as $order) {
            $total = $order->get_total();
            $analysis['total_revenue'] += $total;
            
            $order_date = $order->get_date_created();
            $month = $order_date->format('Y-m');
            
            if (!isset($monthly_sales[$month])) {
                $monthly_sales[$month] = 0;
            }
            $monthly_sales[$month] += $total;
            
            // Get products from order
            foreach ($order->get_items() as $item) {
                $product_id = $item->get_product_id();
                if (!isset($product_sales[$product_id])) {
                    $product_sales[$product_id] = 0;
                }
                $product_sales[$product_id] += $item->get_quantity();
            }
        }
        
        $analysis['monthly_sales'] = $monthly_sales;
        $analysis['average_order_value'] = count($orders) > 0 ? $analysis['total_revenue'] / count($orders) : 0;
        
        // Get top selling products
        arsort($product_sales);
        $top_products = array_slice($product_sales, 0, 10, true);
        
        foreach ($top_products as $product_id => $quantity) {
            $product = wc_get_product($product_id);
            if ($product) {
                $analysis['top_selling_products'][] = array(
                    'id' => $product_id,
                    'name' => $product->get_name(),
                    'quantity_sold' => $quantity
                );
            }
        }
        
        return $analysis;
    }
    
    /**
     * Find content opportunities
     */
    private function find_content_opportunities() {
        $opportunities = array();
        
        // Product guides
        $products = wc_get_products(array('limit' => 20));
        foreach ($products as $product) {
            $opportunities[] = array(
                'type' => 'product_guide',
                'title' => sprintf(__('Complete Guide to %s', 'ai-content-writer'), $product->get_name()),
                'product_id' => $product->get_id(),
                'priority' => 'high',
                'description' => sprintf(__('Comprehensive guide for %s', 'ai-content-writer'), $product->get_name())
            );
        }
        
        // Category guides
        $categories = get_terms(array(
            'taxonomy' => 'product_cat',
            'hide_empty' => true,
            'number' => 10
        ));
        
        foreach ($categories as $category) {
            $opportunities[] = array(
                'type' => 'category_guide',
                'title' => sprintf(__('Best %s Products 2024', 'ai-content-writer'), $category->name),
                'category_id' => $category->term_id,
                'priority' => 'medium',
                'description' => sprintf(__('Product recommendations for %s', 'ai-content-writer'), $category->name)
            );
        }
        
        // Comparison articles
        $product_pairs = $this->get_product_pairs_for_comparison();
        foreach ($product_pairs as $pair) {
            $opportunities[] = array(
                'type' => 'comparison',
                'title' => sprintf(__('%s vs %s: Which is Better?', 'ai-content-writer'), $pair['product1'], $pair['product2']),
                'product_ids' => array($pair['id1'], $pair['id2']),
                'priority' => 'medium',
                'description' => sprintf(__('Detailed comparison between %s and %s', 'ai-content-writer'), $pair['product1'], $pair['product2'])
            );
        }
        
        return $opportunities;
    }
    
    /**
     * Get product content suggestions
     */
    public function get_product_content_suggestions($product_id = null) {
        if ($product_id) {
            $product = wc_get_product($product_id);
            if (!$product) {
                return array();
            }
            
            return $this->get_single_product_suggestions($product);
        } else {
            return $this->get_all_products_suggestions();
        }
    }
    
    /**
     * Get suggestions for single product
     */
    private function get_single_product_suggestions($product) {
        $suggestions = array();
        
        // Product review article
        $suggestions[] = array(
            'type' => 'product_review',
            'title' => sprintf(__('%s Review: Is It Worth It?', 'ai-content-writer'), $product->get_name()),
            'product_id' => $product->get_id(),
            'priority' => 'high',
            'description' => sprintf(__('In-depth review of %s', 'ai-content-writer'), $product->get_name())
        );
        
        // How to use guide
        $suggestions[] = array(
            'type' => 'how_to_guide',
            'title' => sprintf(__('How to Use %s: Complete Tutorial', 'ai-content-writer'), $product->get_name()),
            'product_id' => $product->get_id(),
            'priority' => 'high',
            'description' => sprintf(__('Step-by-step guide for using %s', 'ai-content-writer'), $product->get_name())
        );
        
        // Benefits article
        $suggestions[] = array(
            'type' => 'benefits_article',
            'title' => sprintf(__('10 Benefits of %s', 'ai-content-writer'), $product->get_name()),
            'product_id' => $product->get_id(),
            'priority' => 'medium',
            'description' => sprintf(__('Key benefits and advantages of %s', 'ai-content-writer'), $product->get_name())
        );
        
        // FAQ article
        $suggestions[] = array(
            'type' => 'faq_article',
            'title' => sprintf(__('%s FAQ: Everything You Need to Know', 'ai-content-writer'), $product->get_name()),
            'product_id' => $product->get_id(),
            'priority' => 'medium',
            'description' => sprintf(__('Frequently asked questions about %s', 'ai-content-writer'), $product->get_name())
        );
        
        return $suggestions;
    }
    
    /**
     * Get suggestions for all products
     */
    private function get_all_products_suggestions() {
        $suggestions = array();
        
        // Get popular products
        $popular_products = $this->get_popular_products();
        
        foreach ($popular_products as $product) {
            $product_suggestions = $this->get_single_product_suggestions($product);
            $suggestions = array_merge($suggestions, $product_suggestions);
        }
        
        return $suggestions;
    }
    
    /**
     * Get seasonal opportunities
     */
    private function get_seasonal_opportunities() {
        $current_month = date('n');
        $opportunities = array();
        
        $seasonal_content = array(
            1 => array(
                'New Year Fitness Products',
                'New Year Home Organization',
                'New Year Tech Gadgets'
            ),
            2 => array(
                'Valentine\'s Day Gifts',
                'Romantic Home Decor',
                'Couples Products'
            ),
            3 => array(
                'Spring Cleaning Products',
                'Garden and Outdoor Items',
                'Spring Fashion'
            ),
            4 => array(
                'Easter Decorations',
                'Spring Home Decor',
                'Easter Gifts'
            ),
            5 => array(
                'Mother\'s Day Gifts',
                'Women\'s Products',
                'Family Products'
            ),
            6 => array(
                'Father\'s Day Gifts',
                'Men\'s Products',
                'Summer Products'
            ),
            7 => array(
                'Summer Vacation Products',
                'Beach and Pool Items',
                'Summer Fashion'
            ),
            8 => array(
                'Back to School Products',
                'Educational Items',
                'Student Essentials'
            ),
            9 => array(
                'Fall Fashion',
                'Home Heating Products',
                'Autumn Decor'
            ),
            10 => array(
                'Halloween Decorations',
                'Costume Accessories',
                'Spooky Products'
            ),
            11 => array(
                'Thanksgiving Products',
                'Fall Home Decor',
                'Gratitude Items'
            ),
            12 => array(
                'Christmas Decorations',
                'Holiday Gifts',
                'Winter Products'
            )
        );
        
        if (isset($seasonal_content[$current_month])) {
            foreach ($seasonal_content[$current_month] as $topic) {
                $opportunities[] = array(
                    'type' => 'seasonal',
                    'title' => $topic,
                    'priority' => 'high',
                    'description' => sprintf(__('Seasonal content for %s', 'ai-content-writer'), date('F')),
                    'suggested_date' => $this->get_optimal_publish_date()
                );
            }
        }
        
        return $opportunities;
    }
    
    /**
     * Analyze competitors
     */
    private function analyze_competitors() {
        // This would integrate with external APIs in production
        return array(
            'status' => 'not_implemented',
            'message' => __('Competitor analysis requires external API integration', 'ai-content-writer')
        );
    }
    
    /**
     * Find SEO opportunities
     */
    private function find_seo_opportunities() {
        $opportunities = array();
        
        // Products without SEO content
        $products = wc_get_products(array('limit' => 50));
        foreach ($products as $product) {
            $seo_title = get_post_meta($product->get_id(), '_yoast_wpseo_title', true);
            $seo_description = get_post_meta($product->get_id(), '_yoast_wpseo_metadesc', true);
            
            if (empty($seo_title) || empty($seo_description)) {
                $opportunities[] = array(
                    'type' => 'missing_seo',
                    'product_id' => $product->get_id(),
                    'product_name' => $product->get_name(),
                    'missing' => array(
                        'title' => empty($seo_title),
                        'description' => empty($seo_description)
                    ),
                    'priority' => 'high'
                );
            }
        }
        
        return $opportunities;
    }
    
    /**
     * Generate promotional content for product
     */
    public function generate_promotional_content($product_id, $content_type = 'review') {
        try {
            $product = wc_get_product($product_id);
            if (!$product) {
                return array(
                    'success' => false,
                    'message' => __('Product not found', 'ai-content-writer')
                );
            }
            
            $brand_profile = $this->brand_analyzer->get_brand_profile();
            
            $prompt = $this->build_promotional_prompt($product, $content_type, $brand_profile);
            
            $response = $this->call_openai_api($prompt);
            
            if ($response['success']) {
                return array(
                    'success' => true,
                    'content' => $response['data'],
                    'product_id' => $product_id,
                    'content_type' => $content_type
                );
            } else {
                return $response;
            }
            
        } catch (Exception $e) {
            return array(
                'success' => false,
                'message' => sprintf(__('Error generating promotional content: %s', 'ai-content-writer'), $e->getMessage())
            );
        }
    }
    
    /**
     * Build promotional prompt
     */
    private function build_promotional_prompt($product, $content_type, $brand_profile) {
        $product_name = $product->get_name();
        $product_description = $product->get_description();
        $product_price = $product->get_price();
        $product_categories = wp_get_post_terms($product->get_id(), 'product_cat', array('fields' => 'names'));
        
        $brand_guidelines = '';
        if (!empty($brand_profile['brand_guidelines'])) {
            $brand_guidelines = "Brand Guidelines: " . json_encode($brand_profile['brand_guidelines']);
        }
        
        $prompt = "Write a {$content_type} article about the product '{$product_name}'.
        
Product Details:
- Name: {$product_name}
- Description: {$product_description}
- Price: {$product_price}
- Categories: " . implode(', ', $product_categories) . "

{$brand_guidelines}

Requirements:
- Write in an engaging, informative style
- Include product benefits and features
- Add a call-to-action to purchase the product
- Optimize for SEO with relevant keywords
- Include internal links to the product page
- Make it 1000-1500 words
- Use proper heading structure (H2, H3)
- Include product specifications and details

Please write only the content without any additional commentary.";

        return $prompt;
    }
    
    /**
     * Call OpenAI API
     */
    private function call_openai_api($prompt) {
        if (empty($this->openai_api_key)) {
            return array(
                'success' => false,
                'message' => __('OpenAI API key not configured', 'ai-content-writer')
            );
        }
        
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
     * Helper methods
     */
    private function get_product_sales($product_id) {
        global $wpdb;
        
        $sales = $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(quantity) FROM {$wpdb->prefix}woocommerce_order_items oi
             JOIN {$wpdb->prefix}woocommerce_order_itemmeta oim ON oi.order_item_id = oim.order_item_id
             JOIN {$wpdb->prefix}posts p ON oi.order_id = p.ID
             WHERE oim.meta_key = '_product_id' AND oim.meta_value = %d
             AND p.post_status = 'wc-completed'",
            $product_id
        ));
        
        return intval($sales);
    }
    
    private function get_product_views($product_id) {
        // This would integrate with analytics in production
        return rand(10, 1000);
    }
    
    private function get_product_pairs_for_comparison() {
        $products = wc_get_products(array('limit' => 10));
        $pairs = array();
        
        for ($i = 0; $i < count($products) - 1; $i += 2) {
            $pairs[] = array(
                'id1' => $products[$i]->get_id(),
                'product1' => $products[$i]->get_name(),
                'id2' => $products[$i + 1]->get_id(),
                'product2' => $products[$i + 1]->get_name()
            );
        }
        
        return $pairs;
    }
    
    private function get_popular_products() {
        return wc_get_products(array(
            'limit' => 10,
            'orderby' => 'popularity',
            'order' => 'DESC'
        ));
    }
    
    private function get_optimal_publish_date() {
        return date('Y-m-d H:i:s', strtotime('+1 day 09:00:00'));
    }
    
    /**
     * Get analysis results
     */
    public function get_analysis_results() {
        return get_option('ai_cw_ecommerce_analysis', array());
    }
    
    /**
     * Get analysis date
     */
    public function get_analysis_date() {
        return get_option('ai_cw_ecommerce_analysis_date', '');
    }
}

