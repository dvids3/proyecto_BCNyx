window.addEventListener('click', () => {
    const video = document.getElementById('video-fondo');
    video.muted = false;
    video.play();
  }, { once: true });

