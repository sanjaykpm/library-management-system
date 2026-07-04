document.addEventListener('DOMContentLoaded', function () {
    console.log('Library Management System initialized.');

    // --- Global AJAX Form Handler (Prevents Blinking & Double Submission) ---
    document.addEventListener('submit', function (e) {
        const form = e.target.closest('.ajax-form');
        if (!form) return;

        e.preventDefault();
        e.stopImmediatePropagation(); // Prevent other listeners

        // 1. Prevent Double Submission
        if (form.dataset.submitting === 'true') return;
        form.dataset.submitting = 'true';

        // 2. Loading State
        const submitBtn = form.querySelector('[type="submit"]');
        const originalBtnHtml = submitBtn ? submitBtn.innerHTML : '';
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';
        }

        const formData = new FormData(form);
        const url = form.getAttribute('action');

        // Find logical container (row, list item, or card column)
        const elementToRemove = form.closest('tr') || form.closest('.list-group-item') || form.closest('.col-md-4') || form.closest('.col');

        // Identify Modal
        const modalElement = form.closest('.modal');
        let modal = null;
        if (modalElement) {
            modal = bootstrap.Modal.getOrCreateInstance(modalElement);
        }

        fetch(url, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    if (modal) {
                        // 3. CRITICAL: Wait for modal to fully close before modifying DOM
                        modalElement.addEventListener('hidden.bs.modal', function onHidden() {
                            modalElement.removeEventListener('hidden.bs.modal', onHidden); // Cleanup listener

                            // Small delay to ensure backdrop is fully gone and scrollbar restored
                            setTimeout(() => {
                                // Safety Cleanup: Ensure no backdrop remains (Bootstrap edge case)
                                document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                                document.body.classList.remove('modal-open');
                                document.body.style.overflow = '';
                                document.body.style.paddingRight = '';

                                if (elementToRemove) {
                                    fadeOutAndRemove(elementToRemove, () => {
                                        checkEmptyContainer();
                                        showNotification(data.message || 'Action completed successfully', 'success');
                                    });
                                } else {
                                    showNotification(data.message || 'Action completed successfully', 'success');
                                    // If no element to remove, maybe we need to reload or update UI another way
                                    // But for now, just notify.
                                    if (data.redirect) window.location.href = data.redirect;
                                }
                            }, 100);
                        }, { once: true });

                        modal.hide();
                    } else {
                        // Non-modal action
                        if (elementToRemove) {
                            fadeOutAndRemove(elementToRemove, () => {
                                checkEmptyContainer();
                                showNotification(data.message || 'Action completed successfully', 'success');
                            });
                        } else {
                            showNotification(data.message || 'Action completed successfully', 'success');
                        }
                    }
                } else {
                    // Handle Server Error
                    handleError(form, submitBtn, originalBtnHtml, data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                handleError(form, submitBtn, originalBtnHtml, 'An error occurred. Please try again.');
            })
            .finally(() => {
                // If success and modal, cleanup happens in hidden listener
                // If error, cleanup happens here
                if (submitBtn && (!modal || !submitBtn.disabled)) {
                    // Only reset if we didn't succeed (success keeps proper state until removal)
                    // Actually, on error we reset. On success, the modal closes so button state matters less, 
                    // but good to reset if the form isn't removed.
                }
            });
    });

    // --- Helper Functions ---

    function fadeOutAndRemove(element, callback) {
        element.style.transition = 'all 0.5s ease-out';
        element.style.opacity = '0';
        element.style.transform = 'scale(0.95)'; // Slight shrink 

        setTimeout(() => {
            element.remove();
            if (callback) callback();
        }, 500);
    }

    function handleError(form, btn, originalHtml, message) {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }
        delete form.dataset.submitting;
        showNotification(message, 'danger');

        // If modal is open, keep it open so user can retry or cancel
    }

    function showNotification(message, type = 'success') {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show shadow`;
        alertDiv.role = 'alert';
        alertDiv.style.position = 'fixed';
        alertDiv.style.top = '20px';
        alertDiv.style.right = '20px';
        alertDiv.style.zIndex = '9999';
        alertDiv.style.minWidth = '300px';
        alertDiv.style.maxWidth = '500px';

        alertDiv.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} me-2 fa-lg"></i>
                <div class="flex-grow-1">${message}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;

        document.body.appendChild(alertDiv);

        setTimeout(() => {
            if (alertDiv && document.body.contains(alertDiv)) {
                const bsAlert = new bootstrap.Alert(alertDiv);
                bsAlert.close();
            }
        }, 5000);
    }

    function checkEmptyContainer() {
        // Check tables
        const tbody = document.querySelector('tbody');
        if (tbody && tbody.children.length === 0) {
            const table = tbody.closest('table');
            if (table) {
                const cardBody = table.closest('.card-body');
                if (cardBody) {
                    cardBody.innerHTML = '<div class="alert alert-info text-center m-3">No records found.</div>';
                }
            }
        }

        // Check list groups
        const listGroup = document.querySelector('.list-group');
        if (listGroup && listGroup.children.length === 0) {
            listGroup.innerHTML = '<div class="alert alert-info text-center m-3">No entries found.</div>';
        }

        // Check card rows
        const bookRows = document.querySelectorAll('.row.row-cols-1, .row.g-4');
        bookRows.forEach(row => {
            if (row.children.length === 0 || row.querySelectorAll('.col').length === 0) { // Check for empty cols
                row.innerHTML = '<div class="col-12"><div class="alert alert-info text-center">No books available.</div></div>';
            }
        });
    }

    // --- Dashboard Charts ---
    const issuesCtx = document.getElementById('issuesChart');
    const categoryCtx = document.getElementById('categoryChart');

    if (issuesCtx || categoryCtx) {
        // Use URLROOT if defined, otherwise relative path fallback
        const baseUrl = (typeof URLROOT !== 'undefined') ? URLROOT : '/library-management-system';

        fetch(`${baseUrl}/admin/stats`)
            .then(response => response.json())
            .then(data => {
                // 1. Issue Trends (Line Chart)
                if (issuesCtx && data.monthly_issues) {
                    new Chart(issuesCtx, {
                        type: 'line',
                        data: {
                            labels: data.monthly_issues.map(item => item.month),
                            datasets: [{
                                label: 'Books Issued',
                                data: data.monthly_issues.map(item => item.count),
                                borderColor: '#0d6efd',
                                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                                tension: 0.4,
                                fill: true
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: { display: false }
                            },
                            scales: {
                                y: { beginAtZero: true, ticks: { precision: 0 } }
                            }
                        }
                    });
                }

                // 2. Books by Category (Doughnut Chart)
                if (categoryCtx && data.books_by_category) {
                    new Chart(categoryCtx, {
                        type: 'doughnut',
                        data: {
                            labels: data.books_by_category.map(item => item.name),
                            datasets: [{
                                data: data.books_by_category.map(item => item.count),
                                backgroundColor: [
                                    '#0d6efd', '#198754', '#ffc107', '#0dcaf0', '#dc3545', '#6610f2'
                                ]
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: { position: 'bottom' }
                            }
                        }
                    });
                }

                // 3. Top Books (Bar Chart)
                const topBooksCtx = document.getElementById('topBooksChart');
                if (topBooksCtx && data.top_books) {
                    new Chart(topBooksCtx, {
                        type: 'bar',
                        data: {
                            labels: data.top_books.map(item => item.title),
                            datasets: [{
                                label: 'Times Issued',
                                data: data.top_books.map(item => item.count),
                                backgroundColor: '#198754',
                                borderColor: '#198754',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            indexAxis: 'y', // Horizontal bar chart for better title readability
                            responsive: true,
                            plugins: {
                                legend: { display: false }
                            },
                            scales: {
                                x: { beginAtZero: true, ticks: { precision: 0 } }
                            }
                        }
                    });
                }
            })
            .catch(error => console.error('Error fetching stats:', error));
    }
});
