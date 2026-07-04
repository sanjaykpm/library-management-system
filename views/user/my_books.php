<?php require_once 'views/layouts/header.php'; ?>
<div class="row">
    <div class="col-md-3">
        <?php require_once 'views/layouts/user_sidebar.php'; ?>
    </div>
    <div class="col-md-9">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 text-secondary fw-bold">My Borrowed Books</h1>
            <a href="<?php echo URLROOT; ?>/user/dashboard" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
            </a>
        </div>

        <?php flash('return_message'); ?>

        <!-- Summary Bar -->
        <div class="card border-0 shadow-sm mb-4 bg-light">
            <div class="card-body p-3">
                <div class="row text-center">
                    <div class="col border-end">
                        <small class="text-muted text-uppercase fw-bold">Total Borrowed</small>
                        <h4 class="mb-0 text-primary"><?php echo $data['stats']['total_borrowed']; ?></h4>
                    </div>
                    <div class="col border-end">
                        <small class="text-muted text-uppercase fw-bold">Active</small>
                        <h4 class="mb-0 text-success"><?php echo $data['stats']['active']; ?></h4>
                    </div>
                    <div class="col border-end">
                        <small class="text-muted text-uppercase fw-bold">Returned</small>
                        <h4 class="mb-0 text-secondary"><?php echo $data['stats']['returned']; ?></h4>
                    </div>
                    <div class="col">
                        <small class="text-muted text-uppercase fw-bold">Overdue</small>
                        <h4 class="mb-0 text-danger"><?php echo $data['stats']['overdue']; ?></h4>
                    </div>
                </div>
            </div>
        </div>
        
        <?php if(empty($data['issued_books'])): ?>
            <div class="card shadow-sm border-0">
                <div class="card-body text-center py-5">
                    <div class="empty-state">
                        <i class="fas fa-book-open empty-state-icon text-muted" style="font-size: 3rem;"></i>
                        <h5 class="mt-3">No books borrowed yet</h5>
                        <p class="text-muted">Start reading by requesting a book from the dashboard.</p>
                        <a href="<?php echo URLROOT; ?>/user/dashboard" class="btn btn-primary mt-2">Browse Books</a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 500px;">
                        <table class="table table-hover align-middle mb-0 table-sticky-header">
                            <thead class="table-light text-uppercase small text-muted">
                                <tr>
                                    <th class="ps-4">Book Details</th>
                                    <th>Issue Date</th>
                                    <th>Return By</th>
                                    <th class="text-center">Fine</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($data['issued_books'] as $issue) : 
                                    $today = date('Y-m-d');
                                    $isOverdue = ($issue->status == 'issued' && $issue->return_date < $today);
                                    $rowClass = $isOverdue ? 'bg-danger bg-opacity-10' : '';
                                ?>
                                    <tr class="<?php echo $rowClass; ?>">
                                        <td class="ps-4">
                                            <div class="fw-bold text-dark"><?php echo $issue->book_title; ?></div>
                                            <div class="small text-muted">
                                                <i class="fas fa-user-edit me-1"></i> <?php echo $issue->author ?? 'Unknown'; ?>
                                                <span class="mx-1">•</span>
                                                Accession: <code><?php echo $issue->accession_no; ?></code>
                                            </div>
                                        </td>
                                        <td class="small text-muted">
                                            <?php echo date('M d, Y', strtotime($issue->issue_date)); ?>
                                        </td>
                                        <td class="small fw-bold <?php echo $isOverdue ? 'text-danger' : 'text-dark'; ?>">
                                            <?php echo date('M d, Y', strtotime($issue->return_date)); ?>
                                            <?php if($isOverdue): ?>
                                                <i class="fas fa-exclamation-circle text-danger ms-1" title="Overdue"></i>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php 
                                                $returnDate = new DateTime($issue->return_date);
                                                $currentDate = new DateTime($today);
                                                $fineAmount = 0;
                                                
                                                if ($isOverdue) {
                                                    $interval = $currentDate->diff($returnDate);
                                                    $fineAmount = $interval->days * 10;
                                                }
                                                
                                                if ($fineAmount > 0) {
                                                    echo '<span class="badge bg-danger rounded-pill">₹' . $fineAmount . '</span>';
                                                } else {
                                                    echo '<span class="badge bg-light text-muted border rounded-pill">₹0</span>';
                                                }
                                            ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($issue->status == 'returned'): ?>
                                                <span class="badge bg-success bg-opacity-10 text-success border border-success rounded-pill px-3">Returned</span>
                                            <?php elseif ($isOverdue): ?>
                                                <span class="badge bg-danger text-white rounded-pill px-3">Overdue</span>
                                            <?php else: ?>
                                                <span class="badge bg-primary text-white rounded-pill px-3">Issued</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end pe-4">
                                            <?php if($issue->status == 'issued'): ?>
                                                <button type="button" class="btn btn-sm btn-warning text-dark fw-bold" 
                                                        onclick="openReturnRequestModal(<?php echo $issue->id; ?>, '<?php echo addslashes($issue->book_title); ?>', '<?php echo $issue->accession_no; ?>', <?php echo $fineAmount; ?>)">
                                                    <i class="fas fa-undo me-1"></i> Return
                                                </button>
                                            <?php else: ?>
                                                <button class="btn btn-sm btn-light text-muted" disabled>Returned</button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if($data['total_pages'] > 1): ?>
                    <nav aria-label="Page navigation" class="p-3 border-top">
                        <ul class="pagination justify-content-center mb-0">
                            <li class="page-item <?php echo ($data['current_page'] <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link border-0 shadow-sm" href="?page=<?php echo $data['current_page'] - 1; ?>">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                            <?php for($i = 1; $i <= $data['total_pages']; $i++): ?>
                                <li class="page-item <?php echo ($data['current_page'] == $i) ? 'active' : ''; ?> mx-1">
                                    <a class="page-link border-0 shadow-sm rounded-circle" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?php echo ($data['current_page'] >= $data['total_pages']) ? 'disabled' : ''; ?>">
                                <a class="page-link border-0 shadow-sm" href="?page=<?php echo $data['current_page'] + 1; ?>">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<!-- Single Dynamic Return Request Modal -->
