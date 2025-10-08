<?php
/**
 * Store Assistant Shortcode Class
 * 
 * Handles shortcode functionality for the store assistant
 */

if (!defined('ABSPATH')) {
    exit;
}

class AI_CW_Store_Assistant_Shortcode {
    
    private $store_assistant;
    
    public function __construct() {
        $this->store_assistant = new AI_CW_Store_Assistant();
        add_shortcode('ai_store_assistant', array($this, 'render_shortcode'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    }
    
    /**
     * Render the store assistant shortcode
     */
    public function render_shortcode($atts) {
        $atts = shortcode_atts(array(
            'style' => 'button', // button, inline, modal
            'position' => 'bottom-right', // bottom-right, bottom-left, top-right, top-left
            'text' => 'AI Assistant',
            'icon' => '🤖',
            'width' => '600px',
            'height' => '400px'
        ), $atts);
        
        $this->enqueue_scripts();
        
        $output = '';
        
        switch ($atts['style']) {
            case 'inline':
                $output = $this->render_inline_assistant($atts);
                break;
            case 'modal':
                $output = $this->render_modal_assistant($atts);
                break;
            default:
                $output = $this->render_button_assistant($atts);
                break;
        }
        
        return $output;
    }
    
    /**
     * Render button style assistant
     */
    private function render_button_assistant($atts) {
        $position_class = 'ai-assistant-' . str_replace('-', '-', $atts['position']);
        
        return '<div class="ai-store-assistant-button ' . $position_class . '" style="position: fixed;">' .
            '<span class="ai-assistant-icon">' . esc_html($atts['icon']) . '</span>' .
            '<span class="ai-assistant-text">' . esc_html($atts['text']) . '</span>' .
        '</div>';
    }
    
    /**
     * Render inline style assistant
     */
    private function render_inline_assistant($atts) {
        return '<div class="ai-store-assistant-inline" style="width: ' . esc_attr($atts['width']) . '; height: ' . esc_attr($atts['height']) . ';">' .
            '<div class="ai-assistant-content">' .
                '<div class="ai-assistant-header">' .
                    '<h3>AI Shopping Assistant</h3>' .
                '</div>' .
                '<div class="ai-assistant-body">' .
                    '<div class="ai-assistant-search">' .
                        '<input type="text" class="ai-assistant-input" placeholder="Describe what you\'re looking for...">' .
                        '<button class="ai-assistant-search-btn">Search</button>' .
                    '</div>' .
                    '<div class="ai-assistant-suggestions"></div>' .
                    '<div class="ai-assistant-results"></div>' .
                    '<div class="ai-assistant-loading" style="display: none;">' .
                        '<div class="ai-loading-spinner"></div>' .
                        '<p>Searching products...</p>' .
                    '</div>' .
                '</div>' .
            '</div>' .
        '</div>';
    }
    
    /**
     * Render modal style assistant
     */
    private function render_modal_assistant($atts) {
        return '<div class="ai-store-assistant-modal" style="display: none;">' .
            '<div class="ai-assistant-content" style="max-width: ' . esc_attr($atts['width']) . ';">' .
                '<div class="ai-assistant-header">' .
                    '<h3>AI Shopping Assistant</h3>' .
                    '<button class="ai-assistant-close">&times;</button>' .
                '</div>' .
                '<div class="ai-assistant-body">' .
                    '<div class="ai-assistant-search">' .
                        '<input type="text" class="ai-assistant-input" placeholder="Describe what you\'re looking for...">' .
                        '<button class="ai-assistant-search-btn">Search</button>' .
                    '</div>' .
                    '<div class="ai-assistant-suggestions"></div>' .
                    '<div class="ai-assistant-results"></div>' .
                    '<div class="ai-assistant-loading" style="display: none;">' .
                        '<div class="ai-loading-spinner"></div>' .
                        '<p>Searching products...</p>' .
                    '</div>' .
                '</div>' .
            '</div>' .
        '</div>' .
        '<div class="ai-store-assistant-button" style="position: fixed;">' .
            '<span class="ai-assistant-icon">' . esc_html($atts['icon']) . '</span>' .
            '<span class="ai-assistant-text">' . esc_html($atts['text']) . '</span>' .
        '</div>';
    }
    
    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts() {
        // Only enqueue if shortcode is used
        if (!is_admin() && (has_shortcode(get_post()->post_content ?? '', 'ai_store_assistant') || is_shop() || is_product_category() || is_product_tag())) {
            wp_enqueue_script('ai-store-assistant', CONTENTAI_PRO_PLUGIN_URL . 'assets/js/store-assistant.js', array('jquery'), CONTENTAI_PRO_VERSION, true);
            wp_enqueue_style('ai-store-assistant', CONTENTAI_PRO_PLUGIN_URL . 'assets/css/store-assistant.css', array(), CONTENTAI_PRO_VERSION);
            
            wp_localize_script('ai-store-assistant', 'ai_store_assistant', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('ai_store_assistant_nonce'),
                'strings' => array(
                    'searching' => __('Searching products...', 'contentai-pro'),
                    'no_results' => __('No products found', 'contentai-pro'),
                    'error' => __('Search failed. Please try again.', 'contentai-pro'),
                    'min_length' => __('Please enter at least 3 characters', 'contentai-pro')
                )
            ));
        }
    }
}

