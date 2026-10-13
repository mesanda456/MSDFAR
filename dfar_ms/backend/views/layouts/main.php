<?php

/** @var View $this */

/** @var string $content */

use backend\assets\AppAsset;
use backend\config\Constant;
use backend\config\UserTypeUtil;
use backend\models\ExportCompany;
use backend\models\ProfileOfficers;
use common\components\WebUser;
use common\widgets\Alert;
use yii\bootstrap4\Breadcrumbs;
use yii\bootstrap4\Html;
use yii\web\View;
use backend\controllers\FishermanReCorrectionController;

AppAsset::register($this);
$webURL = Yii::getAlias('@web');
$profileImage = "avatar-1.png";
$userLevel = Constant::Black;
$profile = "";
//$profileExport="";
if (!Yii::$app->user->isGuest && (!UserTypeUtil::hasType(Constant::FISHERMAN) || UserTypeUtil::isDirector())) {
    $profile = ProfileOfficers::findOne(Yii::$app->user->identity->profile_id);
    if ($profile != null && $profile != []) {
        $profileImage = $profile->profile_image == "" ? "avatar-1.png" : $profile->profile_image;
        $userLevel = $profile->user_level == "" ? Constant::Black : $profile->user_level;
    }
}

if (!Yii::$app->user->isGuest && UserTypeUtil::hasType(Constant::EXPORT_COMPANY)) {
    $profileExport = ExportCompany::findOne(Yii::$app->user->identity->profile_id);


}
?>

<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">

<head>

    <link rel="shortcut icon" href="<?= $webURL ?>/ftco-32x32.png">
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/1.5.12/clipboard.min.js"></script>
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <link type="text/css" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/south-street/jquery-ui.css"
        rel="stylesheet">
    <script type="text/javascript"
        src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <!--        <script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js" mc_processed="1"></script>-->
    <script src="
https://cdn.jsdelivr.net/npm/file-saver@2.0.5/dist/FileSaver.min.js
"></script>
</head>
<?php
echo '<body class="bg-light">';

?>


<?php $this->beginBody() ?>
<?php
$pendingFisherman = null;

$excludedRoutes = [
    'officer/password-update',
    // add other routes here if you need to exclude more later
];

$currentRoute = Yii::$app->requestedRoute; // e.g. "officer/password-update"

