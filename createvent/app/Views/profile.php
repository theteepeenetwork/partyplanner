<?= $this->include('header') ?>
<style>
    .status-light {
    width: 20px; 
    height: 20px;
    border-radius: 50%; /* Make it a circle */
    display: inline-block;
}
</style>
<body>
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
    <h2>My Bookings</h2>

    <?php if (!empty($bookings)): ?>
        <ul class="list-group">
            <?php foreach ($bookings as $booking): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <h4>Booking #<?= esc($booking['id']) ?></h4>

                    <ul class="list-inline mb-0">
                        <?php foreach ($booking['services'] as $service): ?>
                            <li class="list-inline-item">
                                <li><?= esc($service['title']) ?> - $<?= esc($service['price']) ?></li>
                            
                            <div class="status-light" style="background-color: <?= 
                        $booking['status'] == 'accepted' ? 'green' : (
                        $booking['status'] == 'pending' ? 'orange' : 'red') ?>;">
                    </div>
                        <?php endforeach; ?>
                    </ul>

                    
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>You don't have any bookings yet.</p>
    <?php endif; ?>

    

    <table class="table">
  <thead>
    <tr>
      <th>Booking</th>
      <th>Service</th>
      <th>Price</th>
      <th>Status</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>Booking 6</td>
      <td>Sweetie Sweet Cart</td>
      <td>$150.00</td>
      <td><div class="status-light" style="background-color: <?= 
                        $booking['status'] == 'accepted' ? 'green' : (
                        $booking['status'] == 'pending' ? 'orange' : 'red') ?>;">
        </td>
    </tr>
    <tr>
      <td></td>
      <td>Mrs Beatys Burgers</td>
      <td>120</td>
      <td><div class="status-light" style="background-color: <?= 
                        $booking['status'] == 'accepted' ? 'green' : (
                        $booking['status'] == 'pending' ? 'orange' : 'red') ?>;"></td>
    </tr>
    <tr>
      <td>Booking 7</td>
      <td>Mrs Beatys Burgers</td>
      <td>120</td>
      <td><div class="status-light" style="background-color: <?= 
                        $booking['status'] == 'accepted' ? 'green' : (
                        $booking['status'] == 'pending' ? 'orange' : 'red') ?>;"></td>
    </tr>
    <td></td>
      <td>Mrs Beatys Burgers</td>
      <td>120</td>
      <td><div class="status-light" style="background-color: <?= 
                        $booking['status'] == 'accepted' ? 'green' : (
                        $booking['status'] == 'pending' ? 'orange' : 'red') ?>;"></td>
    </tr>
    <td></td>
      <td>Mrs Beatys Burgers</td>
      <td>120</td>
      <td><div class="status-light" style="background-color: <?= 
                        $booking['status'] == 'accepted' ? 'green' : (
                        $booking['status'] == 'pending' ? 'orange' : 'red') ?>;"></td>
    </tr>
    <tr>
      <td>Booking 8</td>
      <td>Mrs Beatys Burgers</td>
      <td>120</td>
      <td><div class="status-light" style="background-color: <?= 
                        $booking['status'] == 'accepted' ? 'green' : (
                        $booking['status'] == 'pending' ? 'orange' : 'red') ?>;"></td>
    </tr>
    <td></td>
      <td>Mrs Beatys Burgers</td>
      <td>120</td>
      <td><div class="status-light" style="background-color: <?= 
                        $booking['status'] == 'accepted' ? 'green' : (
                        $booking['status'] == 'pending' ? 'orange' : 'red') ?>;"></td>
    </tr>
    <td></td>
      <td>Mrs Beatys Burgers</td>
      <td>120</td>
      <td><div class="status-light" style="background-color: <?= 
                        $booking['status'] == 'accepted' ? 'green' : (
                        $booking['status'] == 'pending' ? 'orange' : 'red') ?>;"></td>
    </tr>
    

  </tbody>
</table>




</main>

    </main>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
