/**
 * PropIntel CRM — Main JavaScript
 */

'use strict';

// ── Toast Notifications ────────────────────────────────────────────────────────
function showToast(message, type = 'success') {
    const colors = { success: '#10b981', danger: '#ef4444', warning: '#f59e0b', info: '#3b82f6' };
    const bg     = colors[type] || colors.info;

    const el = document.createElement('div');
    el.className = 'position-fixed top-0 end-0 m-3';
    el.style.zIndex = '9999';
    el.innerHTML = `
        <div class="toast show align-items-center text-white border-0" role="alert"
             style="background:${bg};border-radius:10px;min-width:260px;">
            <div class="d-flex">
                <div class="toast-body fw-semibold">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" onclick="this.closest('.position-fixed').remove()"></button>
            </div>
        </div>`;
    document.body.appendChild(el);
    setTimeout(() => el.remove(), 4000);
}

// ── HTMX global events ────────────────────────────────────────────────────────
document.addEventListener('htmx:afterSwap', function (e) {
    // Re-initialize Bootstrap tooltips after HTMX swaps
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
        new bootstrap.Tooltip(el);
    });
});

document.addEventListener('htmx:responseError', function (e) {
    showToast('An error occurred. Please try again.', 'danger');
});

// ── Mobile sidebar toggle ─────────────────────────────────────────────────────
const sidebarToggle = document.getElementById('sidebarToggle');
if (sidebarToggle) {
    sidebarToggle.addEventListener('click', () => {
        document.getElementById('sidebar').classList.toggle('show');
    });
}

// ── Active nav link ───────────────────────────────────────────────────────────
(function highlightNav() {
    const path = window.location.pathname.split('/')[1];
    document.querySelectorAll('#sidebar .nav-link').forEach(link => {
        const href = link.getAttribute('href') || '';
        if (href === '/' + path || (path === '' && href === '/')) {
            link.classList.add('active');
        }
    });
})();

// ── Confirm dialogs ───────────────────────────────────────────────────────────
document.addEventListener('click', function (e) {
    const btn = e.target.closest('[data-confirm]');
    if (btn) {
        if (!confirm(btn.dataset.confirm)) {
            e.preventDefault();
            e.stopPropagation();
        }
    }
});

// ── Quick search dropdown ─────────────────────────────────────────────────────
(function initQuickSearch() {
    const input = document.getElementById('quickSearch');
    const results = document.getElementById('quick-search-results');
    if (!input || !results) return;

    // Hide on outside click
    document.addEventListener('click', (e) => {
        if (!input.contains(e.target) && !results.contains(e.target)) {
            results.innerHTML = '';
        }
    });

    input.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') results.innerHTML = '';
    });
})();

// ── Lead status inline update ─────────────────────────────────────────────────
document.addEventListener('change', function (e) {
    const sel = e.target.closest('.status-select-inline');
    if (!sel) return;

    const leadId = sel.dataset.leadId;
    const status = sel.value;
    const wrap   = sel.closest('.status-wrap');

    fetch(`/leads/${leadId}/status`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || '',
        },
        body: `status=${encodeURIComponent(status)}&_csrf_token=${encodeURIComponent(
            document.querySelector('meta[name="csrf-token"]')?.content || ''
        )}`,
    })
    .then(r => r.text())
    .then(html => {
        showToast('Status updated', 'success');
    })
    .catch(() => showToast('Failed to update status', 'danger'));
});

// ── Deal Calculator tabs ──────────────────────────────────────────────────────
(function initCalcTabs() {
    const tabs = document.querySelectorAll('.calc-tab');
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            document.querySelectorAll('.calc-form').forEach(f => f.classList.add('d-none'));
            const target = document.getElementById('form-' + tab.dataset.calc);
            if (target) target.classList.remove('d-none');
            document.getElementById('calc_type').value = tab.dataset.calc;
        });
    });
})();

// ── Copy to clipboard ─────────────────────────────────────────────────────────
document.addEventListener('click', function (e) {
    const btn = e.target.closest('[data-copy]');
    if (!btn) return;
    const text = document.querySelector(btn.dataset.copy)?.innerText || btn.dataset.copy;
    navigator.clipboard.writeText(text).then(() => showToast('Copied!', 'info'));
});

// ── Bootstrap tooltips (initial) ──────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
        new bootstrap.Tooltip(el);
    });

    // Auto-dismiss alerts
    document.querySelectorAll('.alert-auto-dismiss').forEach(el => {
        setTimeout(() => el.classList.add('fade'), 3000);
        setTimeout(() => el.remove(), 3500);
    });
});

// ── Score progress ring ───────────────────────────────────────────────────────
function updateScoreRing(score) {
    const ring  = document.getElementById('scoreRing');
    const label = document.getElementById('scoreLabel');
    if (!ring || !label) return;

    const circumference = 2 * Math.PI * 45;
    const offset        = circumference - (score / 100) * circumference;
    ring.style.strokeDashoffset = offset;

    let color = '#6b7280';
    if (score >= 80) color = '#ef4444';
    else if (score >= 60) color = '#f59e0b';
    else if (score >= 40) color = '#3b82f6';
    ring.style.stroke = color;

    label.textContent = score;
}
