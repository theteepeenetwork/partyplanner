<?= $this->include('header') ?>

<?= $this->include('service_create/css.php') ?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">


<main class="container mt-4">
    <form action="/service/step6" method="POST" enctype="multipart/form-data" id="publicEventForm">
        <?= csrf_field() ?>
        <section>
            <h4>Cancellation Policy</h4>
            <div class="form-group">
                <label for="cancellation_policy">Cancellation Policy:</label>
                <textarea class="form-control" id="cancellation_policy" name="cancellation_policy" rows="4"
                    placeholder="Enter your cancellation policy here...">Cancellation Policy – [Company Name]

Please be advised that cancellations made up to [48 hours] before a scheduled appointment via [email, text, phone call] will be processed without a penalty.

Cancellations made [48 hours] or less before an appointment will be subject to a charge of [half of the service rate]. This includes appointments where our service provider is unable to access the property, is turned away, or the client is a no-show.

If [Company Name] cancels an appointment with less than [48 hours notice], a new appointment will be scheduled without penalty to the client, subject to availability.</textarea>
            </div>

            <button type="submit" class="btn btn-primary">Review</button>
            </div>
        </section>

    </form>

</main>