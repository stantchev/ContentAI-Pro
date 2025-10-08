<?php
/**
 * E-commerce Analyzer Page
 */

if (!defined('ABSPATH')) {
    exit;
}

$ecommerce_analyzer = new AI_CW_Ecommerce_Analyzer();
$analysis_results = $ecommerce_analyzer->get_analysis_results();
$analysis_date = $ecommerce_analyzer->get_analysis_date();
?>

<div class="wrap">
    <h1><?php _e('E-commerce Analyzer', 'ai-content-writer'); ?></h1>
    
    <?php if (!class_exists('WooCommerce')): ?>
    <div class="notice notice-warning">
        <p>
            <strong><?php _e('WooCommerce Required', 'ai-content-writer'); ?></strong><br>
            <?php _e('WooCommerce plugin is required for e-commerce analysis.', 'ai-content-writer'); ?>
            <a href="<?php echo admin_url('plugin-install.php?s=woocommerce&tab=search&type=term'); ?>" class="button button-primary">
                <?php _e('Install WooCommerce', 'ai-content-writer'); ?>
            </a>
        </p>
    </div>
    <?php endif; ?>
    
    <div class="ai-cw-ecommerce-analyzer">
        <!-- Analysis Actions -->
        <div class="ai-cw-analysis-actions">
            <h2><?php _e('E-commerce Analysis', 'ai-content-writer'); ?></h2>
            <p><?php _e('Deep analysis of your online store for content opportunities and product integration.', 'ai-content-writer'); ?></p>
            
            <div class="ai-cw-action-buttons">
                <button type="button" class="button button-primary" id="ai-cw-run-ecommerce-analysis">
                    <?php _e('Run E-commerce Analysis', 'ai-content-writer'); ?>
                </button>
                
                <button type="button" class="button button-secondary" id="ai-cw-update-ecommerce-analysis">
                    <?php _e('Update Analysis', 'ai-content-writer'); ?>
                </button>
                
                <button type="button" class="button button-secondary" id="ai-cw-generate-product-content">
                    <?php _e('Generate Product Content', 'ai-content-writer'); ?>
                </button>
            </div>
        </div>
        
        <!-- Analysis Results -->
        <?php if (!empty($analysis_results) && !empty($analysis_date)): ?>
        <div class="ai-cw-analysis-results">
            <h2><?php _e('Analysis Results', 'ai-content-writer'); ?></h2>
            <p class="ai-cw-analysis-date">
                <?php printf(__('Last analyzed: %s', 'ai-content-writer'), date('F j, Y \a\t g:i A', strtotime($analysis_date))); ?>
            </p>
            
            <!-- Products Analysis -->
            <?php if (!empty($analysis_results['products'])): ?>
            <div class="ai-cw-analysis-section">
                <h3><?php _e('Products Analysis', 'ai-content-writer'); ?></h3>
                
                <div class="ai-cw-analysis-grid">
                    <div class="ai-cw-analysis-card">
                        <h4><?php _e('Total Products', 'ai-content-writer'); ?></h4>
                        <div class="ai-cw-stat-number"><?php echo $analysis_results['products']['total_products']; ?></div>
                    </div>
                    
                    <div class="ai-cw-analysis-card">
                        <h4><?php _e('Products Without Content', 'ai-content-writer'); ?></h4>
                        <div class="ai-cw-stat-number"><?php echo count($analysis_results['products']['products_without_content']); ?></div>
                    </div>
                    
                    <div class="ai-cw-analysis-card">
                        <h4><?php _e('Popular Products', 'ai-content-writer'); ?></h4>
                        <div class="ai-cw-stat-number"><?php echo count($analysis_results['products']['popular_products']); ?></div>
                    </div>
                    
                    <div class="ai-cw-analysis-card">
                        <h4><?php _e('New Products', 'ai-content-writer'); ?></h4>
                        <div class="ai-cw-stat-number"><?php echo count($analysis_results['products']['new_products']); ?></div>
                    </div>
                </div>
                
                <!-- Products Without Content -->
                <?php if (!empty($analysis_results['products']['products_without_content'])): ?>
                <div class="ai-cw-products-without-content">
                    <h4><?php _e('Products Without Content', 'ai-content-writer'); ?></h4>
                    <div class="ai-cw-products-list">
                        <?php foreach (array_slice($analysis_results['products']['products_without_content'], 0, 10) as $product): ?>
                        <div class="ai-cw-product-item">
                            <h5>
                                <a href="<?php echo esc_url($product['url']); ?>" target="_blank">
                                    <?php echo esc_html($product['name']); ?>
                                </a>
                            </h5>
                            <p><?php printf(__('Content length: %d characters', 'ai-content-writer'), $product['content_length']); ?></p>
                            <button type="button" class="button button-small ai-cw-generate-product-content" 
                                    data-product-id="<?php echo $product['id']; ?>">
                                <?php _e('Generate Content', 'ai-content-writer'); ?>
                            </button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <!-- Categories Analysis -->
            <?php if (!empty($analysis_results['categories'])): ?>
            <div class="ai-cw-analysis-section">
                <h3><?php _e('Categories Analysis', 'ai-content-writer'); ?></h3>
                
                <div class="ai-cw-analysis-grid">
                    <div class="ai-cw-analysis-card">
                        <h4><?php _e('Total Categories', 'ai-content-writer'); ?></h4>
                        <div class="ai-cw-stat-number"><?php echo $analysis_results['categories']['total_categories']; ?></div>
                    </div>
                    
                    <div class="ai-cw-analysis-card">
                        <h4><?php _e('Categories Without Content', 'ai-content-writer'); ?></h4>
                        <div class="ai-cw-stat-number"><?php echo count($analysis_results['categories']['categories_without_content']); ?></div>
                    </div>
                    
                    <div class="ai-cw-analysis-card">
                        <h4><?php _e('Popular Categories', 'ai-content-writer'); ?></h4>
                        <div class="ai-cw-stat-number"><?php echo count($analysis_results['categories']['popular_categories']); ?></div>
                    </div>
                    
                    <div class="ai-cw-analysis-card">
                        <h4><?php _e('Underperforming Categories', 'ai-content-writer'); ?></h4>
                        <div class="ai-cw-stat-number"><?php echo count($analysis_results['categories']['underperforming_categories']); ?></div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Sales Data -->
            <?php if (!empty($analysis_results['sales_data'])): ?>
            <div class="ai-cw-analysis-section">
                <h3><?php _e('Sales Data', 'ai-content-writer'); ?></h3>
                
                <div class="ai-cw-analysis-grid">
                    <div class="ai-cw-analysis-card">
                        <h4><?php _e('Total Orders', 'ai-content-writer'); ?></h4>
                        <div class="ai-cw-stat-number"><?php echo $analysis_results['sales_data']['total_orders']; ?></div>
                    </div>
                    
                    <div class="ai-cw-analysis-card">
                        <h4><?php _e('Total Revenue', 'ai-content-writer'); ?></h4>
                        <div class="ai-cw-stat-number"><?php echo wc_price($analysis_results['sales_data']['total_revenue']); ?></div>
                    </div>
                    
                    <div class="ai-cw-analysis-card">
                        <h4><?php _e('Average Order Value', 'ai-content-writer'); ?></h4>
                        <div class="ai-cw-stat-number"><?php echo wc_price($analysis_results['sales_data']['average_order_value']); ?></div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Content Opportunities -->
            <?php if (!empty($analysis_results['content_opportunities'])): ?>
            <div class="ai-cw-analysis-section">
                <h3><?php _e('Content Opportunities', 'ai-content-writer'); ?></h3>
                
                <div class="ai-cw-opportunities-list">
                    <?php foreach (array_slice($analysis_results['content_opportunities'], 0, 10) as $opportunity): ?>
                    <div class="ai-cw-opportunity-item">
                        <h4><?php echo esc_html($opportunity['title']); ?></h4>
                        <p><?php echo esc_html($opportunity['description']); ?></p>
                        <div class="ai-cw-opportunity-meta">
                            <span class="ai-cw-priority ai-cw-priority-<?php echo $opportunity['priority']; ?>">
                                <?php echo ucfirst($opportunity['priority']); ?>
                            </span>
                            <span class="ai-cw-type"><?php echo esc_html($opportunity['type']); ?></span>
                        </div>
                        <button type="button" class="button button-small ai-cw-use-opportunity" 
                                data-opportunity='<?php echo json_encode($opportunity); ?>'>
                            <?php _e('Use This Opportunity', 'ai-content-writer'); ?>
                        </button>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Product Content Suggestions -->
            <?php if (!empty($analysis_results['product_content_suggestions'])): ?>
            <div class="ai-cw-analysis-section">
                <h3><?php _e('Product Content Suggestions', 'ai-content-writer'); ?></h3>
                
                <div class="ai-cw-suggestions-grid">
                    <?php foreach (array_slice($analysis_results['product_content_suggestions'], 0, 12) as $suggestion): ?>
                    <div class="ai-cw-suggestion-card">
                        <h4><?php echo esc_html($suggestion['title']); ?></h4>
                        <p><?php echo esc_html($suggestion['description']); ?></p>
                        <div class="ai-cw-suggestion-meta">
                            <span class="ai-cw-priority ai-cw-priority-<?php echo $suggestion['priority']; ?>">
                                <?php echo ucfirst($suggestion['priority']); ?>
                            </span>
                            <span class="ai-cw-type"><?php echo esc_html($suggestion['type']); ?></span>
                        </div>
                        <button type="button" class="button button-small ai-cw-generate-suggestion" 
                                data-suggestion='<?php echo json_encode($suggestion); ?>'>
                            <?php _e('Generate Content', 'ai-content-writer'); ?>
                        </button>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="ai-cw-no-analysis">
            <h2><?php _e('No Analysis Results', 'ai-content-writer'); ?></h2>
            <p><?php _e('Run your first e-commerce analysis to discover content opportunities and product integration possibilities.', 'ai-content-writer'); ?></p>
        </div>
        <?php endif; ?>
        
        <!-- Analysis Progress -->
        <div class="ai-cw-analysis-progress" style="display: none;">
            <h2><?php _e('Analyzing E-commerce Store', 'ai-content-writer'); ?></h2>
            <div class="ai-cw-progress-bar">
                <div class="ai-cw-progress-fill"></div>
            </div>
            <p class="ai-cw-progress-text"><?php _e('Analyzing products, categories, and sales data...', 'ai-content-writer'); ?></p>
        </div>
        
        <!-- Generate Product Content Modal -->
        <div id="ai-cw-generate-product-modal" class="ai-cw-modal" style="display: none;">
            <div class="ai-cw-modal-content">
                <span class="ai-cw-close">&times;</span>
                <h2><?php _e('Generate Product Content', 'ai-content-writer'); ?></h2>
                <form id="ai-cw-generate-product-form">
                    <div class="ai-cw-form-group">
                        <label for="product-select"><?php _e('Select Product', 'ai-content-writer'); ?></label>
                        <select id="product-select" name="product_id" required>
                            <option value=""><?php _e('Select a product', 'ai-content-writer'); ?></option>
                            <?php
                            if (class_exists('WooCommerce')) {
                                $products = wc_get_products(array('limit' => 50));
                                foreach ($products as $product) {
                                    echo '<option value="' . $product->get_id() . '">' . $product->get_name() . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="ai-cw-form-group">
                        <label for="content-type"><?php _e('Content Type', 'ai-content-writer'); ?></label>
                        <select id="content-type" name="content_type" required>
                            <option value="review"><?php _e('Product Review', 'ai-content-writer'); ?></option>
                            <option value="guide"><?php _e('How-to Guide', 'ai-content-writer'); ?></option>
                            <option value="benefits"><?php _e('Benefits Article', 'ai-content-writer'); ?></option>
                            <option value="faq"><?php _e('FAQ Article', 'ai-content-writer'); ?></option>
                            <option value="comparison"><?php _e('Comparison Article', 'ai-content-writer'); ?></option>
                        </select>
                    </div>
                    
                    <div class="ai-cw-form-group">
                        <label for="product-word-count"><?php _e('Word Count', 'ai-content-writer'); ?></label>
                        <select id="product-word-count" name="word_count">
                            <option value="500"><?php _e('500 words', 'ai-content-writer'); ?></option>
                            <option value="1000" selected><?php _e('1000 words', 'ai-content-writer'); ?></option>
                            <option value="1500"><?php _e('1500 words', 'ai-content-writer'); ?></option>
                            <option value="2000"><?php _e('2000 words', 'ai-content-writer'); ?></option>
                        </select>
                    </div>
                    
                    <div class="ai-cw-form-actions">
                        <button type="submit" class="button button-primary">
                            <?php _e('Generate Content', 'ai-content-writer'); ?>
                        </button>
                        <button type="button" class="button ai-cw-cancel">
                            <?php _e('Cancel', 'ai-content-writer'); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Run e-commerce analysis
    $('#ai-cw-run-ecommerce-analysis').on('click', function() {
        var button = $(this);
        button.prop('disabled', true).text('<?php _e('Analyzing...', 'ai-content-writer'); ?>');
        
        $('.ai-cw-analysis-progress').show();
        
        $.ajax({
            url: ai_cw_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'ai_cw_analyze_ecommerce',
                nonce: ai_cw_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert(ai_cw_ajax.strings.success);
                    location.reload();
                } else {
                    alert(response.data.message || ai_cw_ajax.strings.error);
                }
            },
            error: function() {
                alert(ai_cw_ajax.strings.error);
            },
            complete: function() {
                button.prop('disabled', false).text('<?php _e('Run E-commerce Analysis', 'ai-content-writer'); ?>');
                $('.ai-cw-analysis-progress').hide();
            }
        });
    });
    
    // Generate product content
    $('#ai-cw-generate-product-content').on('click', function() {
        $('#ai-cw-generate-product-modal').show();
    });
    
    // Generate product content form
    $('#ai-cw-generate-product-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = {
            action: 'ai_cw_generate_product_content',
            product_id: $('#product-select').val(),
            content_type: $('#content-type').val(),
            word_count: $('#product-word-count').val(),
            nonce: ai_cw_ajax.nonce
        };
        
        if (!formData.product_id) {
            alert('<?php _e('Please select a product.', 'ai-content-writer'); ?>');
            return;
        }
        
        $.ajax({
            url: ai_cw_ajax.ajax_url,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    alert(ai_cw_ajax.strings.success);
                    $('#ai-cw-generate-product-modal').hide();
                    // Redirect to edit the generated content
                    if (response.data.post_id) {
                        window.open('<?php echo admin_url('post.php?action=edit&post='); ?>' + response.data.post_id, '_blank');
                    }
                } else {
                    alert(response.data.message || ai_cw_ajax.strings.error);
                }
            },
            error: function() {
                alert(ai_cw_ajax.strings.error);
            }
        });
    });
    
    // Generate content for specific product
    $('.ai-cw-generate-product-content').on('click', function() {
        var productId = $(this).data('product-id');
        $('#product-select').val(productId);
        $('#ai-cw-generate-product-modal').show();
    });
    
    // Use opportunity
    $('.ai-cw-use-opportunity').on('click', function() {
        var opportunity = $(this).data('opportunity');
        // Redirect to content generator with pre-filled data
        var url = '<?php echo admin_url('admin.php?page=ai-content-writer-generator'); ?>';
        url += '&topic=' + encodeURIComponent(opportunity.title);
        window.location.href = url;
    });
    
    // Generate suggestion
    $('.ai-cw-generate-suggestion').on('click', function() {
        var suggestion = $(this).data('suggestion');
        // Redirect to content generator with pre-filled data
        var url = '<?php echo admin_url('admin.php?page=ai-content-writer-generator'); ?>';
        url += '&topic=' + encodeURIComponent(suggestion.title);
        window.location.href = url;
    });
});
</script>

