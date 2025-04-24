//step 1: get DOM
let nextDom = document.getElementById('next');
let prevDom = document.getElementById('prev');

let carouselDom = document.querySelector('.carousel');
let SliderDom = carouselDom.querySelector('.carousel .list');
let thumbnailBorderDom = document.querySelector('.carousel .thumbnail');
let thumbnailItemsDom = thumbnailBorderDom.querySelectorAll('.item');
let timeDom = document.querySelector('.carousel .time');

thumbnailBorderDom.appendChild(thumbnailItemsDom[0]);
let timeRunning = 3000;
let timeAutoNext = 7000;

nextDom.onclick = function () {
    showSlider('next');
}

prevDom.onclick = function () {
    showSlider('prev');
}
let runTimeOut;
let runNextAuto = setTimeout(() => {
    next.click();
}, timeAutoNext)
function showSlider(type) {
    let SliderItemsDom = SliderDom.querySelectorAll('.carousel .list .item');
    let thumbnailItemsDom = document.querySelectorAll('.carousel .thumbnail .item');

    if (type === 'next') {
        SliderDom.appendChild(SliderItemsDom[0]);
        thumbnailBorderDom.appendChild(thumbnailItemsDom[0]);
        carouselDom.classList.add('next');
    } else {
        SliderDom.prepend(SliderItemsDom[SliderItemsDom.length - 1]);
        thumbnailBorderDom.prepend(thumbnailItemsDom[thumbnailItemsDom.length - 1]);
        carouselDom.classList.add('prev');
    }
    clearTimeout(runTimeOut);
    runTimeOut = setTimeout(() => {
        carouselDom.classList.remove('next');
        carouselDom.classList.remove('prev');
    }, timeRunning);

    clearTimeout(runNextAuto);
    runNextAuto = setTimeout(() => {
        next.click();
    }, timeAutoNext)
}



 
// Slider Functionality offers
const textSlides = document.querySelectorAll(".carousel-slide");
const textDots = document.querySelectorAll(".dot");
const textPrevBtn = document.querySelector(".text-carousel .prev");
const textNextBtn = document.querySelector(".text-carousel .next");

let textCurrentIndex = 0;

// Function to show the current slide
function showTextSlide(index) {
    textSlides.forEach((slide, i) => {
        slide.classList.remove("active");
        textDots[i].classList.remove("active");
        if (i === index) {
            slide.classList.add("active");
            textDots[i].classList.add("active");
        }
    });
}

// Function to move to the next slide
function nextTextSlide() {
    textCurrentIndex = (textCurrentIndex + 1) % textSlides.length;
    showTextSlide(textCurrentIndex);
}

// Function to move to the previous slide
function prevTextSlide() {
    textCurrentIndex = (textCurrentIndex - 1 + textSlides.length) % textSlides.length;
    showTextSlide(textCurrentIndex);
}

// Event listeners for navigation buttons
textPrevBtn.addEventListener("click", prevTextSlide);
textNextBtn.addEventListener("click", nextTextSlide);

// Auto-slide functionality
setInterval(nextTextSlide, 5000); // Auto slide every 5 seconds

// Initialize Bootstrap Toast
const copyToast = document.getElementById('copyToast');
const toast = new bootstrap.Toast(copyToast, {
    autohide: true, // Automatically hide the toast
    delay: 3000     // Hide after 3 seconds
});

// Function to copy coupon code and show toast
function copyCoupon(couponId) {
    // Get the coupon code text
    var couponText = document.getElementById(couponId).textContent;

    // Use the Clipboard API to copy the text
    navigator.clipboard.writeText(couponText).then(function () {
        // Update the toast message
        const toastBody = document.querySelector('.toast-body');
        toastBody.textContent = `Coupon Code Copied: ${couponText}`;

        // Show the toast
        toast.show();
    }).catch(function (error) {
        console.error('Failed to copy: ' + error);
    });
}




document.addEventListener("DOMContentLoaded", function () {
    // Function to animate elements on scroll
    const animateOnScroll = (selector, animationClass, delay = 0) => {
        const observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    setTimeout(() => {
                        entry.target.classList.add(animationClass, "animate__animated");
                        observer.unobserve(entry.target);  // Stop observing after animation
                    }, delay);
                }
            });
        });

        document.querySelectorAll(selector).forEach(element => {
            observer.observe(element);
        });
    };

    // Sections and corresponding animations
    animateOnScroll('.booking-section', 'animate__fadeInUp');
    animateOnScroll('.booking-process .step', 'animate__fadeInLeft', 200);
    animateOnScroll('.booking-process .detail', 'animate__fadeInRight', 400);
    animateOnScroll('.why-choose-us .feature-card', 'animate__fadeInUp', 200);
    animateOnScroll('.why-choose-us .section-title', 'animate__zoomIn');
    animateOnScroll('.why-choose-us .divider', 'animate__fadeIn');
});

function showDetail(step) {
    // Hide all details
    document.querySelectorAll('.detail').forEach(detail => {
        detail.style.display = 'none';
    });

    // Remove active class from all steps
    document.querySelectorAll('.step').forEach(stepEl => {
        stepEl.classList.remove('active-step');
    });

    // Show selected detail
    document.getElementById(`detail-${step}`).style.display = 'block';

    // Highlight active step
    document.querySelector(`.step:nth-child(${step})`).classList.add('active-step');
}

// Ensure Step 1 is selected by default when the page loads
document.addEventListener("DOMContentLoaded", () => {
    showDetail(1); // Select Step 1
});



function showDetail(step) {
    // Remove active class from all details
    document.querySelectorAll('.detail').forEach(detail => {
        detail.classList.remove('animate__fadeInUp');
        detail.style.display = 'none';
    });

    // Show selected step with animation
    const detail = document.getElementById('detail-' + step);
    detail.style.display = 'block';
    detail.classList.add('animate__fadeInUp');
}
