<?php require_once 'views/layouts/header.php'; ?>
<div class="row">
    <div class="col-md-3">
        <?php require_once 'views/layouts/admin_sidebar.php'; ?>
    </div>
    <div class="col-md-9">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Manage Categories</h1>
            <a href="<?php echo URLROOT; ?>/category/add" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Category
            </a>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form action="<?php echo URLROOT; ?>/category/manage" method="get" class="row g-3">
                    <div class="col-md-10">
                        <input type="text" name="search" class="form-control" placeholder="Search categories..." value="<?php echo $data['search']; ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-secondary w-100">Search</button>
                    </div>
                </form>
            </div>
        </div>
        <?php flash('category_success'); ?>
        <?php if(empty($data['categories'])): ?>
            <div class="alert alert-info">No categories found.</div>
        <?php else: ?>
            <ul class="list-group mb-4 shadow-sm">
                <?php foreach($data['categories'] as $category) : ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center p-3">
                        <div>
                            <span class="fw-bold"><?php echo $category->name; ?></span>
                        </div>
                        <div>
                            <a href="<?php echo URLROOT; ?>/category/edit/<?php echo $category->id; ?>" class="btn btn-info btn-sm text-white">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <button type="button" 
                                    class="btn btn-danger btn-sm" 
                                    onclick="openDeleteModal(<?php echo $category->id; ?>, '<?php echo addslashes($category->name); ?>')">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>

            <!-- Pagination -->
            <?php if($data['total_pages'] > 1): ?>
            <nav aria-label="Page navigation">
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
        <?php endif; ?>
    </div>
</div>

<!-- Single Dynamic Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold text-danger">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div id="deleteModalBodyText" class="mb-3"></div>
                <p class="text-muted small"><i class="fas fa-exclamation-triangle text-warning me-1"></i> This action cannot be undone.</p>
            </div>
            <div class="modal-footer border-0 bg-light">
                <button type="button" class="btn btn-link text-muted text-decoration-none" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="post" class="ajax-form">
                    <?php echo Csrf::csrfField(); ?>
                    <button type="submit" class="btn btn-danger px-4 rounded-pill">Confirm Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Single Delete Modal Logic
    const deleteModalEl = document.getElementById('deleteModal');
    let deleteModalInstance = null;

    function openDeleteModal(id, name) {
        if (!deleteModalInstance) {
            deleteModalInstance = new bootstrap.Modal(deleteModalEl);
        }

        const modalBodyText = document.getElementById('deleteModalBodyText');
        const deleteForm = document.getElementById('deleteForm');

        modalBodyText.innerHTML = `Are you sure you want to delete category <strong>${name}</strong>?`;
        deleteForm.action = `<?php echo URLROOT; ?>/category/delete/${id}`;

        deleteModalInstance.show();
    }
</script>

<?php require_once 'views/layouts/footer.php'; ?>
