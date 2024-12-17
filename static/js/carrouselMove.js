 // Fonctions de carrousel (gauche et droit) restent les mêmes que dans le script précédent
 function gauche(id_element) {
    const container = document.getElementById(id_element);
    const items = container.querySelectorAll('.annonce-item');
    const itemCount = items.length;
    const stepPercentage = 100 / itemCount;
    const currentTransform = getComputedStyle(container).transform;
    let currentXPercentage = 0;

    if (currentTransform !== 'none') {
        const matrixValues = currentTransform.match(/matrix\((.+)\)/)[1].split(', ');
        const currentX = parseFloat(matrixValues[4]);
        const containerWidth = container.offsetWidth;
        currentXPercentage = (currentX / containerWidth) * 100;
    }

    if (currentXPercentage >= 0) {
        return;
    }

    const newXPercentage = currentXPercentage + stepPercentage;
    container.style.transform = `translateX(${Math.min(newXPercentage, 0)}%)`;
}

function droit(id_element) {
    const container = document.getElementById(id_element);
    const items = container.querySelectorAll('.annonce-item');
    const itemCount = items.length;
    const stepPercentage = 100 / itemCount;
    const currentTransform = getComputedStyle(container).transform;
    let currentXPercentage = 0;

    if (currentTransform !== 'none') {
        const matrixValues = currentTransform.match(/matrix\((.+)\)/)[1].split(', ');
        const currentX = parseFloat(matrixValues[4]);
        const containerWidth = container.offsetWidth;
        currentXPercentage = (currentX / containerWidth) * 100;
    }

    const maxTranslation = -(100 - stepPercentage);
    if (currentXPercentage <= maxTranslation) {
        return;
    }

    const newXPercentage = currentXPercentage - stepPercentage;
    container.style.transform = `translateX(${Math.max(newXPercentage, maxTranslation)}%)`;
}