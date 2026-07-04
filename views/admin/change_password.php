<?php require_once 'views/layouts/header.php'; ?>
<div class="row">
    <div class="col-md-3">
        <?php require_once 'views/layouts/admin_sidebar.php'; ?>
    </div>
    <div class="col-md-9">
        <div class="card card-body bg-light mt-5 shadow">
            <h2 class="text-primary fw-bold mb-3">Change Admin Password</h2>
            <p>Please fill in the fields below to update your password.</p>
            <form action="<?php echo URLROOT; ?>/admin/change_password" method="post">
                <?php echo Csrf::csrfField(); ?>
                <div class="mb-3">
                    <label for="password">New Password: <sup>*</sup></label>
                    <input type="password" name="password" class="form-control form-control-lg <?php echo (!empty($data['password_err'])) ? 'is-invalid' : ''; ?>" value="">
                    <span class="invalid-feedback"><?php echo $data['password_err']; ?></span>
                </div>
                <div class="mb-3">
                    <label for="confirm_password">Confirm Password: <sup>*</sup></label>
                    <input type="password" name="confirm_password" class="form-control form-control-lg <?php echo (!empty($data['confirm_password_err'])) ? 'is-invalid' : ''; ?>" value="">
                    <span class="invalid-feedback"><?php echo $data['confirm_password_err']; ?></span>
                </div>
                <div class="row mt-4">
                    <div class="col">
                        <input type="submit" value="Update Password" class="btn btn-primary w-100 py-3 rounded-pill shadow-sm">
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<?php require_once 'views/layouts/footer.php'; ?>
