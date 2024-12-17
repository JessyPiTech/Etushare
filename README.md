Voici un modèle structuré pour ton fichier **README.md** que tu pourras inclure dans ton projet. Il présente clairement les fonctionnalités, technologies utilisées et le contexte du projet.

---

#  **Projet Etushare: Application de Gestion d'Échanges de Services entre Étudiants**

##  **Description du Projet**

Ce projet consiste à développer une **application web** qui facilite les échanges de services entre étudiants. L'application propose un système d'annonces pour les services, les offres d'aide et de prêt, avec un système de points pour récompenser et équilibrer les transactions.

###  **Objectif Principal**

Créer une plateforme similaire au concept de **Leboncoin**, mais centrée sur les services entre étudiants : aide, prêt d'objets, etc.

---

## 🛠️ **Technologies Utilisées**

### **Backend**
Développement de **microservices** en **PHP** :  
1. **Authentification** : Gestion des utilisateurs et de la sécurité (inscription, connexion).  
2. **Annonces** : Publication, consultation et modification d'annonces d’aide, de services et de prêts.  
3. **Transfert de Points** : Système pour transférer et gérer les points entre utilisateurs.  
4. **Gestion des Transactions** : Suivi des transactions et des échanges entre utilisateurs.  
5. **Commentaires** : Création de commentaires/avis suite à un service ou une annonce.  
6. **Friends** : Création et gestion des relations entre utilisateurs (amis).  
7. **Gestion d'Images** : Gestion de l'upload et du stockage des images pour les annonces.  
8. **Likes** : Ajout de likes pour sauvegarder des annonces en favoris.  
9. **Notifications** : Système de gestion des notifications pour informer des actions comme demandes d'amis, nouvelles annonces, etc.  
10. **Participants** : Gestion des participants intéressés par une annonce.  
11. **Utilisateurs** : Gestion des informations des utilisateurs (mise à jour du profil, suppression, etc.).  




### **Base de Données (BDD)**
- Utilisation d'une base de données **SQL** pour stocker et organiser les informations provenant des **microservices** :  

