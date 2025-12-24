<?php
// viewrecord.php - View All Transaction Records with Live Status Update
require_once 'config.php'; // MongoDB, Remita functions, session

// Optional: Restrict access later with admin login
// For now, public view (parents can check by description/student ID)

// Fetch all transactions, sorted by newest first
$options = ['sort' => ['created_at' => -1]];
$cursor = $collection->find([], $options);

// Update status for each record (checks Remita live status)
$updatedCount = 0;
foreach ($cursor as $doc) {
    if (isset($doc['rrr']) && $doc['status'] === 'pending') {
        $newStatus = checkRRRStatus($doc['rrr'], $doc['order_id']);
        if ($newStatus !== $doc['status']) {
            $collection->updateOne(
                ['_id' => $doc['_id']],
                ['$set' => ['status' => $newStatus]]
            );
            $updatedCount++;
        }
    }
}

// Re-fetch for display (to show updated statuses)
$cursor = $collection->find([], $options);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction Records - School Fees Portal</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="container">
            <h1>Transaction Records</h1>
            <nav>
                <a href="index.php">Home</a>
                <a href="fees.php">Pay Fees</a>
                <a href="viewrecord.php" class="active">View Records</a>
            </nav>
        </div>
    </header>

    <main class="container">
        <div class="card">
            <h2>Payment Records</h2>
            
            <?php if ($updatedCount > 0): ?>
                <div class="message success">Checked live status — <?php echo $updatedCount; ?> record(s) updated.</div>
            <?php endif; ?>

            <?php if ($cursor->isDead() || count(iterator_to_array($cursor)) === 0): ?>
                <p>No transactions recorded yet. Start by <a href="fees.php">generating an RRR</a>.</p>
            <?php else: ?>
                <button id="refreshBtn" style="margin-bottom: 1rem; padding: 0.8rem 1.5rem; background: #004d99; color: white; border: none; border-radius: 8px; cursor: pointer;">
                    Refresh Status (Check Remita)
                </button>

                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>RRR</th>
                            <th>Amount (₦)</th>
                            <th>Payer Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Description</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cursor as $row): ?>
                            <tr>
                                <td data-label="Date"><?php echo date('M j, Y H:i', $row['created_at']->toDateTime()->getTimestamp()); ?></td>
                                <td data-label="RRR"><strong><?php echo htmlspecialchars($row['rrr'] ?? 'N/A'); ?></strong></td>
                                <td data-label="Amount"><?php echo number_format($row['amount'], 2); ?></td>
                                <td data-label="Payer"><?php echo htmlspecialchars($row['payer_name']); ?></td>
                                <td data-label="Email"><?php echo htmlspecialchars($row['payer_email']); ?></td>
                                <td data-label="Phone"><?php echo htmlspecialchars($row['payer_phone']); ?></td>
                                <td data-label="Description"><?php echo htmlspecialchars($row['description']); ?></td>
                                <td data-label="Status">
                                    <span class="status-<?php echo $row['status']; ?>">
                                        <?php echo ucfirst($row['status']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
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