<?php
session_start();

require_once __DIR__ . "/../config/coDB.php";
require_once __DIR__ . "/../class/User.php"; 
require_once __DIR__ . "/../class/Friend.php";  
require_once __DIR__ . "/../class/Annonce.php";  
require_once __DIR__ . "/../class/Auth.php";
require_once __DIR__ . "/../class/Notification.php";
require_once __DIR__ . "/../class/Comment.php";

require_once __DIR__ . "/../class/Like.php";
require_once __DIR__ . "/../class/Participant.php";
require_once __DIR__ . "/../class/Transfere.php";

// Uniformisation erreur
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
    'PARTICIPANT_REQUEST_FAILED' => 'Échec de l\'ajout du participant'
];

// Gestion centralier des message d'erreur
function sendErrorResponse($code, $message) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode(['error' => $message]);
    exit;
}

// Analyser les données d'entrée
function parseInput() {
    $contentType = $_SERVER["CONTENT_TYPE"] ?? '';
    
    if (strpos($contentType, "application/json") !== false) {
        $input = json_decode(file_get_contents("php://input"), true);
        if (!$input) {
            sendErrorResponse(400, ERROR_MESSAGES['INVALID_JSON']);
        }
    } elseif (strpos($contentType, "multipart/form-data") !== false) {
        $input = $_POST;
        if (!empty($_FILES)) {
            $input['files'] = $_FILES;
        }
    } else {
        sendErrorResponse(415, ERROR_MESSAGES['UNSUPPORTED_CONTENT_TYPE']);
    }

    if (!isset($input["action"])) {
        sendErrorResponse(400, ERROR_MESSAGES['NO_ACTION']);
    }

    return $input;
}

// Gestion de l'upload d'image
function handle_image_upload($file, $target_dir = "../upload/") {
    if (filesize($file['tmp_name']) > 1000000) {
        sendErrorResponse(400, ERROR_MESSAGES['FILE_TOO_LARGE']);
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        sendErrorResponse(500, ERROR_MESSAGES['UPLOAD_FAILED']);
    }

    $allowed_types = ["jpg", "png", "jpeg", "gif"];
    $imageFileType = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($imageFileType, $allowed_types)) {
        sendErrorResponse(400, ERROR_MESSAGES['INVALID_FILE_FORMAT']);
    }

    $unique_name = uniqid() . '.' . $imageFileType;
    $target_file = $target_dir . $unique_name;
    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        return substr($target_file, 1);
    } else {
        sendErrorResponse(500, "Erreur inconnue lors du déplacement du fichier");
    }

    return false;
}

// Gestion requete etap par etape
function handleRequest() {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        sendErrorResponse(405, ERROR_MESSAGES['INVALID_METHOD']);
    }
    
    $conn = coDB();
    
    try {
        $input = parseInput();
        process_post_requests($conn, $input);
    } catch (Exception $e) {
        sendErrorResponse(500, $e->getMessage());
    } finally {
        $conn->close();
    }
}

// Routage des requets
function process_post_requests($conn, $input) {
    $part = explode('_', $input["action"]);
    $handlers = [
        "User" => 'handle_user_action',
        "Auth" => 'handle_auth_action',
        "Friend" => 'handle_friend_action',
        "Annonce" => 'handle_annonce_action',
        "Dashboard" => 'handle_dashboard_action',
        "Recherche" => 'handle_recherche_action',
        "Notification" => 'handle_notification_action',
        "Comment" => 'handle_comment_action',
        "Like" => 'handle_annonce_like_action',
        "Participant" => 'handle_annonce_participant_action',
        "Transfer" => 'handle_transfer_action'
    ];

    $handler = $handlers[$part[0]] ?? null;

    if ($handler && function_exists($handler)) {
        $handler($conn, $part, $input);
    } else {
        sendErrorResponse(400, ERROR_MESSAGES['INVALID_ACTION']);
    }
}

