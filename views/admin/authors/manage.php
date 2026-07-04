<?php require_once 'views/layouts/header.php'; ?>
<div class="row">
    <div class="col-md-3">
        <?php require_once 'views/layouts/admin_sidebar.php'; ?>
    </div>
    <div class="col-md-9">
        <?php flash('author_success'); ?>
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Manage Authors</h1>
            <a href="<?php echo URLROOT; ?>/author/add" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Author
            </a>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form action="<?php echo URLROOT; ?>/author/manage" method="get" class="row g-3">
                    <div class="col-md-10">
                        <input type="text" name="search" class="form-control" placeholder="Search by name or bio..." value="<?php echo $data['search']; ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-secondary w-100">Search</button>
                    </div>
                </form>
            </div>
        </div>
        
        <?php if(empty($data['authors'])): ?>
            <div class="alert alert-info">No authors found.</div>
        <?php else: ?>
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Bio</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($data['authors'] as $author) : ?>
                                    <tr>
                                        <td><?php echo $author->id; ?></td>
                                        <td><?php echo $author->name; ?></td>
                                        <td><?php echo substr($author->bio, 0, 50) . (strlen($author->bio) > 50 ? '...' : ''); ?></td>
                                        <td>
                                            <a href="<?php echo URLROOT; ?>/author/edit/<?php echo $author->id; ?>" class="btn btn-sm btn-info">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-sm btn-danger" 
                                                    onclick="openDeleteModal(<?php echo $author->id; ?>, '<?php echo addslashes($author->name); ?>')">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
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

        modalBodyText.innerHTML = `Are you sure you want to delete author <strong>${name}</strong>?`;
        deleteForm.action = `<?php echo URLROOT; ?>/author/delete/${id}`;

        deleteModalInstance.show();
    }
</script>

<?php require_once 'views/layouts/footer.php'; ?>
