<?= $this->include('header') ?>

<main class="container mt-4">
    <h2>Add Availability for <?= esc($service['title']) ?></h2>

    <?php if (session()->has('success')): ?>
        <div class="alert alert-success">
            <?= session('success') ?>
        </div>
    <?php elseif (session()->has('error')): ?>
        <div class="alert alert-danger">
            <?= session('error') ?>
        </div>
    <?php endif; ?>

    <form action="/service/add-availability/<?= esc($service['id']) ?>" method="POST">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="date">Date:</label>
            <input type="date" class="form-control" id="date" name="date" required>
        </div>

        <div class="form-group">
            <label for="start_time">Start Time:</label>
            <input type="time" class="form-control" id="start_time" name="start_time" required>
        </div>

        <div class="form-group">
            <label for="end_time">End Time:</label>
            <input type="time" class="form-control" id="end_time" name="end_time" required>
        </div>

        <button type="submit" class="btn btn-primary">Add Availability</button>
    </form>
</main>

<footer class="footer mt-5 py-3 bg-light">
</footer>

<?= $this->include('footer') ?>