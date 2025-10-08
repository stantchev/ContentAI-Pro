<?php
/**
 * Database Class
 * 
 * Handles database operations for the plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class AI_CW_Database {
    
    /**
     * Create database tables
     */
    public function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Content generation logs table
        $table_name = $wpdb->prefix . 'ai_cw_content_logs';
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            post_id bigint(20) NOT NULL,
            title varchar(255) NOT NULL,
            keyword varchar(255) DEFAULT '',
            seo_score int(3) DEFAULT 0,
            word_count int(6) DEFAULT 0,
            generated_at datetime DEFAULT CURRENT_TIMESTAMP,
            generated_by bigint(20) DEFAULT 0,
            status varchar(20) DEFAULT 'published',
            PRIMARY KEY (id),
            KEY post_id (post_id),
            KEY generated_at (generated_at)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Brand analysis history table
        $table_name = $wpdb->prefix . 'ai_cw_brand_analysis';
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            analysis_data longtext NOT NULL,
            analyzed_at datetime DEFAULT CURRENT_TIMESTAMP,
            content_count int(6) DEFAULT 0,
            PRIMARY KEY (id),
            KEY analyzed_at (analyzed_at)
        ) $charset_collate;";
        
        dbDelta($sql);
        
        // Content scan results table
        $table_name = $wpdb->prefix . 'ai_cw_scan_results';
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            scan_data longtext NOT NULL,
            scanned_at datetime DEFAULT CURRENT_TIMESTAMP,
            seo_gaps_count int(6) DEFAULT 0,
            opportunities_count int(6) DEFAULT 0,
            PRIMARY KEY (id),
            KEY scanned_at (scanned_at)
        ) $charset_collate;";
        
        dbDelta($sql);
        
        // Learning insights table
        $table_name = $wpdb->prefix . 'ai_cw_learning_insights';
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            insight_type varchar(50) NOT NULL,
            insight_data longtext NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY insight_type (insight_type),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        dbDelta($sql);
        
        // Scheduled content table
        $table_name = $wpdb->prefix . 'ai_cw_scheduled_content';
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            topic varchar(255) NOT NULL,
            content_options longtext DEFAULT NULL,
            scheduled_for datetime NOT NULL,
            status varchar(20) DEFAULT 'scheduled',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY scheduled_for (scheduled_for),
            KEY status (status)
        ) $charset_collate;";
        
        dbDelta($sql);
    }
    
    /**
     * Drop database tables
     */
    public function drop_tables() {
        global $wpdb;
        
        $tables = array(
            $wpdb->prefix . 'ai_cw_content_logs',
            $wpdb->prefix . 'ai_cw_brand_analysis',
            $wpdb->prefix . 'ai_cw_scan_results',
            $wpdb->prefix . 'ai_cw_learning_insights',
            $wpdb->prefix . 'ai_cw_scheduled_content'
        );
        
        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS $table");
        }
    }
    
    /**
     * Log content generation
     */
    public function log_content_generation($post_id, $title, $keyword, $seo_score, $word_count, $user_id = 0) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_cw_content_logs';
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'post_id' => $post_id,
                'title' => $title,
                'keyword' => $keyword,
                'seo_score' => $seo_score,
                'word_count' => $word_count,
                'generated_by' => $user_id,
                'generated_at' => current_time('mysql')
            ),
            array(
                '%d',
                '%s',
                '%s',
                '%d',
                '%d',
                '%d',
                '%s'
            )
        );
        
        return $result !== false;
    }
    
    /**
     * Get content generation logs
     */
    public function get_content_logs($limit = 20, $offset = 0) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_cw_content_logs';
        
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name 
             ORDER BY generated_at DESC 
             LIMIT %d OFFSET %d",
            $limit,
            $offset
        ));
        
        return $results;
    }
    
    /**
     * Log brand analysis
     */
    public function log_brand_analysis($analysis_data, $content_count) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_cw_brand_analysis';
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'analysis_data' => json_encode($analysis_data),
                'content_count' => $content_count,
                'analyzed_at' => current_time('mysql')
            ),
            array(
                '%s',
                '%d',
                '%s'
            )
        );
        
        return $result !== false;
    }
    
    /**
     * Get brand analysis history
     */
    public function get_brand_analysis_history($limit = 10) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_cw_brand_analysis';
        
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name 
             ORDER BY analyzed_at DESC 
             LIMIT %d",
            $limit
        ));
        
        return $results;
    }
    
    /**
     * Log scan results
     */
    public function log_scan_results($scan_data, $seo_gaps_count, $opportunities_count) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_cw_scan_results';
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'scan_data' => json_encode($scan_data),
                'seo_gaps_count' => $seo_gaps_count,
                'opportunities_count' => $opportunities_count,
                'scanned_at' => current_time('mysql')
            ),
            array(
                '%s',
                '%d',
                '%d',
                '%s'
            )
        );
        
        return $result !== false;
    }
    
    /**
     * Get scan results history
     */
    public function get_scan_results_history($limit = 10) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_cw_scan_results';
        
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name 
             ORDER BY scanned_at DESC 
             LIMIT %d",
            $limit
        ));
        
        return $results;
    }
    
    /**
     * Log learning insight
     */
    public function log_learning_insight($insight_type, $insight_data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_cw_learning_insights';
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'insight_type' => $insight_type,
                'insight_data' => json_encode($insight_data),
                'created_at' => current_time('mysql')
            ),
            array(
                '%s',
                '%s',
                '%s'
            )
        );
        
        return $result !== false;
    }
    
    /**
     * Get learning insights
     */
    public function get_learning_insights($insight_type = '', $limit = 20) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_cw_learning_insights';
        
        if (!empty($insight_type)) {
            $results = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM $table_name 
                 WHERE insight_type = %s 
                 ORDER BY created_at DESC 
                 LIMIT %d",
                $insight_type,
                $limit
            ));
        } else {
            $results = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM $table_name 
                 ORDER BY created_at DESC 
                 LIMIT %d",
                $limit
            ));
        }
        
        return $results;
    }
    
    /**
     * Schedule content
     */
    public function schedule_content($topic, $scheduled_for, $content_options = array()) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_cw_scheduled_content';
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'topic' => $topic,
                'content_options' => json_encode($content_options),
                'scheduled_for' => $scheduled_for,
                'status' => 'scheduled',
                'created_at' => current_time('mysql')
            ),
            array(
                '%s',
                '%s',
                '%s',
                '%s',
                '%s'
            )
        );
        
        return $result !== false;
    }
    
    /**
     * Get scheduled content
     */
    public function get_scheduled_content($status = 'scheduled') {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_cw_scheduled_content';
        
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name 
             WHERE status = %s 
             ORDER BY scheduled_for ASC",
            $status
        ));
        
        return $results;
    }
    
    /**
     * Update scheduled content status
     */
    public function update_scheduled_content_status($id, $status) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_cw_scheduled_content';
        
        $result = $wpdb->update(
            $table_name,
            array('status' => $status),
            array('id' => $id),
            array('%s'),
            array('%d')
        );
        
        return $result !== false;
    }
    
    /**
     * Get statistics
     */
    public function get_statistics() {
        global $wpdb;
        
        $stats = array();
        
        // Content generation stats
        $content_logs_table = $wpdb->prefix . 'ai_cw_content_logs';
        $stats['total_generated'] = $wpdb->get_var("SELECT COUNT(*) FROM $content_logs_table");
        $stats['avg_seo_score'] = $wpdb->get_var("SELECT AVG(seo_score) FROM $content_logs_table");
        $stats['avg_word_count'] = $wpdb->get_var("SELECT AVG(word_count) FROM $content_logs_table");
        
        // Brand analysis stats
        $brand_analysis_table = $wpdb->prefix . 'ai_cw_brand_analysis';
        $stats['total_analyses'] = $wpdb->get_var("SELECT COUNT(*) FROM $brand_analysis_table");
        
        // Scan results stats
        $scan_results_table = $wpdb->prefix . 'ai_cw_scan_results';
        $stats['total_scans'] = $wpdb->get_var("SELECT COUNT(*) FROM $scan_results_table");
        
        // Learning insights stats
        $learning_insights_table = $wpdb->prefix . 'ai_cw_learning_insights';
        $stats['total_insights'] = $wpdb->get_var("SELECT COUNT(*) FROM $learning_insights_table");
        
        return $stats;
    }
    
    /**
     * Clean old data
     */
    public function clean_old_data($days = 365) {
        global $wpdb;
        
        $cutoff_date = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        // Clean old content logs
        $content_logs_table = $wpdb->prefix . 'ai_cw_content_logs';
        $wpdb->query($wpdb->prepare(
            "DELETE FROM $content_logs_table WHERE generated_at < %s",
            $cutoff_date
        ));
        
        // Clean old scan results
        $scan_results_table = $wpdb->prefix . 'ai_cw_scan_results';
        $wpdb->query($wpdb->prepare(
            "DELETE FROM $scan_results_table WHERE scanned_at < %s",
            $cutoff_date
        ));
        
        // Clean old learning insights
        $learning_insights_table = $wpdb->prefix . 'ai_cw_learning_insights';
        $wpdb->query($wpdb->prepare(
            "DELETE FROM $learning_insights_table WHERE created_at < %s",
            $cutoff_date
        ));
        
        return true;
    }
}
