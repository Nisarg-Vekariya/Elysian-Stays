$(document).ready(() => {
  // Initialize validation rules
  $("#profileForm").validate({
      rules: {
          fullName: {
              required: true,
              minlength: 2,
              maxlength: 50
          },
          username: {
              required: true,
              minlength: 3,
              maxlength: 30
          },
          country_code: {
              required: true
          },
          phone: {
              required: true,
              digits: true,
              minlength: 10,
              maxlength: 10,
          },
          country: {
              required: true
          },
          city: {
              required: true,
              minlength: 2
          },
          password: {
              minlength: 6,
              maxlength: 20,
              pattern: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/,
          },
          current_password: {
              required: function() {
                  // Require current password only if new password is provided
                  return $("#password").val().length > 0;
              },
          },
      },
      messages: {
          fullName: {
              required: "Please enter your full name",
              minlength: "Name must be at least 2 characters",
              maxlength: "Name cannot exceed 50 characters"
          },
          username: {
              required: "Please enter your username",
              minlength: "Username must be at least 3 characters",
              maxlength: "Username cannot exceed 30 characters"
          },
          country_code: {
              required: "Please select a country code"
          },
          phone: {
              required: "Please enter your phone number",
              digits: "Please enter only digits",
              minlength: "Phone number must be at least 10 digits",
              maxlength: "Phone number must be 10 digits",
          },
          country: {
              required: "Please select your country"
          },
          city: {
              required: "Please enter your city"
          },
          password: {
              minlength: "Password should be at least 6 characters long",
              maxlength: "Password cannot exceed 20 characters",
              pattern: "Password should contain at least one uppercase letter, one lowercase letter, and one number",
          },
          current_password: {
              required: "Current password is required to change your password",
          },
      },
      errorElement: "div",
      errorPlacement: function(error, element) {
          error.addClass("invalid-feedback");
          error.insertAfter(element);
      },
      highlight: function(element, errorClass, validClass) {
          $(element).addClass("is-invalid").removeClass("is-valid");
      },
      unhighlight: function(element, errorClass, validClass) {
          $(element).removeClass("is-invalid").addClass("is-valid");
      },
      submitHandler: function(form) {
          // Show loading state on button
          const submitBtn = $(form).find('button[type="submit"]');
          const originalText = submitBtn.html();
          submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Saving...');
          submitBtn.prop('disabled', true);
          
          // Submit the form
          form.submit();
      }
  });

  // File input validation
  $("#fileInput").on("change", function() {
      const file = this.files[0];
      const fileType = file.type;
      const validImageTypes = ['image/jpeg', 'image/png', 'image/gif'];
      const maxSize = 2 * 1024 * 1024; // 2MB
      
      if (!validImageTypes.includes(fileType)) {
          alert("Only JPG, PNG and GIF files are allowed!");
          this.value = '';
          return false;
      }
      
      if (file.size > maxSize) {
          alert("File size must be less than 2MB!");
          this.value = '';
          return false;
      }
  });
});
// Import jQuery
// $(document).ready(() => {
//     $("#profileForm").validate({
//       rules: {
//         fullName: "required",
//         email: {
//           required: true,
//           email: true,
//         },
//         username: "required",
//         phone: {
//           required: true,
//           digits: true,
//           minlength: 10,
//           maxlength: 10,
//         },
//         country: "required",
//         city: "required",
//         password: {
//           required: true,
//           minlength: 6,
//           maxlength: 20,
//           pattern: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/,
//         },
//       },
//       messages: {
//         fullName: "Please enter your full name",
//         email: "Please enter a valid email address",
//         username: "Please enter your username",
//         phone: "Please enter a valid 10-digit phone number",
//         country: "Please enter your country",
//         city: "Please enter your city",
//         password: {
//           required: "Password is required",
//           minlength: "Password should be at least 8 characters long",
//           maxlength: "Password cannot exceed more than 20 characters",
//           pattern: "Password should contain one uppercase letter, one lowercase letter, and one number",
//         },
//       },
//       errorElement: "div",
//       errorPlacement: (error, element) => {
//         error.addClass("invalid-feedback")
//         error.insertAfter(element)
//       },
//       highlight: (element, errorClass, validClass) => {
//         $(element).addClass("is-invalid").removeClass("is-valid")
//       },
//       unhighlight: (element, errorClass, validClass) => {
//         $(element).removeClass("is-invalid").addClass("is-valid")
//       },
//     })
//   })
  
//   document.getElementById("fileInput").addEventListener("change", (event) => {
//     const file = event.target.files[0]
//     if (file) {
//       const reader = new FileReader()
//       reader.onload = (e) => {
//         document.getElementById("profileImage").src = e.target.result
//       }
//       reader.readAsDataURL(file)
//     }
//   })
  
  