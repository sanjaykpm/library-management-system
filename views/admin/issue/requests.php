<?php require_once 'views/layouts/header.php'; ?>
<div class="row">
    <div class="col-md-3">
        <?php require_once 'views/layouts/admin_sidebar.php'; ?>
    </div>
    <div class="col-md-9">
        <?php flash('request_success'); ?>
        <?php flash('request_danger'); ?>
        
        <h1 class="mb-4">Book Issue Requests</h1>

        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form action="<?php echo URLROOT; ?>/issue/requests" method="get" class="row g-3">
                    <div class="col-md-10">
                        <input type="text" name="search" class="form-control" placeholder="Search by Student Name, ID or Book Title..." value="<?php echo $data['search']; ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-secondary w-100">Search</button>
                    </div>
                </form>
            </div>
        </div>
        
        <?php if(empty($data['requests'])): ?>
            <div class="alert alert-info">No pending requests found.</div>
        <?php else: ?>
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Accession No</th>
                                    <th>Book Title</th>
                                    <th>Author</th>
                                    <th>Student ID</th>
                                    <th>Student Name</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($data['requests'] as $request) : ?>
                                    <tr>
                                        <td><?php echo $request->accession_no; ?></td>
                                        <td><?php echo $request->book_title; ?></td>
                                        <td class="small text-muted"><?php echo $request->author ?? 'Unknown Author'; ?></td>
                                        <td><?php echo $request->student_id; ?></td>
                                        <td><?php echo $request->user_name; ?></td>
                                        <td>
                                            <?php if($request->available_quantity > 0): ?>
                                                <span class="badge bg-success bg-opacity-10 text-success">Available</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger bg-opacity-10 text-danger">Out of Stock</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <button type="button" 
                                                        class="btn btn-success btn-sm" 
                                                        onclick="openActionModal('approve', <?php echo $request->id; ?>, '<?php echo addslashes($request->book_title); ?>', '<?php echo addslashes($request->user_name); ?>')"
                                                        <?php echo ($request->available_quantity <= 0) ? 'disabled' : ''; ?>>
                                                    <i class="fas fa-check"></i> Approve
                                                </button>
                                                
                                                <button type="button" 
                                                        class="btn btn-danger btn-sm" 
                                                        onclick="openActionModal('reject', <?php echo $request->id; ?>, '<?php echo addslashes($request->book_title); ?>', '<?php echo addslashes($request->user_name); ?>')">
                                                    <i class="fas fa-times"></i> Reject
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if($data['total_pages'] > 1): ?>
                    <nav aria-label="Page navigation" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?php echo ($data['current_page'] <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $data['current_page'] - 1; ?>&search=<?php echo $data['search']; ?>">Previous</a>
                            </li>
                            <?php for($i = 1; $i <= $data['total_pages']; $i++): ?>
                                <li class="page-item <?php echo ($data['current_page'] == $i) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo $data['search']; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?php echo ($data['current_page'] >= $data['total_pages']) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $data['current_page'] + 1; ?>&search=<?php echo $data['search']; ?>">Next</a>
                            </li>
                        </ul>
                    </nav>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Single Dynamic Modal -->
<div class="modal fade" id="actionModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" id="modalTitle">Confirm Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div id="modalBodyText" class="mb-3">All details...</div>
                <div class="alert" id="modalAlert" style="display:none;"></div>
            </div>
            <div class="modal-footer border-0 bg-light">
                <button type="button" class="btn btn-link text-muted text-decoration-none" data-bs-dismiss="modal">Cancel</button>
                <form id="actionForm" method="post" class="ajax-form">
                    <?php echo Csrf::csrfField(); ?>
                    <button type="submit" id="modalSubmitBtn" class="btn px-4 rounded-pill">Confirm</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Single Modal Logic
    const actionModalEl = document.getElementById('actionModal');
    let actionModalInstance = null;

    function openActionModal(type, id, bookTitle, userName) {
        // Initialize modal only if needed
        if (!actionModalInstance) {
            actionModalInstance = new bootstrap.Modal(actionModalEl);
        }

        const modalTitle = document.getElementById('modalTitle');
        const modalBodyText = document.getElementById('modalBodyText');
        const modalSubmitBtn = document.getElementById('modalSubmitBtn');
        const actionForm = document.getElementById('actionForm');

        if (type === 'approve') {
            modalTitle.innerText = 'Approve Request';
            modalTitle.className = 'modal-title fw-bold text-success';
            modalBodyText.innerHTML = `Are you sure you want to <strong>approve</strong> the request for book <br>"<span class='text-primary'>${bookTitle}</span>"<br> by student <strong>${userName}</strong>?`;
            modalSubmitBtn.innerText = 'Confirm Approve';
            modalSubmitBtn.className = 'btn btn-success px-4 rounded-pill';
            actionForm.action = `<?php echo URLROOT; ?>/issue/approve_request/${id}`;
        } else {
            modalTitle.innerText = 'Reject Request';
            modalTitle.className = 'modal-title fw-bold text-danger';
            modalBodyText.innerHTML = `Are you sure you want to <strong>reject</strong> the request for book <br>"<span class='text-primary'>${bookTitle}</span>"<br> by student <strong>${userName}</strong>?`;
            modalSubmitBtn.innerText = 'Confirm Reject';
            modalSubmitBtn.className = 'btn btn-danger px-4 rounded-pill';
            actionForm.action = `<?php echo URLROOT; ?>/issue/reject_request/${id}`;
        }

        actionModalInstance.show();
    }
</script>

<?php require_once 'views/layouts/footer.php'; ?>