function handle_user_action($conn, $part, $input) {
    $user = new User($conn);
    switch ($part[1]) {
        case "Create":
            if (isset($_FILES['user_image'])) {
                $imagePath = handle_image_upload($_FILES['user_image']);
                if (!$imagePath) {
                    sendErrorResponse(500, "Échec de l'upload de l'image");
                }
                $input['user_image_profil'] = $imagePath;
            }
            $user->create($input);
            break;

        case "Delete":
            $user->delete('user', 'user_id', $input);
            break;
        
        case "Update":
            if (isset($_FILES['user_image'])) {
                $imagePath = handle_image_upload($_FILES['user_image']);
                if (!$imagePath) {
                    sendErrorResponse(500, "Échec de l'upload de l'image");
                }
                $input['user_image_profil'] = $imagePath;
            }
            $user->update($input);
            break;

        case "Detail":
            $annonce = new Annonce($conn);
            $friend = new Friend($conn);
    
            try {
                $userDetails = $user->getUserDetails($input['user_id']);
                $userAnnonces = $annonce->getUserAnnonce($conn, $input['user_id'], $input);
                $friendStatus = $friend->getFriendStatus($input['current_user_id'], $input['user_id']);
    
                echo json_encode([
                    'user' => $userDetails,
                    'annonces' => $userAnnonces,
                    'friendStatus' => $friendStatus
                ]);
            } catch (Exception $e) {
                sendErrorResponse(500, $e->getMessage());
            }
            break;

        default:
            sendErrorResponse(400, ERROR_MESSAGES['INVALID_ACTION']);
    }
    exit;
}

function handle_auth_action($conn, $part, $input) {
    $auth = new Auth($conn);
    switch ($part[1]) {
        case "Login":
            $auth->login($input);
            break;

        case "Logout":
            $auth->logout();
            break;

        default:
            sendErrorResponse(400, "Action auth invalide");
    }
    exit;
}

function handle_friend_action($conn, $part, $input) {
    $friend = new Friend($conn);
    switch ($part[1]) {
        case "Create":
            $friend->create($input);
            break;
        case "Delete":
            $friend->delete('user_friend', 'user_friend_id', $input);
            break;
        case "Update":
            $friend->update($input);
            break;
        case "Respond":
            $friend->createRespond($input);
            break;
        default:
            sendErrorResponse(400, ERROR_MESSAGES['INVALID_ACTION']);
    }
    exit;
}



function handle_annonce_action($conn, $part, $input) {
    $annonce = new Annonce($conn);
    switch ($part[1]) {
        case "Create":
            if (!empty($_FILES)) {
                $input['files'] = $_FILES;
            }
            $annonce->create($input);
            break;
        case "Delete":
            $annonce->delete('annonce', 'annonce_id', $input);
            break;
        case "Update":
            $annonce->update($input);
            break;
        case "Details":
            $like = new Like($conn);
            $participant = new Participant($conn);
        
            try {
                $annonceDetails = $annonce->getAnnonceById($conn, $input['annonce_id']);
                $likeCount = $like->countLikesForAnnonce($input['annonce_id']);
                $participantCount = $participant->countParticipantsForAnnonce($input['annonce_id']);
        
                echo json_encode([
                    'annonce' => $annonceDetails,
                    'likeCount' => $likeCount,
                    'participantCount' => $participantCount
                ]);
            } catch (Exception $e) {
                sendErrorResponse(500, $e->getMessage());
            }
            break;
        case "Detail":
            try {
                $annonceDetails = $annonce->getAnnonceById($conn, $input['annonce_id']);        
                echo json_encode(['annonce' => $annonceDetails]);
            } catch (Exception $e) {
                sendErrorResponse(500, $e->getMessage());
            }
            break;
        default:
            sendErrorResponse(400, ERROR_MESSAGES['INVALID_ACTION']);
    }
    exit;
}

