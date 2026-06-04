// Notification Modal Functions
function showNotification(type, points, kategori, bookTitle) {
    const modal = document.getElementById('notificationModal');
    const notificationContent = document.querySelector('.notification-content');
    
    // Reset class
    notificationContent.className = 'notification-content';
    notificationContent.classList.add(type);
    
    // Update icon and title based on type
    let icon, title, subtitle, badge;
    if (type === 'success') {
        icon = '📚';
        title = 'Pinjam Berhasil!';
        subtitle = 'Selamat! Anda telah meminjam buku';
        badge = `<div class="category-badge">${kategori || 'Buku'}</div>`;
    } else if (type === 'return') {
        icon = '✨';
        title = 'Return Berhasil!';
        subtitle = 'Terima kasih telah mengembalikan buku';
        badge = '<div class="category-badge">Bonus +20 Poin</div>';
    }
    
    // Update content
    document.querySelector('.notification-icon').innerHTML = icon;
    document.querySelector('.notification-title').innerHTML = title;
    document.querySelector('.notification-subtitle').innerHTML = subtitle;
    document.querySelector('.points-number').innerHTML = points;
    
    // Update book title if provided
    let bookTitleHTML = '';
    if (bookTitle) {
        bookTitleHTML = `<div class="book-title-notification">"${bookTitle}"</div>`;
    }
    
    // Update badge and book title
    const pointsDisplay = document.querySelector('.points-display');
    pointsDisplay.innerHTML = `
        ${badge}
        <div class="points-label">Poin Diterima</div>
        <div class="points-number shimmer">${points}+ 🎯</div>
        ${bookTitleHTML}
    `;
    
    // Show modal with animation
    modal.classList.add('show');
    
    // Create confetti
    createConfetti();
    createStarAnimation();
    
    // Auto-close after 4 seconds
    setTimeout(() => {
        modal.classList.remove('show');
    }, 4000);
}

function closeNotification() {
    const modal = document.getElementById('notificationModal');
    modal.classList.remove('show');
}

// Create Confetti Animation
function createConfetti() {
    const colors = ['red', 'blue', 'yellow', 'pink', 'purple'];
    const confettiPieces = 50;
    
    for (let i = 0; i < confettiPieces; i++) {
        const confetti = document.createElement('div');
        confetti.classList.add('confetti', colors[Math.floor(Math.random() * colors.length)]);
        confetti.style.left = Math.random() * 100 + '%';
        confetti.style.top = '-10px';
        confetti.style.delay = Math.random() * 0.5 + 's';
        confetti.style.animation = `confettiFall ${2 + Math.random()}s ease-out forwards`;
        
        document.body.appendChild(confetti);
        
        // Remove after animation
        setTimeout(() => {
            confetti.remove();
        }, 3000);
    }
}

// Create Star Animation
function createStarAnimation() {
    const stars = 8;
    const modal = document.querySelector('.notification-content');
    const rect = modal.getBoundingClientRect();
    
    for (let i = 0; i < stars; i++) {
        const star = document.createElement('span');
        star.classList.add('star-animation');
        star.innerHTML = '⭐';
        
        const randomX = (Math.random() - 0.5) * 200;
        const randomY = (Math.random() - 0.5) * 200;
        
        star.style.left = rect.left + rect.width / 2 + randomX + 'px';
        star.style.top = rect.top + rect.height / 2 + randomY + 'px';
        star.style.animationDelay = Math.random() * 0.3 + 's';
        
        document.body.appendChild(star);
        
        setTimeout(() => {
            star.remove();
        }, 2000);
    }
}

// Close modal when clicking outside
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('notificationModal');
    
    if (modal) {
        modal.addEventListener('click', function(event) {
            if (event.target === modal) {
                closeNotification();
            }
        });
    }
    
    // Close button
    const closeBtn = document.querySelector('.close-notification');
    if (closeBtn) {
        closeBtn.addEventListener('click', closeNotification);
    }
});

// Sound effect (optional - uncomment if you want sound)
function playNotificationSound() {
    // Using Web Audio API to create a simple beep
    const audioContext = new (window.AudioContext || window.webkitAudioContext)();
    const oscillator = audioContext.createOscillator();
    const gain = audioContext.createGain();
    
    oscillator.connect(gain);
    gain.connect(audioContext.destination);
    
    oscillator.frequency.value = 800;
    oscillator.type = 'sine';
    
    gain.gain.setValueAtTime(0.3, audioContext.currentTime);
    gain.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);
    
    oscillator.start(audioContext.currentTime);
    oscillator.stop(audioContext.currentTime + 0.5);
}
