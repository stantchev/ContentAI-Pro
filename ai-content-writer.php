<?php
/**
 * Plugin Name: ContentAI Pro
 * Plugin URI: https://stanchev.bg/
 * Description: Перфектен WordPress плъгин за автоматично генериране на SEO-оптимизирано съдържание с AI анализ на бранда и непрекъснато обучение.
 * Version: 1.0.0
 * Author: Stanchev SEO
 * Author URI: https://stanchev.bg/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: contentai-pro
 * Domain Path: /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('CONTENTAI_PRO_VERSION', '1.0.0');
define('CONTENTAI_PRO_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CONTENTAI_PRO_PLUGIN_URL', plugin_dir_url(__FILE__));
define('AI_CONTENT_WRITER_PLUGIN_FILE', __FILE__);

// Main plugin class
class AI_Content_Writer {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('init', array($this, 'init'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    public function init() {
        // Load text domain
        load_plugin_textdomain('ai-content-writer', false, dirname(plugin_basename(__FILE__)) . '/languages');
        
        // Include required files
        $this->include_files();
        
        // Initialize components
        $this->init_components();
        
        // Add admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Add admin scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        
        // Add AJAX handlers
        add_action('wp_ajax_ai_cw_analyze_brand', array($this, 'ajax_analyze_brand'));
        add_action('wp_ajax_ai_cw_scan_content', array($this, 'ajax_scan_content'));
        add_action('wp_ajax_ai_cw_generate_content', array($this, 'ajax_generate_content'));
        add_action('wp_ajax_ai_cw_optimize_seo', array($this, 'ajax_optimize_seo'));
        add_action('wp_ajax_ai_cw_publish_content', array($this, 'ajax_publish_content'));
        add_action('wp_ajax_ai_cw_schedule_content', array($this, 'ajax_schedule_content'));
        add_action('wp_ajax_ai_cw_cancel_scheduled', array($this, 'ajax_cancel_scheduled'));
        add_action('wp_ajax_ai_cw_bulk_schedule', array($this, 'ajax_bulk_schedule'));
        add_action('wp_ajax_ai_cw_analyze_ecommerce', array($this, 'ajax_analyze_ecommerce'));
        add_action('wp_ajax_ai_cw_generate_product_content', array($this, 'ajax_generate_product_content'));
        add_action('wp_ajax_ai_cw_store_assistant_search', array($this, 'ajax_store_assistant_search'));
        add_action('wp_ajax_ai_cw_store_assistant_suggestions', array($this, 'ajax_store_assistant_suggestions'));
        add_action('wp_ajax_nopriv_ai_cw_store_assistant_search', array($this, 'ajax_store_assistant_search'));
        add_action('wp_ajax_nopriv_ai_cw_store_assistant_suggestions', array($this, 'ajax_store_assistant_suggestions'));
        
        // Add cron hooks
        add_action('ai_cw_scheduled_content_generation', array($this, 'process_scheduled_content'));
        
        // Initialize shortcode
        new AI_CW_Store_Assistant_Shortcode();
    }
    
    private function include_files() {
        require_once CONTENTAI_PRO_PLUGIN_DIR . 'includes/class-brand-analyzer.php';
        require_once CONTENTAI_PRO_PLUGIN_DIR . 'includes/class-content-scanner.php';
        require_once CONTENTAI_PRO_PLUGIN_DIR . 'includes/class-seo-optimizer.php';
        require_once CONTENTAI_PRO_PLUGIN_DIR . 'includes/class-content-generator.php';
        require_once CONTENTAI_PRO_PLUGIN_DIR . 'includes/class-scheduler.php';
        require_once CONTENTAI_PRO_PLUGIN_DIR . 'includes/class-learning-system.php';
        require_once CONTENTAI_PRO_PLUGIN_DIR . 'includes/class-seo-integration.php';
        require_once CONTENTAI_PRO_PLUGIN_DIR . 'includes/class-database.php';
        require_once CONTENTAI_PRO_PLUGIN_DIR . 'includes/class-content-scheduler.php';
        require_once CONTENTAI_PRO_PLUGIN_DIR . 'includes/class-ecommerce-analyzer.php';
        require_once CONTENTAI_PRO_PLUGIN_DIR . 'includes/class-store-assistant.php';
        require_once CONTENTAI_PRO_PLUGIN_DIR . 'includes/class-store-assistant-shortcode.php';
    }
    
    private function init_components() {
        $this->brand_analyzer = new AI_CW_Brand_Analyzer();
        $this->content_scanner = new AI_CW_Content_Scanner();
        $this->seo_optimizer = new AI_CW_SEO_Optimizer();
        $this->content_generator = new AI_CW_Content_Generator();
        $this->scheduler = new AI_CW_Scheduler();
        $this->learning_system = new AI_CW_Learning_System();
        $this->seo_integration = new AI_CW_SEO_Integration();
        $this->database = new AI_CW_Database();
    }
    
    public function activate() {
        // Create database tables
        $this->database->create_tables();
        
        // Set default options
        $this->set_default_options();
        
        // Schedule cron jobs
        $this->scheduler->schedule_cron_jobs();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    public function deactivate() {
        // Clear scheduled cron jobs
        $this->scheduler->clear_cron_jobs();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    private function set_default_options() {
        $default_options = array(
            'openai_api_key' => '',
            'auto_publish' => false,
            'content_frequency' => 'weekly',
            'seo_plugin' => 'yoast',
            'brand_analysis_completed' => false,
            'learning_enabled' => true,
            'min_seo_score' => 8
        );
        
        foreach ($default_options as $option => $value) {
            if (get_option('ai_cw_' . $option) === false) {
                add_option('ai_cw_' . $option, $value);
            }
        }
    }
    
    public function add_admin_menu() {
        add_menu_page(
            __('ContentAI Pro', 'contentai-pro'),
            __('ContentAI Pro', 'contentai-pro'),
            'manage_options',
            'contentai-pro',
            array($this, 'admin_dashboard'),
            'dashicons-edit-large',
            30
        );
        
        add_submenu_page(
            'contentai-pro',
            __('Dashboard', 'contentai-pro'),
            __('Dashboard', 'contentai-pro'),
            'manage_options',
            'contentai-pro',
            array($this, 'admin_dashboard')
        );
        
        add_submenu_page(
            'contentai-pro',
            __('Brand Analysis', 'contentai-pro'),
            __('Brand Analysis', 'contentai-pro'),
            'manage_options',
            'contentai-pro-brand',
            array($this, 'admin_brand_analysis')
        );
        
        add_submenu_page(
            'contentai-pro',
            __('Content Scanner', 'contentai-pro'),
            __('Content Scanner', 'contentai-pro'),
            'manage_options',
            'contentai-pro-scanner',
            array($this, 'admin_content_scanner')
        );
        
        add_submenu_page(
            'contentai-pro',
            __('Content Generator', 'contentai-pro'),
            __('Content Generator', 'contentai-pro'),
            'manage_options',
            'contentai-pro-generator',
            array($this, 'admin_content_generator')
        );
        
        add_submenu_page(
            'contentai-pro',
            __('Content Scheduler', 'contentai-pro'),
            __('Content Scheduler', 'contentai-pro'),
            'manage_options',
            'contentai-pro-scheduler',
            array($this, 'admin_content_scheduler')
        );
        
        add_submenu_page(
            'contentai-pro',
            __('E-commerce Analyzer', 'contentai-pro'),
            __('E-commerce Analyzer', 'contentai-pro'),
            'manage_options',
            'contentai-pro-ecommerce',
            array($this, 'admin_ecommerce_analyzer')
        );
        add_submenu_page(
            'contentai-pro',
            __('Store Assistant', 'contentai-pro'),
            __('Store Assistant', 'contentai-pro'),
            'manage_options',
            'contentai-pro-assistant',
            array($this, 'admin_store_assistant')
        );
        
        add_submenu_page(
            'contentai-pro',
            __('Settings', 'contentai-pro'),
            __('Settings', 'contentai-pro'),
            'manage_options',
            'contentai-pro-settings',
            array($this, 'admin_settings')
        );
    }
    
    public function admin_dashboard() {
        include CONTENTAI_PRO_PLUGIN_DIR . 'admin/dashboard.php';
    }
    
    public function admin_brand_analysis() {
        include CONTENTAI_PRO_PLUGIN_DIR . 'admin/brand-analysis.php';
    }
    
    public function admin_content_scanner() {
        include CONTENTAI_PRO_PLUGIN_DIR . 'admin/content-scanner.php';
    }
    
    public function admin_content_generator() {
        include CONTENTAI_PRO_PLUGIN_DIR . 'admin/content-generator.php';
    }
    
    public function admin_content_scheduler() {
        include CONTENTAI_PRO_PLUGIN_DIR . 'admin/content-scheduler.php';
    }
    
    public function admin_ecommerce_analyzer() {
        include CONTENTAI_PRO_PLUGIN_DIR . 'admin/ecommerce-analyzer.php';
    }
    
    public function admin_store_assistant() {
        include CONTENTAI_PRO_PLUGIN_DIR . 'admin/store-assistant.php';
    }
    
    public function admin_settings() {
        include CONTENTAI_PRO_PLUGIN_DIR . 'admin/settings.php';
    }
    
    public function admin_enqueue_scripts($hook) {
        if (strpos($hook, 'contentai-pro') === false) {
            return;
        }
        
        wp_enqueue_script('contentai-pro-admin', CONTENTAI_PRO_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), CONTENTAI_PRO_VERSION, true);
        wp_enqueue_style('contentai-pro-admin', CONTENTAI_PRO_PLUGIN_URL . 'assets/css/admin.css', array(), CONTENTAI_PRO_VERSION);
        
        wp_localize_script('contentai-pro-admin', 'contentai_pro_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ai_cw_nonce'),
            'strings' => array(
                'analyzing' => __('Analyzing...', 'contentai-pro'),
                'scanning' => __('Scanning content...', 'contentai-pro'),
                'generating' => __('Generating content...', 'contentai-pro'),
                'optimizing' => __('Optimizing SEO...', 'contentai-pro'),
                'publishing' => __('Publishing...', 'contentai-pro'),
                'success' => __('Success!', 'contentai-pro'),
                'error' => __('Error occurred', 'contentai-pro')
            )
        ));
    }
    
    // AJAX handlers
    public function ajax_analyze_brand() {
        check_ajax_referer('ai_cw_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'ai-content-writer'));
        }
        
        $result = $this->brand_analyzer->analyze_brand();
        wp_send_json($result);
    }
    
    public function ajax_scan_content() {
        check_ajax_referer('ai_cw_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'ai-content-writer'));
        }
        
        $result = $this->content_scanner->scan_all_content();
        wp_send_json($result);
    }
    
    public function ajax_generate_content() {
        check_ajax_referer('ai_cw_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'ai-content-writer'));
        }
        
        $topic = sanitize_text_field($_POST['topic']);
        $result = $this->content_generator->generate_content($topic);
        wp_send_json($result);
    }
    
    public function ajax_optimize_seo() {
        check_ajax_referer('ai_cw_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'ai-content-writer'));
        }
        
        $content = wp_kses_post($_POST['content']);
        $keyword = sanitize_text_field($_POST['keyword']);
        $result = $this->seo_optimizer->optimize_content($content, $keyword);
        wp_send_json($result);
    }
    
    public function ajax_publish_content() {
        check_ajax_referer('ai_cw_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'ai-content-writer'));
        }
        
        $content_data = $_POST['content_data'];
        $result = $this->content_generator->publish_content($content_data);
        wp_send_json($result);
    }
    
    public function ajax_schedule_content() {
        check_ajax_referer('ai_cw_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'ai-content-writer'));
        }
        
        $content_scheduler = new AI_CW_Content_Scheduler();
        $content_data = array(
            'topic' => sanitize_text_field($_POST['topic']),
            'keyword' => sanitize_text_field($_POST['keyword']),
            'word_count' => intval($_POST['word_count']),
            'tone' => sanitize_text_field($_POST['tone']),
            'scheduled_for' => sanitize_text_field($_POST['scheduled_for']),
            'category' => intval($_POST['category']),
            'tags' => sanitize_text_field($_POST['tags'])
        );
        
        $result = $content_scheduler->schedule_content($content_data);
        wp_send_json($result);
    }
    
    public function ajax_cancel_scheduled() {
        check_ajax_referer('ai_cw_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'ai-content-writer'));
        }
        
        $content_scheduler = new AI_CW_Content_Scheduler();
        $topic = sanitize_text_field($_POST['topic']);
        $result = $content_scheduler->cancel_scheduled_content($topic);
        wp_send_json($result);
    }
    
    public function ajax_bulk_schedule() {
        check_ajax_referer('ai_cw_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'ai-content-writer'));
        }
        
        $content_scheduler = new AI_CW_Content_Scheduler();
        $topics = array_map('trim', explode("\n", $_POST['topics']));
        $start_date = sanitize_text_field($_POST['start_date']);
        $frequency = sanitize_text_field($_POST['frequency']);
        
        $content_list = array();
        $current_date = $start_date;
        
        foreach ($topics as $topic) {
            if (!empty($topic)) {
                $content_list[] = array(
                    'topic' => $topic,
                    'scheduled_for' => $current_date . ' 09:00:00'
                );
                
                // Calculate next date based on frequency
                switch ($frequency) {
                    case 'daily':
                        $current_date = date('Y-m-d', strtotime($current_date . ' +1 day'));
                        break;
                    case 'weekly':
                        $current_date = date('Y-m-d', strtotime($current_date . ' +1 week'));
                        break;
                    case 'bi-weekly':
                        $current_date = date('Y-m-d', strtotime($current_date . ' +2 weeks'));
                        break;
                }
            }
        }
        
        $result = $content_scheduler->bulk_schedule_content($content_list);
        wp_send_json($result);
    }
    
    public function ajax_analyze_ecommerce() {
        check_ajax_referer('ai_cw_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'ai-content-writer'));
        }
        
        $ecommerce_analyzer = new AI_CW_Ecommerce_Analyzer();
        $result = $ecommerce_analyzer->analyze_ecommerce_store();
        wp_send_json($result);
    }
    
    public function ajax_generate_product_content() {
        check_ajax_referer('ai_cw_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'ai-content-writer'));
        }
        
        $ecommerce_analyzer = new AI_CW_Ecommerce_Analyzer();
        $product_id = intval($_POST['product_id']);
        $content_type = sanitize_text_field($_POST['content_type']);
        
        $result = $ecommerce_analyzer->generate_promotional_content($product_id, $content_type);
        
        if ($result['success']) {
            // Create post with generated content
            $product = wc_get_product($product_id);
            $post_data = array(
                'post_title' => sprintf(__('%s - %s', 'ai-content-writer'), $product->get_name(), ucfirst($content_type)),
                'post_content' => $result['content'],
                'post_status' => 'draft',
                'post_type' => 'post',
                'post_author' => get_current_user_id()
            );
            
            $post_id = wp_insert_post($post_data);
            
            if ($post_id) {
                // Add product link
                $product_link = '<p><a href="' . $product->get_permalink() . '" class="button">' . __('View Product', 'ai-content-writer') . '</a></p>';
                wp_update_post(array(
                    'ID' => $post_id,
                    'post_content' => $result['content'] . $product_link
                ));
                
                $result['post_id'] = $post_id;
                $result['message'] = __('Product content generated successfully', 'ai-content-writer');
            }
        }
        
        wp_send_json($result);
    }
    
    public function process_scheduled_content($content_data) {
        $content_scheduler = new AI_CW_Content_Scheduler();
        $result = $content_scheduler->process_scheduled_content($content_data);
        
        if ($result['success']) {
            // Log successful processing
            error_log('AI Content Writer: Scheduled content processed successfully - ' . $content_data['topic']);
        } else {
            // Log error
            error_log('AI Content Writer: Failed to process scheduled content - ' . $result['message']);
        }
    }
    
    public function ajax_store_assistant_search() {
        check_ajax_referer('ai_store_assistant_nonce', 'nonce');
        
        $query = sanitize_text_field($_POST['query']);
        $user_id = get_current_user_id();
        
        if (empty($query)) {
            wp_send_json_error(array('message' => __('Please enter a search query', 'ai-content-writer')));
        }
        
        $store_assistant = new AI_CW_Store_Assistant();
        $result = $store_assistant->process_query($query, $user_id);
        
        wp_send_json($result);
    }
    
    public function ajax_store_assistant_suggestions() {
        check_ajax_referer('ai_store_assistant_nonce', 'nonce');
        
        $store_assistant = new AI_CW_Store_Assistant();
        $suggestions = $store_assistant->get_search_suggestions();
        
        wp_send_json_success($suggestions);
    }
}

// Initialize the plugin
function ai_content_writer_init() {
    return AI_Content_Writer::get_instance();
}

// Start the plugin
ai_content_writer_init();
