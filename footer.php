<footer class="footer">
        <div class="footer-container">
            <div class="footer-about" data-animation="animate__fadeInLeft">
                <h3>About Us</h3>
                <p>Discover the best hotels worldwide with unbeatable deals and exceptional services. Book your stay with confidence!</p>
            </div>
            <div class="footer-links" data-animation="animate__fadeInUp">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="search.php">Top Hotels</a></li>
                    <li><a href="about-us.php">Why Choose Us</a></li>
                    <li><a href="index.php#booking-process">Booking Process</a></li>
                    <li><a href="contact.php">Contact Us</a></li>
                </ul>
            </div>
            <div class="footer-contact" data-animation="animate__fadeInRight">
                <h3>Contact Us</h3>
                <p><strong>Email:</strong> support@ElysianStays.com</p>
                <p><strong>Phone:</strong> +123 456 7890</p>
                <div class="social-icons">
                    <a href="#"><i class="fa-brands fa-facebook"></i></a>
                    <a href="#"><i class="fa-brands fa-twitter"></i></a>
                    <a href="#"><i class="fa-brands fa-instagram"></i></a>
                    <a href="#"><i class="fa-brands fa-youtube"></i></a>
                </div>
            </div>
            <div class="footer-subscribe" data-animation="animate__fadeInUp">
                <h3>Stay Updated</h3>
                <form class="subscribe-form">
                    <input type="email" placeholder="Enter your email" required>
                    <button type="submit">Subscribe</button>
                </form>
            </div>
        </div>
        <div class="footer-bottom" data-animation="animate__zoomIn">
            <p>&copy; 2025 Elysian Stays. All rights reserved.</p>
        </div>
    </footer>
    <script src="js/bootstrap.bundle.min.js"></script>
    <script src="js/font-awesome-6.0.0-beta3-all.min.js"></script>
    <script src="js/jquery-3.6.4.min.js"></script>
    <script src="js/jquery.validate.min.js"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
    

    <!-- linking JS of other pages -->
    <script src="js/nav-guest.js"></script>
    <script src="js/index.js"></script>
    <script src="js/contact.js"></script>
    <script src="js/hotel.js"></script>
    <script src="js/update-profile-user.js"></script>
    <script>
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



        // about-us
        $(document).ready(function () {
        // Check if element is in viewport
        function isInViewport(element) {
            var elementTop = $(element).offset().top;
            var elementBottom = elementTop + $(element).outerHeight();
            var viewportTop = $(window).scrollTop();
            var viewportBottom = viewportTop + $(window).height();
            return elementBottom > viewportTop && elementTop < viewportBottom;
        }

        // Trigger animations on scroll
        $(window).on("scroll", function () {
            $(".fact-box").each(function () {
                if (isInViewport(this)) {
                    var animationClass = $(this).data("animation");
                    $(this).addClass("animate__animated " + animationClass).css("opacity", 1);
                }
            });
        });
    });

    </script>


</body>