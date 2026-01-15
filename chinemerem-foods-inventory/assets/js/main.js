/**
 * Chinemerem Foods Inventory - Main JavaScript
 */

(function($) {
    'use strict';

    // Global CFI Object
    window.CFI = window.CFI || {};

    // Utility Functions
    CFI.utils = {
        /**
         * Format number with commas
         */
        formatNumber: function(number) {
            return parseFloat(number || 0).toLocaleString('en-NG', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        },

        /**
         * Format currency
         */
        formatCurrency: function(amount) {
            return '₦' + this.formatNumber(amount);
        },

        /**
         * Parse formatted number
         */
        parseNumber: function(str) {
            if (!str) return 0;
            return parseFloat(String(str).replace(/,/g, '')) || 0;
        },

        /**
         * Generate unique ID
         */
        uniqueId: function() {
            return Date.now().toString(36) + Math.random().toString(36).substr(2);
        },

        /**
         * Debounce function
         */
        debounce: function(func, wait) {
            let timeout;
            return function(...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), wait);
            };
        },

        /**
         * Format date
         */
        formatDate: function(date) {
            return new Date(date).toLocaleDateString('en-NG', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
        },

        /**
         * Format time
         */
        formatTime: function(time) {
            return time ? time.substring(0, 5) : '';
        }
    };

    // Toast Notifications
    CFI.toast = {
        container: null,

        init: function() {
            if (!this.container) {
                this.container = $('<div class="cfi-toast-container"></div>');
                $('body').append(this.container);
            }
        },

        show: function(message, type = 'info', duration = 3000) {
            this.init();
            const toast = $(`
                <div class="cfi-toast cfi-toast-${type}">
                    <i class="fas ${this.getIcon(type)}"></i>
                    <span>${message}</span>
                </div>
            `);
            this.container.append(toast);
            setTimeout(() => toast.fadeOut(300, () => toast.remove()), duration);
        },

        getIcon: function(type) {
            const icons = {
                success: 'fa-check-circle',
                error: 'fa-times-circle',
                warning: 'fa-exclamation-triangle',
                info: 'fa-info-circle'
            };
            return icons[type] || icons.info;
        },

        success: function(message) { this.show(message, 'success'); },
        error: function(message) { this.show(message, 'error'); },
        warning: function(message) { this.show(message, 'warning'); },
        info: function(message) { this.show(message, 'info'); }
    };

    // AJAX Handler
    CFI.ajax = {
        request: function(action, data = {}, options = {}) {
            return new Promise((resolve, reject) => {
                $.ajax({
                    url: cfiData.ajaxUrl,
                    type: options.method || 'POST',
                    data: {
                        action: 'cfi_' + action,
                        nonce: cfiData.nonce,
                        ...data
                    },
                    success: function(response) {
                        if (response.success) {
                            resolve(response.data);
                        } else {
                            reject(response.data?.message || 'An error occurred');
                        }
                    },
                    error: function(xhr, status, error) {
                        reject(error || 'Network error');
                    }
                });
            });
        }
    };

    // Modal Handler
    CFI.modal = {
        show: function(content, options = {}) {
            const overlay = $(`
                <div class="cfi-modal-overlay">
                    <div class="cfi-modal">
                        <div class="cfi-modal-header">
                            <h3>${options.title || 'Confirmation'}</h3>
                            <button class="cfi-modal-close">&times;</button>
                        </div>
                        <div class="cfi-modal-body"></div>
                        <div class="cfi-modal-footer">
                            ${options.showCancel !== false ? '<button class="cfi-btn cfi-btn-outline cfi-modal-cancel">Cancel</button>' : ''}
                            <button class="cfi-btn cfi-btn-primary cfi-modal-confirm">${options.confirmText || 'Confirm'}</button>
                        </div>
                    </div>
                </div>
            `);

            overlay.find('.cfi-modal-body').html(content);
            $('body').append(overlay);

            setTimeout(() => overlay.addClass('visible'), 10);

            return new Promise((resolve, reject) => {
                overlay.find('.cfi-modal-confirm').on('click', function() {
                    CFI.modal.close(overlay);
                    resolve(true);
                });

                overlay.find('.cfi-modal-cancel, .cfi-modal-close').on('click', function() {
                    CFI.modal.close(overlay);
                    resolve(false);
                });

                overlay.on('click', function(e) {
                    if ($(e.target).is(overlay)) {
                        CFI.modal.close(overlay);
                        resolve(false);
                    }
                });
            });
        },

        close: function(overlay) {
            overlay.removeClass('visible');
            setTimeout(() => overlay.remove(), 300);
        }
    };

    // Header Functions
    CFI.header = {
        init: function() {
            this.initClock();
            this.initMenuToggle();
            this.initLogout();
        },

        initClock: function() {
            const updateClock = () => {
                const now = new Date();
                $('#cfi-current-date').text(now.toLocaleDateString('en-NG', {
                    weekday: 'short',
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric'
                }));
                $('#cfi-current-time').text(now.toLocaleTimeString('en-NG', {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                }));
            };
            updateClock();
            setInterval(updateClock, 1000);
        },

        initMenuToggle: function() {
            $('#cfi-menu-toggle').on('click', function() {
                $(this).toggleClass('active');
                $('#cfi-main-nav').toggleClass('open');
            });
        },

        initLogout: function() {
            $('#cfi-logout-btn').on('click', function() {
                CFI.ajax.request('logout').then(function(data) {
                    window.location.href = data.redirect;
                }).catch(function(error) {
                    CFI.toast.error(error);
                });
            });
        }
    };

    // Scroll to Top
    CFI.scrollTop = {
        init: function() {
            const btn = $('#cfi-scroll-top');
            const indicator = btn.find('.cfi-scroll-indicator');

            $(window).on('scroll', CFI.utils.debounce(function() {
                const scrollTop = $(window).scrollTop();
                const docHeight = $(document).height() - $(window).height();
                const scrollPercent = (scrollTop / docHeight) * 100;

                if (scrollTop > 200) {
                    btn.addClass('visible');
                } else {
                    btn.removeClass('visible');
                }

                // Update progress circle
                const circumference = 2 * Math.PI * 45;
                const offset = circumference - (scrollPercent / 100 * circumference);
                indicator.css('stroke-dashoffset', offset);
            }, 10));

            btn.on('click', function() {
                $('html, body').animate({ scrollTop: 0 }, 500);
            });
        }
    };

    // Login Handler
    CFI.login = {
        init: function() {
            const form = $('#cfi-login-form');
            if (!form.length) return;

            form.on('submit', function(e) {
                e.preventDefault();
                const btn = form.find('button[type="submit"]');
                btn.prop('disabled', true).text('Logging in...');

                CFI.ajax.request('login', {
                    username: form.find('[name="username"]').val(),
                    password: form.find('[name="password"]').val(),
                    remember: form.find('[name="remember"]').is(':checked')
                }).then(function(data) {
                    CFI.toast.success(data.message);
                    setTimeout(() => window.location.href = data.redirect, 500);
                }).catch(function(error) {
                    CFI.toast.error(error);
                    btn.prop('disabled', false).text('Login');
                });
            });
        }
    };

    // Order Handler
    CFI.order = {
        items: [],
        products: [],

        init: function() {
            const container = $('#cfi-order-form');
            if (!container.length) return;

            this.loadProducts();
            this.initPaymentMethods();
            this.initSubmit();
        },

        loadProducts: function() {
            const self = this;
            CFI.ajax.request('get_products').then(function(data) {
                self.products = data.products;
                self.renderTable();
            }).catch(function(error) {
                CFI.toast.error(error);
            });
        },

        renderTable: function() {
            const tbody = $('#cfi-order-table tbody');
            tbody.empty();

            this.products.forEach(function(product) {
                tbody.append(`
                    <tr data-product-id="${product.id}">
                        <td data-label="Item">${product.name}</td>
                        <td data-label="Price">
                            <span class="cfi-product-price" data-price="${product.price}">${CFI.utils.formatCurrency(product.price)}</span>
                        </td>
                        <td data-label="Quantity">
                            <input type="number" class="cfi-input cfi-qty-input" min="0" step="0.01" value="0">
                        </td>
                        <td data-label="Discount">
                            <input type="number" class="cfi-input cfi-discount-input" min="0" step="0.01" value="0">
                        </td>
                        <td data-label="Total">
                            <span class="cfi-row-total">₦0.00</span>
                        </td>
                    </tr>
                `);
            });

            this.initCalculations();
        },

        initCalculations: function() {
            const self = this;

            $(document).on('input', '.cfi-qty-input, .cfi-discount-input', function() {
                const row = $(this).closest('tr');
                self.calculateRowTotal(row);
                self.calculateGrandTotal();
            });
        },

        calculateRowTotal: function(row) {
            const price = parseFloat(row.find('.cfi-product-price').data('price')) || 0;
            const qty = parseFloat(row.find('.cfi-qty-input').val()) || 0;
            const discount = parseFloat(row.find('.cfi-discount-input').val()) || 0;
            const total = (price * qty) - discount;
            row.find('.cfi-row-total').text(CFI.utils.formatCurrency(Math.max(0, total)));
        },

        calculateGrandTotal: function() {
            let totalQty = 0;
            let totalAmount = 0;
            let totalDiscount = 0;

            $('#cfi-order-table tbody tr').each(function() {
                const qty = parseFloat($(this).find('.cfi-qty-input').val()) || 0;
                const price = parseFloat($(this).find('.cfi-product-price').data('price')) || 0;
                const discount = parseFloat($(this).find('.cfi-discount-input').val()) || 0;

                if (qty > 0) {
                    totalQty += qty;
                    totalAmount += price * qty;
                    totalDiscount += discount;
                }
            });

            const grandTotal = totalAmount - totalDiscount;

            $('#cfi-total-qty').text(CFI.utils.formatNumber(totalQty));
            $('#cfi-total-amount').text(CFI.utils.formatCurrency(totalAmount));
            $('#cfi-total-discount').text(CFI.utils.formatCurrency(totalDiscount));
            $('#cfi-grand-total').text(CFI.utils.formatCurrency(grandTotal));
        },

        initPaymentMethods: function() {
            const self = this;

            $('.cfi-payment-method').on('click', function() {
                const $this = $(this);
                $this.toggleClass('active');

                // Show/hide bank options
                if ($this.data('method') === 'transfer') {
                    $('#cfi-bank-options').toggleClass('visible', $this.hasClass('active'));
                }

                // Show/hide split payment
                const transferActive = $('.cfi-payment-method[data-method="transfer"]').hasClass('active');
                const cashActive = $('.cfi-payment-method[data-method="cash"]').hasClass('active');
                $('#cfi-split-payment').toggleClass('visible', transferActive && cashActive);
            });

            // Split payment validation
            $('#cfi-transfer-amount, #cfi-cash-amount').on('input', function() {
                self.validateSplitPayment();
            });
        },

        validateSplitPayment: function() {
            const grandTotal = CFI.utils.parseNumber($('#cfi-grand-total').text().replace('₦', ''));
            const transferAmount = parseFloat($('#cfi-transfer-amount').val()) || 0;
            const cashAmount = parseFloat($('#cfi-cash-amount').val()) || 0;
            const total = transferAmount + cashAmount;

            const isValid = Math.abs(total - grandTotal) < 0.01;
            $('#cfi-submit-order').prop('disabled', !isValid);

            if (!isValid && total > 0) {
                const diff = grandTotal - total;
                CFI.toast.warning(`Amount difference: ${CFI.utils.formatCurrency(diff)}`);
            }
        },

        initSubmit: function() {
            const self = this;

            $('#cfi-submit-order').on('click', async function() {
                const btn = $(this);
                
                // Validate payment confirmation
                if (!$('#cfi-payment-confirm').is(':checked')) {
                    CFI.toast.warning('Please confirm payment has been received');
                    return;
                }

                // Build order data
                const orderData = self.buildOrderData();
                
                if (orderData.items.length === 0) {
                    CFI.toast.warning('Please add at least one item');
                    return;
                }

                // Show confirmation modal
                const confirmed = await CFI.modal.show(self.buildConfirmationContent(orderData), {
                    title: 'Confirm Order',
                    confirmText: 'Submit Order'
                });

                if (!confirmed) return;

                btn.prop('disabled', true).text('Submitting...');

                CFI.ajax.request('submit_order', {
                    order: JSON.stringify(orderData)
                }).then(function(data) {
                    CFI.toast.success(data.message);
                    setTimeout(() => location.reload(), 500);
                }).catch(function(error) {
                    CFI.toast.error(error);
                    btn.prop('disabled', false).text('Submit Order');
                });
            });
        },

        buildOrderData: function() {
            const items = [];
            let totalQty = 0;
            let totalAmount = 0;
            let totalDiscount = 0;

            $('#cfi-order-table tbody tr').each(function() {
                const row = $(this);
                const qty = parseFloat(row.find('.cfi-qty-input').val()) || 0;
                
                if (qty > 0) {
                    const productId = row.data('product-id');
                    const price = parseFloat(row.find('.cfi-product-price').data('price')) || 0;
                    const discount = parseFloat(row.find('.cfi-discount-input').val()) || 0;
                    const total = (price * qty) - discount;

                    items.push({
                        product_id: productId,
                        quantity: qty,
                        price: price,
                        discount: discount,
                        total: Math.max(0, total)
                    });

                    totalQty += qty;
                    totalAmount += price * qty;
                    totalDiscount += discount;
                }
            });

            const grandTotal = totalAmount - totalDiscount;
            const transferActive = $('.cfi-payment-method[data-method="transfer"]').hasClass('active');
            const cashActive = $('.cfi-payment-method[data-method="cash"]').hasClass('active');

            let paymentMethod = 'cash';
            let transferAmount = 0;
            let cashAmount = grandTotal;
            let bankName = '';

            if (transferActive && cashActive) {
                paymentMethod = 'both';
                transferAmount = parseFloat($('#cfi-transfer-amount').val()) || 0;
                cashAmount = parseFloat($('#cfi-cash-amount').val()) || 0;
                bankName = $('input[name="bank"]:checked').val() || 'Moniepoint MFB';
            } else if (transferActive) {
                paymentMethod = 'transfer';
                transferAmount = grandTotal;
                cashAmount = 0;
                bankName = $('input[name="bank"]:checked').val() || 'Moniepoint MFB';
            }

            return {
                items: items,
                total_quantity: totalQty,
                total_amount: totalAmount,
                discount_amount: totalDiscount,
                grand_total: grandTotal,
                payment_method: paymentMethod,
                transfer_amount: transferAmount,
                cash_amount: cashAmount,
                bank_name: bankName,
                order_type: 'cash'
            };
        },

        buildConfirmationContent: function(orderData) {
            let html = '<div class="cfi-order-confirmation">';
            html += '<h4>Order Summary</h4>';
            html += '<table class="cfi-table"><thead><tr><th>Item</th><th>Qty</th><th>Total</th></tr></thead><tbody>';
            
            orderData.items.forEach(function(item) {
                const product = CFI.order.products.find(p => p.id == item.product_id);
                html += `<tr>
                    <td>${product ? product.name : 'Unknown'}</td>
                    <td>${item.quantity}</td>
                    <td>${CFI.utils.formatCurrency(item.total)}</td>
                </tr>`;
            });

            html += '</tbody></table>';
            html += `<div class="cfi-order-total">
                <div class="cfi-order-total-item">
                    <span class="cfi-order-total-label">Total Items</span>
                    <span class="cfi-order-total-value">${orderData.items.length}</span>
                </div>
                <div class="cfi-order-total-item">
                    <span class="cfi-order-total-label">Grand Total</span>
                    <span class="cfi-order-total-value">${CFI.utils.formatCurrency(orderData.grand_total)}</span>
                </div>
                <div class="cfi-order-total-item">
                    <span class="cfi-order-total-label">Payment</span>
                    <span class="cfi-order-total-value">${orderData.payment_method}</span>
                </div>
            </div>`;
            html += '</div>';

            return html;
        }
    };

    // Stock Handler
    CFI.stock = {
        init: function() {
            const container = $('#cfi-stock-form');
            if (!container.length) return;

            this.loadStock();
            this.initSubmit();
        },

        loadStock: function() {
            const date = $('#cfi-stock-date').val() || new Date().toISOString().split('T')[0];
            
            CFI.ajax.request('get_stock', { date: date }).then(function(data) {
                CFI.stock.renderTable(data.stock);
            }).catch(function(error) {
                CFI.toast.error(error);
            });
        },

        renderTable: function(stockData) {
            const tbody = $('#cfi-stock-table tbody');
            tbody.empty();

            stockData.forEach(function(item) {
                tbody.append(`
                    <tr data-product-id="${item.product_id}">
                        <td data-label="Item">${item.product_name}</td>
                        <td data-label="Opening">
                            <input type="number" class="cfi-input" value="${item.opening}" readonly>
                        </td>
                        <td data-label="Import">
                            <input type="number" class="cfi-input" value="${item.import_qty}" readonly>
                        </td>
                        <td data-label="Cash Supply">
                            <input type="number" class="cfi-input" value="${item.cash_supply}" readonly>
                        </td>
                        <td data-label="Credit Supply">
                            <input type="number" class="cfi-input" value="${item.credit_supply}" readonly>
                        </td>
                        <td data-label="Not Supplied">
                            <input type="number" class="cfi-input" value="${item.not_supplied}" readonly>
                        </td>
                        <td data-label="Supplied Today">
                            <input type="number" class="cfi-input" value="${item.supplied_today}" readonly>
                        </td>
                        <td data-label="To Packing">
                            <input type="number" class="cfi-input cfi-to-packing" min="0" step="0.01" value="${item.to_packing_store}">
                        </td>
                        <td data-label="From Packing">
                            <input type="number" class="cfi-input" value="${item.from_packing_store}" readonly>
                        </td>
                        <td data-label="Closing">
                            <input type="number" class="cfi-input cfi-closing" value="${item.closing}" readonly>
                        </td>
                    </tr>
                `);
            });

            this.initCalculations();
        },

        initCalculations: function() {
            $(document).on('input', '.cfi-to-packing', function() {
                const row = $(this).closest('tr');
                CFI.stock.calculateClosing(row);
            });
        },

        calculateClosing: function(row) {
            const opening = parseFloat(row.find('td:eq(1) input').val()) || 0;
            const importQty = parseFloat(row.find('td:eq(2) input').val()) || 0;
            const cashSupply = parseFloat(row.find('td:eq(3) input').val()) || 0;
            const creditSupply = parseFloat(row.find('td:eq(4) input').val()) || 0;
            const notSupplied = parseFloat(row.find('td:eq(5) input').val()) || 0;
            const suppliedToday = parseFloat(row.find('td:eq(6) input').val()) || 0;
            const toPacking = parseFloat(row.find('.cfi-to-packing').val()) || 0;
            const fromPacking = parseFloat(row.find('td:eq(8) input').val()) || 0;

            const closing = opening + importQty - cashSupply - creditSupply + notSupplied - suppliedToday - toPacking + fromPacking;
            row.find('.cfi-closing').val(closing.toFixed(2));
        },

        initSubmit: function() {
            $('#cfi-save-stock').on('click', function() {
                const btn = $(this);
                const stockData = [];

                $('#cfi-stock-table tbody tr').each(function() {
                    const row = $(this);
                    stockData.push({
                        product_id: row.data('product-id'),
                        to_packing_store: parseFloat(row.find('.cfi-to-packing').val()) || 0
                    });
                });

                btn.prop('disabled', true).text('Saving...');

                CFI.ajax.request('update_stock', {
                    stock: JSON.stringify(stockData)
                }).then(function(data) {
                    CFI.toast.success(data.message);
                    btn.prop('disabled', false).text('Save Changes');
                }).catch(function(error) {
                    CFI.toast.error(error);
                    btn.prop('disabled', false).text('Save Changes');
                });
            });
        }
    };

    // Offline Support
    CFI.offline = {
        queue: [],

        init: function() {
            this.loadQueue();
            this.initListeners();
        },

        loadQueue: function() {
            const stored = localStorage.getItem('cfi_offline_queue');
            if (stored) {
                this.queue = JSON.parse(stored);
            }
        },

        saveQueue: function() {
            localStorage.setItem('cfi_offline_queue', JSON.stringify(this.queue));
        },

        addToQueue: function(type, data) {
            this.queue.push({
                id: CFI.utils.uniqueId(),
                type: type,
                data: data,
                timestamp: Date.now()
            });
            this.saveQueue();
            this.updateBanner();
        },

        syncQueue: function() {
            if (this.queue.length === 0) return Promise.resolve();

            return CFI.ajax.request('sync_offline_data', {
                data: JSON.stringify(this.queue)
            }).then(function(data) {
                CFI.offline.queue = [];
                CFI.offline.saveQueue();
                CFI.offline.updateBanner();
                CFI.toast.success('Offline data synced successfully');
            }).catch(function(error) {
                CFI.toast.error('Failed to sync offline data');
            });
        },

        initListeners: function() {
            const self = this;

            window.addEventListener('online', function() {
                $('#cfi-offline-banner').removeClass('visible');
                self.syncQueue();
            });

            window.addEventListener('offline', function() {
                $('#cfi-offline-banner').addClass('visible');
            });

            // Check initial state
            if (!navigator.onLine) {
                $('#cfi-offline-banner').addClass('visible');
            }
        },

        updateBanner: function() {
            if (this.queue.length > 0) {
                $('#cfi-offline-count').text(this.queue.length);
            }
        }
    };

    // Initialize
    $(document).ready(function() {
        CFI.header.init();
        CFI.scrollTop.init();
        CFI.login.init();
        CFI.order.init();
        CFI.stock.init();
        CFI.offline.init();

        // Add offline banner
        $('body').append('<div class="cfi-offline-banner" id="cfi-offline-banner">You are offline. Data will sync when online. <span id="cfi-offline-count"></span></div>');

        // Initialize number inputs to handle smart input
        $(document).on('focus', 'input[type="number"]', function() {
            if ($(this).val() === '0') {
                $(this).val('');
            }
        });

        $(document).on('blur', 'input[type="number"]', function() {
            if ($(this).val() === '') {
                $(this).val('0');
            }
        });
    });

})(jQuery);
