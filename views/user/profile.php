<?php require_once 'views/layouts/header.php'; ?>
<div class="row">
    <div class="col-md-3">
        <?php require_once 'views/layouts/user_sidebar.php'; ?>
    </div>
    <div class="col-md-9">
        <div class="card card-body bg-light">
            <h2>My Profile</h2>
            <p>Update your personal details below</p>
            <?php flash('profile_message'); ?>
            <form action="<?php echo URLROOT; ?>/user/edit_profile" method="post">
                <?php echo Csrf::csrfField(); ?>
                <div class="mb-3">
                    <label>Student ID:</label>
                    <input type="text" class="form-control" value="<?php echo $data['user']->student_id; ?>" disabled>
                </div>
                <div class="mb-3">
                    <label>Registration No:</label>
                    <input type="text" class="form-control" value="<?php echo $data['user']->reg_no; ?>" disabled>
                </div>
                <div class="mb-3">
                    <label for="name">Name: <sup>*</sup></label>
                    <input type="text" name="name" class="form-control" value="<?php echo $data['user']->name; ?>" required>
                </div>
                <div class="mb-3">
                    <label for="class">Class: <sup>*</sup></label>
                    <input type="text" name="class" class="form-control" value="<?php echo $data['user']->class; ?>" required>
                </div>
                <div class="mb-3">
                    <label for="mobile_no">Mobile No: <sup>*</sup></label>
                    <input type="text" name="mobile_no" class="form-control" value="<?php echo $data['user']->mobile_no; ?>" required>
                </div>
                <div class="mb-3">
                    <label for="email">Email: <sup>*</sup></label>
                    <input type="email" name="email" class="form-control" value="<?php echo $data['user']->email; ?>" required>
                </div>
                <hr>
                <h5>Change Password</h5>
                <div class="mb-3">
                    <label for="password">New Password (Leave blank to keep current):</label>
                    <input type="password" name="password" class="form-control <?php echo (!empty($data['password_err'])) ? 'is-invalid' : ''; ?>">
                    <span class="invalid-feedback"><?php echo $data['password_err']; ?></span>
                </div>
                <input type="submit" class="btn btn-primary" value="Update Profile">
            </form>
        </div>
    </div>
</div>
<?php require_once 'views/layouts/footer.php'; ?>
