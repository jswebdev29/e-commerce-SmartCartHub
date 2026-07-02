<?php
session_start();
require_once __DIR__ . '/connectfinity.php';

$msg = "";

if (isset($_POST['register'])) {

    $name = trim($_POST['name']);
    $dob = trim($_POST['dob']);
    $phone = trim($_POST['phone']);

    $state = trim($_POST['state']);
    $district = trim($_POST['district']);
    $tehsil = trim($_POST['tehsil']);
    $address = trim($_POST['address']);

    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm = trim($_POST['confirm_password']);

    // =========================
    // PROFILE IMAGE
    // =========================

    $profile_pic = "";

    if (!empty($_POST['cropped_image'])) {

        $folder = "uploads/profile/";

        if (!is_dir($folder)) {
            mkdir($folder, 0777, true);
        }

        $image_parts = explode(";base64,", $_POST['cropped_image']);

        if (count($image_parts) > 1) {

            $image_base64 = base64_decode($image_parts[1]);

            $file_name = time() . ".png";

            $profile_pic = $file_name;

            file_put_contents($folder . $file_name, $image_base64);
        }
    }

    // VALIDATION

    if (
        $name == "" ||
        $dob == "" ||
        $phone == "" ||
        $state == "" ||
        $district == "" ||
        $tehsil == "" ||
        $address == "" ||
        $email == "" ||
        $password == "" ||
        $confirm == ""
    ) {

        $msg = "⚠️ All fields are required.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $msg = "⚠️ Invalid email format.";

    } elseif (!preg_match("/^[0-9]{10}$/", $phone)) {

        $msg = "⚠️ Phone number must be 10 digits.";

    } elseif ($password !== $confirm) {

        $msg = "⚠️ Passwords do not match.";

    } else {

        $check = mysqli_query(
            $conn,
            "SELECT * FROM customers WHERE email='$email'"
        );

        if (mysqli_num_rows($check) > 0) {

            $msg = "⚠️ Email already registered.";

        } else {

            $hashed = password_hash($password, PASSWORD_BCRYPT);

            $insert = "
                INSERT INTO customers
                (
                    profile_pic,
                    name,
                    dob,
                    phone,
                    state,
                    district,
                    tehsil,
                    address,
                    email,
                    password
                )
                VALUES
                (
                    '$profile_pic',
                    '$name',
                    '$dob',
                    '$phone',
                    '$state',
                    '$district',
                    '$tehsil',
                    '$address',
                    '$email',
                    '$hashed'
                )
            ";

            if (mysqli_query($conn, $insert)) {

                $_SESSION['customer_email'] = $email;
                $_SESSION['customer_name'] = $name;

                header("Location: index.php");
                exit;

            } else {

                $msg = "❌ Registration failed.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Customer Register</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" />

    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

    <style>
        .container {
            padding-bottom: 50px;
        }

        .preview-box {
            width: 300px;
            margin: auto;
        }

        #preview {
            max-width: 100%;
            display: none;
        }

        .cropped-preview {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #0d6efd;
            margin-top: 15px;
        }

        .cropper-view-box,
        .cropper-face {
            border-radius: 50%;
        }

        @media (max-width:768px) {

            .container {
                max-width: 100% !important;
                padding-left: 30px;
                padding-right: 30px;
            }

            .col-lg-5,
            .col-md-7,
            .col-sm-10 {
                width: 100% !important;
                max-width: 100% !important;
                flex: 0 0 100%;
            }

            .login-box {
                width: 100%;
                margin-top: 20px;
                padding: 10px;
                height: min-height:
            }

            .preview-box {
                width: 250px;
                margin: auto;
            }
        }
    </style>

</head>

<body class="bg-light">

    <div class="container mt-5">

        <div class="col-md-7 mx-auto">

            <div class="card shadow p-4 border-0 rounded-4">

                <h2 class="text-center mb-4 text-primary">
                    Customer Registration
                </h2>

                <?php if ($msg != "") { ?>

                    <div class="alert alert-danger text-center">
                        <?php echo $msg; ?>
                    </div>

                <?php } ?>

                <form method="post">

                    <!-- PROFILE IMAGE -->

                    <div class="mb-4 text-center">

                        <label class="form-label fw-bold">
                            Profile Picture (Optional)
                        </label>

                        <input type="file" id="profileInput" class="form-control" accept="image/*">

                        <div class="preview-box mt-3">

                            <img id="preview">

                        </div>

                        <button type="button" id="cropBtn" class="btn btn-dark mt-3" style="display:none;">

                            Crop Image

                        </button>

                        <div>

                            <img id="croppedPreview" class="cropped-preview" style="display:none;">

                        </div>

                        <input type="hidden" name="cropped_image" id="cropped_image">

                    </div>

                    <!-- NAME -->

                    <div class="row">

                        <div class="col-md-6 mb-3">

                            <label class="form-label fw-bold">
                                Full Name
                            </label>

                            <input type="text" name="name" class="form-control" required>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="form-label fw-bold">
                                Date of Birth
                            </label>

                            <input type="date" name="dob" class="form-control" required>

                        </div>

                    </div>

                    <div class="mb-3">

                        <label class="form-label fw-bold">
                            Phone Number
                        </label>

                        <input type="text" name="phone" class="form-control" required>

                    </div>

                    <div class="row">

                        <div class="col-md-6 mb-3">

                            <label class="form-label fw-bold">
                                State
                            </label>

                            <input type="text" name="state" class="form-control" required>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="form-label fw-bold">
                                District
                            </label>

                            <input type="text" name="district" class="form-control" required>

                        </div>

                    </div>

                    <div class="mb-3">

                        <label class="form-label fw-bold">
                            Tehsil
                        </label>

                        <input type="text" name="tehsil" class="form-control" required>

                    </div>

                    <div class="mb-3">

                        <label class="form-label fw-bold">
                            Address
                        </label>

                        <textarea name="address" class="form-control" rows="3" required></textarea>

                    </div>

                    <div class="mb-3">

                        <label class="form-label fw-bold">
                            Email
                        </label>

                        <input type="email" name="email" class="form-control" required>

                    </div>

                    <div class="row">

                        <div class="col-md-6 mb-3">

                            <label class="form-label fw-bold">
                                Password
                            </label>

                            <input type="password" name="password" class="form-control" required>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="form-label fw-bold">
                                Confirm Password
                            </label>

                            <input type="password" name="confirm_password" class="form-control" required>

                        </div>

                    </div>

                    <button type="submit" name="register" class="btn btn-success w-100">

                        Register Account

                    </button>

                </form>

            </div>

        </div>

    </div>

    <script>

        let cropper;

        document.getElementById('profileInput')
            .addEventListener('change', function (e) {

                const file = e.target.files[0];

                if (file) {

                    const reader = new FileReader();

                    reader.onload = function (event) {

                        const image = document.getElementById('preview');

                        image.src = event.target.result;

                        image.style.display = "block";

                        document.getElementById('cropBtn').style.display = "inline-block";

                        if (cropper) {
                            cropper.destroy();
                        }

                        cropper = new Cropper(image, {

                            aspectRatio: 1,
                            viewMode: 1,
                            dragMode: 'move',
                            autoCropArea: 0.5,
                            cropBoxResizable: true,
                            cropBoxMovable: true,
                            movable: true,
                            zoomable: true,
                            scalable: false,
                            rotatable: false

                        });

                    }

                    reader.readAsDataURL(file);
                }

            });

        document.getElementById('cropBtn')
            .addEventListener('click', function () {

                const canvas = cropper.getCroppedCanvas({

                    width: 300,
                    height: 300

                });

                const croppedImage = canvas.toDataURL('image/png');

                document.getElementById('cropped_image').value = croppedImage;

                const preview = document.getElementById('croppedPreview');

                preview.src = croppedImage;

                preview.style.display = "block";

                alert('✅ Image cropped successfully');

            });

    </script>

</body>

</html>