function handle_dashboard_action($conn, $part, $input) {
    $annonce = new Annonce($conn);
    $like = new Like($conn);
    $participant = new Participant($conn);
    $notif = new Notification($conn);

    switch ($part[1]) {
        case "Load":
            try {
                $allAnnonces = $annonce->getAnnonces($conn, 'all', $input, 5, 0);
                $categoryAnnonces = $annonce->getAnnonces($conn, 'category', $input, 5, 0);
                $likeAnnonces = $annonce->getAnnonces($conn, 'like', $input, 5, 0);
                $participantAnnonces = $annonce->getAnnonces($conn, 'participant', $input, 5, 0);
                $notifications =  $notif->fetchUserNotifications($conn, $input['user_id']);

                echo json_encode([
                    'allAnnonces' => $allAnnonces,
                    'categoryAnnonces' => $categoryAnnonces,
                    'likeAnnonces' => $likeAnnonces,
                    'participantAnnonces' => $participantAnnonces,
                    'notifications' => $notifications
                ]);
            } catch (Exception $e) {
                sendErrorResponse(500, $e->getMessage());
            }
            break;

        case "More":
            switch ($part[2]) {
                case "Annonce":
                    try {
                        switch ($part[3]) {
                            case "Annonce":
                                $allAnnonces = $annonce->getAnnonces($conn, 'all', $input, 7, 5);
                                echo json_encode(['allAnnonces' => $allAnnonces]);
                                break;
                            case "Like":
                                $likeAnnonces = $annonce->getAnnonces($conn, 'like', $input, 7, 5);
                                $totalCount = $like->countItems('annonce_like', $input['user_id']);
                                echo json_encode([
                                    'likeAnnonces' => $likeAnnonces,
                                    'totalCount' => $totalCount
                                ]);
                                break;
                            case "Category":
                                $categoryAnnonces = $annonce->getAnnonces($conn, 'category', $input, 7, 5);
                                echo json_encode(['categoryAnnonces' => $categoryAnnonces]);
                                break;
                            case "Participant":
                                $participantAnnonces = $annonce->getAnnonces($conn, 'participant', $input, 7, 5);
                                $totalCount = $participant->countItems('annonce_participant', $input['user_id']);
                                echo json_encode([
                                    'participantAnnonces' => $participantAnnonces,
                                    'totalCount' => $totalCount
                                ]);
                                break;
                            case "MesAnnonces":
                                $mesAnnonces = $annonce->getUserAnnonce($conn, $input['user_id'], $input, 7, 5);
                                $totalCount = $like->countItems('annonce', $input['user_id']);
                                echo json_encode([
                                    'mesAnnonces' => $mesAnnonces,
                                    'totalCount' => $totalCount
                                ]);
                                break;
                            default:
                                sendErrorResponse(400, ERROR_MESSAGES['INVALID_ACTION']);
                        }
                    } catch (Exception $e) {
                        sendErrorResponse(500, $e->getMessage());
                    }
                    break;

                default:
                    sendErrorResponse(400, ERROR_MESSAGES['INVALID_ACTION']);
            }
            break;

        default:
            sendErrorResponse(400, ERROR_MESSAGES['INVALID_ACTION']);
    }
}






function handle_recherche_action($conn, $part, $input) {
    $annonce = new Annonce($conn);
    switch ($part[1]) {
        case "Load":
            try {
                $allAnnonces = $annonce->getAnnonces($conn, 'all', $input, 5, 0);
                $categoryAnnonces = $annonce->getAnnonces($conn, 'category', $input, 5, 0);
                $likeAnnonces = $annonce->getAnnonces($conn, 'like', $input, 5, 0);
                $participantAnnonces = $annonce->getAnnonces($conn, 'participant', $input, 5, 0);

                echo json_encode([
                    'allAnnonces' => $allAnnonces,
                    'categoryAnnonces' => $categoryAnnonces,
                    'likeAnnonces' => $likeAnnonces,
                    'participantAnnonces' => $participantAnnonces,
                ]);
            } catch (Exception $e) {
                sendErrorResponse(500, $e->getMessage());
            }
            break;

        case "More":
            switch ($part[2]) {
                case "Annonce":
                    try {
                        switch ($part[3]) {
                            case "Annonce":
                                $allAnnonces = $annonce->getAnnonces($conn, 'all', $input, 7, 5);
                                echo json_encode(['allAnnonces' => $allAnnonces]);
                                break;
                            case "Like":
                                $likeAnnonces = $annonce->getAnnonces($conn, 'like', $input, 7, 5);
                                echo json_encode(['likeAnnonces' => $likeAnnonces]);
                                break;
                            case "Category":
                                $categoryAnnonces = $annonce->getAnnonces($conn, 'category', $input, 7, 5);
                                echo json_encode(['categoryAnnonces' => $categoryAnnonces]);
                                break;
                            case "Participant":
                                $participantAnnonces = $annonce->getAnnonces($conn, 'participant', $input, 7, 5);
                                echo json_encode(['participantAnnonces' => $participantAnnonces]);
                                break;
                            default:
                                sendErrorResponse(400, ERROR_MESSAGES['INVALID_ACTION']);
                        }
                    } catch (Exception $e) {
                        sendErrorResponse(500, $e->getMessage());
                    }
                    break;

                default:
                    sendErrorResponse(400, ERROR_MESSAGES['INVALID_ACTION']);
            }
            break;

        default:
            sendErrorResponse(400, ERROR_MESSAGES['INVALID_ACTION']);
    }
}

