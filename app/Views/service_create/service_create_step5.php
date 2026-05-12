<?= $this->include('header') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/service-form.css'); ?>">

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">


<main class="container mt-4">
    <form action="/service/step5" method="POST" enctype="multipart/form-data" id="publicEventForm" class="service-form">
        <?= csrf_field() ?>
        <section>
            <h4>
                Optional Extras
            </h4>
            <!-- Optional Extras Section -->
            <div>
                <h5>Optional Extras</h5>
                <p class="form-text text-muted">Add any optional extras your service offers, such as additional
                    staff or equipment.
                </p>

                <!-- Optional Extras Container -->

                <div id="optionalExtrasContainer">
                    <!-- Dynamic optional extras rows will be added here -->
                </div>
                <p class="form-text text-muted mt-2">
                    Need ideas for optional extras?
                    <span class="info-icon info-trigger" tabindex="0" data-bs-toggle="popover" data-bs-trigger="focus"
                        data-bs-html="true" title="Examples of Optional Extras" data-bs-content="
                      <strong>Good optional extras clearly explain what the customer gets and why it’s useful.</strong><br><br>

<strong>🎧 DJs & Entertainment</strong><br>
<strong>Additional lighting unit</strong><br>
An extra professional lighting fixture added to improve coverage across larger venues or darker areas. Ideal for bigger dancefloors or venues with limited ambient lighting.<br><br>

<strong>Dancefloor lighting upgrade</strong><br>
Upgraded lighting package with additional effects and movement, synced to the music to create a more immersive dancefloor experience.<br><br>

<strong>🍸 Bars & Mobile Drinks Services</strong><br>
<strong>Cocktail-making class</strong><br>
A guided cocktail-making experience led by a professional bartender. Includes ingredients, tools, and instruction for guests to create two classic cocktails each.<br><br>

<strong>Premium cocktail menu</strong><br>
Expanded drinks menu featuring premium spirits, fresh ingredients, and bespoke cocktails tailored to your event.<br><br>

<strong>🍬 Sweet Stands & Food Carts</strong><br>
<strong>Gazebo hire</strong><br>
Weather-resistant gazebo supplied to protect equipment and guests at outdoor events, helping service continue smoothly in poor weather.<br><br>

<strong>Extended serving time</strong><br>
Additional service time added to your booking, ideal for events running over schedule or experiencing high demand.<br><br>

<strong>📸 Photo Booths</strong><br>
<strong>Guest book service</strong><br>
A physical guest book where one photo strip is added per group, with space for guests to leave handwritten messages.<br><br>

<strong>Premium guest book upgrade</strong><br>
Luxury guest book with a personalised cover and staff assistance to encourage guest participation throughout the event.<br><br>

<strong>💡 Tip:</strong><br>
Describe what’s included, how long it lasts, or why a customer might want it.

                        " role="button" aria-label="View example optional extras">
                        (see examples)
                    </span>
                </p>
                <button type="button" class="btn btn-primary mb-3" id="addOptionalExtra">Add Another Extra</button>


                <!-- Navigation Buttons -->



            </div>
            <button type="submit" class="btn btn-primary">
                <?= empty($step6_data) ? "Next" : "Review" ?>

            </button>
        </section>
    </form>




</main>

<script src="<?= base_url('assets/js/service_forms/step5.js') ?>"></script>
<script src="<?= base_url('assets/js/test1.js') ?>"></script>
<script>const step5Data = <?= json_encode(session()->get('step5_data') ?? []) ?>;</script>