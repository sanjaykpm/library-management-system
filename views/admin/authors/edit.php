<?php require_once 'views/layouts/header.php'; ?>
<div class="row">
    <div class="col-md-3">
        <?php require_once 'views/layouts/admin_sidebar.php'; ?>
    </div>
    <div class="col-md-9">
        <h1 class="mb-4">Edit Author</h1>
        
        <div class="card">
            <div class="card-body">
                <form action="<?php echo URLROOT; ?>/author/edit/<?php echo $data['id']; ?>" method="post">
                    <?php echo Csrf::csrfField(); ?>
                    <div class="mb-3">
                        <label for="name">Author Name: <sup>*</sup></label>
                        <input type="text" name="name" class="form-control <?php echo (!empty($data['name_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['name']; ?>">
                        <span class="invalid-feedback"><?php echo $data['name_err']; ?></span>
                    </div>
                    
                    <div class="mb-3">
                        <label for="bio">Biography:</label>
                        <textarea name="bio" class="form-control" rows="4"><?php echo $data['bio']; ?></textarea>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">Update Author</button>
                        <a href="<?php echo URLROOT; ?>/author/manage" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require_once 'views/layouts/footer.php'; ?>
