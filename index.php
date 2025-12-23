<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Secure online school fees payment portal powered by Remita. Pay school fees easily and track payment status.">
    <title>School Fees Payment Portal</title>
    
    <!-- Favicon (optional - add your school logo later) -->
    <!-- <link rel="icon" href="favicon.ico" type="image/x-icon"> -->
    
    <!-- Stylesheet -->
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Header Section -->
    <header>
        <div class="container">
            <h1>School Fees Payment Portal</h1>
            <nav>
                <a href="index.php" class="active">Home</a>
                <a href="fees.php">Pay Fees</a>
                <a href="viewrecord.php">View Records</a>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container">
        <section class="card">
            <h2>Welcome to Our Secure Payment Portal</h2>
            <p>
                Thank you for choosing our school. This portal allows parents and guardians to conveniently pay school fees online using <strong>Remita</strong> — Nigeria's trusted payment platform.
            </p>
            <p style="margin-top: 1.5rem;">
                Payments can be made via:
            </p>
            <ul style="text-align: left; margin: 1.5rem auto; max-width: 400px; line-height: 2;">
                <li>Bank Branch (with generated RRR)</li>
                <li>Debit/Credit Card</li>
                <li>Internet Banking</li>
                <li>USSD (*901# etc.)</li>
                <li>Remita Wallet or App</li>
            </ul>
            <p style="margin-top: 1.5rem;">
                All transactions are secure, and payment status updates automatically.
            </p>
        </section>

        <section class="card">
            <h2>How to Pay School Fees</h2>
            <ol style="text-align: left; margin: 1.5rem auto; max-width: 500px; line-height: 2;">
                <li>Click <strong>"Pay Fees"</strong> in the navigation above.</li>
                <li>Fill in the student's details and fee amount.</li>
                <li>Submit to generate a unique <strong>Remita Retrieval Reference (RRR)</strong>.</li>
                <li>Use the RRR to complete payment through any Remita channel.</li>
                <li>Return here and click <strong>"View Records"</strong> to confirm payment status.</li>
            </ol>
        </section>

        <section class="card">
            <h2>Need Help?</h2>
            <p>
                For support, contact the school bursar or admin office.<br>
                <strong>Email:</strong> bursar@yourschool.edu.ng<br>
                <strong>Phone:</strong> +234 800 000 0000
            </p>
            <p style="margin-top: 1rem; font-size: 0.9rem; color: #666;">
                This portal uses industry-standard security measures including encryption, CSRF protection, and secure API communication.
            </p>
        </section>
    </main>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>&copy; 2025 Your School Name. All rights reserved. | Powered by Remita</p>
        </div>
    </footer>

    <!-- JavaScript (optional enhancements) -->
    <script src="script.js"></script>
</body>
</html>