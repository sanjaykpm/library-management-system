<?php require_once 'views/layouts/header.php'; ?>
<div class="row">
    <div class="col-md-3">
        <?php require_once 'views/layouts/admin_sidebar.php'; ?>
    </div>
    <div class="col-md-9">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Issued Books</h1>
            <div>
                <a href="<?php echo URLROOT; ?>/issue/export" class="btn btn-success me-2">
                    <i class="fas fa-file-csv"></i> Export CSV
                </a>
                <a href="<?php echo URLROOT; ?>/issue/issue_book" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Issue New Book
                </a>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form action="<?php echo URLROOT; ?>/issue/manage" method="get" class="row g-3">
                    <div class="col-md-3">
                        <input type="text" name="search" class="form-control" placeholder="Search User/Book..." value="<?php echo $data['search']; ?>">
                    </div>
                    <div class="col-md-2">
                        <input type="text" name="class" class="form-control" placeholder="Class" value="<?php echo $data['class']; ?>">
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="issued" <?php echo ($data['status'] == 'issued') ? 'selected' : ''; ?>>Issued</option>
                            <option value="returned" <?php echo ($data['status'] == 'returned') ? 'selected' : ''; ?>>Returned</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="from_date" class="form-control" title="From Date" value="<?php echo $data['from_date']; ?>">
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="to_date" class="form-control" title="To Date" value="<?php echo $data['to_date']; ?>">
                    </div>
                    <div class="col-md-1">
                        <div class="form-check mt-1">
                            <input class="form-check-input" type="checkbox" name="overdue" value="1" id="overdueCheck" <?php echo ($data['overdue']) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="overdueCheck">
                                Overdue
                            </label>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-secondary w-100">Filter</button>
                    </div>
                </form>
            </div>
        </div>
        <?php flash('issue_success'); ?>
        <?php if(empty($data['issues'])): ?>
            <div class="alert alert-info">No issued records found.</div>
        <?php else: ?>
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small text-uppercase text-muted">
                                <tr>
                                    <th class="ps-3">User</th>
                                    <th>Class</th>
                                    <th>Book</th>
                                    <th>Author</th>
                                    <th>Accession</th>
                                    <th>Issued</th>
                                    <th>Due Date</th>
                                    <th>Fine</th>
                                    <th>Status</th>
                                    <th class="text-end pe-3">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($data['issues'] as $issue) : 
                                    $today = date('Y-m-d');
                                    $isOverdue = ($today > $issue->return_date && $issue->status == 'issued');
                                    $rowClass = $isOverdue ? 'table-danger' : '';
                                    
                                    // Calculate fine dynamically for display
                                    $fineAmount = 0;
                                    if ($isOverdue) {
                                        $returnDate = new DateTime($issue->return_date);
                                        $currentDate = new DateTime($today);
                                        $interval = $currentDate->diff($returnDate);
                                        $fineAmount = $interval->days * 10;
                                    }
                                ?>
                                    <tr class="<?php echo $rowClass; ?>">
                                        <td class="ps-3">
                                            <div class="fw-bold"><?php echo $issue->user_name; ?></div>
                                            <div class="small text-muted"><?php echo $issue->student_id; ?></div>
                                        </td>
                                        <td><?php echo $issue->class; ?></td>
                                        <td>
                                            <div class="text-truncate" style="max-width: 200px;" title="<?php echo $issue->book_title; ?>">
                                                <?php echo $issue->book_title; ?>
                                            </div>
                                        </td>
                                        <td class="small text-muted"><?php echo $issue->author ?? 'Unknown Author'; ?></td>
                                        <td><code><?php echo $issue->accession_no; ?></code></td>
                                        <td class="small"><?php echo date('d M Y', strtotime($issue->issue_date)); ?></td>
                                        <td class="small">
                                            <span class="<?php echo $isOverdue ? 'text-danger fw-bold' : ''; ?>">
                                                <?php echo date('d M Y', strtotime($issue->return_date)); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php 
                                                if ($fineAmount > 0) {
                                                    echo '<span class="badge bg-danger">₹' . $fineAmount . '</span>';
                                                } else {
                                                    echo '<span class="text-muted small">₹0</span>';
                                                }
                                            ?>
                                        </td>
                                        <td>
                                            <?php if($issue->status == 'returned'): ?>
                                                <span class="badge bg-success text-white border-0">
                                                    <i class="fas fa-check-circle me-1"></i> Returned
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark border-0">
                                                    <i class="fas fa-clock me-1"></i> Issued
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end pe-3">
                                            <?php if($issue->status == 'issued') : ?>
                                                <button type="button" 
                                                        class="btn btn-outline-primary btn-sm" 
                                                        onclick="openReturnModal(<?php echo $issue->id; ?>, '<?php echo addslashes($issue->book_title); ?>', '<?php echo addslashes($issue->user_name); ?>', '<?php echo $issue->student_id; ?>', <?php echo $fineAmount; ?>)">
                                                    Process Return
                                                </button>
                                            <?php else : ?>
                                                <div class="small text-muted">
                                                    Ret: <?php echo date('d M Y', strtotime($issue->actual_return_date)); ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <nav aria-label="Page navigation" class="p-4 border-top">
                        <ul class="pagination justify-content-center mb-0">
                            <li class="page-item <?php echo ($data['current_page'] <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $data['current_page'] - 1; ?>&search=<?php echo $data['search']; ?>&status=<?php echo $data['status']; ?>&overdue=<?php echo $data['overdue'] ? '1' : '0'; ?>&from_date=<?php echo $data['from_date']; ?>&to_date=<?php echo $data['to_date']; ?>&class=<?php echo $data['class']; ?>">Previous</a>
                            </li>
                            <?php for($i = 1; $i <= $data['total_pages']; $i++): ?>
                                <li class="page-item <?php echo ($data['current_page'] == $i) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo $data['search']; ?>&status=<?php echo $data['status']; ?>&overdue=<?php echo $data['overdue'] ? '1' : '0'; ?>&from_date=<?php echo $data['from_date']; ?>&to_date=<?php echo $data['to_date']; ?>&class=<?php echo $data['class']; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?php echo ($data['current_page'] >= $data['total_pages']) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $data['current_page'] + 1; ?>&search=<?php echo $data['search']; ?>&status=<?php echo $data['status']; ?>&overdue=<?php echo $data['overdue'] ? '1' : '0'; ?>&from_date=<?php echo $data['from_date']; ?>&to_date=<?php echo $data['to_date']; ?>&class=<?php echo $data['class']; ?>">Next</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Single Dynamic Return Modal -->
