<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Create Test Page</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>

<body>
    <div class="container mt-4">
        <h2>Service Create Test Page</h2>

        <?php if (session()->has('errors')): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach (session('errors') as $error): ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach ?>
                </ul>
            </div>
        <?php endif ?>

        <h4>Service Information</h4>
        <ul>
            <li><strong>Title:</strong> <?= esc($serviceData['title']) ?></li>
            <li><strong>Short Description:</strong> <?= esc($serviceData['short_description']) ?></li>
            <li><strong>Description:</strong> <?= esc($serviceData['description']) ?></li>
            <li><strong>Category ID:</strong> <?= esc($serviceData['category_id']) ?></li>
            <li><strong>Subcategory ID:</strong> <?= esc($serviceData['subcategory_id']) ?></li>
            <li><strong>Third Category ID:</strong> <?= esc($serviceData['third_category_id']) ?></li>
            <li><strong>Service Tags:</strong> <?= esc($serviceData['service_tags']) ?></li>
            <li><strong>Service Location:</strong> <?= esc($serviceData['service_location']) ?></li>
            <li><strong>Latitude:</strong> <?= esc($serviceData['latitude']) ?></li>
            <li><strong>Longitude:</strong> <?= esc($serviceData['longitude']) ?></li>
            <li><strong>All Travel Included:</strong> <?= esc($serviceData['all_travel_included']) ?></li>
            <li><strong>No Travel Limit:</strong> <?= esc($serviceData['no_travel_limit']) ?></li>
            <li><strong>Free Coverage Radius:</strong> <?= esc($serviceData['free_coverage_radius']) ?></li>
            <li><strong>Paid Coverage Radius:</strong> <?= esc($serviceData['paid_coverage_radius']) ?></li>
            <li><strong>Travel Fee per KM:</strong> <?= esc($serviceData['travel_fee_per_km']) ?></li>
            <li><strong>Cancellation Policy:</strong> <?= esc($serviceData['cancellation_policy']) ?></li>
        </ul>

        <h4>Event Types</h4>
        <ul>
            <?php foreach ($eventTypes as $eventType): ?>
                <li><?= esc($eventType) ?></li>
            <?php endforeach ?>
        </ul>

        <h4>Optional Extras</h4>
        <ul>
            <?php foreach ($optionalExtras as $extra): ?>
                <li><strong>Name:</strong> <?= esc($extra['name']) ?>, <strong>Price:</strong> <?= esc($extra['price']) ?>
                </li>
            <?php endforeach ?>
        </ul>

        <h4>Public Event Data</h4>
        <ul>
            <li><strong>Commission Percentage:</strong> <?= esc($publicEventData['commission_percentage']) ?></li>
            <li><strong>License:</strong> <?= esc($publicEventData['license']) ?></li>
            <?php foreach ($publicEventData['attendance_thresholds'] as $index => $threshold): ?>
                <li><strong>Attendance Threshold:</strong> <?= esc($threshold) ?>, <strong>Max Pitch Fee:</strong>
                    <?= esc($publicEventData['max_pitch_fees'][$index]) ?></li>
            <?php endforeach ?>
        </ul>

        <h4>Private Event Data</h4>
        <ul>
            <li><strong>Price:</strong> <?= esc($privateEventData['price']) ?></li>
            <li><strong>Pricing Type:</strong> <?= esc($privateEventData['pricing_type']) ?></li>
            <?php if ($privateEventData['pricing_type'] == 'guest_based'): ?>
                <?php foreach ($privateEventData['guest_ranges'] as $index => $range): ?>
                    <li><strong>Guest Range:</strong> <?= esc($range) ?>, <strong>Price:</strong>
                        <?= esc($privateEventData['guest_prices'][$index]) ?></li>
                <?php endforeach ?>
            <?php elseif ($privateEventData['pricing_type'] == 'custom_duration'): ?>
                <?php foreach ($privateEventData['duration_labels'] as $index => $label): ?>
                    <li><strong>Duration:</strong> <?= esc($label) ?>, <strong>Price:</strong>
                        <?= esc($privateEventData['duration_prices'][$index]) ?></li>
                <?php endforeach ?>
            <?php elseif ($privateEventData['pricing_type'] == 'tiered_packages'): ?>
                <?php foreach ($privateEventData['package_names'] as $index => $name): ?>
                    <li><strong>Package Name:</strong> <?= esc($name) ?>, <strong>Description:</strong>
                        <?= esc($privateEventData['package_descriptions'][$index]) ?>, <strong>Price:</strong>
                        <?= esc($privateEventData['package_prices'][$index]) ?></li>
                <?php endforeach ?>
            <?php endif ?>
        </ul>
    </div>
</body>

</html>