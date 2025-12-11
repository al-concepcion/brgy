    </div> <!-- Close main-content -->

    <!-- Footer -->
    <footer class="footer py-5 bg-dark text-white">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5>Contact Information</h5>
                    <p><i class="fas fa-map-marker-alt"></i> 123 Barangay Hall Road<br>Dasmariñas, Cavite<br>Philippines 4114</p>
                    <p><i class="fas fa-phone"></i> (02) 8123-4567</p>
                    <p><i class="fas fa-envelope"></i> info@barangaysantoniño1.gov.ph</p>
                </div>
                <div class="col-md-4">
                    <h5>Office Hours</h5>
                    <p>Monday - Friday: 8:00 AM - 5:00 PM<br>
                        Saturday: 8:00 AM - 12:00 PM<br>
                        Sunday & Holidays: Closed</p>
                </div>
                <div class="col-md-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="https://www.gov.ph/" class="text-white" target="_blank">Republic of the Philippines</a></li>
                        <li><a href="https://dilg.gov.ph/" class="text-white" target="_blank">Department of Interior and Local Government</a></li>
                        <li><a href="privacy-policy.php" class="text-white">Privacy Policy</a></li>
                        <li><a href="terms-of-service.php" class="text-white">Terms of Service</a></li>
                    </ul>
                </div>
            </div>
            <hr class="mt-4">
            <div class="text-center mt-3">
                <p>&copy; <?php echo date('Y'); ?> Barangay Santo Niño 1. All rights reserved.</p>
                <small>Maka-Diyos, Maka-Tao, Makakalikasan, at Makabansa</small>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Custom JS -->
    <script src="assets/js/main.js"></script>
    <?php if (isset($additional_scripts)): ?>
        <?php echo $additional_scripts; ?>
    <?php endif; ?>
    </body>

    </html>