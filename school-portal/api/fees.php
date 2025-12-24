<?php
// fees.php - School Fees Payment Form with Remita RRR Generation
require_once 'config.php'; // Includes MongoDB connection, Remita functions, CSRF

$message = '';
$rrr = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. CSRF Protection
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = '<div class="message error">Invalid security token. Please refresh and try again.</div>';
    } else {
        // 2. Server-side validation & sanitization (security layer)
        $payerName = trim(filter_input(INPUT_POST, 'payer_name', FILTER_SANITIZE_STRING));
        $payerEmail = trim(filter_input(INPUT_POST, 'payer_email', FILTER_SANITIZE_EMAIL));
        $payerPhone = trim(filter_input(INPUT_POST, 'payer_phone', FILTER_SANITIZE_STRING));
        $amount = floatval($_POST['amount']);
        $description = trim(filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING));

        // Additional validation
        $errors = [];
        if (strlen($payerName) < 3) $errors[] = "Valid full name is required.";
        if (!filter_var($payerEmail, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";
        if (!preg_match('/^[\+]?[0-9]{10,15}$/', str_replace(' ', '', $payerPhone))) $errors[] = "Valid phone number is required.";
        if ($amount <= 0) $errors[] = "Amount must be greater than zero.";
        if (strlen($description) < 10) $errors[] = "Detailed description is required (e.g., Student ID, Class, Term).";

        if (!empty($errors)) {
            $message = '<div class="message error"><strong>Please correct the following:</strong><ul style="text-align:left;margin-top:0.5rem;">';
            foreach ($errors as $err) $message .= "<li>$err</li>";
            $message .= '</ul></div>';
        } else {
            // 3. Generate unique order ID
            $orderId = 'SCH_' . uniqid() . time();

            // 4. Call Remita to generate RRR
            $result = generateRRR($payerName, $payerEmail, $payerPhone, $description, $amount, $orderId);

            // Debug: You can uncomment next line temporarily to see raw response
            // $message .= '<pre>' . print_r($result, true) . '</pre>';

            if (isset($result['statuscode']) && $result['statuscode'] === '00' && isset($result['RRR'])) {
                $rrr = $result['RRR'];

                // 5. Store transaction in MongoDB
                $insertData = [
                    'order_id'      => $orderId,
                    'rrr'           => $rrr,
                    'amount'        => $amount,
                    'payer_name'    => $payerName,
                    'payer_email'   => $payerEmail,
                    'payer_phone'   => $payerPhone,
                    'description'   => $description,
                    'status'        => 'pending',
                    'created_at'    => new MongoDB\BSON\UTCDateTime()
                ];

                $insertResult = $collection->insertOne($insertData);

                if ($insertResult->getInsertedCount() === 1) {
                    $message = '<div class="message success">RRR Generated Successfully!</div>';
                    $message .= '<div class="rrr-box" id="rrr">Your Remita Retrieval Reference (RRR):<br><strong>' . $rrr . '</strong><br><br>Copy this number and pay via any Remita channel (bank, card, USSD, etc.).<br>Payment status will update automatically.</div>';
                    // Auto-scroll to RRR
                    echo '<script>window.location.hash = "rrr";</script>';
                } else {
                    $message = '<div class="message error">RRR generated but failed to save record. Contact admin.</div>';
                }
            } else {
                // Detailed error from Remita
                $errorMsg = $result['statusmessage'] ?? $result['message'] ?? 'Unknown error';
                $httpCode = $result['httpCode'] ?? 'N/A';
                $message = "<div class=\"message error\">Failed to generate RRR.<br><strong>Error:</strong> $errorMsg<br><strong>HTTP Code:</strong> $httpCode<br>Please try again or contact support.</div>";
            }
        }
    }
}

// Generate fresh CSRF token for the form
$csrfToken = generateCSRFToken();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pay School Fees - Remita Portal</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="container">
            <h1>Pay School Fees</h1>
            <nav>
                <a href="index.php">Home</a>
                <a href="fees.php" class="active">Pay Fees</a>
                <a href="viewrecord.php">View Records</a>
            </nav>
        </div>
    </header>

    <main class="container">
        <div class="card">
            <h2>Generate Payment Reference (RRR)</h2>
            <p>Fill in the details below to generate your unique Remita Retrieval Reference (RRR).</p>

            <?php echo $message; ?>

            <form id="feeForm" method="POST" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">

                <label for="payer_name">Parent/Guardian Full Name *</label>
                <input type="text" id="payer_name" name="payer_name" value="<?php echo htmlspecialchars($_POST['payer_name'] ?? ''); ?>" required>

                <label for="payer_email">Email Address *</label>
                <input type="email" id="payer_email" name="payer_email" value="<?php echo htmlspecialchars($_POST['payer_email'] ?? ''); ?>" required>

                <label for="payer_phone">Phone Number *</label>
                <input type="text" id="payer_phone" name="payer_phone" placeholder="+2348012345678" value="<?php echo htmlspecialchars($_POST['payer_phone'] ?? ''); ?>" required>

                <label for="amount">Fee Amount (₦) *</label>
                <input type="number" id="amount" name="amount" step="0.01" min="1" value="<?php echo htmlspecialchars($_POST['amount'] ?? ''); ?>" required>

                <label for="description">Description (e.g., Student ID: S12345, Class: JSS1, Term: First Term 2025/2026) *</label>
                <textarea id="description" name="description" required><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>

                <button type="submit">Generate RRR & Proceed to Payment</button>
            </form>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>© 2025 Your School Name. Secure payments powered by Remita.</p>
        </div>
    </footer>

    <script src="script.js"></script>
</body>
</html>