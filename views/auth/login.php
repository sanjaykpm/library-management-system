<?php require_once 'views/layouts/header.php'; ?>
<div class="row">
    <div class="col-md-6 mx-auto">
        <div class="card card-body bg-light mt-5">
            <?php flash('register_success'); ?>
            <?php flash('auth_error'); ?>
            <h2><?php echo ($data['login_type'] == 'admin') ? 'Admin Login' : 'Student Login'; ?></h2>
            <p>Please fill in your credentials to log in</p>
            <form action="<?php echo URLROOT; ?>/auth/<?php echo ($data['login_type'] == 'admin') ? 'admin_login' : 'login'; ?>" method="post">
                <?php echo Csrf::csrfField(); ?>
                <div class="mb-3">
                    <label for="email">Email: <sup>*</sup></label>
                    <input type="email" name="email" class="form-control form-control-lg <?php echo (!empty($data['email_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['email']; ?>">
                    <span class="invalid-feedback"><?php echo $data['email_err']; ?></span>
                </div>
                <div class="mb-3">
                    <label for="password">Password: <sup>*</sup></label>
                    <input type="password" name="password" class="form-control form-control-lg <?php echo (!empty($data['password_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['password']; ?>">
                    <span class="invalid-feedback"><?php echo $data['password_err']; ?></span>
                </div>
                <div class="row">
                    <div class="col">
                        <input type="submit" value="Login" class="btn btn-success w-100">
                    </div>
                    <?php if($data['login_type'] == 'student') : ?>
                        <div class="col">
                            <a href="<?php echo URLROOT; ?>/auth/register" class="btn btn-light w-100">No account? Register</a>
                        </div>
                    <?php endif; ?>
                </div>
                <?php if($data['login_type'] == 'student') : ?>
                    <div class="text-center mt-3">
                        <a href="<?php echo URLROOT; ?>/auth/admin_login">Are you an Admin? Login here</a>
                    </div>
                <?php else : ?>
                    <div class="text-center mt-3">
                        <a href="<?php echo URLROOT; ?>/auth/login">Are you a Student? Login here</a>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>
</div>
<?php require_once 'views/layouts/footer.php'; ?>
