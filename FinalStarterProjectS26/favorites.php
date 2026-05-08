<?php
require_once 'config/config.php';
require_once 'includes/auth.php';
require_once 'classes/RealEstateDatabase.php';

requireRole(['buyer', 'renter']);
$propertyId = isset($_POST['propertyId'])
    ? (int)$_POST['propertyId']
    : 0;

if ($propertyId <= 0) {
    header('Location: properties.php');
    exit;
}

$db = new RealEstateDatabase();

try {
$db->addFavorite(
        (int)$_SESSION['user']['userId'],
        $propertyId
    );

    header('Location: property_details.php?id=' . $propertyId . '&saved=1');
    exit;

} catch (Throwable $e) {

    die(
        'Unable save favorite: ' .
        htmlspecialchars($e->getMessage())
    );
}
?>    