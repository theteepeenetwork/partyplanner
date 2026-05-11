<?= $this->include('header') ?>
<main class="container py-5">
    <article class="cms-page">
        <h1 class="mb-3"><?= esc($page['title']) ?></h1>
        <div class="cms-body">
            <?= $page['content'] ?>
        </div>
    </article>
</main>
<?= $this->include('footer') ?>
