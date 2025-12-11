document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('idApplicationForm');
    
    if (form) {
        // Form validation
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            let isValid = true;
            const requiredFields = form.querySelectorAll('[required]');
            
            // Reset validation styles
            requiredFields.forEach(field => {
                field.classList.remove('is-invalid');
            });

            // Check required fields
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                }
            });

            // Validate date of birth
            const birthDate = document.getElementById('birthDate');
            if (birthDate && birthDate.value) {
                const today = new Date();
                const birthDateValue = new Date(birthDate.value);
                const age = today.getFullYear() - birthDateValue.getFullYear();
                
                if (age < 18) {
                    birthDate.classList.add('is-invalid');
                    isValid = false;
                    alert('You must be at least 18 years old to apply for a Barangay ID.');
                }
            }

            // If form is valid, proceed to next step
            if (isValid) {
                // Here you would typically submit the form data via AJAX
                // For now, we'll simulate moving to the next step
                window.location.href = 'apply-id-step2.php';
            }
        });

        // Real-time validation
        const inputs = form.querySelectorAll('input, select');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                if (this.hasAttribute('required') && !this.value.trim()) {
                    this.classList.add('is-invalid');
                } else {
                    this.classList.remove('is-invalid');
                }
            });
        });
    }
});