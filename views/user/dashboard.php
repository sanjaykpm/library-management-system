<?php require_once 'views/layouts/header.php'; ?>
<div class="row">
    <div class="col-md-4">
        <?php require_once 'views/layouts/user_sidebar.php'; ?>
    </div>
    <div class="col-md-8">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 text-secondary fw-bold">My Dashboard</h1>
            <span class="text-muted small"><?php echo date('l, F j, Y'); ?></span>
        </div>
        
        <?php flash('request_message'); ?>
        
        <!-- Summary Stats Cards -->
        <div class="row row-cols-1 row-cols-md-3 g-4 mb-5">
            <!-- Total Books -->
            <div class="col">
                <div class="card text-white bg-primary h-100 border-0 shadow-sm hover-elevate">
                    <div class="card-body p-4 d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle text-white-50 text-uppercase small fw-bold mb-2">Total Books</h6>
                            <h2 class="card-title fw-bold mb-0"><?php echo $data['stats']['total_available']; ?></h2>
                        </div>
                        <i class="fas fa-book fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
            
            <!-- Issued Books -->
            <div class="col">
                <div class="card text-white bg-warning h-100 border-0 shadow-sm hover-elevate">
                    <div class="card-body p-4 d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle text-white-50 text-uppercase small fw-bold mb-2">Issued Books</h6>
                            <h2 class="card-title fw-bold mb-0"><?php echo $data['stats']['my_issued']; ?></h2>
                        </div>
                        <i class="fas fa-book-reader fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>

            <!-- Total Fine -->
            <div class="col">
                <div class="card text-white bg-danger h-100 border-0 shadow-sm hover-elevate">
                    <div class="card-body p-4 d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle text-white-50 text-uppercase small fw-bold mb-2">My Fine</h6>
                            <h2 class="card-title fw-bold mb-0">₹<?php echo $data['stats']['total_fine']; ?></h2>
                        </div>
                        <i class="fas fa-rupee-sign fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notifications Section -->
        <?php if (!empty($data['notifications']['due_soon']) || $data['notifications']['overdue_count'] > 0 || $data['notifications']['has_fine']): ?>
        <div class="mb-5">
            <h5 class="mb-3">Notifications</h5>
            <div class="list-group shadow-sm">
                <?php if ($data['notifications']['has_fine']): ?>
                    <div class="list-group-item list-group-item-danger d-flex justify-content-between align-items-center">
                        <div><i class="fas fa-exclamation-triangle me-2"></i> You have outstanding fines. Please pay to avoid account suspension.</div>
                        <a href="<?php echo URLROOT; ?>/user/my_books" class="btn btn-sm btn-danger">View Details</a>
                    </div>
                <?php endif; ?>

                <?php if ($data['notifications']['overdue_count'] > 0): ?>
                    <div class="list-group-item list-group-item-warning d-flex justify-content-between align-items-center">
                        <div><i class="fas fa-clock me-2"></i> You have <?php echo $data['notifications']['overdue_count']; ?> overdue book(s). Please return them immediately.</div>
                        <a href="<?php echo URLROOT; ?>/user/my_books" class="btn btn-sm btn-warning">View Books</a>
                    </div>
                <?php endif; ?>

                <?php foreach($data['notifications']['due_soon'] as $book): ?>
                    <div class="list-group-item list-group-item-info d-flex justify-content-between align-items-center">
                        <div><i class="fas fa-calendar-alt me-2"></i> Reminder: "<?php echo $book->book_title; ?>" is due on <?php echo date('M d', strtotime($book->return_date)); ?>.</div>
                        <small class="text-muted">Due in <?php echo (strtotime($book->return_date) - strtotime(date('Y-m-d'))) / (60 * 60 * 24); ?> days</small>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Search & Filter -->
        <h3 class="mb-3 fw-bold h5">Browse Collection</h3>
        <div class="card shadow-sm mb-4 border-0">
            <div class="card-body p-2">
                <form action="<?php echo URLROOT; ?>/user/dashboard" method="get" class="row g-2 align-items-center">
                    <div class="col-md-9">
                        <div class="input-group search-input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search by Title, Author, ISBN..." value="<?php echo isset($data['search']) ? $data['search'] : ''; ?>">
                        </div>
                    </div>
                    <!-- <div class="col-md-3">
                        <select name="category" class="form-select border-0 bg-light">
                            <option value="">All Categories</option>
                            <?php foreach($data['categories'] as $cat): ?>
                                <option value="<?php echo $cat->id; ?>"><?php echo $cat->name; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div> -->
                    <div class="col-md-3">
                        <button class="btn btn-primary w-100" type="submit">Search</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Book Grid -->
        <div class="row row-cols-1 row-cols-md-3 row-cols-lg-4 g-4 mb-5">
            <?php if(empty($data['books'])): ?>
                <div class="col-12 text-center py-5">
                    <div class="empty-state">
                        <i class="fas fa-search empty-state-icon"></i>
                        <h5>No books found</h5>
                        <p class="text-muted">Try adjusting your search criteria or browse our categories.</p>
                        <a href="<?php echo URLROOT; ?>/user/dashboard" class="btn btn-outline-primary mt-2">Clear Search</a>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach($data['books'] as $book) : ?>
                    <div class="col">
                        <div class="card h-100 border-0 shadow-sm hover-lift">
                            <div class="book-cover-placeholder">
                                <i class="fas fa-book"></i>
                            </div>
                            <!-- Future: <img src="<?php echo URLROOT; ?>/uploads/<?php echo $book->image; ?>" class="card-img-top" alt="..."> -->
                            
                            <div class="card-body d-flex flex-column p-3">
                                <div class="mb-2">
                                    <?php if($book->available_quantity > 1): ?>
                                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2">Available</span>
                                    <?php elseif($book->available_quantity == 1): ?>
                                        <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill px-2">Last Copy</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2">Out of Stock</span>
                                    <?php endif; ?>
                                </div>

                                <h6 class="card-title fw-bold text-truncate" title="<?php echo $book->title; ?>">
                                    <?php echo $book->title; ?>
                                </h6>
                                <p class="card-text text-muted small mb-3 flex-grow-1">
                                    By <?php echo $book->author ?? 'Unknown'; ?><br>
                                    <span class="text-secondary"><?php echo $book->category_name; ?></span>
                                </p>
                                
                                <form action="<?php echo URLROOT; ?>/user/request_book/<?php echo $book->id; ?>" method="post" class="ajax-form mt-auto">
                                    <?php if($book->available_quantity > 0): ?>
                                        <button type="submit" class="btn btn-primary btn-sm w-100 rounded-pill">
                                            Request Book
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-light btn-sm w-100 rounded-pill text-muted" disabled>
                                            Notify Me
                                        </button>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php require_once 'views/layouts/footer.php'; ?>