<div class="modal fade" id="returnModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-start border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Confirm Return</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4">
                <p class="mb-1">Are you sure you want to return this book?</p>
                <div class="p-3 bg-light rounded mt-3 text-start">
                    <div class="small text-muted mb-1 text-uppercase">Book Details</div>
                    <div class="fw-bold" id="modalBookTitle"></div>
                    <div class="small">Student: <span id="modalStudentName"></span> (<span id="modalStudentID"></span>)</div>
                </div>
                <div id="modalFineContainer" class="alert alert-warning mt-3 mb-0" style="display:none;">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Fine Applicable: ₹<span id="modalFineAmount"></span></strong>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <form id="returnForm" method="post" class="ajax-form">
                    <?php echo Csrf::csrfField(); ?>
                    <button type="submit" class="btn btn-primary">Confirm & Process</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Single Return Modal Logic
    const returnModalEl = document.getElementById('returnModal');
    let returnModalInstance = null;

    function openReturnModal(id, bookTitle, userName, studentId, fineAmount) {
        if (!returnModalInstance) {
            returnModalInstance = new bootstrap.Modal(returnModalEl);
        }

        document.getElementById('modalBookTitle').textContent = bookTitle;
        document.getElementById('modalStudentName').textContent = userName;
        document.getElementById('modalStudentID').textContent = studentId;

        const fineContainer = document.getElementById('modalFineContainer');
        const fineAmountSpan = document.getElementById('modalFineAmount');
        
        if (fineAmount > 0) {
            fineAmountSpan.textContent = fineAmount;
            fineContainer.style.display = 'block';
        } else {
            fineContainer.style.display = 'none';
        }

        const returnForm = document.getElementById('returnForm');
        returnForm.action = `<?php echo URLROOT; ?>/issue/return_book/${id}`;

        returnModalInstance.show();
    }
</script>

<?php require_once 'views/layouts/footer.php'; ?>