if (
    !in_array($currentRoute, $excludedRoutes, true)
    && !Yii::$app->user->isGuest
    && UserTypeUtil::hasType(Constant::FI)
) {
    $mainId = Yii::$app->user->identity->id;

    $officerProfile = ProfileOfficers::findOne(Yii::$app->user->identity->profile_id);

    if ($officerProfile !== null) {
        $district = $officerProfile->district;
        $division = $officerProfile->division;

        $pendingFisherman = FishermanReCorrectionController::getPendingFishermanForOfficer(
            $mainId,
            $district,
            $division
        );
    }
}
?>
<?php if ($pendingFisherman !== null): ?>

    <style>
        /* Mobile-friendly, smaller-text NIC correction modal */
        #fiNicCorrectionModal .modal-dialog,
        #confirmActionModal .modal-dialog {
            max-width: 420px;
            margin: 1rem auto;
        }

        #fiNicCorrectionModal .modal-content,
        #confirmActionModal .modal-content {
            font-size: 0.82rem;
            border-radius: 10px;
        }

        #fiNicCorrectionModal .modal-header,
        #confirmActionModal .modal-header {
            padding: 0.75rem 1rem;
        }

        #fiNicCorrectionModal .modal-title {
            font-size: 0.95rem;
            line-height: 1.35;
        }

        #fiNicCorrectionModal .modal-title small {
            font-size: 0.72rem;
            display: block;
            font-weight: 400;
            color: #555;
        }

        #fiNicCorrectionModal .modal-body,
        #confirmActionModal .modal-body {
            padding: 0.85rem 1rem;
            font-size: 0.8rem;
            line-height: 1.4;
        }

        #fiNicCorrectionModal .modal-body p,
        #confirmActionModal .modal-body p {
            margin-bottom: 0.5rem;
        }

        #fiNicCorrectionModal .form-group label {
            font-size: 0.78rem;
            margin-bottom: 0.25rem;
        }

        #fiNicCorrectionModal .form-control {
            font-size: 0.8rem;
            padding: 0.4rem 0.6rem;
            height: auto;
        }

        #fiNicCorrectionModal .modal-footer,
        #confirmActionModal .modal-footer {
            padding: 0.6rem 1rem;
        }

        #fiNicCorrectionModal .btn,
        #confirmActionModal .btn {
            font-size: 0.78rem;
            padding: 0.35rem 0.75rem;
        }

        #fiNicCorrectionModal .close {
            font-size: 1.1rem;
        }

        /* Mobile screens */
        @media (max-width: 480px) {

            #fiNicCorrectionModal .modal-dialog,
            #confirmActionModal .modal-dialog {
                max-width: 92%;
                margin: 0.75rem auto;
            }

            #fiNicCorrectionModal .modal-content,
            #confirmActionModal .modal-content {
                font-size: 0.76rem;
            }

            #fiNicCorrectionModal .modal-title {
                font-size: 0.88rem;
            }

            #fiNicCorrectionModal .modal-title small {
                font-size: 0.68rem;
            }

            #fiNicCorrectionModal .modal-body,
            #confirmActionModal .modal-body {
                font-size: 0.75rem;
                padding: 0.7rem 0.85rem;
            }

            #fiNicCorrectionModal .modal-header,
            #confirmActionModal .modal-header {
                padding: 0.6rem 0.85rem;
            }

            #fiNicCorrectionModal .modal-footer,
            #confirmActionModal .modal-footer {
                padding: 0.5rem 0.85rem;
                flex-wrap: wrap;
                gap: 0.4rem;
            }

            #fiNicCorrectionModal .btn,
            #confirmActionModal .btn {
                flex: 1 1 auto;
                font-size: 0.72rem;
                padding: 0.4rem 0.5rem;
            }

            #fiNicCorrectionModal .form-control {
                font-size: 0.75rem;
            }
        }
    </style>

    <!-- NIC Correction Modal -->
    <div class="modal fade" id="fiNicCorrectionModal" tabindex="-1" role="dialog"
        aria-labelledby="fiNicCorrectionModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">

        <div class="modal-dialog modal-dialog-centered" role="document">

            <?= Html::beginForm(
                [
                    '/fisherman-re-correction/index',
                    'fishermanId' => $pendingFisherman->id,
                    'mainId' => Yii::$app->user->identity->id,
                ],
                'post',
                ['id' => 'fiNicCorrectionForm']
            ) ?>

            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="fiNicCorrectionModalLabel">
                        NIC Correction Needed<br>
                        <small>ජාතික හැඳුනුම්පත් අංකය නිවැරදි කිරීම අවශ්‍යයි</small>
                        <hr class="my-2">
                        <small>MSDFAR දත්ත ගබඩාව වැඩි දියුණු කිරීම සඳහා ඔබේ දායකත්වය ලබාදෙන්න.</small>
                        <small>MSDFAR தரவுத்தளத்தை மேம்படுத்துவதற்கு தங்களது பங்களிப்பை வழங்கவும்.</small>
                    </h5>

                    <button type="button" class="close" id="fiNicCloseBtn">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">

                    <p>
                       The NIC number of the fisherman 
                        <strong><?= Html::encode($pendingFisherman->first_name . ' ' . $pendingFisherman->last_name) ?></strong>
                       registered in your Distric appears invalid. Please enter the correct NIC number. If you believe it is still correct, you can skip
                    </p>

                    <p>
                        உங்கள் மாவட்டத்தில் பதிவு செய்யப்பட்டுள்ள மீனவர்
                        <strong><?= Html::encode($pendingFisherman->first_name . ' ' . $pendingFisherman->last_name) ?></strong>
                       அவர்களின் தேசிய அடையாள அட்டை எண் தவறானதாகத் தெரிகிறது. சரியான எண்ணை உள்ளிடவும் அல்லது சரியாக இருக்கும் பட்சத்தில், இதைத் தவிர்க்கவும்.
                    </p>

                    <p>
                        ඔබේ දිස්ත්‍රික්කයේ ලියාපදිංචි වී ඇති
                        <strong><?= Html::encode($pendingFisherman->first_name . ' ' . $pendingFisherman->last_name) ?></strong>
                        යන ධීවර මහතාගේ ජාතික හැඳුනුම්පත් අංකය වලංගු ආකෘතියට පටහැනි වේ. කරුණාකර වලංගු අංකය ඇතුළත් කරන්න හෝ එය නිවැරදි නම් මෙය මඟ හරින්න.
                    </p>

                    <hr>

                    <p>
                        <strong>Current NIC / වත්මන් NIC:</strong><br>
                        <span class="text-danger">
                            <?= Html::encode($pendingFisherman->nic ?: '(empty / හිස්)') ?>
                        </span>
                    </p>

                    <p>
                        <strong>Mobile / ජංගම දුරකථනය:</strong><br>
                        <?= Html::encode($pendingFisherman->mobile ?: '(none on record / සටහන් වී නොමැත)') ?>
                    </p>

                    <div class="form-group">
                        <label for="nic">
                            Correct NIC / නිවැරදි NIC
                        </label>

                        <input type="text" class="form-control" id="nic" name="FishermanReCorrection[nic]"
                            placeholder="Enter Correct NIC / නිවැරදි NIC ඇතුළත් කරන්න" required>
                    </div>

                </div>

                <div class="modal-footer">

                    <button type="button" class="btn btn-secondary" id="fiNicCancelBtn">
                        Cancel / අවලංගු කරන්න
                    </button>

                    <button type="button" class="btn btn-primary" id="fiNicOkBtn">
                        OK / තහවුරු කරන්න
                    </button>

                </div>

            </div>

            <?= Html::endForm() ?>

        </div>
    </div>

    <!-- Confirmation Modal -->
    <div class="modal fade" id="confirmActionModal" tabindex="-1" role="dialog" data-backdrop="static"
        data-keyboard="false">

        <div class="modal-dialog modal-dialog-centered">

            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">
                        Confirm Action<br>
                        <small>ක්‍රියාව තහවුරු කරන්න</small>
                    </h5>
                </div>

                <div class="modal-body" id="confirmMessage"></div>

                <div class="modal-footer">

                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        No / නැහැ
                    </button>

                    <button type="button" class="btn btn-primary" id="confirmYesBtn">
                        Yes / ඔව්
                    </button>

                </div>

            </div>

        </div>

    </div>

    <script>
        $(function () {

            $('#fiNicCorrectionModal').modal('show');

            let action = '';

            function goIgnore() {
                window.location.href = '<?= Yii::$app->urlManager->createUrl([
                    '/fisherman-re-correction/ignore',
                    'fishermanId' => $pendingFisherman->id,
                    'mainId' => Yii::$app->user->identity->id,
                ]) ?>';
            }

            // OK button
            $('#fiNicOkBtn').click(function () {

                if ($('#nic').val().trim() === '') {
                    alert("Please enter the correct NIC.\n\nකරුණාකර නිවැරදි NIC අංකය ඇතුළත් කරන්න.");
                    return;
                }

                action = 'submit';

                $('#confirmMessage').html(
                    '<p>Thank you for your contribution towards improving the MSDFAR database.</p>' +
                    '<p>Msdfar දත්ත ගබඩාව වැඩි දියුණු කිරීම සඳහා ඔබේ දායකත්වය ලබාදුන්නාට ස්තූතියි  </p>' +
                    '<p>நீங்கள் வழங்கிய ஆதரவுக்கு நன்றி. இதே போன்று, எதிர்காலத்திலும் தங்களின் உதவியை எதிர்பார்க்கிறோம்.<p/>'
                );

                $('#confirmActionModal').modal('show');
            });

            // Cancel or Close
            $('#fiNicCancelBtn, #fiNicCloseBtn').click(function () {

                action = 'ignore';

                $('#confirmMessage').html(
                    '<p>We appreciate your support, and we hope to receive your assistance next time.</p>' +
                    '<p>මීලඟ අවස්ථාවේ දී ඔබේ සහයෝගය අගය කරමු. </p>' +
                    '<p>நீங்கள் வழங்கிய ஆதரவுக்கு நன்றி. மீண்டும் இதேபோன்ற உங்கள் உதவியை எதிர்பார்க்கின்றோம்.</p>'
                );

                $('#confirmActionModal').modal('show');
            });

            // Yes button
            $('#confirmYesBtn').click(function () {

                $('#confirmActionModal').modal('hide');

                if (action === 'submit') {

                    $('#fiNicCorrectionForm').submit();

                } else if (action === 'ignore') {

                    goIgnore();

                }

            });

        });
    </script>