1. **Table Utilisateurs**  
   - Stockage des données utilisateurs (nom, email, mot de passe hashé, date d'inscription, etc.).  
   - Gestion des relations (amis).  
2. **Table Annonces**  
   - Stockage des annonces (titre, description, type d’annonce : aide/service/prêt, utilisateur propriétaire, image associée, statut, etc.).  
3. **Table Transactions**  
   - Enregistrement des transactions réalisées entre utilisateurs.  
   - Historique des transferts de points.  
4. **Table Transferts_Points**  
   - Gestion des points échangés entre utilisateurs (expéditeur, receveur, montant des points, date).  
5. **Table Commentaires**  
   - Enregistrement des avis et commentaires (contenu, auteur, date de création, annonce concernée).  
6. **Table Amis**  
   - Stockage des relations d’amitié (ID utilisateur A, ID utilisateur B, statut de la relation, date de création).  
7. **Table Images**  
   - Informations sur les images uploadées pour les annonces (chemin d’accès, annonce associée, date de création).  
8. **Table Likes**  
   - Stockage des annonces likées par les utilisateurs pour les ajouter aux favoris.  
9. **Table Notifications**  
   - Gestion des notifications pour les utilisateurs (type : demande d'amis, nouvelles annonces, etc., ID utilisateur concerné, statut lu/non-lu).  
10. **Table Participants**  
    - Stockage des informations des participants à une annonce (utilisateur, annonce, date d'inscription comme participant).  

Voici une version corrigée et améliorée de la section **API REST** pour assurer une meilleure précision, clarté et exhaustivité dans la rédaction :  

---

### **API REST**  
Les **microservices** communiquent entre eux à travers des API REST qui suivent les principes RESTful pour assurer la modularité et l'interopérabilité :  

1. **Authentification et Gestion des Sessions**  
   - **Endpoints** pour :  
     - **Inscription** d'un nouvel utilisateur.  
     - **Connexion** avec génération et gestion de tokens (JWT).  
     - **Déconnexion** et invalidation des tokens.  
   - Sécurisation des routes grâce à des middlewares d'authentification.  

2. **Gestion des Annonces**  
   - **Endpoints CRUD** pour :  
     - **Créer** une nouvelle annonce.  
     - **Récupérer** une ou plusieurs annonces (avec filtrage, pagination, etc.).  
     - **Modifier** une annonce existante.  
     - **Supprimer** une annonce.  
   - Upload et gestion des **images associées** aux annonces.  

3. **Transfert et Gestion des Points**  
   - **Endpoints** pour :  
     - **Transférer des points** entre utilisateurs (vérification des soldes, historique).  
     - **Consulter le solde** actuel de points d'un utilisateur.  
     - **Récupérer l'historique des transferts**.  

4. **Gestion des Transactions**  
   - **Endpoints** pour :  
     - **Créer** une nouvelle transaction liée à un échange ou transfert.  
     - **Suivre l'historique des transactions** entre utilisateurs.  

5. **Commentaires et Avis**  
   - **Endpoints** pour :  
     - **Publier un commentaire** ou un avis sur une annonce ou un service.  
     - **Récupérer** les commentaires associés à une annonce.  
     - **Supprimer** un commentaire existant (autorisation requise).  

6. **Gestion des Amis**  
   - **Endpoints** pour :  
     - **Envoyer, accepter ou refuser** une demande d'ami.  
     - **Lister les amis** d’un utilisateur.  
     - **Supprimer** une relation d’amitié.  

7. **Gestion des Likes (Favoris)**  
   - **Endpoints** pour :  
     - **Liker/Déliker** une annonce pour l'ajouter ou la retirer des favoris.  
     - **Récupérer les annonces likées** par un utilisateur.  

8. **Gestion des Notifications**  
   - **Endpoints** pour :  
     - **Créer** et envoyer une notification (exemple : nouvelle annonce, demande d'ami, etc.).  
     - **Récupérer** les notifications d'un utilisateur avec statut (lues/non-lues).  
     - **Mettre à jour** le statut d'une notification (passage à "lu").  

9. **Participants aux Annonces**  
   - **Endpoints** pour :  
     - **Inscrire un utilisateur** comme participant à une annonce.  
     - **Lister les participants** d'une annonce.  
     - **Supprimer** un utilisateur de la liste des participants.  

10. **Gestion des Utilisateurs**  
    - **Endpoints** pour :  
      - **Consulter, mettre à jour** ou **supprimer** le profil d'un utilisateur.  
      - **Récupérer les informations publiques** d’autres utilisateurs.  
 
---

## 🔒 **Sécurité et Gestion des Sessions**
1. **Sessions** : Gestion de la connexion utilisateur et maintien des informations d'authentification.
2. **Tokens** : Utilisés pour sécuriser chaque appel aux microservices (authentification par token JWT).
3. **Cookies** : Sauvegarde locale des informations nécessaires pour simplifier l'expérience utilisateur.

---


## 🌐 **Architecture de l’Application**

L'application repose sur une architecture **microservices** centralisée avec une **API Gateway** pour :
- Simplifier et centraliser les appels API vers les microservices.
- Optimiser la gestion de la charge.

---


## 📋 **Fonctionnalités Principales**  

1. **Inscription et Authentification des Utilisateurs**  
   - Création de comptes utilisateurs sécurisés.  
   - Connexion avec gestion des sessions et génération de tokens.  

2. **Publication et Gestion des Annonces**  
   - Création d’annonces pour l’aide, les services ou le prêt d’objets.  
   - Modification et suppression des annonces publiées.  

3. **Consultation et Recherche des Annonces**  
   - Affichage des annonces avec options de **filtrage**, **tri** et **pagination**.  
   - Fonctionnalité de recherche par mots-clés et catégories.  

4. **Gestion des Points d’Échange**  
   - Gagner des points grâce aux actions réalisées (ex. services rendus).  
   - Transférer et dépenser des points pour accéder à certains services ou objets.  

5. **Sécurisation des Requêtes**  
   - Protection des API grâce à des tokens (JWT) et gestion des sessions sécurisées.  
   - Chiffrement des données sensibles pour garantir la sécurité.  

6. **Interface Utilisateur Moderne et Dynamique**  
   - Design intuitif et réactif pour une expérience utilisateur optimale.  
   - Affichage adaptatif sur mobile, tablette et ordinateur.  

7. **Tableau de Bord Administratif (Back-Office)**  
   - Gestion des utilisateurs, des annonces et des transactions.  
   - Surveillance des activités pour maintenir la qualité et la sécurité de la plateforme.  


---

Voici une version corrigée et légèrement améliorée pour plus de clarté et de précision :  

---

## 🗂️ **Structure du Projet**  

```plaintext
Etushare/
│
├── backend/                    # Dossier contenant les microservices du backend
│   ├── auth-service/           # Microservice pour l'authentification et la gestion des utilisateurs
│   ├── ads-service/            # Microservice pour la gestion des annonces
│   ├── points-service/         # Microservice pour la gestion des points d'échange
│   └── ...                     # Autres microservices si nécessaires
│
├── frontend/                   # Code source de l’interface utilisateur
│   ├── src/                    # Dossiers des composants et ressources sources
│   ├── public/                 # Fichiers statiques accessibles au public
│   └── ...                     # Autres configurations (styles, assets, etc.)
│
├── api-gateway/                # Configuration de l'API Gateway pour unifier l'accès aux microservices
│
├── database/                   # Scripts SQL pour créer, initialiser et gérer la base de données
│
└── README.md                   # Documentation principale du projet
```  


---

## 💻 **Pré-requis pour Exécuter le Projet**

1. **Serveur Local** : PHP (>= 7.x), Apache ou Nginx.
2. **Base de Données** : MySQL ou MariaDB.

---

## 🚀 **Instructions d'Installation**

### **1. Cloner le Projet**
```bash
git clone https://github.com/JessyPiTech/Etushare.git
cd Etushare
```

### **2. Configuration Backend**
- Assurez-vous de configurer les fichiers `.env` dans chaque microservice avec vos informations de base de données.


## 📸 **Aperçu**

🚧 **Capture d'écran à venir**.

---

## 👨‍💻 **Contributeurs**
- **[JessyPiTech]** - Développeur Principal.
- **[JessyPiTech]** - Développeur Principal.

---

## 📜 **Licence**
Ce projet est sous licence **MIT**.

---

## ✅ **Conclusion**

Cette application apporte une solution pratique et sécurisée pour favoriser les échanges de services entre étudiants, tout en utilisant une architecture moderne basée sur des microservices. 