<div class="modal fade" id="returnRequestModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-start border-0 shadow">
            <div class="modal-header bg-light border-0">
                <h6 class="modal-title fw-bold">Request Return</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="mb-3">Do you want to request a return for:</p>
                <div class="d-flex align-items-center bg-light p-3 rounded">
                    <div class="flex-grow-1">
                        <div class="fw-bold" id="modalBookTitle"></div>
                        <div class="small text-muted">Accession: <span id="modalAccessionNo"></span></div>
                    </div>
                    <div id="modalFineContainer" class="text-danger fw-bold ms-3" style="display:none;">
                        <small>Fine Due</small><br>
                        ₹<span id="modalFineAmount"></span>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light text-muted" data-bs-dismiss="modal">Cancel</button>
                <form id="returnRequestForm" method="post" class="ajax-form">
                    <?php echo Csrf::csrfField(); ?>
                    <button type="submit" class="btn btn-warning px-4 rounded-pill">Confirm Request</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Single Modal Logic for Return Request
    const returnRequestModalEl = document.getElementById('returnRequestModal');
    let returnRequestModalInstance = null;

    function openReturnRequestModal(id, bookTitle, accessionNo, fineAmount) {
        if (!returnRequestModalInstance) {
            returnRequestModalInstance = new bootstrap.Modal(returnRequestModalEl);
        }

        document.getElementById('modalBookTitle').textContent = bookTitle;
        document.getElementById('modalAccessionNo').textContent = accessionNo;

        const fineContainer = document.getElementById('modalFineContainer');
        const fineAmountSpan = document.getElementById('modalFineAmount');
        
        if (fineAmount > 0) {
            fineAmountSpan.textContent = fineAmount;
            fineContainer.style.display = 'block';
        } else {
            fineContainer.style.display = 'none';
        }

        const form = document.getElementById('returnRequestForm');
        form.action = `<?php echo URLROOT; ?>/user/request_return/${id}`;

        returnRequestModalInstance.show();
    }
</script>

<?php require_once 'views/layouts/footer.php'; ?>
