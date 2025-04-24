let lastScrollTop = 0;
        const navbar = document.querySelector('.navbar');

        // Add animations on load
        document.addEventListener('DOMContentLoaded', () => {
            const animatedElements = document.querySelectorAll('.animate__animated');
            animatedElements.forEach((el, index) => {
                setTimeout(() => {
                    el.classList.add('animate__fadeInDown');
                }, index * 150);
            });
        });

        window.addEventListener('scroll', () => {
            const scrollTop = window.scrollY || document.documentElement.scrollTop;

            // Toggle scrolled state
            if (scrollTop > 50) {
                navbar.classList.add('scrolled');
                navbar.classList.add('animate__fadeInDown');
            } else {
                navbar.classList.remove('scrolled');
            }

            // Hide/show navbar on scroll
            if (scrollTop > lastScrollTop && scrollTop > 100) {
                navbar.classList.add('hidden');
                navbar.classList.remove('animate__fadeInDown');
                navbar.classList.add('animate__fadeOutUp');
            } else {
                navbar.classList.remove('hidden');
                navbar.classList.remove('animate__fadeOutUp');
                navbar.classList.add('animate__fadeInDown');
            }

            lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
        });