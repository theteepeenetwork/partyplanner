<?= $this->include('header') ?>

<main class="container mt-4">
    <h2>Create New Event</h2>
    
    <?php if (session()->has('error')): ?>
        <div class="alert alert-danger">
            <?= session('error') ?>
        </div>
    <?php endif; ?>

    <?php if (session()->has('errors')): ?> 
        <div class="alert alert-danger">
            <ul>
                <?php foreach (session('errors') as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="/event/create" method="POST">
        <?= csrf_field() ?>
        <div class="form-group">
            <label for="title">Event Title:</label>
            <input type="text" class="form-control" id="title" name="title" value="<?= old('title') ?>">
        </div>

        <div class="form-group">
            <label for="ceremony_type">Ceremony Type:</label>
            <select class="form-control" id="ceremony_type" name="ceremony_type">
                <option value="wedding" <?= old('ceremony_type') == 'wedding' ? 'selected' : '' ?>>Wedding</option>
                <option value="party" <?= old('ceremony_type') == 'party' ? 'selected' : '' ?>>Party</option>
                <option value="corporate" <?= old('ceremony_type') == 'corporate' ? 'selected' : '' ?>>Corporate</option>
                <option value="other" <?= old('ceremony_type') == 'other' ? 'selected' : '' ?>>Other</option>
            </select>
        </div>

        <div class="form-group">
            <label for="location">Location:</label>
            <input type="text" class="form-control" id="location" name="location" value="<?= old('location') ?>">
        </div>

        <div class="form-group">
            <label for="date">Date:</label>
            <input type="date" class="form-control" id="date" name="date" value="<?= old('date') ?>">
        </div>

        <button type="submit" class="btn btn-primary">Create Event</button>
    </form>
</main>

<footer class="footer mt-5 py-3 bg-light">
</footer>
