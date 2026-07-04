<?php require_once 'views/layouts/header.php'; ?>
<div class="row">
    <div class="col-md-3">
        <?php require_once 'views/layouts/admin_sidebar.php'; ?>
    </div>
    <div class="col-md-9">
        <h1 class="mb-4">Edit Category</h1>
        
        <div class="card">
            <div class="card-body">
                <form action="<?php echo URLROOT; ?>/category/edit/<?php echo $data['category']->id; ?>" method="post">
                    <?php echo Csrf::csrfField(); ?>
                    <div class="mb-3">
                        <label for="name">Category Name: <sup>*</sup></label>
                        <input type="text" name="name" class="form-control" value="<?php echo $data['category']->name; ?>" required>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">Update Category</button>
                        <a href="<?php echo URLROOT; ?>/category/manage" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require_once 'views/layouts/footer.php'; ?>
