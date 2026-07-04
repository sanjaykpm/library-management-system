<?php require_once 'views/layouts/header.php'; ?>
<div class="row">
    <div class="col-md-4">
        <?php require_once 'views/layouts/admin_sidebar.php'; ?>
    </div>
    <div class="col-md-8">
        <h1 class="mb-4 text-secondary fw-bold">Admin Dashboard</h1>
        
        <!-- Summary Stats Grid -->
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-2 g-4 mb-5">
            <!-- Books Card -->
            <div class="col">
                <div class="card text-white bg-primary h-100 border-0 shadow-sm hover-elevate">
                    <div class="card-body p-4 d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle text-white-50 text-uppercase small fw-bold mb-2">Books</h6>
                            <h2 class="card-title fw-bold mb-0"><?php echo $data['total_books']; ?></h2>
                        </div>
                        <i class="fas fa-book fa-3x opacity-50"></i>
                    </div>
                    <a href="<?php echo URLROOT; ?>/book/manage" class="card-footer bg-transparent border-0 text-white text-decoration-none small d-flex justify-content-between align-items-center">
                        <span>Manage Books</span> <i class="fas fa-chevron-right"></i>
                    </a>
                </div>
            </div>

            <!-- Members Card -->
            <div class="col">
                <div class="card text-white bg-success h-100 border-0 shadow-sm hover-elevate">
                    <div class="card-body p-4 d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle text-white-50 text-uppercase small fw-bold mb-2">Members</h6>
                            <h2 class="card-title fw-bold mb-0"><?php echo $data['total_users']; ?></h2>
                        </div>
                        <i class="fas fa-users fa-3x opacity-50"></i>
                    </div>
                    <a href="<?php echo URLROOT; ?>/member/manage" class="card-footer bg-transparent border-0 text-white text-decoration-none small d-flex justify-content-between align-items-center">
                        <span>Manage Members</span> <i class="fas fa-chevron-right"></i>
                    </a>
                </div>
            </div>

            <!-- Issued Card -->
            <div class="col">
                <div class="card text-white bg-warning h-100 border-0 shadow-sm hover-elevate">
                    <div class="card-body p-4 d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle text-white-50 text-uppercase small fw-bold mb-2">Issued</h6>
                            <h2 class="card-title fw-bold mb-0"><?php echo $data['total_issued']; ?></h2>
                        </div>
                        <i class="fas fa-exchange-alt fa-3x opacity-50"></i>
                    </div>
                    <a href="<?php echo URLROOT; ?>/issue/manage" class="card-footer bg-transparent border-0 text-white text-decoration-none small d-flex justify-content-between align-items-center">
                        <span>View Issued</span> <i class="fas fa-chevron-right"></i>
                    </a>
                </div>
            </div>

            <!-- Requests Card -->
            <div class="col">
                <div class="card text-white bg-info h-100 border-0 shadow-sm hover-elevate">
                    <div class="card-body p-4 d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle text-white-50 text-uppercase small fw-bold mb-2">Requests</h6>
                            <h2 class="card-title fw-bold mb-0"><?php echo $data['pending_requests']; ?></h2>
                        </div>
                        <i class="fas fa-hand-holding fa-3x opacity-50"></i>
                    </div>
                    <a href="<?php echo URLROOT; ?>/issue/requests" class="card-footer bg-transparent border-0 text-white text-decoration-none small d-flex justify-content-between align-items-center">
                        <span>Approve Requests</span> <i class="fas fa-chevron-right"></i>
                    </a>
                </div>
            </div>

            <!-- Returns Card -->
            <div class="col">
                <div class="card text-white bg-danger h-100 border-0 shadow-sm hover-elevate">
                    <div class="card-body p-4 d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle text-white-50 text-uppercase small fw-bold mb-2">Returns</h6>
                            <h2 class="card-title fw-bold mb-0"><?php echo $data['pending_returns']; ?></h2>
                        </div>
                        <i class="fas fa-undo-alt fa-3x opacity-50"></i>
                    </div>
                    <a href="<?php echo URLROOT; ?>/issue/return_requests" class="card-footer bg-transparent border-0 text-white text-decoration-none small d-flex justify-content-between align-items-center">
                        <span>Accept Returns</span> <i class="fas fa-chevron-right"></i>
                    </a>
                </div>
            </div>
        </div>


    </div>
</div>
<?php require_once 'views/layouts/footer.php'; ?>
