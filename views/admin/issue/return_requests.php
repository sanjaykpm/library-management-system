<?php require_once 'views/layouts/header.php'; ?>
<div class="row">
    <div class="col-md-3">
        <?php require_once 'views/layouts/admin_sidebar.php'; ?>
    </div>
    <div class="col-md-9">
        <h1 class="mb-4">Return Requests</h1>

        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form action="<?php echo URLROOT; ?>/issue/return_requests" method="get" class="row g-3">
                    <div class="col-md-10">
                        <input type="text" name="search" class="form-control" placeholder="Search by Student Name, ID or Book Title..." value="<?php echo $data['search']; ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-secondary w-100">Search</button>
                    </div>
                </form>
            </div>
        </div>
        <?php flash('return_request_message'); ?>
        
        <?php if(empty($data['requests'])): ?>
            <div class="alert alert-info">No pending return requests found.</div>
        <?php else: ?>
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Student ID</th>
                                    <th>Student Name</th>
                                    <th>Book Title</th>
                                    <th>Author</th>
                                    <th>Accession No</th>
                                    <th>Return Date</th>
                                    <th>Request Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($data['requests'] as $request) : ?>
                                    <?php 
                                        $today = date('Y-m-d');
                                        $isOverdue = $request->return_date < $today;
                                    ?>
                                    <tr class="<?php echo $isOverdue ? 'table-danger' : ''; ?>">
                                        <td><?php echo $request->student_id; ?></td>
                                        <td><?php echo $request->user_name; ?></td>
                                        <td><?php echo $request->book_title; ?></td>
                                        <td class="small text-muted"><?php echo $request->author ?? 'Unknown Author'; ?></td>
                                        <td><?php echo $request->accession_no; ?></td>
                                        <td>
                                            <?php echo date('M d, Y', strtotime($request->return_date)); ?>
                                            <?php if($isOverdue): ?>
                                                <span class="badge bg-danger ms-1">Overdue</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($request->request_date)); ?></td>
                                        <td><span class="badge bg-warning">Pending</span></td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <button type="button" 
                                                        class="btn btn-sm btn-success" 
                                                        onclick="openReturnActionModal('approve', <?php echo $request->id; ?>, '<?php echo addslashes($request->book_title); ?>', '<?php echo addslashes($request->user_name); ?>')">
                                                    <i class="fas fa-check"></i> Approve
                                                </button>
                                                
                                                <button type="button" 
                                                        class="btn btn-sm btn-danger" 
                                                        onclick="openReturnActionModal('reject', <?php echo $request->id; ?>, '<?php echo addslashes($request->book_title); ?>', '<?php echo addslashes($request->user_name); ?>')">
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

<!-- Single Dynamic Modal for Return Requests -->
<div class="modal fade" id="returnActionModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" id="returnModalTitle">Confirm Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div id="returnModalBodyText" class="mb-3"></div>
            </div>
            <div class="modal-footer border-0 bg-light">
                <button type="button" class="btn btn-link text-muted text-decoration-none" data-bs-dismiss="modal">Cancel</button>
                <form id="returnActionForm" method="post" class="ajax-form">
                    <?php echo Csrf::csrfField(); ?>
                    <button type="submit" id="returnModalSubmitBtn" class="btn px-4 rounded-pill">Confirm</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Single Modal Logic for Returns
    const returnModalEl = document.getElementById('returnActionModal');
    let returnModalInstance = null;

    function openReturnActionModal(type, id, bookTitle, userName) {
        if (!returnModalInstance) {
            returnModalInstance = new bootstrap.Modal(returnModalEl);
        }

        const modalTitle = document.getElementById('returnModalTitle');
        const modalBodyText = document.getElementById('returnModalBodyText');
        const modalSubmitBtn = document.getElementById('returnModalSubmitBtn');
        const actionForm = document.getElementById('returnActionForm');

        if (type === 'approve') {
            modalTitle.innerText = 'Approve Return';
            modalTitle.className = 'modal-title fw-bold text-success';
            modalBodyText.innerHTML = `Are you sure you want to <strong>approve</strong> the return for book <br>"<span class='text-primary'>${bookTitle}</span>"<br> by student <strong>${userName}</strong>?`;
            modalSubmitBtn.innerText = 'Confirm Approve';
            modalSubmitBtn.className = 'btn btn-success px-4 rounded-pill';
            actionForm.action = `<?php echo URLROOT; ?>/issue/approve_return/${id}`;
        } else {
            modalTitle.innerText = 'Reject Return';
            modalTitle.className = 'modal-title fw-bold text-danger';
            modalBodyText.innerHTML = `Are you sure you want to <strong>reject</strong> the return for book <br>"<span class='text-primary'>${bookTitle}</span>"<br> by student <strong>${userName}</strong>?`;
            modalSubmitBtn.innerText = 'Confirm Reject';
            modalSubmitBtn.className = 'btn btn-danger px-4 rounded-pill';
            actionForm.action = `<?php echo URLROOT; ?>/issue/reject_return/${id}`;
        }

        returnModalInstance.show();
    }
</script>

<?php require_once 'views/layouts/footer.php'; ?>
