<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>
<?php $isEdit = isset($news) && $news; ?>
<h2 class="mb-4"><?= $isEdit ? 'Edit News' : 'Create News' ?></h2>
<form method="post" enctype="multipart/form-data" action="<?= $isEdit ? '/admin/news/edit/' . esc($news['id']) : '/admin/news/create' ?>">
    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-control" value="<?= $isEdit ? esc($news['title']) : '' ?>" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Subtitle</label>
            <input type="text" name="subtitle" class="form-control" value="<?= $isEdit ? esc($news['subtitle']) : '' ?>">
        </div>
        <div class="col-md-12">
            <label class="form-label">Lead Text</label>
            <textarea name="lead_text" class="form-control" rows="2"><?= $isEdit ? esc($news['lead_text']) : '' ?></textarea>
        </div>
        <div class="col-md-12">
            <div class="alert alert-info small mb-2">
                You can either upload an image or provide an image URL. <strong>If both are provided, the uploaded image will be used.</strong>
            </div>
        </div>
        <div class="col-md-6">
            <label class="form-label">Image Upload</label>
            <input type="file" name="image" class="form-control">
                            <?php if ($isEdit && !empty($news['image_url']) && strpos($news['image_url'], '/public/uploads/news/') === 0): ?>
                <img src="<?= esc($news['image_url']) ?>" alt="Current Image" class="img-thumbnail mt-2" style="max-width: 200px;">
            <?php endif; ?>
        </div>
        <div class="col-md-6">
            <label class="form-label">Image URL</label>
            <input type="text" name="image_url" class="form-control" value="<?= $isEdit ? esc($news['image_url']) : '' ?>">
        </div>
        <div class="col-md-12">
            <label class="form-label">Content</label>
            <textarea name="content" id="content" class="form-control" rows="6" required><?= $isEdit ? esc($news['content']) : '' ?></textarea>
        </div>
        <div class="col-md-4">
            <label class="form-label">Category</label>
            <select name="category_id" class="form-select" required>
                <option value="">Select Category</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= esc($cat['id']) ?>" <?= $isEdit && $news['category_id'] == $cat['id'] ? 'selected' : '' ?>><?= esc($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Tags</label>
            <select name="tags[]" class="form-select" multiple>
                <?php foreach ($tags as $tag): ?>
                    <option value="<?= esc($tag['id']) ?>" <?= isset($selectedTagIds) && in_array($tag['id'], $selectedTagIds ?? []) ? 'selected' : '' ?>><?= esc($tag['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Status</label>
            <select name="status" class="form-select" required>
                <option value="draft" <?= $isEdit && $news['status'] == 'draft' ? 'selected' : '' ?>>Draft</option>
                <option value="published" <?= $isEdit && $news['status'] == 'published' ? 'selected' : '' ?>>Published</option>
                <option value="archived" <?= $isEdit && $news['status'] == 'archived' ? 'selected' : '' ?>>Archived</option>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Slug</label>
            <input type="text" name="slug" class="form-control" value="<?= $isEdit ? esc($news['slug']) : '' ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Source</label>
            <input type="text" name="source" class="form-control" value="<?= $isEdit ? esc($news['source']) : '' ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Dateline</label>
            <input type="text" name="dateline" class="form-control" value="<?= $isEdit ? esc($news['dateline']) : '' ?>">
        </div>
        <div class="col-md-2">
            <label class="form-label">Word Count</label>
            <input type="number" name="word_count" class="form-control" value="<?= $isEdit ? esc($news['word_count']) : '' ?>">
        </div>
        <div class="col-md-2">
            <label class="form-label">Language</label>
            <select name="language" class="form-select" required>
                <option value="en" <?= $isEdit && $news['language'] == 'en' ? 'selected' : '' ?>>English</option>
                <option value="bn" <?= $isEdit && $news['language'] == 'bn' ? 'selected' : '' ?>>Bengali</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Published At</label>
            <input type="datetime-local" name="published_at" class="form-control" value="<?= $isEdit && $news['published_at'] ? date('Y-m-d\TH:i', strtotime($news['published_at'])) : '' ?>">
        </div>
        <div class="col-md-4">
            <div class="form-check mt-4">
                <input class="form-check-input" type="checkbox" name="featured" value="1" id="featured" <?= $isEdit && $news['featured'] ? 'checked' : '' ?>>
                <label class="form-check-label" for="featured">
                    Featured Article
                </label>
            </div>
        </div>
    </div>
    <button type="submit" class="btn btn-success">Save</button>
    <a href="/admin/news" class="btn btn-secondary">Cancel</a>
</form>
<?= $this->endSection() ?>
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
tinymce.init({
  selector: '#content',
  height: 400,
  plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table code help wordcount',
  toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
  menubar: false
});
</script> 