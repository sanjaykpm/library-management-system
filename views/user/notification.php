<?php require_once 'views/layouts/header.php'; ?>
<div class="row">
    <div class="col-md-3">
        <?php require_once 'views/layouts/user_sidebar.php'; ?>
    </div>
    <div class="col-md-9">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 text-secondary fw-bold">Notifications</h1>
            <?php if(!empty($data['notifications'])): ?>
                <form action="<?php echo URLROOT; ?>/notification/mark_all_read" method="post" class="ajax-form">
                    <button type="submit" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-check-double me-1"></i> Mark All as Read
                    </button>
                </form>
            <?php endif; ?>
        </div>

        <?php flash('notification_msg'); ?>

        <?php if(empty($data['notifications'])): ?>
            <div class="card shadow-sm border-0">
                <div class="card-body text-center py-5">
                    <div class="empty-state">
                        <i class="fas fa-bell-slash empty-state-icon text-muted" style="font-size: 3rem;"></i>
                        <h5 class="mt-3">No new notifications</h5>
                        <p class="text-muted">You're all caught up!</p>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="list-group shadow-sm">
                <?php foreach($data['notifications'] as $notification): ?>
                    <div class="list-group-item list-group-item-action p-4 border-0 mb-2 rounded shadow-sm d-flex justify-content-between align-items-center bg-white">
                        <div class="d-flex align-items-start">
                            <div class="me-3 mt-1">
                                <?php if($notification->type == 'success'): ?>
                                    <div class="icon-circle bg-success bg-opacity-10 text-success p-2 rounded-circle">
                                        <i class="fas fa-check"></i>
                                    </div>
                                <?php elseif($notification->type == 'danger'): ?>
                                    <div class="icon-circle bg-danger bg-opacity-10 text-danger p-2 rounded-circle">
                                        <i class="fas fa-times"></i>
                                    </div>
                                <?php elseif($notification->type == 'warning'): ?>
                                    <div class="icon-circle bg-warning bg-opacity-10 text-warning p-2 rounded-circle">
                                        <i class="fas fa-exclamation-triangle"></i>
                                    </div>
                                <?php else: ?>
                                    <div class="icon-circle bg-info bg-opacity-10 text-info p-2 rounded-circle">
                                        <i class="fas fa-info"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div>
                                <h6 class="mb-1 text-dark fw-bold"><?php echo $notification->message; ?></h6>
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i> 
                                    <?php echo date('M d, Y h:i A', strtotime($notification->created_at)); ?>
                                </small>
                            </div>
                        </div>
                        <div class="ms-3">
                            <form action="<?php echo URLROOT; ?>/notification/mark_read/<?php echo $notification->id; ?>" method="post" class="ajax-form">
                                <button type="submit" class="btn btn-sm btn-light text-muted" title="Mark as Read">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php require_once 'views/layouts/footer.php'; ?>
