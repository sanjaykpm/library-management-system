<?php require_once 'views/layouts/header.php'; ?>
<div class="row">
    <div class="col-md-3">
        <?php require_once 'views/layouts/admin_sidebar.php'; ?>
    </div>
    <div class="col-md-9">
        <h1 class="mb-4">Edit Book</h1>
        
        <div class="card">
            <div class="card-body">
                <form action="<?php echo URLROOT; ?>/book/edit/<?php echo $data['book']->id; ?>" method="post">
                    <?php echo Csrf::csrfField(); ?>
                    <div class="mb-3">
                        <label for="accession_no">Accession No:</label>
                        <input type="text" class="form-control" value="<?php echo $data['book']->accession_no; ?>" disabled>
                    </div>
                    
                    <div class="mb-3">
                        <label for="title">Title: <sup>*</sup></label>
                        <input type="text" name="title" class="form-control" value="<?php echo $data['book']->title; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="author_id">Author: <sup>*</sup></label>
                        <select name="author_id" class="form-control" required>
                            <option value="">Select Author</option>
                            <?php foreach($data['authors'] as $author) : ?>
                                <option value="<?php echo $author->id; ?>" <?php echo ($author->id == $data['book']->author_id) ? 'selected' : ''; ?>>
                                    <?php echo $author->name; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="category_id">Category: <sup>*</sup></label>
                        <select name="category_id" class="form-control" required>
                            <option value="">Select Category</option>
                            <?php foreach($data['categories'] as $category) : ?>
                                <option value="<?php echo $category->id; ?>" <?php echo ($category->id == $data['book']->category_id) ? 'selected' : ''; ?>>
                                    <?php echo $category->name; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="isbn">ISBN:</label>
                        <input type="text" name="isbn" class="form-control" value="<?php echo $data['book']->isbn; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="quantity">Quantity: <sup>*</sup></label>
                        <input type="number" name="quantity" class="form-control" value="<?php echo $data['book']->quantity; ?>" min="1" required>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">Update Book</button>
                        <a href="<?php echo URLROOT; ?>/book/manage" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require_once 'views/layouts/footer.php'; ?>
