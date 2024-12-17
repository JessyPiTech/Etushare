<?php
session_start();

require_once __DIR__ . "/../config/coDB.php";
require_once __DIR__ . "/../class/User.php"; 
require_once __DIR__ . "/../class/Friend.php";  
require_once __DIR__ . "/../class/Annonce.php";  
require_once __DIR__ . "/../class/Auth.php";
require_once __DIR__ . "/../class/Notification.php";
require_once __DIR__ . "/../class/Comment.php";
require_once __DIR__ . "/../class/Image.php";
require_once __DIR__ . "/../class/Like.php";
require_once __DIR__ . "/../class/Participant.php";

// Uniformisation des messages d'erreur
const ERROR_MESSAGES = [
    'INVALID_METHOD' => 'Méthode non autorisée',
    'INVALID_JSON' => 'JSON invalide',
    'UNSUPPORTED_CONTENT_TYPE' => 'Content-type non supporté',
    'NO_ACTION' => 'Action non spécifiée',
    'INVALID_ACTION' => 'Action invalide'
];

// Gestion etap par etap
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


function parseInput() {
    $contentType = $_SERVER["CONTENT_TYPE"] ?? '';
    
    if (strpos($contentType, "application/json") !== false) {
        $input = json_decode(file_get_contents("php://input"), true);
        if (!$input) {
            throw new Exception(ERROR_MESSAGES['INVALID_JSON']);
        }
    } elseif (strpos($contentType, "multipart/form-data") !== false) {
        $input = $_POST;
        if (!empty($_FILES)) {
            $input['files'] = $_FILES;
        }
    } else {
        throw new Exception(ERROR_MESSAGES['UNSUPPORTED_CONTENT_TYPE']);
    }

    if (!isset($input["action"])) {
        throw new Exception(ERROR_MESSAGES['NO_ACTION']);
    }

    return $input;
}

// Routage requete
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
        "Image" => 'handle_annonce_image_action',
        "Like" => 'handle_annonce_like_action',
        "Participant" => 'handle_annonce_participant_action',
        "Transfer" => 'handle_transfer_action'
    ];

    $handler = $handlers[$part[0]] ?? null;

    if ($handler && function_exists($handler)) {
        $handler($conn, $part, $input);
    } else {
        throw new Exception(ERROR_MESSAGES['INVALID_ACTION']);
    }
}


function handle_user_action($conn, $part, $input) {
    $user = new User($conn);
    switch ($part[1]) {
        case "Create":
            if (isset($_FILES['user_image'])) {
                $imagePath = handle_image_upload($_FILES['user_image']);
                if (!$imagePath) {
                    http_response_code(500);
                    echo json_encode(["error" => "echec upload image"]);
                    exit;
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
                    http_response_code(500);
                    echo json_encode(["error" => "echec upload image"]);
                    exit;
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
                $friendStatus = $friend->checkFriendStatus($input['current_user_id'], $input['user_id']);

                echo json_encode([
                    'user' => $userDetails,
                    'annonces' => $userAnnonces,
                    'friendStatus' => $friendStatus
                ]);
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['error' => $e->getMessage()]);
            }
            break;
        default:
            http_response_code(400);
            echo json_encode(["error" => "action utilisateur invalide"]);
            exit;
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
            http_response_code(400);
            echo json_encode(["error" => "action auth invalide"]);
            exit;
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
        default:
            http_response_code(400);
            echo json_encode(["error" => "action ami invalide"]);
            exit;
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
                http_response_code(500);
                echo json_encode(['error' => $e->getMessage()]);
            }
            break;
        case "Detail":
        
            try {
                $annonceDetails = $annonce->getAnnonceById($conn, $input['annonce_id']);        
                echo json_encode(['annonce' => $annonceDetails]);
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['error' => $e->getMessage()]);
            }
            break;
        default:
            http_response_code(400);
            echo json_encode(["error" => "action annonce invalide"]);
            exit;
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

                // New: Fetch notifications
                $notifications =  $notif->fetchUserNotifications($conn, $input['user_id']);

                echo json_encode([
                    'allAnnonces' => $allAnnonces,
                    'categoryAnnonces' => $categoryAnnonces,
                    'likeAnnonces' => $likeAnnonces,
                    'participantAnnonces' => $participantAnnonces,
                    'notifications' => $notifications
                ]);
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['error' => $e->getMessage()]);
                exit;
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
                                $totalCount = $participant->countItems('annonce_participant', $input['user_id'] );
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
                                    'totalCount' => $totalCount]);
                                break;
                            default:
                                http_response_code(400);
                                echo json_encode(['error' => 'action invalide pour more > annonce']);
                                exit;
                        }
                    } catch (Exception $e) {
                        http_response_code(500);
                        echo json_encode(['error' => $e->getMessage()]);
                        exit;
                    }
                    break;

                default:
                    http_response_code(400);
                    echo json_encode(['error' => 'action invalide more']);
                    exit;
            }
            break;

        default:
            http_response_code(400);
            echo json_encode(["error" => "action invalide dashboard"]);
            exit;
    }
}
//-------------------------------







