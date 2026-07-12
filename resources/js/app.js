

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// Razorpay checkout — delegated so it works regardless of how/when the
// tenant-action-button is rendered into the page. The webhook
// (TenantWebhookController::handleRazorpay) remains the sole source of
// truth for recording paid orders; this handler only opens the checkout
// modal and gives the buyer visual confirmation.
document.addEventListener('click', function (e) {
    const btn = e.target.closest('.razorpay-btn');
    if (!btn) return;

    e.preventDefault();

    if (typeof Razorpay === 'undefined') {
        console.error('Razorpay checkout.js did not load.');
        return;
    }

    const rzp = new Razorpay({
        key: btn.dataset.key,
        amount: btn.dataset.amount,
        currency: btn.dataset.currency || 'INR',
        name: btn.dataset.name,
        description: btn.dataset.description,
        notes: {
            product_id: btn.dataset.productId || '',
            tenant_id: btn.dataset.tenantId || '',
        },
        handler: function () {
            btn.outerHTML = '<span class="eos-btn eos-btn-secondary" style="opacity:0.85;cursor:default;">'
                + '<i class="ti ti-check"></i> Payment received — thank you!</span>';
        },
        modal: {
            ondismiss: function () {
                // Button stays as-is, still clickable for another attempt.
            },
        },
    });

    rzp.on('payment.failed', function () {
        console.warn('Razorpay payment failed or was cancelled.');
    });

    rzp.open();
});
