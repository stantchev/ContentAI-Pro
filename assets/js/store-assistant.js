/**
 * Store Assistant Frontend JavaScript
 */

(function($) {
    'use strict';
    
    // Store Assistant Class
    var StoreAssistant = function() {
        this.isActive = false;
        this.currentQuery = '';
        this.searchTimeout = null;
        this.minQueryLength = 3;
        this.searchDelay = 500;
        this.init();
    };
    
    StoreAssistant.prototype.init = function() {
        this.createInterface();
        this.bindEvents();
        this.loadSuggestions();
    };
    
    StoreAssistant.prototype.createInterface = function() {
        // Create assistant button
        var assistantButton = $('<div class="ai-store-assistant-button">' +
            '<span class="ai-assistant-icon">🤖</span>' +
            '<span class="ai-assistant-text">AI Assistant</span>' +
        '</div>');
        
        // Create assistant modal
        var assistantModal = $('<div class="ai-store-assistant-modal">' +
            '<div class="ai-assistant-content">' +
                '<div class="ai-assistant-header">' +
                    '<h3>AI Shopping Assistant</h3>' +
                    '<button class="ai-assistant-close">&times;</button>' +
                '</div>' +
                '<div class="ai-assistant-body">' +
                    '<div class="ai-assistant-search">' +
                        '<input type="text" class="ai-assistant-input" placeholder="Describe what you\'re looking for...">' +
                        '<button class="ai-assistant-search-btn">Search</button>' +
                    '</div>' +
                    '<div class="ai-assistant-suggestions"></div>' +
                    '<div class="ai-assistant-results"></div>' +
                    '<div class="ai-assistant-loading" style="display: none;">' +
                        '<div class="ai-loading-spinner"></div>' +
                        '<p>Searching products...</p>' +
                    '</div>' +
                '</div>' +
            '</div>' +
        '</div>');
        
        // Add to page
        $('body').append(assistantButton);
        $('body').append(assistantModal);
    };
    
    StoreAssistant.prototype.bindEvents = function() {
        var self = this;
        
        // Open assistant
        $(document).on('click', '.ai-store-assistant-button', function(e) {
            e.preventDefault();
            self.openAssistant();
        });
        
        // Close assistant
        $(document).on('click', '.ai-assistant-close, .ai-store-assistant-modal', function(e) {
            if (e.target === this || $(e.target).hasClass('ai-assistant-close')) {
                self.closeAssistant();
            }
        });
        
        // Search input
        $(document).on('input', '.ai-assistant-input', function() {
            var query = $(this).val().trim();
            self.handleSearchInput(query);
        });
        
        // Search button
        $(document).on('click', '.ai-assistant-search-btn', function() {
            var query = $('.ai-assistant-input').val().trim();
            if (query.length >= self.minQueryLength) {
                self.searchProducts(query);
            }
        });
        
        // Enter key in search input
        $(document).on('keypress', '.ai-assistant-input', function(e) {
            if (e.which === 13) {
                var query = $(this).val().trim();
                if (query.length >= self.minQueryLength) {
                    self.searchProducts(query);
                }
            }
        });
        
        // Suggestion clicks
        $(document).on('click', '.ai-assistant-suggestion', function() {
            var suggestion = $(this).data('suggestion');
            $('.ai-assistant-input').val(suggestion);
            self.searchProducts(suggestion);
        });
        
        // Product clicks
        $(document).on('click', '.ai-assistant-product', function() {
            var productUrl = $(this).data('url');
            if (productUrl) {
                window.location.href = productUrl;
            }
        });
        
        // Prevent modal close when clicking inside
        $(document).on('click', '.ai-assistant-content', function(e) {
            e.stopPropagation();
        });
    };
    
    StoreAssistant.prototype.openAssistant = function() {
        $('.ai-store-assistant-modal').fadeIn(300);
        $('.ai-assistant-input').focus();
        this.isActive = true;
    };
    
    StoreAssistant.prototype.closeAssistant = function() {
        $('.ai-store-assistant-modal').fadeOut(300);
        this.isActive = false;
        this.clearResults();
    };
    
    StoreAssistant.prototype.handleSearchInput = function(query) {
        var self = this;
        
        // Clear previous timeout
        if (this.searchTimeout) {
            clearTimeout(this.searchTimeout);
        }
        
        // Show suggestions if query is too short
        if (query.length < this.minQueryLength) {
            this.showSuggestions();
            this.clearResults();
            return;
        }
        
        // Set timeout for search
        this.searchTimeout = setTimeout(function() {
            self.searchProducts(query);
        }, this.searchDelay);
    };
    
    StoreAssistant.prototype.searchProducts = function(query) {
        var self = this;
        
        if (query.length < this.minQueryLength) {
            return;
        }
        
        this.currentQuery = query;
        this.showLoading();
        this.clearResults();
        
        $.ajax({
            url: ai_store_assistant.ajax_url,
            type: 'POST',
            data: {
                action: 'ai_cw_store_assistant_search',
                query: query,
                nonce: ai_store_assistant.nonce
            },
            success: function(response) {
                self.hideLoading();
                
                if (response.success) {
                    self.showResults(response.data);
                } else {
                    self.showError(response.data.message || 'Search failed');
                }
            },
            error: function() {
                self.hideLoading();
                self.showError('Search failed. Please try again.');
            }
        });
    };
    
    StoreAssistant.prototype.showLoading = function() {
        $('.ai-assistant-loading').show();
        $('.ai-assistant-results').hide();
    };
    
    StoreAssistant.prototype.hideLoading = function() {
        $('.ai-assistant-loading').hide();
    };
    
    StoreAssistant.prototype.showResults = function(data) {
        var resultsHtml = '';
        
        if (data.products && data.products.length > 0) {
            resultsHtml += '<div class="ai-assistant-results-header">' +
                '<h4>Found ' + data.total_found + ' matching products:</h4>' +
            '</div>';
            
            resultsHtml += '<div class="ai-assistant-products">';
            
            data.products.forEach(function(product) {
                var price = product.sale_price ? 
                    '<span class="sale-price">' + product.sale_price + '</span> <span class="regular-price">' + product.regular_price + '</span>' :
                    '<span class="price">' + product.price + '</span>';
                
                var stockStatus = product.in_stock ? 
                    '<span class="in-stock">In Stock</span>' : 
                    '<span class="out-of-stock">Out of Stock</span>';
                
                resultsHtml += '<div class="ai-assistant-product" data-url="' + product.url + '">' +
                    '<div class="product-image">' +
                        (product.image ? '<img src="' + product.image + '" alt="' + product.name + '">' : '') +
                    '</div>' +
                    '<div class="product-info">' +
                        '<h5 class="product-name">' + product.name + '</h5>' +
                        '<p class="product-description">' + (product.short_description || product.description).substring(0, 100) + '...</p>' +
                        '<div class="product-meta">' +
                            '<div class="product-price">' + price + '</div>' +
                            '<div class="product-stock">' + stockStatus + '</div>' +
                        '</div>' +
                        (product.categories.length > 0 ? 
                            '<div class="product-categories">Categories: ' + product.categories.join(', ') + '</div>' : '') +
                    '</div>' +
                '</div>';
            });
            
            resultsHtml += '</div>';
        } else {
            resultsHtml += '<div class="ai-assistant-no-results">' +
                '<h4>No products found</h4>' +
                '<p>Try different keywords or browse our categories:</p>';
            
            if (data.suggestions && data.suggestions.length > 0) {
                resultsHtml += '<div class="ai-assistant-suggestions">';
                data.suggestions.forEach(function(suggestion) {
                    if (suggestion.type === 'category') {
                        resultsHtml += '<a href="' + suggestion.url + '" class="ai-assistant-suggestion">' +
                            suggestion.name + ' (' + suggestion.count + ' products)' +
                        '</a>';
                    }
                });
                resultsHtml += '</div>';
            }
            
            resultsHtml += '</div>';
        }
        
        $('.ai-assistant-results').html(resultsHtml).show();
    };
    
    StoreAssistant.prototype.showError = function(message) {
        var errorHtml = '<div class="ai-assistant-error">' +
            '<h4>Search Error</h4>' +
            '<p>' + message + '</p>' +
        '</div>';
        
        $('.ai-assistant-results').html(errorHtml).show();
    };
    
    StoreAssistant.prototype.clearResults = function() {
        $('.ai-assistant-results').empty().hide();
    };
    
    StoreAssistant.prototype.loadSuggestions = function() {
        var self = this;
        
        $.ajax({
            url: ai_store_assistant.ajax_url,
            type: 'POST',
            data: {
                action: 'ai_cw_store_assistant_suggestions',
                nonce: ai_store_assistant.nonce
            },
            success: function(response) {
                if (response.success) {
                    self.showSuggestions(response.data);
                }
            }
        });
    };
    
    StoreAssistant.prototype.showSuggestions = function(suggestions) {
        var suggestionsHtml = '';
        
        if (suggestions && suggestions.length > 0) {
            suggestionsHtml += '<div class="ai-assistant-suggestions-header">' +
                '<h4>Popular searches:</h4>' +
            '</div>';
            
            suggestionsHtml += '<div class="ai-assistant-suggestions-list">';
            
            suggestions.forEach(function(suggestion) {
                suggestionsHtml += '<div class="ai-assistant-suggestion" data-suggestion="' + suggestion.text + '">' +
                    suggestion.text +
                    (suggestion.count ? ' (' + suggestion.count + ')' : '') +
                '</div>';
            });
            
            suggestionsHtml += '</div>';
        }
        
        $('.ai-assistant-suggestions').html(suggestionsHtml);
    };
    
    // Initialize when document is ready
    $(document).ready(function() {
        if (typeof ai_store_assistant !== 'undefined') {
            new StoreAssistant();
        }
    });
    
})(jQuery);