<?php endif; ?>
<?php if (!Yii::$app->user->isGuest) { ?>
    <!-- ============================================================== -->
    <!-- main wrapper -->
    <!-- ============================================================== -->
    <div class="dashboard-main-wrapper">
        <?php
        /** @var WebUser $webUser */
        $webUser = Yii::$app->getUser();
        $mainIdentityId = $webUser->getMainIdentityId();

        if ($mainIdentityId != Yii::$app->user->identity->getId()) { ?>
            <div class=""
                style="    min-height: 40px; width: 100%; background-color: red; text-align: center; padding-top: 12px; padding-right: 10px">
                <p style="color: white; font-weight: bold">
                    Currently, You are viewing a profile as someone else.
                    <a href="../site/stop-impersonating" style="color:#f7f700;" class=""> Click here to go back to your
                        original profile.</a>
                </p>
            </div>
        <?php } ?>
        <!-- ============================================================== -->
        <!-- navbar -->
        <!-- ============================================================== -->
        <div class="dashboard-header">
            <nav class="navbar navbar-expand-lg navbar-light bg-white fixed-top">
                <a class="navbar-brand" href="<?= $webURL ?>">
                    <h1>MSDFAR </h1>
                </a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
                    aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="ml-auto" id="navbarSupportedContent">
                    <ul class="navbar-nav ml-auto navbar-right-top flex-row ">
                        <!--                        <li class="nav-item dropdown connection">-->
                        <!--                            --><?php
                        //                            foreach (yii::$app->params['languages'] as $key => $lanuage) {
                        //                                echo ' <a href="' . $webURL . '/site/language?id=' . $key . '" class="language btn" id="' . $key . '">' . $lanuage . '</button>';
                        //                            }
                        //                            ?>
                        <!---->
                        <!--                        </li>-->


                        <li class="nav-item dropdown nav-user order-lg-4 ">
                            <a class="nav-link nav-user-img" href="#" id="navbarDropdownMenuLink2" data-toggle="dropdown"
                                aria-haspopup="true" aria-expanded="false"><?php
                                    $avatarSrc = ($profileImage === 'avatar-1.png' || empty($profileImage))
                                        ? $webURL . '/avatar-1.png'
                                        : Constant::$FILE_VIEW_PATH . 'officer/profile/' . $profileImage;
                                ?><img src="<?= $avatarSrc ?>" alt="" class="avatar-xs
                                        rounded-circle">

                            </a>
                            <div class="dropdown-menu dropdown-menu-right nav-user-dropdown"
                                aria-labelledby="navbarDropdownMenuLink2">
                                <div class="nav-user-info">
                                    <h5 class="mb-0 text-white nav-user-name">
                                        <?php if (
                                            !UserTypeUtil::hasType(Constant::FISHERMAN) && !UserTypeUtil::hasType
                                            (Constant::EXPORT_COMPANY)
                                        ) {
                                            if (!empty($profile)) {
                                                echo $profile->first_name;
                                            }
                                        }
                                        if (
                                            UserTypeUtil::hasType
                                            (Constant::EXPORT_COMPANY)
                                        ) {
                                            if (!empty($profileExport)) {
                                                echo $profileExport->company_name;
                                            }
                                        }
                                        ?>
                                        <?= "(" . Yii::$app->user->identity->nic . ")"
                                            ?>     <?= !UserTypeUtil::hasType(Constant::FISHERMAN) && !UserTypeUtil::hasType
                                             (Constant::EXPORT_COMPANY) ?
                                                 Constant::$USER_RANKS_LABEL[$userLevel]
                                                 ?? "" : ""
                                                 ?>
                                    </h5>
                                    <span class="status"></span><span> <?= UserTypeUtil::getTypeNames(Yii::$app->user->identity->type)
                                        ?? "" ?> <br>
                                        <?=
                                            Yii::$app->session->get("officer_district") ?? "" ?>-
                                        <?=
                                            Yii::$app->session->get("officer_division") ?? "" ?>
                                        <?=
                                            Yii::$app->session->get("officer_harbour") ?? "" ?> ID:
                                        <?=
                                            Yii::$app->user->identity->id ?? "" ?> P:
                                        <?= Yii::$app->session->get("userPermission") ?? "" ?>
                                    </span>
                                    <!--                                </div>-->
                                    <HR>
                                    <!--                                <div class="nav-user-info">-->
                                    <p style="padding: 0; margin: 0">
                                        <?php
                                        if (UserTypeUtil::hasType(Constant::FISHERMAN)) {
                                            foreach (yii::$app->params['languages'] as $key => $lanuage) {
                                                echo ' <a href="' . $webURL . '/site/language?id=' . $key . '" class="language" id="' . $key . '">' . $lanuage . '</button>';
                                            }
                                        }
                                        //                            ?>
                                    </p>
                                </div>
                                <?php
                                if (
                                    !UserTypeUtil::hasType(Constant::FISHERMAN) && !UserTypeUtil::hasType
                                    (Constant::EXPORT_COMPANY)
                                ) {
                                    echo '<a class="dropdown-item" href="' . $webURL . '/profile-officers/view?id=' .
                                        Yii::$app->user->identity->profile_id . '"><i class="fas fa-user mr-2"></i>My Profile
                                    </a>';
                                    echo '<a target="_blank" class="dropdown-item" href="' . $webURL . '/helps"><i class="fas fa-question-circle"></i> Help
                                    </a>';
                                    echo '<a target="_blank" class="dropdown-item" href="' . $webURL . '/inquiry/create"><i class="fas fa-exclamation-circle"></i> Inquiry
                                    </a>';

                                }
                                ?>
                                <HR>
                                <!--                                <div class="nav-user-info">-->
                                <p style="padding: 0; margin: 0">
                                    <?php
                                    if (UserTypeUtil::hasType(Constant::FISHERMAN)) {
                                        foreach (yii::$app->params['languages'] as $key => $lanuage) {
                                            echo ' <a href="' . $webURL . '/site/language?id=' . $key . '" class="language" id="' . $key . '">' . $lanuage . '</button>';
                                        }
                                    }
                                    //                            ?>
                                </p>
                                <?php echo '<a class="dropdown-item">'
                                    . Html::beginForm(['/site/logout'], 'post')
                                    . Html::submitButton(
                                        'Logout',
                                        ['class' => 'dropdown-item']
                                    )
                                    . Html::endForm()
                                    . '</a>'; ?>
                            </div>

                        </li>
                    </ul>
                </div>
            </nav>
        </div>


        <!-- ============================================================== -->
        <!-- end navbar -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- left sidebar -->
        <!-- ============================================================== -->
        <div class="nav-left-sidebar sidebar-dark">
            <div class="menu-list">
                <nav class="navbar navbar-expand-lg navbar-light">
                    <a class="d-xl-none d-lg-none text-white" href="#">Menu bar</a>
                    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
                        aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarNav">
                        <ul class="navbar-nav flex-column">
                            <?php if (UserTypeUtil::hasType(Constant::DG)): ?>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?= $webURL ?>/analytics/dg-dashboard">
                                        <i class="fa fa-fw fa-tachometer-alt"></i> <?= Yii::t('app', 'Dashboard') ?>
                                    </a>
                                </li>
                            <?php endif; ?>
                            <?php if (UserTypeUtil::hasType(Constant::ADMINISTRATION)):
                                require("administration_menu.php");
                            endif; ?>
                            <?php if (UserTypeUtil::hasType(Constant::DM)): ?>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?= $webURL ?>/analytics/dm-dashboard">
                                        <i class="fa fa-fw fa-tachometer-alt"></i> <?= Yii::t('app', 'Dashboard') ?>
                                    </a>
                                </li>
                            <?php endif; ?>
                            <?php if (UserTypeUtil::hasType(Constant::AD)): ?>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?= $webURL ?>/analytics/ad-dashboard">
                                        <i class="fa fa-fw fa-tachometer-alt"></i> <?= Yii::t('app', 'Dashboard') ?>
                                    </a>
                                </li>
                            <?php endif; ?>
                            <?php if (UserTypeUtil::hasType(Constant::FI)): ?>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?= $webURL ?>/analytics/fi-dashboard">
                                        <i class="fa fa-fw fa-tachometer-alt"></i> <?= Yii::t('app', 'Dashboard') ?>
                                    </a>
                                </li>
                            <?php endif; ?>
                            <?php if (Yii::$app->user->identity->type == Constant::DFI): ?>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?= $webURL ?>/analytics/dfi-dashboard">
                                        <i class="fa fa-fw fa-tachometer-alt"></i> <?= Yii::t('app', 'Dashboard') ?>
                                    </a>
                                </li>
                            <?php endif; ?>
                            <?php if (

                                (!UserTypeUtil::hasType(Constant::FISHERMAN)
                                    && !UserTypeUtil::hasType(Constant::EXPORT_COMPANY))
                                && (UserTypeUtil::hasType(Constant::ITU)
                                    || UserTypeUtil::hasType(Constant::ALTER)
                                    || UserTypeUtil::hasType(Constant::FI)
                                    || UserTypeUtil::hasType(Constant::AD)
                                    || UserTypeUtil::hasType(Constant::DM)
                                    || UserTypeUtil::hasType(Constant::DO)
                                    || UserTypeUtil::hasType(Constant::DG)
                                    || UserTypeUtil::hasType(Constant::MEA)
                                    || UserTypeUtil::hasType(Constant::DFI)
                                    || UserTypeUtil::hasType(Constant::MANAGEMENT)
                                    || UserTypeUtil::hasType(Constant::CALL_SIGN)
                                    || UserTypeUtil::hasType(Constant::ITD)
                                    || UserTypeUtil::hasType(Constant::ADMIN)
                                    || UserTypeUtil::hasType(Constant::HARBOUR_OFFICER)
                                    || UserTypeUtil::hasType(Constant::PRINT)
                                    || UserTypeUtil::hasType(Constant::SPECIAL_LICENCE)
                                    || UserTypeUtil::hasType(Constant::DIRECTOR)
                                    || UserTypeUtil::hasType(Constant::ICT_OFFICER)
                                    || UserTypeUtil::hasType(Constant::ICT_ASSISTANT)
                                    || UserTypeUtil::hasType(Constant::FISHERIES_OFFICER)
                                    || UserTypeUtil::hasType(Constant::DEVELOPMENT_OFFICER)
                                    || UserTypeUtil::hasType(Constant::DEVELOPMENT_DIVISION)
                                    || UserTypeUtil::hasType(Constant::MANAGEMENT_SERVICE_OFFICER)
                                )
                                && Yii::$app->user->identity->profile_id != 0
                            ) {

                                require("special-license-menu_office.php");
                                Yii::$app->user->can("Reports-view") ? require("reports.php") : "";
                                // echo Yii::$app->user->id . ' | can=' . var_export(Yii::$app->user->can("Reports-view"), true);
                                require("officer-menu.php");

                            } ?>

                            <?php if (UserTypeUtil::hasType(Constant::CC) || UserTypeUtil::hasType(Constant::KKS)) {
                                require("headOffice-menu.php");
                            } ?>

                            <?php if (UserTypeUtil::hasType(Constant::FISHERMAN)) {
                                require("fisherman-menu.php");
                            } ?>

                            <?php if (UserTypeUtil::hasType(Constant::ITU)) {
                                require("itu_menu.php");
                            } ?>
                            <?php if (UserTypeUtil::hasType(Constant::EXPORT_COMPANY)) {
                                require("special-license-menu.php");
                            } ?>

                            <?php if (UserTypeUtil::hasType(Constant::REPORT_OFFICER)) {
                                require("report-officer-menu.php");
                            } ?>

                            <?php
                            if
                            (
                                UserTypeUtil::hasType(Constant::HARBOUR_OFFICER) ||
                                Yii::$app->user->can("DepartureController") &&
                                Yii::$app->user->identity->profile_id != 0
                            ) {
                                require("departure_menu.php");
                            } ?>
                            <?php if (UserTypeUtil::hasType(Constant::ALTER)) { ?>
                                <?php if (
                                    UserTypeUtil::hasType(Constant::ALTER) &&
                                    Yii::$app->user->identity->profile_id != 0
                                ) { ?>

                                    <li class="nav-item ">
                                        <a class="nav-link " href="<?= $webURL ?>/boat-registration-special/index"><i
                                                class="fa fa-fw fa-user-circle"></i><?= Yii::t('app', 'Boat Registration - Alter') ?>

                                        </a>
                                    </li>
                                <?php } ?>    <?php } ?>
                            <?php if (UserTypeUtil::hasType(Constant::VMS_PAYMENT_OFFICER_SECONDARY)) { ?>
                                <?php if (
                                    UserTypeUtil::hasType(Constant::VMS_PAYMENT_OFFICER_SECONDARY) &&
                                    Yii::$app->user->identity->profile_id != 0
                                ) {
                                    require("vms_payment_menu.php");
                                    ?>

                                <?php } ?>    <?php } ?>
                            <?php
                            if (
                                (UserTypeUtil::hasType(Constant::EXPORT_FISH_DATA_COLLECTOR_MAIN) ||
                                    UserTypeUtil::hasType(Constant::EXPORT_FISH_DATA_COLLECTOR_SECONDARY))
                                && Yii::$app->user->identity->profile_id != 0
                            ) {
                                require("export-fish-data-collector-menu.php");
                            }
                            ?>
                            <?php if (
                                UserTypeUtil::hasType(Constant::CALL_SIGN) &&
                                Yii::$app->user->identity->profile_id != 0
                            ) { ?>

                                <li class="nav-item ">
                                    <a class="nav-link " href="<?= $webURL ?>/boat-registration/index"><i
                                            class="fa fa-fw fa-user-circle"></i><?= Yii::t('app', 'Boat Registration - Alter') ?>

                                    </a>
                                </li>
                            <?php } ?>

                            <?php if (
                                UserTypeUtil::hasType(Constant::MANAGEMENT) &&
                                Yii::$app->user->identity->profile_id != 0
                            ) { ?>
                                <li class="nav-item ">
                                    <a class="nav-link" href="<?= $webURL ?>/scientific/index">
                                        <i class="fa fa-fw fa-user-circle"></i><?= Yii::t('app', 'Scientific Inspections') ?>
                                    </a>
                                </li>

                                <li class="nav-item ">
                                    <a class="nav-link" href="<?= $webURL ?>/boat-numbers/index">
                                        <i class="fa fa-fw fa-user-circle"></i><?= Yii::t('app', 'Boat numbers') ?>
                                    </a>
                                </li>


                            <?php } ?>


                            <?php if (
                                UserTypeUtil::hasType(Constant::ITD) &&
                                Yii::$app->user->identity->profile_id != 0
                            ) {
                                require("itd-menu.php");
                            } ?>

                            <?php if (UserTypeUtil::hasType(Constant::PRINT_OFFICER)) {
                                require("print-officer-menu.php");
                            } ?>
                            <?php if (UserTypeUtil::hasType(Constant::EXPORTER)) {
                                require("exporter-menu.php");
                            } ?>
                            <?php if (UserTypeUtil::hasType(Constant::QUALITY_EXPORT_OFFICER)) {
                                require("quality-export-officer-menu.php");
                            } ?>


                            <!--                            --><?php //if (UserTypeUtil::hasType(Constant::DFI) {
                                //                                require("dfi-menu.php");
                                //                            } ?>
                            <!--                            --><?php //if (!UserTypeUtil::hasType
                                //(Constant::FISHERMAN) && Constant::FI && Yii::$app->user->identity->profile_id != 0) {
                                //                                require("officer-menu.php");
                                //                            } ?>

                            <li class="nav-item ">
                                <a class="nav-link">

                                </a>
                            </li>
                            <li class="nav-item ">
                                <a class="nav-link">

                                </a>
                            </li>
                        </ul>
                    </div>
                </nav>
            </div>
        </div>
        <!-- ============================================================== -->
        <!-- end left sidebar -->


        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- wrapper  -->
        <!-- ============================================================== -->
        <div class="dashboard-wrapper">
            <div class="dashboard-ecommerce">

                <div class="container-fluid  dashboard-content">
                    <!-- ============================================================== -->
                    <!-- pageheader -->
                    <!-- ============================================================== -->
                    <div class="row">
                        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                            <div class="page-header">
                                <?php if (!empty($profile) && $profile->force_reset_pw == 1) { ?>
                                    <div class="alert alert-danger">
                                        <strong>🚨 Notice 🚨 </strong> </br>
                                        <UL>
                                            <LI>To help protect your account from unauthorized access, please update
                                                your password as soon as possible. <a href="<?= $webURL
                                                    ?>/officer/password-update"> Click here to
                                                    change your password.</a>
                                            </LI>
                                            <LI>ඔබගේ ගිණුමට අනවසර පිවිසුම් වලක්වා ගැනීමට, කරුණාකර හැකි ඉක්මණින් ඔබේ
                                                මුරපදය යාවත්කාලීන කරන්න. <a href="<?= $webURL
                                                    ?>/officer/password-update"> මුරපදය වෙනස් කිරීමට
                                                    මෙහි ක්ලික්
                                                    කරන්න.</a>
                                            </LI>
                                        </UL>

                                    </div>
                                <?php } ?>
                                <?php if (date("Y-m-d") < "2026-07-16") { ?>
                                    <div class="alert alert-info">
                                        <strong>🚨 Notice 🚨 </strong> </br>
                                        <UL>
                                            <li>
                                                <strong>Notice:</strong> A major update has been deployed to the <strong>MSDFAR
                                                    System</strong> on
                                                <strong>12 July 2026</strong>. Users may notice changes to the approval workflow
                                                and other system features.
                                                If you experience any issues or require assistance while using the system,
                                                please contact the IT Division.
                                            </li>

                                            <li>
                                                <strong>දැනුම්දීම:</strong> <strong>2026 ජූලි 12</strong> දින <strong>MSDFAR
                                                    පද්ධතිය</strong> සඳහා
                                                ප්‍රධාන යාවත්කාලීන කිරීමක් සිදු කර ඇත. අනුමත කිරීමේ ක්‍රියාවලිය සහ අනෙකුත්
                                                පද්ධති ක්‍රියාකාරකම්වල
                                                වෙනස්කම් ඔබට දැකිය හැක. පද්ධතිය භාවිතා කිරීමේදී කිසියම් ගැටලුවක් ඇතිවුවහොත් හෝ
                                                සහාය අවශ්‍ය නම්,
                                                කරුණාකර තොරතුරු තාක්ෂණ (IT) අංශය අමතන්න.
                                            </li>
                                        </UL>

                                    </div>
                                <?php } ?>

                                <?php if (date("Y-m-d") < "2026-08-05") { ?>
                                    <div class="alert alert-info">
                                        <strong>🚨 Important Notice 🚨</strong><br><br>

                                        <ul>
                                            <li>
                                                <strong>Notice:</strong> A <strong>major cyber security update</strong> is
                                                currently being implemented in the
                                                <strong>MSDFAR System</strong>. As part of this update, users may experience
                                                changes to the approval workflow
                                                and other system features. If you encounter any issues or require assistance,
                                                please contact IT Division.
                                            </li>

                                            <li>
                                                <strong>දැනුම්දීම:</strong> <strong>MSDFAR පද්ධතිය</strong> සඳහා
                                                <strong>ප්‍රධාන සයිබර් ආරක්ෂක ( Cyber Security ) යාවත්කාලීන කිරීමක්</strong> මේ
                                                වන විට ක්‍රියාත්මක වෙමින් පවතී.
                                                මෙම යාවත්කාලීන කිරීමේ කොටසක් ලෙස අනුමත කිරීමේ ක්‍රියාවලිය සහ අනෙකුත් පද්ධති
                                                ක්‍රියාකාරකම්වල
                                                වෙනස්කම් ඔබට දැකිය හැක. පද්ධතිය භාවිතා කිරීමේදී කිසියම් ගැටලුවක් ඇතිවුවහොත් හෝ
                                                සහාය අවශ්‍ය නම්,
                                                තොරතුරු තාක්ෂණ අංශය අමතන්න
                                            </li>
                                        </ul>

                                    </div>
                                <?php } ?>
                                <h2 class="pageheader-title"><?= $this->title ?> </h2>
                                <div class="page-breadcrumb">
                                    <nav aria-label="breadcrumb">
                                        <?= Breadcrumbs::widget([
                                            'itemTemplate' => '<li class="breadcrumb-item">{link}</li>',
                                            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
                                            // 'options' => ['class'=>"breadcrumb-link"],
                                        ]) ?>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- ============================================================== -->
                    <!-- end pageheader -->


                    <!-- <div class="container"> -->

                    <div class="row">


                    <?php } ?>
                    <div class="col-lg-12">
                        <?= Alert::widget() ?>
                    </div>
                    <?= $content ?>


                    <?php if (!Yii::$app->user->isGuest) { ?>
                        <!-- </div> -->
                        <!--                            </div>-->

                        <!-- ============================================================== -->
                        <!-- end basic table  -->
                        <!-- ============================================================== -->
                    </div>
                </div>

            </div>
            <div class="footer">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                            2024 © IT division - Designed and Developed by<a
                                href="https://www.fisheriesdept.gov.lk/software-development-unit/" target="_blank"
                                class="ml-1">IT Division, Department of
                                Fisheries and Aquatic Resources, Sri Lanka.</a>.
                        </div>

                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
</div>

<?php $this->endBody() ?>

</html>
<?php $this->endPage() ?>