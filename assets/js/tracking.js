document.addEventListener('DOMContentLoaded', function() {
    const trackingForm = document.getElementById('trackingForm');
    const trackingResult = document.getElementById('trackingResult');

    if (trackingForm) {
        trackingForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Show tracking result after submission
            trackingResult.style.display = 'block';
            
            // Smooth scroll to result
            trackingResult.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        });
    }

    // Sample reference number click handling
    const sampleRefs = document.querySelectorAll('.sample-refs .badge');
    sampleRefs.forEach(ref => {
        ref.style.cursor = 'pointer';
        ref.addEventListener('click', function() {
            const input = document.querySelector('#trackingForm input[type="text"]');
            input.value = this.textContent.trim();
        });
    });
});

// Contact form validation
const contactForm = document.getElementById('contactForm');
if (contactForm) {
    contactForm.addEventListener('submit', function(e) {
        e.preventDefault();
        if (!this.checkValidity()) {
            e.stopPropagation();
        } else {
            // Handle form submission
            alert('Thank you for your message. We will get back to you soon!');
            this.reset();
        }
        this.classList.add('was-validated');
    });
}

// Certificate selection handling
const certificateCards = document.querySelectorAll('.certificate-card');
if (certificateCards) {
    certificateCards.forEach(card => {
        card.addEventListener('click', function() {
            const radio = this.querySelector('input[type="radio"]');
            radio.checked = true;
            
            // Remove active class from all cards
            certificateCards.forEach(c => c.classList.remove('active'));
            
            // Add active class to selected card
            this.classList.add('active');
        });
    });
}