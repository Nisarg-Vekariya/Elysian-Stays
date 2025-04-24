const slider = document.getElementById('range-slider');
const earningsDisplay = document.getElementById('earnings');
const nightsDisplay = document.getElementById('nights');

const pricePerNight = 4898;

slider.addEventListener('input', () => {
    const nights = slider.value;
    const totalEarnings = nights * pricePerNight;

    earningsDisplay.textContent = `₹${totalEarnings.toLocaleString('en-IN')}`;
    nightsDisplay.innerHTML = `<span style="font-weight: bolder; text-decoration: underline;">${nights} nights</span> at an estimated ₹${pricePerNight.toLocaleString('en-IN')} a night`;

});
$(document).ready(function() {
    // Check if the section is in the viewport
    function isInView(element) {
        var windowHeight = $(window).height();
        var elementOffset = $(element).offset().top;
        var elementHeight = $(element).outerHeight();
        var scrollTop = $(window).scrollTop();

        return (scrollTop + windowHeight >= elementOffset && scrollTop <= (elementOffset + elementHeight));
    }

    // Apply animation when section comes into view
    function triggerAnimationOnScroll() {
        $('.animate__animated').each(function() {
            if (isInView(this) && !$(this).hasClass('animated')) {
                $(this).addClass('animate__fadeInUp').addClass('animated');
            }
        });
    }

    // Initial trigger on page load
    triggerAnimationOnScroll();

    // Trigger animation on scroll
    $(window).on('scroll', function() {
        triggerAnimationOnScroll();
    });

    // Optionally, handle resizing of the window
    $(window).on('resize', function() {
        triggerAnimationOnScroll();
    });
});

$(document).ready(function() {
    function isInViewport(element) {
        var elementTop = $(element).offset().top;
        var elementBottom = elementTop + $(element).outerHeight();
        var viewportTop = $(window).scrollTop();
        var viewportBottom = viewportTop + $(window).height();
        return elementBottom > viewportTop && elementTop < viewportBottom;
    }

    $(window).on("scroll", function() {
        $(".footer-container > div, .footer-bottom").each(function() {
            if (isInViewport(this)) {
                var animationClass = $(this).data("animation");
                $(this).addClass("animate__animated " + animationClass).css("opacity", 1);
            }
        });
    });
});