<?php
session_start();
?>


<!DOCTYPE html>
<html lang="en">

<?php include_once '../includes/head.php' ?>

<body>
    <!-- Start Top Nav -->
    <nav class="navbar navbar-expand-lg bg-dark navbar-light d-none d-lg-block" id="templatemo_nav_top">
        <div class="container text-light">
            <div class="w-100 d-flex justify-content-between">
                <div>
                    <i class="fa fa-envelope mx-2"></i>
                    <a class="navbar-sm-brand text-light text-decoration-none" href="mailto:info@company.com">info@company.com</a>
                    <i class="fa fa-phone mx-2"></i>
                    <a class="navbar-sm-brand text-light text-decoration-none" href="tel:010-020-0340">010-020-0340</a>
                </div>
                <div>
                    <a class="text-light" href="https://fb.com/templatemo" target="_blank" rel="sponsored"><i class="fab fa-facebook-f fa-sm fa-fw me-2"></i></a>
                    <a class="text-light" href="https://www.instagram.com/" target="_blank"><i class="fab fa-instagram fa-sm fa-fw me-2"></i></a>
                    <a class="text-light" href="https://twitter.com/" target="_blank"><i class="fab fa-twitter fa-sm fa-fw me-2"></i></a>
                    <a class="text-light" href="https://www.linkedin.com/" target="_blank"><i class="fab fa-linkedin fa-sm fa-fw"></i></a>
                </div>
            </div>
        </div>
    </nav>
    <!-- Close Top Nav -->


    <!-- Header -->
    <?php
include_once '../includes/header.php';
 ?>
    <!-- Close Header -->

    <!-- Modal -->
    <div class="modal fade bg-white" id="templatemo_search" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="w-100 pt-1 mb-5 text-right">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="get" class="modal-content modal-body border-0 p-0">
                <div class="input-group mb-2">
                    <input type="text" class="form-control" id="inputModalSearch" name="q" placeholder="Search ...">
                    <button type="submit" class="input-group-text bg-success text-light">
                        <i class="fa fa-fw fa-search text-white"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>


    <!-- Start Content Page -->
    <div class="container-fluid bg-light py-5">
        <div class="col-md-6 m-auto text-center">
            <h1 class="h1">Contact Us</h1>
            <p>
                Proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
                Lorem ipsum dolor sit amet.
            </p>
        </div>
    </div>

    <!-- Start Map -->
<!--    <div id="mapid" style="width: 100%; height: 300px;"></div>-->
<!--    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js" integrity="sha512-XQoYMqMTK8LvdxXYG3nZ448hOEQiglfqkJs1NOQV44cWnUrBc8PkAOcXy20w0vlaXaVUearIOBhiXZ5V3ynxwA==" crossorigin=""></script>-->
<!--    <script>-->
<!--        var mymap = L.map('mapid').setView([-23.013104, -43.394365, 13], 13);-->
<!---->
<!--        L.tileLayer('https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token=pk.eyJ1IjoibWFwYm94IiwiYSI6ImNpejY4NXVycTA2emYycXBndHRqcmZ3N3gifQ.rJcFIG214AriISLbB6B5aw', {-->
<!--            maxZoom: 18,-->
<!--            attribution: 'Zay Telmplte | Template Design by <a href="https://templatemo.com/">Templatemo</a> | Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, ' +-->
<!--                '<a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, ' +-->
<!--                'Imagery © <a href="https://www.mapbox.com/">Mapbox</a>',-->
<!--            id: 'mapbox/streets-v11',-->
<!--            tileSize: 512,-->
<!--            zoomOffset: -1-->
<!--        }).addTo(mymap);-->
<!---->
<!--        L.marker([-23.013104, -43.394365, 13]).addTo(mymap)-->
<!--            .bindPopup("<b>Zay</b> eCommerce Template<br />Location.").openPopup();-->
<!---->
<!--        mymap.scrollWheelZoom.disable();-->
<!--        mymap.touchZoom.disable();-->
<!--    </script>-->
    <!-- Ena Map -->

    <!-- Start Contact -->
    <div class="container py-5">
    <div class="row py-5">
        <form class="col-md-9 m-auto" method="post" id="contact" action="../db/spracovanieFormulara.php" role="form">
            <div class="row">
                <div class="form-group col-md-6 mb-3">
                    <label for="inputname">Name</label>
                    <input type="text" class="form-control mt-1" id="meno" name="meno" placeholder="Name"
                           value="<?= isset($_SESSION['user']['meno']) ? htmlspecialchars($_SESSION['user']['meno']) : '' ?>"
                           <?= isset($_SESSION['user']) ? 'readonly' : '' ?>>
                </div>
                <div class="form-group col-md-6 mb-3">
                    <label for="inputemail">Email</label>
                    <input type="email" class="form-control mt-1" id="email" name="email" placeholder="Email"
                           value="<?= isset($_SESSION['user']['email']) ? htmlspecialchars($_SESSION['user']['email']) : '' ?>"
                           <?= isset($_SESSION['user']) ? 'readonly' : '' ?>>
                </div>
            </div>
            <div class="mb-3">
                <label for="inputsubject">Subject</label>
                <input type="text" class="form-control mt-1" id="subject" name="objekt" placeholder="Subject" required>
            </div>
            <div class="mb-3">
                <label for="inputmessage">Message</label>
                <textarea class="form-control mt-1" id="sprava" name="sprava" placeholder="Message" rows="8" required></textarea>
            </div>
            <div class="mb-3">
                <input type="checkbox" name="suhlas" id="check" required>
                <label for="check">Súhlasím so spracovaním údajov</label>
            </div>

            <?php if (!isset($_SESSION['user'])): ?>
                <p class="text-danger text-center">Pre odoslanie správy sa prosím <a href="../includes/pripojenie/login.php">prihlás</a>.</p>
            <?php endif; ?>

            <div class="row">
                <div class="col text-end mt-2">
                    <button type="submit" class="btn btn-success btn-lg px-3"
                        <?= !isset($_SESSION['user']) ? 'disabled' : '' ?>>
                        Let’s Talk
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>


    
    <!-- End Contact -->
    


    <!-- Start Footer -->
<?php include_once('../includes/footer.php') ?>
    <!-- End Footer -->

    <!-- Start Script -->
    <script src="../assets_sablon/js/jquery-1.11.0.min.js"></script>
    <script src="../assets_sablon/js/jquery-migrate-1.2.1.min.js"></script>
    <script src="../assets_sablon/js/bootstrap.bundle.min.js"></script>
    <script src="../assets_sablon/js/templatemo.js"></script>
    <script src="../assets_sablon/js/custom.js"></script>
    
    <!-- End Script -->
</body>
