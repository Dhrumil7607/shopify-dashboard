<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Shopify-like Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background-color: #f8f9fa; }
    .sidebar {
      width: 250px;
      height: 100vh;
      position: fixed;
      top: 0;
      left: 0;
      background: #fff;
      border-right: 1px solid #dee2e6;
      padding: 1rem;
    }
    .sidebar .nav-link.active {
      background: #f1f1f1;
      font-weight: bold;
    }
    .main-content {
      margin-left: 250px;
      padding: 2rem;
    }
  </style>
</head>
<body>
<?php
session_start();
if (!isset($_SESSION['loggedin'])):
?>
  <div class="d-flex justify-content-center align-items-center vh-100">
    <form method="post" class="p-4 border rounded bg-white text-center">
      <h3 class="mb-3">Login</h3>
      <input type="text" name="username" class="form-control mb-2" placeholder="Username" required>
      <input type="password" name="password" class="form-control mb-3" placeholder="Password" required>
      <input type="submit" name="login" value="Login" class="btn btn-primary w-100">
    </form>
  </div>
  <?php
  if (isset($_POST['login'])) {
    $_SESSION['loggedin'] = true;
    $_SESSION['username'] = $_POST['username'];
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
  }
  ?>
<?php else:
  if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
  }

  if (!isset($_SESSION['products'])) {
    $_SESSION['products'] = [
      ["name" => "Wireless Earbuds", "sales" => 320, "amount" => 20000],
      ["name" => "Smart Watch", "sales" => 275, "amount" => 15000],
      ["name" => "Phone Case", "sales" => 215, "amount" => 10000]
    ];
  }

  $products = $_SESSION['products'];
  $totalEarnings = array_sum(array_column($products, 'amount'));

  if (isset($_POST['add_product'])) {
    $_SESSION['products'][] = [
      "name" => $_POST['prod_name'],
      "sales" => (int)$_POST['prod_sales'],
      "amount" => (int)$_POST['prod_amount']
    ];
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
  }

  if (isset($_POST['withdraw'])) {
    $amt = (int)$_POST['amount'];
    if ($amt <= $totalEarnings) {
      $_SESSION['withdrawal'] = $amt;
      $withdraw_success = true;
    } else {
      $withdraw_error = true;
    }
  }
?>
<div class="sidebar">
  <h4>MyStore</h4>
  <nav class="nav flex-column">
    <form method="post">
      <input type="submit" name="tab" value="Dashboard" class="nav-link <?= $_POST['tab'] == 'Dashboard' ? 'active' : '' ?>">
      <input type="submit" name="tab" value="Products" class="nav-link <?= $_POST['tab'] == 'Products' ? 'active' : '' ?>">
      <input type="submit" name="tab" value="Earnings" class="nav-link <?= $_POST['tab'] == 'Earnings' ? 'active' : '' ?>">
      <input type="submit" name="logout" value="Logout" class="btn btn-outline-danger mt-3">
    </form>
  </nav>
</div>
<div class="main-content">
<?php $tab = $_POST['tab'] ?? 'Dashboard'; ?>
<?php if ($tab === 'Dashboard'): ?>
  <h2>Welcome, <?= $_SESSION['username'] ?></h2>
  <h3 class="mt-4">Sales Overview</h3>
  <div class="row">
    <?php foreach ($products as $prod): ?>
      <div class="col-md-4 mb-3">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title"><?= $prod['name'] ?></h5>
            <p class="card-text">Sales: <?= $prod['sales'] ?></p>
            <p class="card-text">Amount: ₹<?= $prod['amount'] ?></p>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
  <div class="mt-3 text-end">
    <strong>Total Earnings: ₹<?= $totalEarnings ?></strong>
  </div>
<?php elseif ($tab === 'Products'): ?>
  <h2>Manage Products</h2>
  <form method="post" class="row g-3 mb-4">
    <input type="hidden" name="tab" value="Products">
    <div class="col-md-4">
      <input type="text" name="prod_name" class="form-control" placeholder="Product Name" required>
    </div>
    <div class="col-md-3">
      <input type="number" name="prod_sales" class="form-control" placeholder="Sales" required>
    </div>
    <div class="col-md-3">
      <input type="number" name="prod_amount" class="form-control" placeholder="Amount ₹" required>
    </div>
    <div class="col-md-2">
      <button type="submit" name="add_product" class="btn btn-success w-100">Add</button>
    </div>
  </form>
  <ul class="list-group">
    <?php foreach ($products as $prod): ?>
      <li class="list-group-item d-flex justify-content-between">
        <?= $prod['name'] ?> <span><?= $prod['sales'] ?> sales • ₹<?= $prod['amount'] ?></span>
      </li>
    <?php endforeach; ?>
  </ul>
<?php elseif ($tab === 'Earnings'): ?>
  <h2>Withdraw Earnings</h2>
  <form method="post">
    <input type="hidden" name="tab" value="Earnings">
    <div class="mb-3">
      <label for="amount" class="form-label">Enter Amount</label>
      <input type="number" id="amount" name="amount" class="form-control" required>
    </div>
    <button type="submit" name="withdraw" class="btn btn-success">Withdraw</button>
  </form>
  <?php if (!empty($withdraw_success)): ?>
    <p class="text-success mt-2">Withdrawal of ₹<?= $_SESSION['withdrawal'] ?> successful!</p>
  <?php elseif (!empty($withdraw_error)): ?>
    <p class="text-danger mt-2">Amount exceeds available earnings.</p>
  <?php endif; ?>
<?php endif; ?>
</div>
<?php endif; ?>
</body>
</html>