function handle_comment_action($conn, $part, $input) {
    $comment = new Comment($conn);
    switch ($part[1]) {
        case "Create":
            $comment->create($input);
            break;
        case "Delete":
            $comment->delete('commantaire', 'commantaire_id', $input);
            break;
        case "Update":
            $comment->update($input);
            break;
        default:
            sendErrorResponse(400, ERROR_MESSAGES['INVALID_ACTION']);
    }
    exit;
}



function handle_annonce_like_action($conn, $part, $input) {
    $Like = new Like($conn);
    switch ($part[1]) {
        case "Create":
            $Like->create($input);
            break;
        case "Delete":
            $Like->delete('annonce_like', 'annonce_like_id', $input, true);
            break;
        case "Count":
            $count = $Like->countItems('annonce_like', $input['user_id']);
            echo json_encode(['count' => $count]);
            break;
        default:
            sendErrorResponse(400, ERROR_MESSAGES['INVALID_ACTION']);
    }
    exit;
}



function handle_transfer_action($conn, $part, $input) {
    $transfere = new Transfere($conn);
    switch ($part[1]) {
        case "Validate":
            if (!isset($input['transfer_id'])) {
                sendErrorResponse(400, ERROR_MESSAGES['MISSING_PARAMETERS']);
            }
            $transfere->validateTransfer($conn, $input['transfer_id']);
            break;

        case "Reject":
            if (!isset($input['transfer_id'])) {
                sendErrorResponse(400, ERROR_MESSAGES['MISSING_PARAMETERS']);
            }
            $transfere->rejectTransfer($conn, $input['transfer_id']);
            break;

        case "Participants":
            if (!isset($input['annonce_id'], $input['user_id'])) {
                sendErrorResponse(400, ERROR_MESSAGES['MISSING_PARAMETERS']);
            }
            $transfere->fetchValidatedParticipants($conn, $input['annonce_id'], $input['user_id']);
            break;

        default:
            sendErrorResponse(400, ERROR_MESSAGES['INVALID_TRANSFER_ACTION']);
    }
    exit;
}

function handle_annonce_participant_action($conn, $part, $input) {
    $Participant = new Participant($conn);
    switch ($part[1]) {
        case "Create":
            $Participant->create($input);
            break;
        case "Delete":
            $Participant->delete('annonce_participant', 'participant_id', $input, true);
            break;
        case "Count":
            $count = $Participant->countItems('annonce_participant', $input['user_id']);
            echo json_encode(['count' => $count]);
            break;
        case "Respond":
            $Participant->creatRespond($conn, $input);
            break;
        default:
            sendErrorResponse(400, ERROR_MESSAGES['INVALID_ACTION']);
    }
    exit;
}
function handle_notification_action($conn, $part , $input) {
    if (!isset($input['notification_id'], $input['notification_type'], $input['notification_action'])) {
        sendErrorResponse(400, ERROR_MESSAGES['MISSING_PARAMETERS']);
    }
    $notif = new Notification($conn);
    $part = explode('_', $input['notification_type']);
    switch ($part[0]) {
        case 'Friend':
            handle_friend_action($conn, $part,$input);
            break;

        case 'like':
            $notif->handleLikeNotification($conn, $input);
            break;

        case 'Participant':
            handle_annonce_participant_action($conn, $part, $input);
            break;

        default:
            sendErrorResponse(400,  ERROR_MESSAGES['INVALID_NOTIFICATION_TYPE']);
    }
}




handleRequest();
