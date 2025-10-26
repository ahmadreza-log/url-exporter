/**
 * URL Exporter Frontend Script
 * Handles the modal and AJAX functionality for exporting URLs
 */
(function ($) {
    'use strict';

    /**
     * URL Exporter Module
     */
    const URLExporterModule = {

        /**
         * Modal selector
         */
        $modal: null,

        /**
         * Current export data
         */
        currentExport: {
            taxonomy: null,
            termId: null,
            termName: '',
            allUrls: [],
            totalCount: 0,
            loadedCount: 0,
            isLoading: false
        },

        /**
         * Initialize the module
         */
        init: function () {
            this.createModal();
            this.bindEvents();
        },

        /**
         * Create modal HTML structure
         */
        createModal: function () {
            const modalHTML = `
                <div class="url-exporter-modal" style="display: none;">
            <div class="url-exporter-modal-overlay"></div>
            <div class="url-exporter-modal-content">
                        <div class="url-exporter-modal-header">
                            <h2>${UrlExporter.i18n.title}</h2>
                            <button type="button" class="url-exporter-close">
                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24">
                                    <path fill="currentColor" d="m12 13.4l-4.9 4.9q-.275.275-.7.275t-.7-.275t-.275-.7t.275-.7l4.9-4.9l-4.9-4.9q-.275-.275-.275-.7t.275-.7t.7-.275t.7.275l4.9 4.9l4.9-4.9q.275-.275.7-.275t.7.275t.275.7t-.275.7L13.4 12l4.9 4.9q.275.275.275.7t-.275.7t-.7.275t-.7-.275z"/>
                                </svg>
                            </button>
                        </div>
                        <div class="url-exporter-progress" style="display: none;">
                            <div class="progress-info">
                                <span class="progress-text">${UrlExporter.i18n.loading}</span>
                                <span class="progress-count">0 / 0</span>
                            </div>
                            <div class="progress-bar-wrapper">
                                <div class="progress-bar" style="width: 0%"></div>
                            </div>
                        </div>
                        <div class="url-exporter-modal-body"></div>
                        <div class="url-exporter-modal-footer">
                            <button type="button" class="button url-exporter-copy-all">${UrlExporter.i18n.copyAll}</button>
                        </div>
                    </div>
                </div>
            `;

            $('body').append(modalHTML);
            this.$modal = $('.url-exporter-modal');
        },

        /**
         * Bind event listeners
         */
        bindEvents: function () {
            const self = this;

            // Close modal on overlay click or close button
            this.$modal.on('click', '.url-exporter-modal-overlay, .url-exporter-close', function (e) {
                e.preventDefault();
                self.closeModal();
            });

            // Prevent modal content clicks from closing
            this.$modal.on('click', '.url-exporter-modal-content', function (e) {
                e.stopPropagation();
            });

            // Export URLs trigger
            $(document).on('click', '.url-exporter-trigger', function (e) {
            e.preventDefault();

                const taxonomy = $(this).data('taxonomy');
                const termId = $(this).data('term-id');

                if (taxonomy && termId) {
                    self.fetchURLs(taxonomy, termId);
                }
            });

            // Copy all URLs
            this.$modal.on('click', '.url-exporter-copy-all', function (e) {
            e.preventDefault();
                self.copyAllURLs();
            });

            // ESC key to close modal
            $(document).on('keyup', function (e) {
                if (e.key === 'Escape' && self.$modal.hasClass('show')) {
                    self.closeModal();
                }
            });
        },

        /**
         * Open modal
         */
        openModal: function () {
            this.$modal.addClass('show').fadeIn(200);
            $('body').addClass('url-exporter-modal-open');
        },

        /**
         * Close modal
         */
        closeModal: function () {
            this.$modal.removeClass('show').fadeOut(200);
            $('body').removeClass('url-exporter-modal-open');
        },

        /**
         * Show loading state
         */
        showLoading: function () {
            const loadingHTML = `
                <div class="url-exporter-loading">
                    <span class="spinner is-active"></span>
                    <p>${UrlExporter.i18n.loading}</p>
                </div>
            `;
            this.$modal.find('.url-exporter-modal-body').html(loadingHTML);
        },

        /**
         * Show error message
         */
        showError: function (message) {
            const errorHTML = `
                <div class="url-exporter-error">
                    <span class="dashicons dashicons-warning"></span>
                    <p>${message || UrlExporter.i18n.error}</p>
                </div>
            `;
            this.$modal.find('.url-exporter-modal-body').html(errorHTML);
        },

        /**
         * Fetch URLs via AJAX with batch processing
         */
        fetchURLs: function (taxonomy, termId) {
            const self = this;

            // Reset current export data
            this.currentExport = {
                taxonomy: taxonomy,
                termId: termId,
                termName: '',
                allUrls: [],
                totalCount: 0,
                loadedCount: 0,
                isLoading: true
            };

            this.openModal();
            this.showLoading();

            // First, get the total count
            $.ajax({
                url: UrlExporter.ajaxurl,
                type: 'GET',
                data: {
                    action: 'url_exporter_get_urls',
                    request_type: 'count',
                    taxonomy: taxonomy,
                    ID: termId,
                    nonce: UrlExporter.nonce
                },
                timeout: 30000, // 30 seconds timeout for count
                success: function (response) {
                    if (response.success && response.data) {
                        self.currentExport.totalCount = response.data.total_count;
                        self.currentExport.termName = response.data.term_name;

                        if (self.currentExport.totalCount === 0) {
                            self.showError(UrlExporter.i18n.noResults);
                            return;
                        }

                        // Show progress bar for large datasets
                        if (self.currentExport.totalCount > 50) {
                            self.showProgressBar();
                        }

                        // Start fetching data in batches
                        self.fetchBatch(1, 50);
                    } else {
                        const errorMsg = response.data && response.data.message
                            ? response.data.message
                            : UrlExporter.i18n.error;
                        self.showError(errorMsg);
                    }
                },
                error: function (xhr, status, error) {
                    console.error('URL Exporter Count Error:', error);
                    self.showError(UrlExporter.i18n.error);
                    self.currentExport.isLoading = false;
                }
            });
        },

        /**
         * Fetch a single batch of URLs
         */
        fetchBatch: function (page, perPage) {
            const self = this;

            $.ajax({
                url: UrlExporter.ajaxurl,
                type: 'GET',
                data: {
                    action: 'url_exporter_get_urls',
                    request_type: 'data',
                    taxonomy: self.currentExport.taxonomy,
                    ID: self.currentExport.termId,
                    page: page,
                    per_page: perPage,
                    nonce: UrlExporter.nonce
                },
                timeout: 60000, // 60 seconds timeout per batch
                success: function (response) {
                    if (response.success && response.data) {
                        // Add URLs to collection
                        self.currentExport.allUrls = self.currentExport.allUrls.concat(response.data.urls);
                        self.currentExport.loadedCount = self.currentExport.allUrls.length;

                        // Update progress
                        self.updateProgress();

                        // Check if there are more pages
                        if (response.data.has_more) {
                            // Fetch next batch
                            self.fetchBatch(page + 1, perPage);
                        } else {
                            // All data loaded, render results
                            self.currentExport.isLoading = false;
                            self.hideProgressBar();
                            self.renderURLs({
                                urls: self.currentExport.allUrls,
                                count: self.currentExport.loadedCount,
                                term: self.currentExport.termName
                            });
                        }
                    } else {
                        const errorMsg = response.data && response.data.message
                            ? response.data.message
                            : UrlExporter.i18n.error;
                        self.showError(errorMsg);
                        self.currentExport.isLoading = false;
                    }
                },
                error: function (xhr, status, error) {
                    console.error('URL Exporter Batch Error:', error, 'Page:', page);

                    // Show error but keep already loaded data
                    if (self.currentExport.allUrls.length > 0) {
                        self.hideProgressBar();
                        self.renderURLs({
                            urls: self.currentExport.allUrls,
                            count: self.currentExport.allUrls.length,
                            term: self.currentExport.termName
                        });
                        const warningMsg = UrlExporter.i18n.partialLoad
                            .replace('%1$d', self.currentExport.allUrls.length)
                            .replace('%2$d', self.currentExport.totalCount);
                        self.showWarning(warningMsg);
                    } else {
                        self.showError(UrlExporter.i18n.error);
                    }
                    self.currentExport.isLoading = false;
                }
            });
        },

        /**
         * Show progress bar
         */
        showProgressBar: function () {
            this.$modal.find('.url-exporter-progress').show();
            this.updateProgress();
        },

        /**
         * Hide progress bar
         */
        hideProgressBar: function () {
            this.$modal.find('.url-exporter-progress').hide();
        },

        /**
         * Update progress bar
         */
        updateProgress: function () {
            const loaded = this.currentExport.loadedCount;
            const total = this.currentExport.totalCount;
            const percentage = total > 0 ? Math.round((loaded / total) * 100) : 0;

            this.$modal.find('.progress-bar').css('width', percentage + '%');
            this.$modal.find('.progress-count').text(loaded + ' / ' + total);
            this.$modal.find('.progress-text').text(UrlExporter.i18n.loading + ' (' + percentage + '%)');
        },

        /**
         * Show warning message
         */
        showWarning: function (message) {
            const warningHTML = `
                <div class="url-exporter-warning">
                    <span class="dashicons dashicons-info"></span>
                    <p>${this.escapeHtml(message)}</p>
                </div>
            `;
            this.$modal.find('.url-exporter-modal-body').prepend(warningHTML);
        },

        /**
         * Render URLs in table
         */
        renderURLs: function (data) {
            const urls = data.urls || [];
            const count = data.count || 0;
            const termName = data.term || '';

            if (count === 0) {
                this.showError(UrlExporter.i18n.noResults);
                return;
            }

            let html = `
                <div class="url-exporter-info">
                    <p><strong>${UrlExporter.i18n.category}:</strong> ${this.escapeHtml(termName)}</p>
                    <p><strong>${UrlExporter.i18n.totalPosts}:</strong> ${count}</p>
                </div>
                <div class="url-exporter-table-wrapper">
                    <table class="url-exporter-table wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th class="column-title">${UrlExporter.i18n.titleColumn}</th>
                                <th class="column-url">${UrlExporter.i18n.urlColumn}</th>
                                <th class="column-date">${UrlExporter.i18n.dateColumn}</th>
                                <th class="column-actions">${UrlExporter.i18n.actionsColumn}</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            urls.forEach((item, index) => {
                html += `
                    <tr>
                        <td class="column-title">${this.escapeHtml(item.title)}</td>
                        <td class="column-url">
                            <a href="${this.escapeHtml(item.url)}" target="_blank" rel="noopener noreferrer">
                                ${this.truncateURL(item.url)}
                            </a>
                        </td>
                        <td class="column-date">${this.escapeHtml(item.date)}</td>
                        <td class="column-actions">
                            <button type="button" class="button button-small url-exporter-copy" data-url="${this.escapeHtml(item.url)}">
                                ${UrlExporter.i18n.copy}
                            </button>
                        </td>
                    </tr>
                `;
            });

            html += `
                        </tbody>
                    </table>
                </div>
            `;

            this.$modal.find('.url-exporter-modal-body').html(html);

            // Bind copy buttons
            this.bindCopyButtons();
        },

        /**
         * Bind copy button events
         */
        bindCopyButtons: function () {
            const self = this;

            this.$modal.find('.url-exporter-copy').on('click', function (e) {
                e.preventDefault();
                const url = $(this).data('url');
                self.copyToClipboard(url, $(this));
            });
        },

        /**
         * Copy all URLs to clipboard
         */
        copyAllURLs: function () {
            const urls = [];
            this.$modal.find('.url-exporter-table tbody tr').each(function () {
                const url = $(this).find('.url-exporter-copy').data('url');
                if (url) {
                    urls.push(url);
                }
            });

            if (urls.length > 0) {
                this.copyToClipboard(urls.join('\n'));
            }
        },

        /**
         * Copy text to clipboard
         */
        copyToClipboard: function (text, $button) {
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(() => {
                    this.showCopySuccess($button);
                }).catch(err => {
                    console.error('Copy failed:', err);
                    this.fallbackCopy(text, $button);
                });
            } else {
                this.fallbackCopy(text, $button);
            }
        },

        /**
         * Fallback copy method for older browsers
         */
        fallbackCopy: function (text, $button) {
            const $temp = $('<textarea>');
            $('body').append($temp);
            $temp.val(text).select();

            try {
                document.execCommand('copy');
                this.showCopySuccess($button);
            } catch (err) {
                console.error('Fallback copy failed:', err);
            }

            $temp.remove();
        },

        /**
         * Show copy success feedback
         */
        showCopySuccess: function ($button) {
            if ($button && $button.length) {
                const originalText = $button.text();
                $button.text('✓ ' + UrlExporter.i18n.copied).prop('disabled', true);

                setTimeout(() => {
                    $button.text(originalText).prop('disabled', false);
                }, 2000);
            }
        },

        /**
         * Escape HTML to prevent XSS
         */
        escapeHtml: function (text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return String(text).replace(/[&<>"']/g, m => map[m]);
        },

        /**
         * Truncate URL for display
         */
        truncateURL: function (url, maxLength = 50) {
            if (url.length <= maxLength) {
                return this.escapeHtml(url);
            }
            return this.escapeHtml(url.substring(0, maxLength) + '...');
        }
    };

    /**
     * Initialize on document ready
     */
    $(document).ready(function () {
        URLExporterModule.init();
    });

})(jQuery);