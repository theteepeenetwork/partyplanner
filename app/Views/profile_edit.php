<?= $this->include('header') ?>

    <main class="container mt-4">
        <h2>Edit Profile</h2>

        <?php if (session()->has('errors')): ?>
            </div>
        <?php endif; ?>
        <?php if (isset($user)): ?>
            <form action="/profile/edit" method="post">
                <div class="form-group">
                    <label for="name">Name:</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?= old('name', $user['name'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" class="form-control" id="username" name="username" value="<?= old('username', $user['username'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?= old('email', $user['email'] ?? '') ?>">
                </div>
                <button type="submit" class="btn btn-primary">Update Profile</button>
            </form>
        <?php else: ?>
            <div class="alert alert-danger">User not found.</div>
        <?php endif; ?>
    </main>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
