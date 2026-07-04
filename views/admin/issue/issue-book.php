<?php require_once 'views/layouts/header.php'; ?>
<div class="row">
    <div class="col-md-3">
        <?php require_once 'views/layouts/admin_sidebar.php'; ?>
    </div>
    <div class="col-md-9">
        <div class="card card-body bg-light">
            <h2>Issue Book</h2>
            <?php if(!empty($data['error'])) : ?>
                <div class="alert alert-danger"><?php echo $data['error']; ?></div>
            <?php endif; ?>
            <form action="<?php echo URLROOT; ?>/issue/issue_book" method="post">
                <?php echo Csrf::csrfField(); ?>
                <div class="mb-3">
                    <label for="accession_no">Book Accession No: <sup>*</sup></label>
                    <input type="text" name="accession_no" class="form-control" value="<?php echo $data['accession_no']; ?>" placeholder="Enter Book ID / Accession Number" required>
                </div>
                <div class="mb-3">
                    <label for="student_id">Student ID: <sup>*</sup></label>
                    <input type="text" name="student_id" class="form-control" value="<?php echo $data['student_id']; ?>" placeholder="Enter Student Registration ID" required>
                </div>
                <div class="mb-3">
                    <label for="return_date">Due Date: <sup>*</sup></label>
                    <input type="date" name="return_date" class="form-control" value="<?php echo (!empty($data['return_date'])) ? $data['return_date'] : date('Y-m-d', strtotime('+15 days')); ?>" required>
                </div>
                <input type="submit" class="btn btn-success" value="Issue Book">
            </form>
        </div>
    </div>
</div>
<?php require_once 'views/layouts/footer.php'; ?>
