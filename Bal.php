<?php
header("Content-Type: text/html; charset=UTF-8");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/png" href="https://github.com/ONEThousandandONE-1001/Jiya/blob/main/Screenshot_2025_0214_135557.jpg?raw=true">
  <title>Bongoboltu | Shooting Game</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    @keyframes flash {
      0%, 100% { filter: none; }
      50% { filter: brightness(2) saturate(2); }
    }
    .character-flash {
      animation: flash 0.2s;
    }
  </style>
</head>
<body class="h-screen w-screen overflow-hidden bg-cover bg-center" 
      style="background-image: url('https://github.com/AkramHossain0/data/blob/main/bgis.JPG?raw=true');">

  <div id="game-container" class="relative h-full w-full">
    <div id="score" class="absolute top-4 left-4 text-white text-2xl font-bold">Score: 0</div>
    <div id="timer" class="absolute top-4 right-4 text-white text-2xl font-bold">Time: 60s</div>
    <img id="gun" 
         src="https://github.com/AkramHossain0/data/blob/main/g.png?raw=true" 
         alt="Gun" 
         class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-64 h-64 object-contain">

    <div id="game-over" class="hidden absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center">
      <div class="bg-white p-8 rounded-lg text-center">
        <h2 class="text-3xl font-bold mb-4">Game Over</h2>
        <p id="final-score" class="text-xl mb-4">Your score: 0</p>
        <button id="restart-btn" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
          Play Again
        </button>
      </div>
    </div>
  </div>

  <audio id="gunshot-sound" src="https://github.com/AkramHossain0/bongoboltu/raw/refs/heads/main/gun-shot-1-7069.mp3"></audio>

  <script>
    const gameContainer = document.getElementById('game-container');
    const gun = document.getElementById('gun');
    const scoreElement = document.getElementById('score');
    const timerElement = document.getElementById('timer');
    const gameOverElement = document.getElementById('game-over');
    const finalScoreElement = document.getElementById('final-score');
    const restartBtn = document.getElementById('restart-btn');
    const gunshotSound = document.getElementById('gunshot-sound');

    let score = 0;
    let timeLeft = 60;
    let gameInterval;
    const enemies = [];
    const numEnemies = 3;

    function createEnemy() {
      const enemy = document.createElement('img');
      enemy.src = "https://github.com/ONEThousandandONE-1001/Jiya/blob/main/Screenshot_2025_0214_135557.jpg?raw=true";
      enemy.className = "absolute w-24 h-24 object-contain";
      gameContainer.appendChild(enemy);
      updateEnemyPosition(enemy);
      enemies.push(enemy);
    }

    function updateEnemyPosition(enemy) {
      const maxX = gameContainer.clientWidth - enemy.clientWidth;
      const maxY = gameContainer.clientHeight - enemy.clientHeight;
      enemy.style.left = `${Math.random() * maxX}px`;
      enemy.style.top = `${Math.random() * maxY}px`;
    }

    function hitEnemy(enemy) {
      enemy.classList.add('character-flash');
      setTimeout(() => enemy.classList.remove('character-flash'), 200);

      gunshotSound.currentTime = 0;
      gunshotSound.play();

      createBlastEffect(enemy.offsetLeft + enemy.clientWidth / 2, enemy.offsetTop + enemy.clientHeight / 2);
      updateScore();
      updateEnemyPosition(enemy);
    }

    function createBullet(x, y, targetX, targetY) {
      const bullet = document.createElement('div');
      bullet.className = 'absolute w-2 h-2 bg-yellow-400 rounded-full';
      bullet.style.left = `${x}px`;
      bullet.style.top = `${y}px`;
      gameContainer.appendChild(bullet);

      const angle = Math.atan2(targetY - y, targetX - x);
      const speed = 12;
      const vx = Math.cos(angle) * speed;
      const vy = Math.sin(angle) * speed;

      function moveBullet() {
        bullet.style.left = `${parseFloat(bullet.style.left) + vx}px`;
        bullet.style.top = `${parseFloat(bullet.style.top) + vy}px`;

        if (
          parseFloat(bullet.style.left) < 0 ||
          parseFloat(bullet.style.left) > gameContainer.clientWidth ||
          parseFloat(bullet.style.top) < 0 ||
          parseFloat(bullet.style.top) > gameContainer.clientHeight
        ) {
          gameContainer.removeChild(bullet);
        } else {
          detectBulletHit(bullet);
          requestAnimationFrame(moveBullet);
        }
      }

      requestAnimationFrame(moveBullet);
    }

    function detectBulletHit(bullet) {
      enemies.forEach(enemy => {
        const enemyRect = enemy.getBoundingClientRect();
        const bulletRect = bullet.getBoundingClientRect();
        if (
          bulletRect.left < enemyRect.right &&
          bulletRect.right > enemyRect.left &&
          bulletRect.top < enemyRect.bottom &&
          bulletRect.bottom > enemyRect.top
        ) {
          gameContainer.removeChild(bullet);
          hitEnemy(enemy);
        }
      });
    }

    function createBlastEffect(x, y) {
      const blast = document.createElement('div');
      blast.className = 'absolute w-8 h-8 bg-red-500 rounded-full opacity-75';
      blast.style.left = `${x - 16}px`;
      blast.style.top = `${y - 16}px`;
      gameContainer.appendChild(blast);

      setTimeout(() => {
        gameContainer.removeChild(blast);
      }, 200);
    }

    function updateScore() {
      score++;
      scoreElement.textContent = `Score: ${score}`;
    }

    function updateTimer() {
      timeLeft--;
      timerElement.textContent = `Time: ${timeLeft}s`;
      if (timeLeft <= 0) {
        endGame();
      }
    }

    function endGame() {
      clearInterval(gameInterval);
      gameOverElement.classList.remove('hidden');
      finalScoreElement.textContent = `Your score: ${score}`;
    }

    function startGame() {
      score = 0;
      timeLeft = 60;
      scoreElement.textContent = 'Score: 0';
      timerElement.textContent = 'Time: 60s';
      gameOverElement.classList.add('hidden');

      enemies.forEach(enemy => gameContainer.removeChild(enemy));
      enemies.length = 0;

      for (let i = 0; i < numEnemies; i++) {
        createEnemy();
      }

      gameInterval = setInterval(() => {
        updateTimer();
        enemies.forEach(updateEnemyPosition);
      }, 1000);
    }

    gameContainer.addEventListener('click', (e) => {
      const gunRect = gun.getBoundingClientRect();
      const gunCenterX = gunRect.left + gunRect.width / 2;
      const gunCenterY = gunRect.top + gunRect.height / 2;
      createBullet(gunCenterX, gunCenterY, e.clientX, e.clientY);
    });

    restartBtn.addEventListener('click', startGame);

    startGame();
  </script>

</body>
</html>
