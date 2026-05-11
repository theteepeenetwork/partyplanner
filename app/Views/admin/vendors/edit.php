<h1 class="h3 mb-3">Edit vendor</h1>
<form method="post" action="<?= site_url('/admin/vendors/' . $user['id'] . '/edit') ?>" class="card shadow-sm p-4" style="max-width:640px">
    <?= csrf_field() ?>
    <div class="mb-3">
        <label class="form-label">Name</label>
        <input class="form-control" name="name" value="<?= esc(old('name', $user['name'])) ?>" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Username</label>
        <input class="form-control" name="username" value="<?= esc(old('username', $user['username'])) ?>" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" class="form-control" name="email" value="<?= esc(old('email', $user['email'])) ?>" required>
    </div>
    <button class="btn btn-primary" type="submit">Save</button>
    <a class="btn btn-link" href="<?= site_url('/admin/vendors/' . $user['id']) ?>">Cancel</a>
</form>
