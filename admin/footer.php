<script src="../js/bootstrap.bundle.min.js"></script>
<script src="../js/font-awesome-6.0.0-beta3-all.min.js"></script>
<script src="../js/jquery-3.6.4.min.js"></script>
<script src="../js/jquery.validate.min.js"></script>
<script src="validation.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const toggleNavbar = (toggleId, navId, bodyId, headerId) => {
                const toggle = document.getElementById(toggleId),
                    nav = document.getElementById(navId),
                    body = document.body,
                    header = document.getElementById(headerId);

                if (toggle && nav && body && header) {
                    toggle.addEventListener("click", () => {
                        nav.classList.toggle("sidebar-show");
                        body.classList.toggle("sidebar-body-pd");
                        header.classList.toggle("sidebar-body-pd");
                        toggle.classList.toggle("active");
                    });
                }
            };

            toggleNavbar("header-toggle", "nav-bar", "body", "header");

            const links = document.querySelectorAll(".sidebar-nav-link");
            links.forEach(link => link.addEventListener("click", function () {
                links.forEach(l => l.classList.remove("sidebar-active"));
                this.classList.add("sidebar-active");
            }));

            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });

        // Add animation on scroll
        window.addEventListener('scroll', () => {
            document.querySelectorAll('.animate__animated').forEach((elem) => {
                const elemTop = elem.getBoundingClientRect().top;
                if (elemTop < window.innerHeight - 100) {
                    elem.classList.add('animate__fadeIn');
                }
            });
        });


        // manage-hotels
        document.addEventListener('DOMContentLoaded', function() {
            const addHotelForm = document.getElementById('addHotelForm');
            addHotelForm.addEventListener('submit', function(e) {
                e.preventDefault();
                // Here you would typically send the form data to the server
                // For this example, we'll just log it to the console
                console.log('Form submitted:', {
                    name: document.getElementById('hotelName').value,
                    location: document.getElementById('hotelLocation').value,
                    price: document.getElementById('hotelPrice').value,
                    rooms: document.getElementById('hotelRooms').value,
                    status: document.getElementById('hotelStatus').value
                });
                // Close the modal
                bootstrap.Modal.getInstance(document.getElementById('addHotelModal')).hide();
                // Reset the form
                addHotelForm.reset();
            });
        });


        //manage-bookings
        document.addEventListener('DOMContentLoaded', function() {
            const addBookingForm = document.getElementById('addBookingForm');
            if (addBookingForm) {
                addBookingForm.addEventListener('submit', function(e) {
                    // Validate form data
                    const customerName = document.getElementById('customerName').value;
                    const roomType = document.getElementById('roomType').value;
                    const checkInDate = document.getElementById('checkInDate').value;
                    const checkOutDate = document.getElementById('checkOutDate').value;
                    const status = document.getElementById('status').value;
                    
                    // Check if all required fields are filled
                    if (!customerName || !roomType || !checkInDate || !checkOutDate || !status) {
                        return;
                    }
                });
            } else {
                console.error('Add booking form not found');
            }
        });


        // platform-settings
    document.addEventListener("DOMContentLoaded", function () {
        let actionType = ""; // Stores the action type (Reset/Backup)

        // Select all confirmation buttons
        const buttons = document.querySelectorAll(".confirm-btn");

        // Select modal and confirmation button
        const confirmModal = new bootstrap.Modal(document.getElementById("confirmModal"));
        const confirmActionBtn = document.getElementById("confirmActionBtn");

        // Attach click event to all buttons with class "confirm-btn"
        buttons.forEach(button => {
            button.addEventListener("click", function () {
                actionType = this.textContent.trim(); // Get button text (Reset/Backup)
                document.querySelector("#confirmModal .modal-body").textContent = 
                    `Are you sure you want to ${actionType.toLowerCase()}?`;
                confirmModal.show(); // Show Bootstrap modal
            });
        });

        // Handle Confirm Button Click
        confirmActionBtn.addEventListener("click", function () {
            alert(`${actionType} confirmed.`); // Replace with actual action logic
            confirmModal.hide(); // Close modal
        });
    });


        // revenue-analytics
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.revenue-analytics .card');
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animate__fadeIn');
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.1 });

            cards.forEach(card => {
                observer.observe(card);
            });
        });

        <?php
        // Only include loading screen JavaScript on admin/index.php page
        $currentPage = basename($_SERVER['SCRIPT_NAME']);
        $isIndexPage = ($currentPage === 'index.php');
        
        if ($isIndexPage):
        ?>
        // Fun Loading Screen Handler with jokes - Only for Dashboard
        (function() {
            const loadingOverlay = document.getElementById('loadingOverlay');
            const loadingJoke = document.getElementById('loadingJoke');
            if (!loadingOverlay) return;

            // Hotel jokes collection
            const jokes = [
                "Why did the hotel manager go to prison? He got too many bookings!",
                "What's a hotel's favorite kind of music? Suite melodies!",
                "Did you hear about the new hotel made of cheese? It was brie-lliant, but the beds were too grate!",
                "Why don't hotels use soft pillows? Because they're not down with that!",
                "Hotel policy: If you take a shower, please leave it where you found it.",
                "I stayed at a 5-star hotel once. I stole one and now I'm in jail.",
                "Why was the hotel bed always exhausted? Because it was always made up!",
                "What do you call a hotel manager with no arms or legs? A room service runner!",
                "What's a hotel's least favorite guest? The one who checks out the competition!",
                "Why did the guest bring a ladder to the hotel? They heard the rates were climbing!"
            ];

            // Display random joke
            loadingJoke.textContent = jokes[Math.floor(Math.random() * jokes.length)];

            // Function to hide loading screen
            const hideLoadingScreen = () => {
                loadingOverlay.style.opacity = '0';
                setTimeout(() => {
                    loadingOverlay.style.display = 'none';
                }, 300);
            };

            // Hide loading screen when page is fully loaded
            window.addEventListener('load', function() {
                setTimeout(hideLoadingScreen, 2000); // Extended for joke reading time
            });

            // Fail-safe: Force hide loading screen after 5 seconds
            // This ensures the loading screen doesn't get stuck
            setTimeout(hideLoadingScreen, 5000);

            // Also hide loading screen on DOMContentLoaded (backup)
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(hideLoadingScreen, 2000); // Extended for joke reading time
            });
        })();
        <?php endif; ?>

//         $(document).ready(function() {
//     $("#header-container").load("header.php", function(response, status, xhr) {
//         if (status == "success") {
//             console.log("✅ Header loaded successfully via AJAX.");
//         } else {
//             console.log("❌ Error loading header:", xhr.status, xhr.statusText);
//         }
//     });
// });


    

    </script>

</body>
</html>