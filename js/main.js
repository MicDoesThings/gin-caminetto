document.addEventListener('DOMContentLoaded', () => {
    // Fetch the gin data
    fetch('/_gins/index.json')
        .then(response => response.json())
        .then(data => {
            const ginList = document.getElementById('ginList');
            data.forEach(gin => {
                const card = createGinCard(gin);
                ginList.appendChild(card);
            });
        })
        .catch(error => console.error('Error loading gins:', error));
});

function createGinCard(gin) {
    const card = document.createElement('div');
    card.className = 'gin-card';
    
    card.innerHTML = `
        <img src="${gin.image}" alt="${gin.name}" class="gin-image">
        <div class="gin-info">
            <h2>${gin.name}</h2>
            <p><strong>Distillery:</strong> ${gin.distillery}</p>
            <p><strong>Country:</strong> ${gin.country}</p>
            <p><strong>Alcohol:</strong> ${gin.alcohol_volume}%</p>
        </div>
    `;
    
    card.addEventListener('click', () => showGinDetails(gin));
    return card;
}

function showGinDetails(gin) {
    const modal = document.createElement('div');
    modal.className = 'modal';
    modal.innerHTML = `
        <div class="modal-content">
            <div class="modal-header">
                <h2>${gin.name}</h2>
                <button onclick="this.closest('.modal').remove()">×</button>
            </div>
            <div class="modal-body">
                <img src="${gin.image}" alt="${gin.name}">
                <div class="gin-details">
                    <p><strong>Distillery:</strong> ${gin.distillery}</p>
                    <p><strong>Country:</strong> ${gin.country}</p>
                    <p><strong>Alcohol Volume:</strong> ${gin.alcohol_volume}%</p>
                    <p><strong>Botanics:</strong> ${gin.botanics}</p>
                    <p><strong>Recommended Tonic:</strong> ${gin.recommended_tonic}</p>
                    <p><strong>Garnish:</strong> ${gin.garnish}</p>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    setTimeout(() => modal.classList.add('show'), 10);
} 