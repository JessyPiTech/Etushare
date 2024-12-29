<?php
// Gestion centralier des message d'erreur
const ERROR_MESSAGES = [
    'INVALID_METHOD' => 'Méthode non autorisée',
    'INVALID_JSON' => 'JSON invalide',
    'UNSUPPORTED_CONTENT_TYPE' => 'Content-type non supporté',
    'NO_ACTION' => 'Action non spécifiée',
    'INVALID_ACTION' => 'Action invalide',
    'INVALID_PARAM' => 'Paramètre invalide',
    'UPLOAD_FAILED' => 'Erreur lors de l\'upload du fichier',
    'FILE_TOO_LARGE' => 'Le fichier est trop grand',
    'INVALID_FILE_FORMAT' => 'Format de fichier non supporté',
    'INVALID_TRANSFER_ACTION' => 'Action de transfert invalide',
    'MISSING_PARAMETERS' => 'Paramètres manquants',
    'INVALID_NOTIFICATION_TYPE' => 'Type de notification invalide',
    'FRIEND_REQUEST_FAILED' => 'Échec de la création de la demande d\'ami',
    'PARTICIPANT_REQUEST_FAILED' => 'Échec de l\'ajout du participant',
    'CONVERSION_FAILED' => 'Erreur lors de la conversion de l\'image en WebP.'
];


function sendErrorResponse($code, $message) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode(['error' => $message]);
    exit;
}
?>