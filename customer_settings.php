<?php
session_start();
require_once __DIR__ . '/connectfinity.php';

if (!isset($_SESSION['customer_email'])) {
    header("Location: customer_login.php");
    exit;
}

$email = $_SESSION['customer_email'];

$customer = $conn->query("
    SELECT * FROM customers 
    WHERE email='$email'
");

$data = $customer->fetch_assoc();

// UPDATE PROFILE
if (isset($_POST['update'])) {

    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $dob = trim($_POST['dob']);
    $state = trim($_POST['state']);
    $district = trim($_POST['district']);
    $tehsil = trim($_POST['tehsil']);

    // PROFILE PIC
    $profile_pic = $data['profile_pic'];


    if (!empty($_POST['cropped_image'])) {

        // old image delete
        if (!empty($data['profile_pic'])) {

            $old_file = "uploads/profile/" . $data['profile_pic'];

            if (!empty($data['profile_pic']) && file_exists($old_file)) {
                unlink($old_file);
            }
        }

        $image_parts = explode(";base64,", $_POST['cropped_image']);

        $image_base64 = base64_decode($image_parts[1]);

        $pic_name = time() . ".png";

        file_put_contents(
            "uploads/profile/" . $pic_name,
            $image_base64
        );

        $profile_pic = $pic_name;
    }

    $conn->query("
        UPDATE customers SET
        name='$name',
        phone='$phone',
        address='$address',
        dob='$dob',
        state='$state',
        district='$district',
        tehsil='$tehsil',
        profile_pic='$profile_pic'
        WHERE email='$email'
    ");

    echo "<script>
        alert('✅ Profile Updated Successfully');
        window.location='customer_profile.php';
    </script>";

    exit;
}
?>

<!DOCTYPE html>
<html>

<head>

    <title>Customer Settings</title>

    <link rel="stylesheet" href="admin/assets/bootstrap.min.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" />

    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
    <style>
        body {
            background: #f4f7fb;
        }

        .settings-box {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.1);
        }

        .profile-preview {
            width: 140px;
            height: 140px;
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid #0d6efd;
        }

        .crop-container {
            width: 100%;
            max-width: 500px;
            margin: auto;
            overflow: hidden;
            border-radius: 10px;
        }

        #preview {
            max-width: 100%;
            display: none;
        }

        .cropper-view-box,
        .cropper-face {
            border-radius: 50%;
        }

        .preview-box {
            width: 300px;
            margin: auto;
        }

        .cropped-preview {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #0d6efd;
            margin-top: 15px;
        }
    </style>

</head>

<body>

    <div class="container mt-5">

        <div class="settings-box">

            <h2 class="mb-4 text-primary">
                <i class="fa-solid fa-gear"></i>
                Account Settings
            </h2>

            <form method="post" enctype="multipart/form-data">

                <!-- PROFILE IMAGE -->
                <div class="text-center mb-4">

                    <?php if (!empty($data['profile_pic'])) { ?>

                        <img src="uploads/profile/<?php echo $data['profile_pic']; ?>" class="profile-preview">

                    <?php } else { ?>

                        <i class="fa-solid fa-circle-user text-primary" style="font-size:120px;"></i>

                    <?php } ?>

                    <div class="mt-3">

                        <input type="file" name="profile_pic" id="profileInput" class="form-control" accept="image/*">

                    </div>

                    <div class="crop-container mt-3">

                        <img id="preview">

                    </div>

                    <input type="hidden" name="cropped_image" id="cropped_image">

                    <div class="text-center mt-3">

                        <button type="button" class="btn btn-dark" id="cropBtn" style="display:none;">
                            Crop Image
                        </button>

                    </div>
                    <div>

                        <img id="croppedPreview" class="cropped-preview" style="display:none;">

                    </div>

                </div>


                <div class="row">

                    <div class="col-md-6 mb-3">

                        <label class="fw-bold">
                            Full Name
                        </label>

                        <input type="text" name="name" value="<?php echo $data['name']; ?>" class="form-control"
                            required>

                    </div>

                    <div class="col-md-6 mb-3">

                        <label class="fw-bold">
                            Phone Number
                        </label>

                        <input type="text" name="phone" value="<?php echo $data['phone']; ?>" class="form-control"
                            required>

                    </div>

                </div>

                <div class="mb-3">

                    <label class="fw-bold">
                        Date of Birth
                    </label>

                    <input type="date" name="dob" value="<?php echo $data['dob']; ?>" class="form-control">

                </div>

                <div class="mb-3">

                    <label class="fw-bold">
                        Address
                    </label>

                    <textarea name="address" class="form-control" rows="3"
                        required><?php echo $data['address']; ?></textarea>

                </div>

                <div class="row">

                    <div class="col-md-4 mb-3">

                        <label class="fw-bold">
                            State
                        </label>

                        <input type="text" name="state" value="<?php echo $data['state']; ?>" class="form-control">

                    </div>

                    <div class="col-md-4 mb-3">

                        <label class="fw-bold">
                            District
                        </label>

                        <input type="text" name="district" value="<?php echo $data['district']; ?>"
                            class="form-control">

                    </div>

                    <div class="col-md-4 mb-3">

                        <label class="fw-bold">
                            Tehsil
                        </label>

                        <input type="text" name="tehsil" value="<?php echo $data['tehsil']; ?>" class="form-control">

                    </div>

                </div>

                <button type="submit" name="update" class="btn btn-primary w-100">

                    <i class="fa-solid fa-floppy-disk"></i>
                    Update Profile

                </button>

            </form>

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