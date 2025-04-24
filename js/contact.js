// IntersectionObserver for form animation (using the data-animation attribute)
const observer = new IntersectionObserver((entries, observer) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            const element = entry.target;
            const animationClass = element.dataset.animation; // Get animation class from data attribute
            element.classList.add("animate__animated", animationClass);
            element.classList.remove("animate-on-scroll"); // Once animated, remove the trigger class
            observer.unobserve(element); // Stop observing the element once it's animated
        }
    });
}, {
    threshold: 0.5
}); // Trigger when 50% of the element is in the viewport

// Observe all elements with class `animate-on-scroll`
document.querySelectorAll('.animate-on-scroll').forEach(element => {
    observer.observe(element);
});

$(document).ready(function() {
    // Function to check if an element is in the viewport
    function isElementInViewport(el) {
        var rect = el.getBoundingClientRect();
        return rect.top >= 0 && rect.left >= 0 && rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) && rect.right <= (window.innerWidth || document.documentElement.clientWidth);
    }

    // Scroll event to trigger animations for form section and other elements
    $(window).on('scroll', function() {
        // For each contact card, trigger animation when it comes into the viewport
        $('#contact-cards .contact-card').each(function() {
            if (isElementInViewport(this)) {
                $(this).addClass('animate__animated animate__fadeInUp');
            }
        });

        // Trigger animation for the form when it comes into the viewport
        $('.container.py-5').each(function() {
            if (isElementInViewport(this)) {
                $(this).find('.animate-on-scroll').each(function() {
                    const animationClass = $(this).data('animation');
                    $(this).addClass('animate__animated ' + animationClass);
                });
            }
        });
    });
});

// Example starter JavaScript for Bootstrap validation 
(function() {
    'use strict'

    // Fetch all the forms we want to apply custom Bootstrap validation styles to
    var forms = document.querySelectorAll('.needs-validation')

    // Loop over them and prevent submission
    Array.prototype.slice.call(forms)
        .forEach(function(form) {
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()

                    // Add animation to invalid feedback messages
                    form.querySelectorAll('.invalid-feedback').forEach(function(el) {
                        el.classList.add('animate__animated', 'animate__fadeIn');
                        setTimeout(() => {
                            el.classList.remove('animate__fadeIn');
                        }, 1000); // Remove animation after 1 second to allow re-trigger
                    });
                }

                form.classList.add('was-validated')
            }, false)
        })
})()