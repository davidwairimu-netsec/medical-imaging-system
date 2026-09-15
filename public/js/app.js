/**
 * Digital Medical Imaging Management System
 * Main Application JavaScript
 */

(function() {
    'use strict';

    // ============================================================
    // SIDEBAR TOGGLE (Mobile)
    // ============================================================
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');

    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('show');
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(e) {
            if (window.innerWidth < 992 &&
                !sidebar.contains(e.target) &&
                !sidebarToggle.contains(e.target)) {
                sidebar.classList.remove('show');
            }
        });
    }

    // ============================================================
    // AUTO-DISMISS ALERTS
    // ============================================================
    document.querySelectorAll('.alert-dismissible').forEach(function(alert) {
        setTimeout(function() {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            if (bsAlert) bsAlert.close();
        }, 8000);
    });

    // ============================================================
    // FORM VALIDATION
    // ============================================================
    document.querySelectorAll('form[novalidate]').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            let valid = true;

            form.querySelectorAll('[required]').forEach(function(field) {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    valid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            });

            // Email validation
            form.querySelectorAll('input[type="email"]').forEach(function(field) {
                if (field.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(field.value)) {
                    field.classList.add('is-invalid');
                    valid = false;
                }
            });

            if (!valid) {
                e.preventDefault();
                e.stopPropagation();

                const firstInvalid = form.querySelector('.is-invalid');
                if (firstInvalid) {
                    firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstInvalid.focus();
                }
            }
        });

        // Remove invalid class on input
        form.querySelectorAll('.form-control, .form-select').forEach(function(field) {
            field.addEventListener('input', function() {
                this.classList.remove('is-invalid');
            });
        });
    });

    // ============================================================
    // CONFIRM DIALOGS
    // ============================================================
    document.querySelectorAll('[data-confirm]').forEach(function(el) {
        el.addEventListener('click', function(e) {
            if (!confirm(this.dataset.confirm)) {
                e.preventDefault();
            }
        });
    });

    // ============================================================
    // TOOLTIPS
    // ============================================================
    const tooltipTriggerList = [].slice.call(
        document.querySelectorAll('[title]')
    );
    tooltipTriggerList.forEach(function(el) {
        new bootstrap.Tooltip(el, { trigger: 'hover' });
    });

    // ============================================================
    // PRINT CURRENT DATE
    // ============================================================
    document.querySelectorAll('[data-current-date]').forEach(function(el) {
        el.textContent = new Date().toLocaleDateString();
    });

})();