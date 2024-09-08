<?= $this->include('header') ?>

<body>
    <main class="container mt-4">
        <div class="alert alert-success">
            <?= esc($success) ?> 
        </div>

        <p>You can now <a href="/login">login</a> to your account.</p>
    </main>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>