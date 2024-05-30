<?= $this->include('header') ?>

    <main class="container mt-4">
        <h2>My Profile</h2>

        <div class="card">
            <div class="card-body">
            <h5 class="card-title"><?= esc($user['name']) ?></h5>    
            <h5 class="card-title"><?= esc($user['username']) ?></h5>
                <p class="card-text">Email: <?= esc($user['email']) ?></p>
                <p class="card-text">Role: <?= esc($user['role']) ?></p>
                <a href="/profile/edit" class="btn btn-primary">Edit Profile</a> 
            </div>
        </div>

        <?= $this->include('header') ?>

        <main class="container mt-4">