<?php require_once 'views/layouts/header.php'; ?>
<div class="row">
    <div class="col-md-3">
        <?php require_once 'views/layouts/admin_sidebar.php'; ?>
    </div>
    <div class="col-md-9">
        <h1 class="mb-4 text-secondary fw-bold">Manage Fines</h1>

        <!-- Statistics Cards -->
        <div class="row row-cols-1 row-cols-md-3 g-4 mb-4">
            <div class="col">
                <div class="card bg-primary text-white shadow-sm border-0">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2 opacity-75 uppercase small fw-bold">Total Fine</h6>
                        <h3 class="card-title mb-0 fw-bold">₹<?php echo number_format($data['total_fine_amount'] ? $data['total_fine_amount'] : 0, 2); ?></h3>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card bg-success text-white shadow-sm border-0">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2 opacity-75 uppercase small fw-bold">Paid Fine</h6>
                        <h3 class="card-title mb-0 fw-bold">₹<?php echo number_format($data['paid_fine_amount'] ? $data['paid_fine_amount'] : 0, 2); ?></h3>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card bg-danger text-white shadow-sm border-0">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2 opacity-75 uppercase small fw-bold">Unpaid Fine</h6>
                        <h3 class="card-title mb-0 fw-bold">₹<?php echo number_format($data['unpaid_fine_amount'] ? $data['unpaid_fine_amount'] : 0, 2); ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Class-wise Fines Section -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold text-secondary">Class-wise Fine Statistics</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Class</th>
                                <th class="text-end">Total Fine (₹)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($data['class_wise_fines'])): ?>
                                <tr>
                                    <td colspan="2" class="text-center text-muted py-4">No data available</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($data['class_wise_fines'] as $cw): ?>
                                    <tr>
                                        <td><?php echo $cw->class ?: 'N/A'; ?></td>
                                        <td class="text-end fw-bold">₹<?php echo number_format($cw->total_fine, 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <form action="<?php echo URLROOT; ?>/fine/manage" method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted">Search</label>
                        <input type="text" name="search" class="form-control" placeholder="Name, ID, Book..." value="<?php echo $data['search']; ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-muted">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="paid" <?php echo $data['status'] == 'paid' ? 'selected' : ''; ?>>Paid</option>
                            <option value="unpaid" <?php echo $data['status'] == 'unpaid' ? 'selected' : ''; ?>>Unpaid</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-muted">Class</label>
                        <input type="text" name="class" class="form-control" placeholder="Class" value="<?php echo $data['class']; ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-muted">From Date</label>
                        <input type="date" name="from_date" class="form-control" value="<?php echo $data['from_date']; ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-muted">To Date</label>
                        <input type="date" name="to_date" class="form-control" value="<?php echo $data['to_date']; ?>">
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                             <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <?php flash('fine_message'); ?>

        <!-- Fine List -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-secondary">Fine Records</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Student</th>
                                <th>Book Title</th>
                                <th>Amount</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($data['fines'])): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-5">
                                        <i class="fas fa-search-minus fa-3x mb-3 opacity-25"></i>
                                        <p class="mb-0">No fine records found matching your criteria</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($data['fines'] as $fine): ?>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold"><?php echo $fine->user_name; ?></div>
                                            <div class="small text-muted"><?php echo $fine->student_id; ?> (<?php echo $fine->class; ?>)</div>
                                        </td>
                                        <td><?php echo $fine->book_title; ?></td>
                                        <td class="fw-bold">₹<?php echo number_format($fine->amount, 2); ?></td>
                                        <td><?php echo date('d M Y', strtotime($fine->created_at)); ?></td>
                                        <td>
                                            <?php if($fine->status == 'paid'): ?>
                                                <span class="badge rounded-pill bg-success">Paid</span>
                                            <?php elseif($fine->status == 'unpaid'): ?>
                                                <span class="badge rounded-pill bg-danger">Unpaid</span>
                                            <?php else: ?>
                                                <span class="badge rounded-pill bg-warning text-dark">Accrued (Overdue)</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end pe-4">
                                            <?php if($fine->status == 'unpaid'): ?>
                                                <form action="<?php echo URLROOT; ?>/fine/update_status/<?php echo $fine->id; ?>" method="POST" class="d-inline">
                                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                    <input type="hidden" name="status" value="paid">
                                                    <button type="submit" class="btn btn-sm btn-outline-success">Mark Paid</button>
                                                </form>
                                            <?php elseif($fine->status == 'paid'): ?>
                                                <span class="text-success small fw-bold"><i class="fas fa-check-circle"></i> Completed</span>
                                            <?php else: ?>
                                                <a href="<?php echo URLROOT; ?>/issue/manage?search=<?php echo urlencode($fine->student_id); ?>&overdue=1" class="btn btn-sm btn-outline-primary">Process Return</a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Pagination -->
            <div class="card-footer bg-white py-3">
                <nav>
                    <ul class="pagination pagination-sm justify-content-center mb-0">
                        <?php for($i = 1; $i <= $data['total_pages']; $i++): ?>
                            <li class="page-item <?php echo $i == $data['current_page'] ? 'active' : ''; ?>">
                                <a class="page-link" href="<?php echo URLROOT; ?>/fine/manage?page=<?php echo $i; ?>&search=<?php echo $data['search']; ?>&status=<?php echo $data['status']; ?>&class=<?php echo $data['class']; ?>&from_date=<?php echo $data['from_date']; ?>&to_date=<?php echo $data['to_date']; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>
<?php require_once 'views/layouts/footer.php'; ?>
