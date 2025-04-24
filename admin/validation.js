$(document).ready(function () {
    function validateField(input) {
        let field = $(input);
        let value = field.val().trim();
        let errorSpan = $("#" + field.attr("name") + "Error");
        let fieldType = field.data("validation") || "";
        let minLength = field.data("min") || 0;
        let maxLength = field.data("max") || 9999;
        let errorMessage = "";

        if (fieldType.includes("required") && value === "") {
            errorMessage = "This field is required.";
        }
        else if (fieldType.includes("email") && !/^\S+@\S+\.\S+$/.test(value)) {
            errorMessage = "Enter a valid email.";
        }
        else if (fieldType.includes("strongPassword") && !/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/.test(value)) {
            errorMessage = "Password must be at least 8 characters, including uppercase, lowercase, number, and a special character.";
        }
        else if (fieldType.includes("confirmPassword")) {
            let password = $("#password").val().trim();
            if (value !== password) {
                errorMessage = "Passwords do not match.";
            }
        }
        else if (fieldType.includes("terms") && !field.is(":checked")) {
            errorMessage = "You must agree to the terms and conditions.";
        }
        else if (fieldType.includes("alphabetical") && !/^[a-zA-Z]+$/.test(value)) {
            errorMessage = "This field must contain only letters.";
        }
        else if (fieldType.includes("numeric") && !/^\d+$/.test(value)) {
            errorMessage = "This field must be numeric.";
        }
        else if (fieldType.includes("phone") && !/^\+?[0-9]{7,15}$/.test(value)) {
            errorMessage = "Enter a valid phone number.";
        }
        else if (fieldType.includes("username") && !/^(?!.*\.\.)(?!.*\_\_)[a-zA-Z0-9._]{3,20}$/.test(value)) {
            errorMessage = "Username can only contain letters, numbers, underscores, and dots (no consecutive dots/underscores).";
        }
        else if (fieldType.includes("address") && !/^[a-zA-Z0-9\s,.'-]{3,100}$/.test(value)) {
            errorMessage = "Enter a valid address.";
        }
        else if (fieldType.includes("age")) {
            let age = parseInt(value, 10);
            if (isNaN(age) || age < 18 || age > 100) {
                errorMessage = "Age must be between 18 and 100.";
            }
        }
        else if (fieldType.includes("min") && value.length < minLength) {
            errorMessage = `This field must be at least ${minLength} characters long.`;
        }
        else if (fieldType.includes("max") && value.length > maxLength) {
            errorMessage = `This field must be no more than ${maxLength} characters long.`;
        }
        else if (fieldType.includes("file")) {
            let file = field[0].files[0];
            if (file) {
                let allowedExtensions = /\.(jpg|jpeg|png)$/i;
                if (!allowedExtensions.test(file.name)) {
                    errorMessage = "Only .jpg, .jpeg, and .png files are allowed.";
                } else if (file.size > 200000) {
                    errorMessage = "File size must be less than 200KB.";
                }
            }
        }

        if (errorMessage) {
            errorSpan.text(errorMessage).show();
            field.addClass("is-invalid").removeClass("is-valid");
        } else {
            errorSpan.text("").hide();
            field.addClass("is-valid").removeClass("is-invalid");
        }
    }

    $(document).on("input change", "input, textarea", function () {
        validateField(this);
    });

    $("form").on("submit", function (e) {
        let isValid = true;
        $(this).find("input, textarea").each(function () {
            validateField(this);
            if ($("#" + $(this).attr("name") + "Error").text() !== "") {
                isValid = false;
            }
        });

        if (!isValid) {
            e.preventDefault();
        }
    });
});


