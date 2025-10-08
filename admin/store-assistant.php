<?php
/**
 * Store Assistant Admin Page
 */

if (!defined('ABSPATH')) {
    exit;
}

$store_assistant = new AI_CW_Store_Assistant();
$analytics = $store_assistant->get_search_analytics(30);
?>

<div class="wrap">
    <h1><?php _e('AI Store Assistant', 'contentai-pro'); ?></h1>
    
    <div class="ai-cw-admin-container">
        <!-- Settings Section -->
        <div class="ai-cw-section">
            <h2><?php _e('Assistant Settings', 'contentai-pro'); ?></h2>
            
            <form method="post" action="options.php">
                <?php settings_fields('ai_cw_store_assistant_settings'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="ai_cw_assistant_enabled"><?php _e('Enable Store Assistant', 'contentai-pro'); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" id="ai_cw_assistant_enabled" name="ai_cw_assistant_enabled" value="1" <?php checked(get_option('ai_cw_assistant_enabled', 1)); ?> />
                            <p class="description"><?php _e('Enable the AI assistant for product search', 'contentai-pro'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="ai_cw_assistant_style"><?php _e('Assistant Style', 'contentai-pro'); ?></label>
                        </th>
                        <td>
                            <select id="ai_cw_assistant_style" name="ai_cw_assistant_style">
                                <option value="button" <?php selected(get_option('ai_cw_assistant_style', 'button'), 'button'); ?>><?php _e('Floating Button', 'contentai-pro'); ?></option>
                                <option value="inline" <?php selected(get_option('ai_cw_assistant_style', 'button'), 'inline'); ?>><?php _e('Inline Widget', 'contentai-pro'); ?></option>
                                <option value="modal" <?php selected(get_option('ai_cw_assistant_style', 'button'), 'modal'); ?>><?php _e('Modal Popup', 'contentai-pro'); ?></option>
                            </select>
                            <p class="description"><?php _e('Choose how the assistant appears on your site', 'contentai-pro'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="ai_cw_assistant_position"><?php _e('Button Position', 'contentai-pro'); ?></label>
                        </th>
                        <td>
                            <select id="ai_cw_assistant_position" name="ai_cw_assistant_position">
                                <option value="bottom-right" <?php selected(get_option('ai_cw_assistant_position', 'bottom-right'), 'bottom-right'); ?>><?php _e('Bottom Right', 'contentai-pro'); ?></option>
                                <option value="bottom-left" <?php selected(get_option('ai_cw_assistant_position', 'bottom-right'), 'bottom-left'); ?>><?php _e('Bottom Left', 'contentai-pro'); ?></option>
                                <option value="top-right" <?php selected(get_option('ai_cw_assistant_position', 'bottom-right'), 'top-right'); ?>><?php _e('Top Right', 'contentai-pro'); ?></option>
                                <option value="top-left" <?php selected(get_option('ai_cw_assistant_position', 'bottom-right'), 'top-left'); ?>><?php _e('Top Left', 'contentai-pro'); ?></option>
                            </select>
                            <p class="description"><?php _e('Position of the floating assistant button', 'contentai-pro'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="ai_cw_assistant_text"><?php _e('Button Text', 'contentai-pro'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="ai_cw_assistant_text" name="ai_cw_assistant_text" value="<?php echo esc_attr(get_option('ai_cw_assistant_text', 'AI Assistant')); ?>" class="regular-text" />
                            <p class="description"><?php _e('Text displayed on the assistant button', 'contentai-pro'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="ai_cw_assistant_icon"><?php _e('Button Icon', 'contentai-pro'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="ai_cw_assistant_icon" name="ai_cw_assistant_icon" value="<?php echo esc_attr(get_option('ai_cw_assistant_icon', '🤖')); ?>" class="regular-text" />
                            <p class="description"><?php _e('Emoji or icon for the assistant button', 'contentai-pro'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="ai_cw_assistant_max_results"><?php _e('Max Results', 'contentai-pro'); ?></label>
                        </th>
                        <td>
                            <input type="number" id="ai_cw_assistant_max_results" name="ai_cw_assistant_max_results" value="<?php echo esc_attr(get_option('ai_cw_assistant_max_results', 5)); ?>" min="1" max="20" />
                            <p class="description"><?php _e('Maximum number of products to show in search results', 'contentai-pro'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="ai_cw_assistant_min_query_length"><?php _e('Minimum Query Length', 'contentai-pro'); ?></label>
                        </th>
                        <td>
                            <input type="number" id="ai_cw_assistant_min_query_length" name="ai_cw_assistant_min_query_length" value="<?php echo esc_attr(get_option('ai_cw_assistant_min_query_length', 3)); ?>" min="1" max="10" />
                            <p class="description"><?php _e('Minimum characters required to start searching', 'contentai-pro'); ?></p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
        </div>
        
        <!-- Analytics Section -->
        <div class="ai-cw-section">
            <h2><?php _e('Search Analytics', 'contentai-pro'); ?></h2>
            
            <div class="ai-cw-stats-grid">
                <div class="ai-cw-stat-box">
                    <h3><?php echo number_format($analytics['total_searches']); ?></h3>
                    <p><?php _e('Total Searches (30 days)', 'contentai-pro'); ?></p>
                </div>
                
                <div class="ai-cw-stat-box">
                    <h3><?php echo number_format($analytics['unique_queries']); ?></h3>
                    <p><?php _e('Unique Queries', 'contentai-pro'); ?></p>
                </div>
                
                <div class="ai-cw-stat-box">
                    <h3><?php echo $analytics['avg_results_per_search']; ?></h3>
                    <p><?php _e('Avg Results per Search', 'contentai-pro'); ?></p>
                </div>
            </div>
            
            <?php if (!empty($analytics['popular_queries'])): ?>
            <div class="ai-cw-analytics-section">
                <h3><?php _e('Popular Search Queries', 'contentai-pro'); ?></h3>
                <div class="ai-cw-popular-queries">
                    <?php foreach ($analytics['popular_queries'] as $query => $count): ?>
                    <div class="ai-cw-query-item">
                        <span class="ai-cw-query-text"><?php echo esc_html($query); ?></span>
                        <span class="ai-cw-query-count"><?php echo $count; ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($analytics['no_results_queries'])): ?>
            <div class="ai-cw-analytics-section">
                <h3><?php _e('Queries with No Results', 'contentai-pro'); ?></h3>
                <div class="ai-cw-no-results-queries">
                    <?php foreach ($analytics['no_results_queries'] as $query): ?>
                    <div class="ai-cw-query-item">
                        <span class="ai-cw-query-text"><?php echo esc_html($query); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Shortcode Section -->
        <div class="ai-cw-section">
            <h2><?php _e('Shortcode Usage', 'contentai-pro'); ?></h2>
            
            <p><?php _e('Use the following shortcode to display the AI assistant on any page or post:', 'contentai-pro'); ?></p>
            
            <div class="ai-cw-shortcode-examples">
                <h4><?php _e('Basic Usage', 'contentai-pro'); ?></h4>
                <code>[ai_store_assistant]</code>
                
                <h4><?php _e('Customized Button', 'contentai-pro'); ?></h4>
                <code>[ai_store_assistant style="button" text="Help Me Find" icon="🔍" position="bottom-left"]</code>
                
                <h4><?php _e('Inline Widget', 'contentai-pro'); ?></h4>
                <code>[ai_store_assistant style="inline" width="500px" height="300px"]</code>
                
                <h4><?php _e('Modal Popup', 'contentai-pro'); ?></h4>
                <code>[ai_store_assistant style="modal" text="AI Shopping Help" icon="🛍️"]</code>
            </div>
            
            <h4><?php _e('Available Parameters', 'contentai-pro'); ?></h4>
            <ul>
                <li><strong>style</strong> - button, inline, modal (default: button)</li>
                <li><strong>position</strong> - bottom-right, bottom-left, top-right, top-left (default: bottom-right)</li>
                <li><strong>text</strong> - Button text (default: AI Assistant)</li>
                <li><strong>icon</strong> - Button icon/emoji (default: 🤖)</li>
                <li><strong>width</strong> - Width for inline/modal (default: 600px)</li>
                <li><strong>height</strong> - Height for inline (default: 400px)</li>
            </ul>
        </div>
        
        <!-- Test Section -->
        <div class="ai-cw-section">
            <h2><?php _e('Test Assistant', 'contentai-pro'); ?></h2>
            
            <div class="ai-cw-test-assistant">
                <input type="text" id="ai-cw-test-query" placeholder="<?php _e('Enter a test query...', 'contentai-pro'); ?>" class="regular-text" />
                <button type="button" id="ai-cw-test-search" class="button button-primary"><?php _e('Test Search', 'contentai-pro'); ?></button>
                <div id="ai-cw-test-results" class="ai-cw-test-results"></div>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('#ai-cw-test-search').on('click', function() {
        var query = $('#ai-cw-test-query').val();
        var resultsDiv = $('#ai-cw-test-results');
        
        if (!query.trim()) {
            resultsDiv.html('<p class="error"><?php _e('Please enter a test query', 'contentai-pro'); ?></p>');
            return;
        }
        
        resultsDiv.html('<p><?php _e('Testing search...', 'contentai-pro'); ?></p>');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'ai_cw_store_assistant_search',
                query: query,
                nonce: '<?php echo wp_create_nonce('ai_store_assistant_nonce'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    var html = '<h4><?php _e('Test Results:', 'contentai-pro'); ?></h4>';
                    html += '<p><strong><?php _e('Query:', 'contentai-pro'); ?></strong> ' + response.data.query + '</p>';
                    html += '<p><strong><?php _e('Results Found:', 'contentai-pro'); ?></strong> ' + response.data.total_found + '</p>';
                    
                    if (response.data.products && response.data.products.length > 0) {
                        html += '<ul>';
                        response.data.products.forEach(function(product) {
                            html += '<li><strong>' + product.name + '</strong> - ' + product.price + '</li>';
                        });
                        html += '</ul>';
                    } else {
                        html += '<p><?php _e('No products found for this query', 'contentai-pro'); ?></p>';
                    }
                    
                    resultsDiv.html(html);
                } else {
                    resultsDiv.html('<p class="error"><?php _e('Test failed:', 'contentai-pro'); ?> ' + response.data.message + '</p>');
                }
            },
            error: function() {
                resultsDiv.html('<p class="error"><?php _e('Test failed. Please try again.', 'contentai-pro'); ?></p>');
            }
        });
    });
});
</script>

<style>
.ai-cw-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.ai-cw-stat-box {
    background: #f9f9f9;
    padding: 20px;
    border-radius: 8px;
    text-align: center;
    border: 1px solid #ddd;
}

.ai-cw-stat-box h3 {
    font-size: 2em;
    margin: 0 0 10px 0;
    color: #0073aa;
}

.ai-cw-stat-box p {
    margin: 0;
    color: #666;
}

.ai-cw-analytics-section {
    margin: 30px 0;
}

.ai-cw-popular-queries,
.ai-cw-no-results-queries {
    background: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 15px;
    max-height: 200px;
    overflow-y: auto;
}

.ai-cw-query-item {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid #eee;
}

.ai-cw-query-item:last-child {
    border-bottom: none;
}

.ai-cw-query-text {
    flex: 1;
}

.ai-cw-query-count {
    background: #0073aa;
    color: white;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: bold;
}

.ai-cw-shortcode-examples {
    background: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    margin: 20px 0;
}

.ai-cw-shortcode-examples code {
    display: block;
    background: #2c3e50;
    color: #ecf0f1;
    padding: 10px;
    border-radius: 4px;
    margin: 10px 0;
    font-family: 'Courier New', monospace;
}

.ai-cw-test-assistant {
    background: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
}

.ai-cw-test-results {
    margin-top: 15px;
    padding: 15px;
    background: white;
    border: 1px solid #ddd;
    border-radius: 4px;
    min-height: 50px;
}

.ai-cw-test-results .error {
    color: #d63384;
    font-weight: bold;
}
</style>

