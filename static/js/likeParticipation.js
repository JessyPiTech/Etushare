async function affichageInteraction(item, userId) {
    if (!item || !userId) {
        console.error("Invalid parameters passed to affichageLikeParticipation:", { item, userId });
        return document.createElement('div');
    }

    const div = document.createElement('div');
    div.classList.add('interaction-container');
    if (item.user_id == userId) {
        const modifyButton = document.createElement('button');
        modifyButton.textContent = "Modify";
        modifyButton.classList.add('modify-button');
        modifyButton.addEventListener('click', (e) => {
            e.stopPropagation();
            window.location.href = `modify-annonce.php?annonce_id=${item.annonce_id}`;
        });

        const deleteButton = document.createElement('button');
        deleteButton.textContent = "Delete";
        deleteButton.classList.add('delete-button');
        deleteButton.addEventListener('click', async (e) => {
            e.stopPropagation();
            if (confirm("Are you sure you want to delete this announcement ?")) {
                try {
                    const requestData = {
                        action: "Annonce_Delete",
                        annonce_id: item.annonce_id,
                        user_id: userId,
                    };
                    const response = await postData(apiUrl, requestData);
                    if (response.success) {
                        alert("Annonce deleted successfully!");
                        loadData(userId);
                    } else {
                        alert("Failed to delete the annonce.");
                    }
                } catch (error) {
                    console.error('Error deleting annonce:', error);
                }
            }
        });

        div.appendChild(modifyButton);
        div.appendChild(deleteButton);
    } else {
        try {
            const likeButton = document.createElement('button');
            likeButton.classList.add('like-button');
            likeButton.setAttribute('id', `like-${item.annonce_id}`);

            function updateLikeButton(isLiked) {
                if (isLiked) {
                    likeButton.innerHTML = '<ion-icon name="heart"></ion-icon> Dislike';
                    likeButton.classList.add('liked');
                } else {
                    likeButton.innerHTML = '<ion-icon name="heart-outline"></ion-icon> Like';
                    likeButton.classList.remove('liked');
                }
            }
            updateLikeButton(item.is_liked);

            likeButton.addEventListener('click', async () => {
                try {
                    likeButton.disabled = true;
                    const isCurrentlyLiked = likeButton.classList.contains('liked');
                    const response = isCurrentlyLiked
                        ? await suppLike(item.annonce_id, userId)
                        : await addLike(item.annonce_id, userId);

                    if (response && response.success) {
                        item.is_liked = !isCurrentlyLiked;
                        updateLikeButton(!isCurrentlyLiked);
                        updateItemDisplay(item, 'containerId');
                        loadMore(userId, currentType, `${currentType.toLowerCase()}-annonces`, currentPage);
                    } else {
                        console.error('Like action failed:', response);
                    }
                } catch (error) {
                    console.error('Error handling Like button:', error);
                } finally {
                    likeButton.disabled = false;
                }
            });

            const participateButton = document.createElement('button');
            participateButton.classList.add('participate-button');
            participateButton.setAttribute('id', `participate-${item.annonce_id}`);

            function updateParticipationButton(isParticipated) {
                if (isParticipated) {
                    participateButton.innerHTML = '<ion-icon name="checkmark"></ion-icon> Unparticipate';
                    participateButton.classList.add('participated');
                } else {
                    participateButton.innerHTML = '<ion-icon name="arrow-forward-outline"></ion-icon> Participate';
                    participateButton.classList.remove('participated');
                }
            }
            updateParticipationButton(item.is_participant);

            participateButton.addEventListener('click', async () => {
                try {
                    participateButton.disabled = true;
                    const isCurrentlyParticipated = participateButton.classList.contains('participated');
                    const response = isCurrentlyParticipated
                        ? await suppParticipant(item.annonce_id, userId)
                        : await addParticipant(item.annonce_id, userId);

                    if (response && response.success) {
                        item.is_participant = !isCurrentlyParticipated;
                        updateParticipationButton(!isCurrentlyParticipated);
                        updateItemDisplay(item, 'containerId');
                        loadMore(userId, currentType, `${currentType.toLowerCase()}-annonces`, currentPage);
                    } else {
                        console.error('Participation action failed:', response);
                    }
                } catch (error) {
                    console.error('Error handling Participate button:', error);
                } finally {
                    participateButton.disabled = false;
                }
            });

            div.appendChild(likeButton);
            div.appendChild(participateButton);
        } catch (error) {
            console.error("Error creating interaction buttons:", error);
        }
    }

    return div;
}
async function addLike(annonceId, userId) {
    try {
        const requestData = {
            action: 'Like_Create',
            annonce_id: annonceId,
            user_id: userId
        };

        const response = await postData(apiUrl, requestData);

        if (response.error) {
            throw new Error(response.error);
        }

        return response;
    } catch (error) {
        console.error("Erreur lors du like de l'annonce", error);
        alert("Erreur lors du like");
    }
}

async function addParticipant(annonceId, userId) {
    try {
        const requestData = {
            action: 'Participant_Create',
            annonce_id: annonceId,
            user_id: userId
        };

        const response = await postData(apiUrl, requestData);

        if (response.error) {
            throw new Error(response.error);
        }

        return response;
    } catch (error) {
        console.error("Erreur lors de la participation à l'annonce", error);
        alert("Erreur lors de la participation");
    }
}

async function suppLike(annonceId, userId) {
    try {
        const requestData = {
            action: 'Like_Delete',
            annonce_id: annonceId,
            user_id: userId
        };

        const response = await postData(apiUrl, requestData);
        
        if (response.error) {
            throw new Error(response.error);
        }

        return response;
    } catch (error) {
        console.error("Erreur lors du retrait du like", error);
        alert("Erreur lors du retrait du like");
    }
}

async function suppParticipant(annonceId, userId) {
    try {
        const requestData = {
            action: 'Participant_Delete',
            annonce_id: annonceId,
            user_id: userId
        };

        const response = await postData(apiUrl, requestData);

        if (response.error) {
            throw new Error(response.error);
        }

        return response;
    } catch (error) {
        console.error("Erreur lors du retrait de la participation", error);
        alert("Erreur lors du retrait de la participation");
    }
}
function updateLikeButton(button, isLiked) {
    button.classList.toggle('liked', isLiked);
    button.innerHTML = '';
    button.innerHTML = isLiked
        ? '<ion-icon name="heart"></ion-icon> Dislike'
        : '<ion-icon name="heart-outline"></ion-icon> Like';
}

function updateParticipationButton(button, isParticipated) {
    button.classList.toggle('participated', isParticipated);
    button.innerHTML = '';
    button.innerHTML = isParticipated
        ? '<ion-icon name="checkmark"></ion-icon> Unparticipate'
        : '<ion-icon name="arrow-forward-outline"></ion-icon> Participate';
}


function updateItemDisplay(item, listElementId) {
    const itemElement = document.querySelector(`#${listElementId} [data-id="${item.annonce_id}"]`);

    if (itemElement) {
        const likeButton = itemElement.querySelector('.like-button');
        const participateButton = itemElement.querySelector('.participate-button');

        if (likeButton) {
            updateLikeButton(likeButton, item.is_liked);
        }
        if (participateButton) {
            updateParticipationButton(participateButton, item.is_participant);
        }
    }
}