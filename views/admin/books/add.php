<?php require_once 'views/layouts/header.php'; ?>
<div class="row">
    <div class="col-md-3">
        <?php require_once 'views/layouts/admin_sidebar.php'; ?>
    </div>
    <div class="col-md-9">
        <div class="card card-body bg-light">
            <h2>Add Book</h2>
            <form action="<?php echo URLROOT; ?>/book/add" method="post">
                <?php echo Csrf::csrfField(); ?>
                <div class="mb-3">
                    <label for="title">Title: <sup>*</sup></label>
                    <input type="text" name="title" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="author_id">Author: <sup>*</sup></label>
                    <select name="author_id" class="form-control" required>
                        <option value="">Select Author</option>
                        <?php foreach($data['authors'] as $author) : ?>
                            <option value="<?php echo $author->id; ?>"><?php echo $author->name; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="category_id">Category: <sup>*</sup></label>
                    <select name="category_id" class="form-control">
                        <?php foreach($data['categories'] as $category) : ?>
                            <option value="<?php echo $category->id; ?>"><?php echo $category->name; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="isbn">ISBN:</label>
                    <input type="text" name="isbn" class="form-control">
                </div>
                <div class="mb-3">
                    <label for="quantity">Quantity: <sup>*</sup></label>
                    <input type="number" name="quantity" class="form-control" value="1" min="1">
                </div>
                <input type="submit" class="btn btn-success" value="Submit">
            </form>
        </div>
    </div>
</div>
<?php require_once 'views/layouts/footer.php'; ?>
