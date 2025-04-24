// let currentIndex = 0; // Track the current position
// const visibleSlides = 3; // Number of visible reviews at a time
// const carousel = document.querySelector('.carousel');
// const slides = document.querySelectorAll('.review-card');
// const totalSlides = slides.length;

// // Function to move the carousel
// function moveSlide(step) {
//   // Temporarily update index for logic
//   let newIndex = currentIndex + step;

//   // Loop to the beginning if clicking "Next" at the end
//   if (newIndex > visibleSlides) {
//     newIndex = 0;
//   }
//   // Loop to the end if clicking "Prev" at the beginning
//   else if (newIndex < 0) {
//     newIndex = visibleSlides;
//   }

//   // Update the global index and apply translation
//   currentIndex = newIndex;
//   const translateX = -(currentIndex * (100 / visibleSlides));
//   carousel.style.transform = `translateX(${translateX}%)`;
// }

// Attach event listeners to buttons
document.querySelector('.prev').addEventListener('click', () => moveSlide(-1));
document.querySelector('.next').addEventListener('click', () => moveSlide(1));

// Updated JavaScript
document.addEventListener('DOMContentLoaded', () => {
  const slider = document.querySelector('.reviews-section .reviews-slider');
  const slides = slider.querySelectorAll('.slide');
  const prevBtn = slider.querySelector('.slider-prev');
  const nextBtn = slider.querySelector('.slider-next');
  const dotsContainer = slider.querySelector('.slider-dots');
  let currentSlide = 0;
  let isAnimating = false;

  // Create dots
  slides.forEach((_, index) => {
    const dot = document.createElement('span');
    dot.classList.add('dot');
    if (index === 0) dot.classList.add('active');
    dot.addEventListener('click', () => {
      if (!isAnimating) goToSlide(index);
    });
    dotsContainer.appendChild(dot);
  });

  const dots = slider.querySelectorAll('.dot');

  function updateSlides() {
    isAnimating = true;
    
    slides.forEach((slide, index) => {
      slide.classList.remove('active');
      if (index === currentSlide) {
        slide.classList.add('active');
      }
    });
    
    dots.forEach((dot, index) => {
      dot.classList.toggle('active', index === currentSlide);
    });

    setTimeout(() => {
      isAnimating = false;
    }, 300); // Match CSS transition duration
  }

  function goToSlide(index) {
    if (isAnimating || index === currentSlide) return;
    
    currentSlide = index;
    if (currentSlide >= slides.length) currentSlide = 0;
    if (currentSlide < 0) currentSlide = slides.length - 1;
    updateSlides();
  }

  prevBtn.addEventListener('click', () => {
    if (!isAnimating) goToSlide(currentSlide - 1);
  });

  nextBtn.addEventListener('click', () => {
    if (!isAnimating) goToSlide(currentSlide + 1);
  });

  // Auto-slide every 5 seconds
  let autoSlide = setInterval(() => {
    goToSlide(currentSlide + 1);
  }, 5000);

  // Pause auto-slide on hover
  slider.addEventListener('mouseenter', () => {
    clearInterval(autoSlide);
  });

  slider.addEventListener('mouseleave', () => {
    autoSlide = setInterval(() => {
      goToSlide(currentSlide + 1);
    }, 5000);
  });
});