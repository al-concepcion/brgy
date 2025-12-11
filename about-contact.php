<?php
require_once 'includes/config.php';
$current_page = 'about';
$page_title = 'About & Contact';

$contact_success = false;
$contact_errors = [];

// Handle contact form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['contact_submit'])) {
    $full_name = sanitize_input($_POST['fullName'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $subject = sanitize_input($_POST['subject'] ?? '');
    $message = sanitize_input($_POST['message'] ?? '');
    
    // Validation
    if (empty($full_name)) {
        $contact_errors[] = 'Full name is required';
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $contact_errors[] = 'Valid email address is required';
    }
    if (empty($subject)) {
        $contact_errors[] = 'Subject is required';
    }
    if (empty($message)) {
        $contact_errors[] = 'Message is required';
    }
    
    // If no errors, save to database
    if (empty($contact_errors)) {
        try {
            $stmt = $conn->prepare("INSERT INTO contact_messages (full_name, email, subject, message) VALUES (?, ?, ?, ?)");
            $stmt->execute([$full_name, $email, $subject, $message]);
            $contact_success = true;
            
            // Optional: Send email notification to admin
            // mail('admin@barangaysanmiguel.gov.ph', 'New Contact Message', $message);
            
        } catch(PDOException $e) {
            $contact_errors[] = 'Unable to send message. Please try again.';
        }
    }
}

include 'includes/header.php';
?>

    <style>
        .official-card {
            background: #fff;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.08);
            height: 100%;
            position: relative;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease, background 0.3s ease, color 0.3s ease;
            cursor: pointer;
        }

        .official-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(8, 71, 48, 0.25);
            background: linear-gradient(155deg, #0b5d3c, #12754a);
            color: #f1fff7 !important;
        }

        .official-card:hover h4,
        .official-card:hover p {
            color: #f1fff7 !important;
        }

        .official-card .official-image img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border: 5px solid #fff;
            box-shadow: 0 6px 18px rgba(0,0,0,0.15);
            transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
        }

        .official-card:hover .official-image img {
            transform: scale(1.08);
            box-shadow: 0 16px 32px rgba(8, 71, 48, 0.35);
            border-color: rgba(241, 255, 247, 0.95);
        }
    </style>

    <!-- About Section -->
    <section class="about-section py-5 bg-light">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <h1>About Barangay Santo Niño 1</h1>
                    <p class="lead mb-5">Serving our community with dedication, integrity, and excellence. Maka-Diyos, Maka-Tao, Makakalikasan, at Makabansa.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Officials Section -->
    <section class="officials-section py-5">
        <div class="container">
            <h2 class="text-center mb-4">Barangay Officials</h2>
            <p class="text-center text-muted mb-5">Meet the dedicated leaders serving our barangay</p>

            <div class="row g-4">
                <?php
                $officials = [
                    ['name' => 'Hon. Ivan Adal', 'position' => 'Punong Barangay', 'img' => 'cpt.jpg'],
                    ['name' => 'Hon. Yasser Alapag', 'position' => 'Barangay Kagawad', 'img' => '1stkag.jpg'],
                    ['name' => 'Hon. Dave Abellanosa', 'position' => 'Barangay Kagawad', 'img' => 'd8e8af2f-f6f8-418a-b043-05145a3bb736.jpg'],
                    ['name' => 'Hon. Hadden Abogado', 'position' => 'Barangay Kagawad', 'img' => 'fe848f6f-a260-4a93-9122-44347dc78cff.jpg'],
                    ['name' => 'Hon. Minsey Alfaro  ', 'position' => 'Barangay Kagawad', 'img' => '6b63d933-36b0-44a4-b542-bdd1a7e48faa.jpg'],
                ];
                
                foreach ($officials as $official):
                ?>
                <div class="col-lg-3 col-md-6">
                    <div class="official-card text-center">
                        <div class="official-image mb-3">
                            <img src="assets/images/<?php echo $official['img']; ?>" alt="<?php echo $official['name']; ?>" class="rounded-circle mx-auto d-block">
                        </div>
                        <h4><?php echo $official['name']; ?></h4>
                        <p class="text-muted"><?php echo $official['position']; ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="contact-section py-5 bg-light" id="contact-form">
        <div class="container">
            <div class="row">
                <!-- Contact Information -->
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <h2>Contact Us</h2>
                    <p class="text-muted mb-4">Get in touch with us for inquiries, assistance, or feedback</p>

                    <div class="contact-info">
                        <h4>Barangay Hall Information</h4>
                        <div class="info-item mb-3">
                            <i class="fas fa-map-marker-alt text-primary me-2"></i>
                            <div>
                                <strong>Address</strong><br>
                                123 Barangay Hall Road<br>
                               Dasmarinas City, Cavite<br>
                                Philippines 4114
                            </div>
                        </div>
                        <div class="info-item mb-3">
                            <i class="fas fa-phone text-primary me-2"></i>
                            <div>
                                <strong>Phone Numbers</strong><br>
                                Landline: (02) 8123-4567<br>
                                Mobile: +63 917 123 4567
                            </div>
                        </div>
                        <div class="info-item mb-3">
                            <i class="fas fa-envelope text-primary me-2"></i>
                            <div>
                                <strong>Email</strong><br>
                                info@barangaysantoniño1.gov.ph
                            </div>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-clock text-primary me-2"></i>
                            <div>
                                <strong>Office Hours</strong><br>
                                Monday - Friday: 8:00 AM - 5:00 PM<br>
                                Saturday: 8:00 AM - 12:00 PM<br>
                                Sunday & Holidays: Closed
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Form -->
                <div class="col-lg-8">
                    <div class="contact-form bg-white p-4 rounded shadow-sm">
                        <h3>Send us a Message</h3>
                        <p class="text-muted mb-4">Fill out the form below and we'll get back to you as soon as possible</p>

                        <?php if ($contact_success): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i> Thank you for your message! We will get back to you soon.
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($contact_errors)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($contact_errors as $error): ?>
                                        <li><?php echo $error; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="#contact-form" class="needs-validation" novalidate>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Full Name *</label>
                                    <input type="text" class="form-control" name="fullName" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email Address *</label>
                                    <input type="email" class="form-control" name="email" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Subject *</label>
                                    <input type="text" class="form-control" name="subject" placeholder="What is this about?" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Message *</label>
                                    <textarea class="form-control" name="message" rows="5" placeholder="Your message here..." required></textarea>
                                </div>
                                <div class="col-12">
                                    <button type="submit" name="contact_submit" class="btn btn-primary">
                                        <i class="fas fa-paper-plane me-2"></i> Send Message
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?php include 'includes/footer.php'; ?>