function handle_notification_action($conn, $part, $input) {
    $notif = new Notification($conn);
    switch ($input['notification_type']) {
        case 'friend_request':
            if (!isset($input['notification_id'])) {
                $createQuery = "INSERT INTO user_friend (user_id_1, user_id_2, user_friend_notif) VALUES (?, ?, 1)";
                $stmt = $conn->prepare($createQuery);
                $stmt->bind_param('ii', $input['sender_id'], $input['receiver_id']);
                $stmt->execute();
                echo json_encode(['success' => true]);
            
            }else{
                $notif->handleFriendRequestNotification($conn, $input);
            }
            break;
        case 'like':
            $notif->handleLikeNotification($conn, $input);
            break;
        case 'participant':
            if (!isset($input['notification_id'])) {
                $createQuery = "INSERT INTO annonce_participant (user_id, annonce_id, annonce_participant_user_id_2, annonce_participant_status, annonce_participant_notif) VALUES (?, ?, ?, 'pending', 1)";
                $stmt = $conn->prepare($createQuery);
                $stmt->bind_param('iii', $input['sender_id'], $input['annonce_id'], $input['receiver_id']);
                $stmt->execute();
                echo json_encode(['success' => true]);
            } else {
                $notif->handleParticipantNotification($conn, $input);
            }
            break;
        default:
            http_response_code(400);
            echo json_encode(["error" => "Type de notification invalide"]);
            exit;
    }
}




//-------------------------



function handle_recherche_action($conn, $part, $input) {
    $annonce = new Annonce($conn);

    switch ($part[1]) {
        case "Load":
            try {
                $allAnnonces = $annonce->getAnnonces($conn, 'all',$input, 5, 0);
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
                http_response_code(500);
                echo json_encode(['error' => $e->getMessage()]);
                exit;
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
                                echo json_encode(['participantAnnonces' => $participantAnnonces,]);
                                break;
                            default:
                                http_response_code(400);
                                echo json_encode(['error' => 'action invalide pour more > annonce']);
                                exit;
                        }
                    } catch (Exception $e) {
                        http_response_code(500);
                        echo json_encode(['error' => $e->getMessage()]);
                        exit;
                    }
                    break;

                default:
                    http_response_code(400);
                    echo json_encode(['error' => 'action invalide more']);
                    exit;
            }
            break;

        default:
            http_response_code(400);
            echo json_encode(["error" => "action invalide dashboard"]);
            exit;
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
            http_response_code(400);
            echo json_encode(["error" => "action commentaire invalide"]);
            exit;
    }
    exit;
}


function handle_annonce_image_action($conn, $part, $input) {
    $Image = new Image($conn);
    switch ($part[1]) {
        case "Create":
            $Image->create($input);
            break;
        case "Delete":
            $Image->delete('image', 'image_id', $input);
            break;
        case "Update":
            $Image->update($input);
            break;
        default:
            http_response_code(400);
            echo json_encode(["error" => "action image annonce invalide"]);
            exit;
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
            $count = $Like->countItems('annonce_like' , $input['user_id']);
            echo json_encode(['count' => $count]);
            break;
        default:
            http_response_code(400);
            echo json_encode(["error" => "action like annonce invalide"]);
            exit;
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
        default:
            http_response_code(400);
            echo json_encode(["error" => "action participant annonce invalide"]);
            exit;
    }
    exit;
}


function handle_transfer_action($conn, $part, $input) {
    $transfere = new Transfere($conn);
    switch ($part[1]) {
        case "Validate":
            $result = $transfere->validateTransfer($conn, $input['transfer_id']);
            echo json_encode(['success' => $result]);
            break;
        case "Reject":
            $result = $transfere->rejectTransfer($conn, $input['transfer_id']);
            echo json_encode(['success' => $result]);
            break;
        case "Participants":
            $participants = $transfere->fetchValidatedParticipants($conn, $input['annonce_id'], $input['user_id']);
            echo json_encode($participants);
            break;
        default:
            http_response_code(400);
            echo json_encode(["error" => "Invalid transfer action"]);
            exit;
    }
    exit;
}


function handle_image_upload($file, $target_dir = "../upload/") {
    //recu = fichier, renvoi = url

    if (filesize($file['tmp_name']) > 1000000) {
        http_response_code(400);
        echo json_encode(["error" => "fichier trop lourd"]);
        return false;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        http_response_code(500);
        echo json_encode(["error" => "erreur lors de l'upload", "details" => $file['error']]);
        return false;
    }


    $allowed_types = ["jpg", "png", "jpeg", "gif"];
    $imageFileType = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($imageFileType, $allowed_types)) {
        http_response_code(400);
        echo json_encode(["error" => "format de fichier non supporté", "details" => $imageFileType]);
        return false;
    }

    $unique_name = uniqid() . '.' . $imageFileType;
    $target_file = $target_dir . $unique_name;
    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        return substr($target_file, 1);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "erreur inconnue lors du déplacement du fichier"]);
        return false;
    }
}

function sendErrorResponse($code, $message) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode(['error' => $message]);
    exit;
}
// Exécution du point d'entrée
handleRequest();