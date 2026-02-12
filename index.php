<?php
require_once __DIR__ . '/PayWayApiCheckout.php';

// ====== SIMPLE SETTINGS ======
$amount   = '2.00';   // USD example
$currency = 'USD';    // USD or KHR (KHR must be >= 100)
$itemData = [         // Proper items format: array of items
    ['name' => 'Test Item', 'quantity' => 1, 'price' => 2.00]
];
$items    = base64_encode(json_encode(['item' => $itemData]));  // Required: base64(json)
$shipping = '0.00';   // Decimal string
// =============================

// Auto base URL (works local + render)
$scheme  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host    = $_SERVER['HTTP_HOST'] ?? 'localhost';
$baseUrl = $scheme . '://' . $host . rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/');

// Redirect URLs
$returnUrl  = $baseUrl . '/index.php?status=return';
$cancelUrl  = $baseUrl . '/index.php?status=cancel';
$successUrl = $baseUrl . '/index.php?status=success';

// Show redirect result
if (isset($_GET['status'])) {
    echo "<h3>Status: " . htmlspecialchars($_GET['status']) . "</h3>";
    echo "<pre>" . htmlspecialchars(print_r($_GET, true)) . "</pre>";
    echo '<p><a href="index.php">Back</a></p>';
    exit;
}

// Validate KHR minimum
if ($currency === 'KHR' && (float)$amount < 100) {
    die("KHR amount must be >= 100");
}

// Create tran id and req_time
$tranId   = 'T' . date('YmdHis') . rand(1000, 9999);
$reqTime  = gmdate('YmdHis');  // UTC, mandatory

// Build payload (use exact param names, include all for hash)
$payload = [
    'req_time'             => $reqTime,
    'merchant_id'          => PayWayApiCheckout::getMerchantId(),
    'tran_id'              => $tranId,
    'amount'               => $amount,
    'items'                => $items,
    'shipping'             => $shipping,
    'ctid'                 => '',  // Empty if not using Credentials on File
    'pwt'                  => '',  // Empty if not using
    'firstname'            => 'Test',  // No underscore
    'lastname'             => 'User',  // No underscore
    'email'                => 'test@example.com',
    'phone'                => '012345678',
    'type'                 => 'purchase',
    'payment_option'       => 'abapay',  // Or 'cards' for card payments
    'return_url'           => $returnUrl,
    'cancel_url'           => $cancelUrl,
    'continue_success_url' => $successUrl,
    'return_deeplink'      => '',  // For mobile apps if needed
    'currency'             => $currency,
    'custom_fields'        => '',  // base64(json) if needed
    'return_params'        => '',  // Any note/params returned on callback
];

$payload['hash'] = PayWayApiCheckout::buildHash($payload);

$checkoutUrl = PayWayApiCheckout::getApiUrl();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>ABA PayWay - abapay</title>
  <style>
    body{font-family:Arial,sans-serif;padding:18px}
    .card{max-width:520px;margin:0 auto;border:1px solid #ddd;border-radius:10px;padding:16px}
    button{padding:12px 14px;font-size:16px;width:100%;cursor:pointer}
    pre{white-space:pre-wrap;word-break:break-word}
  </style>
</head>
<body>
<div class="card">
  <h2>Checkout</h2>
  <p><b>Total:</b> <?= htmlspecialchars($amount) ?> <?= htmlspecialchars($currency) ?></p>
  <p><b>Payment Option:</b> abapay</p>

  <form id="paywayForm" method="post" action="<?= htmlspecialchars($checkoutUrl) ?>">
    <?php foreach ($payload as $k => $v): ?>
      <input type="hidden" name="<?= htmlspecialchars($k) ?>" value="<?= htmlspecialchars((string)$v) ?>">
    <?php endforeach; ?>
    <button type="submit">Checkout Now</button>
  </form>

  <script>
    // Auto submit (optional). If you want manual click, comment next line.
    // document.getElementById('paywayForm').submit();
  </script>

  <details style="margin-top:12px">
    <summary>Debug payload</summary>
    <pre><?= htmlspecialchars(print_r($payload, true)) ?></pre>
  </details>
</div>
</body>
</html>
