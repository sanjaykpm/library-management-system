<?php require_once 'views/layouts/header.php'; ?>
<div class="row">
    <div class="col-md-3">
        <?php require_once 'views/layouts/admin_sidebar.php'; ?>
    </div>
    <div class="col-md-9">
        <div class="card card-body bg-light">
            <h2>Add Category</h2>
            <form action="<?php echo URLROOT; ?>/category/add" method="post">
                <?php echo Csrf::csrfField(); ?>
                <div class="mb-3">
                    <label for="name">Name: <sup>*</sup></label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <input type="submit" class="btn btn-success" value="Submit">
            </form>
        </div>
    </div>
</div>
<?php require_once 'views/layouts/footer.php'; ?>
