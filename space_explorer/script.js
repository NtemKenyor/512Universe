// <!-- Custom Solflare Wallet Adapter -->

class SolflareWalletAdapter {
    constructor() {
        this.connected = false;
        this.publicKey = null;
    }

    async connect() {
        try {
            const provider = window.solflare;
            if (provider) {
                await provider.connect();
                this.publicKey = provider.publicKey.toString();
                this.connected = true;
            } else {
                await alert_("Solflare wallet is not installed.");
            }
        } catch (err) {
            console.error(err);
            this.connected = false;
        }
    }

    disconnect() {
        if (this.connected) {
            window.solflare.disconnect();
            this.connected = false;
            this.publicKey = null;
        }
    }
}

class PhantomWalletAdapter {
    constructor() {
        this.connected = false;
        this.publicKey = null;
    }

    async connect() {
        try {
            const provider = window.solana;
            if (provider && provider.isPhantom) {
                await provider.connect();
                this.publicKey = provider.publicKey.toString();
                this.connected = true;
            } else {
                await alert_("Phantom wallet is not installed.");
            }
        } catch (err) {
            console.error(err);
            this.connected = false;
        }
    }

    disconnect() {
        if (this.connected) {
            window.solana.disconnect();
            this.connected = false;
            this.publicKey = null;
        }
    }
}

async function connectSolflare() {
    const solflareWallet = new SolflareWalletAdapter();
    await solflareWallet.connect();

    if (solflareWallet.connected) {
        await alert_(`Connected! Public Key: ${solflareWallet.publicKey} . You have been whitelisted for our Project. Thank you.`);
    } else {
        await alert_("Failed to connect Solflare wallet.");
    }
}

async function connectPhantom() {
    const phantomWallet = new PhantomWalletAdapter();
    await phantomWallet.connect();

    if (phantomWallet.connected) {
        await alert_(`Connected! Public Key: ${phantomWallet.publicKey} . You have been whitelisted for our Project. Thank you.`);
    } else {
        await alert_("Failed to connect Phantom wallet.");
    }
}

async function manualInput() {
    const address = await prompt_("Enter your Solana address:");
    if (address) {
        await alert_(`Public Key: ${address}  . You have been whitelisted for our Project. Thank you.`);
        sendTokenDistribution(address, token_name, score, 'Sending scored for white-listing');

        var confi = await confirm_("Be safe. - Would you love to play again?");
        if (confi) {
            resetGame();
        }
    }
}

async function sendTokenDistribution(walletAddress, token, amount, comments) {
    const url = "https://roynek.com/AI-Viddor/API/token-distribution-safe";
    
    const data = {
        walletAddress: walletAddress,
        token: token,
        amount: amount,
        comments: comments
    };

    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });

        if (response.ok) {
            const result = await response.json();
            console.log("Success:", result);
        } else {
            console.error("Failed:", response.statusText);
        }
    } catch (error) {
        console.error("Error:", error);
    }
}

let score = 0;
let timer = 30;
let obstacles = [];
let tokens = [];
let gameInterval;
let timerInterval;
let token_name = "SAFE-TEST";

// window.onload = function() {
document.addEventListener('DOMContentLoaded', () => {
    const canvas = document.getElementById('gameCanvas');
    const ctx = canvas.getContext('2d');

    // Set the canvas background to a space-themed image
    canvas.style.background = "url('https://roynek.com/Safe-Journey/space_background.jpg') no-repeat center center fixed";
    canvas.style.backgroundSize = "cover";

    const laneWidth = canvas.width / 3;
    const playerHeight = canvas.height / 12;
    const playerWidth = laneWidth; // Plane width
    let playerX = laneWidth + (laneWidth - playerWidth) / 2;
    let playerY = canvas.height - playerHeight - 20;
    let playerLane = 1;
    let isJumping = false; 
    let jumpStartY = playerY; 
    let jumpHeight = 100; 
    let jumpSpeed = 5; 
    let jumpDirection = 1; 

    function startGame() {
        gameInterval = setInterval(gameLoop, 1000 / 30); 
        timerInterval = setInterval(updateTimer, 1000);
    }

    window.resetGame = function () {
        score = 0;
        timer = 30;
        playerLane = 1;
        playerX = laneWidth + (laneWidth - playerWidth) / 2;
        obstacles = [];
        tokens = [];
        clearInterval(gameInterval);
        clearInterval(timerInterval);
        startGame();
    }

    function updateTimer() {
        timer--;
        document.getElementById('timerDisplay').innerText = `Time: ${timer}`;
        if (timer <= 0) {
            endGame();
        }
    }

    async function endGame() {
        clearInterval(gameInterval);
        clearInterval(timerInterval);
        await alert_(`Game Over! Your score: ${score}`);
        const walletChoice = await confirm_("Do you have a Solana wallet: Solfare or Phantom? (yes/no)");
        if (walletChoice) {
            document.getElementById('walletButtons').style.display = 'flex';
        } else {
            manualInput();
        }
    }

    let playerFace = '😐'; // Default face emoji (neutral)

    function drawPlayer() {
        ctx.font = `${playerWidth}px Arial`;
        ctx.fillStyle = 'white';
        ctx.textAlign = 'center';
        ctx.fillText(playerFace, playerX + playerWidth / 2, playerY + playerHeight / 1.5); // Player face emoji
    }

    // Update this function to change the player's face based on game events
    function updatePlayerFace() {
        if (score > 0 && tokens.length > 0) {
            playerFace = '😄'; // Smiling face when collecting tokens
        } else if (obstacles.length > 0 && playerY < canvas.height - playerHeight) {
            playerFace = '😢'; // Broken face when hitting an obstacle
        } else {
            playerFace = '😐'; // Neutral face if nothing happens
        }
    }

    // Modify the drawTokens function to include updating the player face
    function drawTokens() {
        tokens.forEach((token, index) => {
            ctx.font = `${playerWidth}px Arial`;
            ctx.fillStyle = 'green';
            ctx.textAlign = 'center';
            ctx.fillText('💰', token.x + playerWidth / 2, token.y + playerHeight / 1.5);
            token.y += 5;
            if (token.y > canvas.height) {
                tokens.splice(index, 1); // Remove off-screen tokens
            }
            if (!isJumping && token.y + playerHeight > playerY && token.y < playerY + playerHeight &&
                token.x < playerX + playerWidth && token.x + playerWidth > playerX) {
                tokens.splice(index, 1);
                score += 10; // Increase score on collection
                document.getElementById('scoreDisplay').innerText = `Score: ${score}`;
            }
        });
    }

    // Modify the gameLoop function to update player face
    function gameLoop() {
        draw();
        updatePlayerFace(); // Update face based on game events
        if (Math.random() < 0.02) {
            obstacles.push({
                x: Math.floor(Math.random() * 3) * laneWidth,
                y: -playerHeight
            });
        }
        if (Math.random() < 0.01) {
            tokens.push({
                x: Math.floor(Math.random() * 3) * laneWidth,
                y: -playerHeight
            });
        }
        handleJump();
    }

    function drawObstacles() {
        const obstacleEmojis = ['🌑', '🔥', '💨']; // Array of obstacle emojis
        obstacles.forEach((obs, index) => {
            ctx.font = `${playerWidth}px Arial`;
            ctx.fillStyle = 'orange'; // Color for obstacles
            ctx.textAlign = 'center';
            
            // Select a random emoji from the obstacleEmojis array
            const randomEmoji = obstacleEmojis[Math.floor(Math.random() * obstacleEmojis.length)];
            
            ctx.fillText(randomEmoji, obs.x + playerWidth / 2, obs.y + playerHeight / 1.5); // Draw random obstacle emoji
            obs.y += 5; // Move obstacles down
            if (obs.y > canvas.height) {
                obstacles.splice(index, 1); // Remove off-screen obstacles
            }
            if (!isJumping && obs.y + playerHeight > playerY && obs.y < playerY + playerHeight &&
                obs.x < playerX + playerWidth && obs.x + playerWidth > playerX) {
                endGame(); // Collision detection
            }
        });
    }
    

    // function drawTokens() {
    //     tokens.forEach((token, index) => {
    //         ctx.font = `${playerWidth}px Arial`;
    //         ctx.fillStyle = 'green';
    //         ctx.textAlign = 'center';
    //         ctx.fillText('💰', token.x + playerWidth / 2, token.y + playerHeight / 1.5);
    //         token.y += 5;
    //         if (token.y > canvas.height) {
    //             tokens.splice(index, 1); // Remove off-screen tokens
    //         }
    //         if (!isJumping && token.y + playerHeight > playerY && token.y < playerY + playerHeight &&
    //             token.x < playerX + playerWidth && token.x + playerWidth > playerX) {
    //             tokens.splice(index, 1);
    //             score += 10; // Increase score on collection
    //             document.getElementById('scoreDisplay').innerText = `Score: ${score}`;
    //         }
    //     });
    // }

    function draw() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        drawPlayer();
        drawObstacles();
        drawTokens();
    }

    // function gameLoop() {
    //     draw();
    //     if (Math.random() < 0.02) {
    //         obstacles.push({
    //             x: Math.floor(Math.random() * 3) * laneWidth,
    //             y: -playerHeight
    //         });
    //     }
    //     if (Math.random() < 0.01) {
    //         tokens.push({
    //             x: Math.floor(Math.random() * 3) * laneWidth,
    //             y: -playerHeight
    //         });
    //     }
    //     handleJump();
    // }

    function movePlayer(direction) {
        if (direction === 'left' && playerLane > 0) {
            playerLane--;
        } else if (direction === 'right' && playerLane < 2) {
            playerLane++;
        }
        playerX = playerLane * laneWidth + (laneWidth - playerWidth) / 2;
    }

    function handleJump() {
        if (isJumping) {
            playerY -= jumpSpeed * jumpDirection;
            if (playerY <= jumpStartY - jumpHeight) {
                jumpDirection = -1; 
            }
            if (playerY >= jumpStartY) {
                playerY = jumpStartY;
                isJumping = false;
                jumpDirection = 1; 
            }
        }
    }

    // Mobile-specific: Tap to move left or right, or jump
    canvas.addEventListener('touchstart', (event) => {
        const touchX = event.touches[0].clientX;
        if (touchX < canvas.width / 2) {
            movePlayer('left');
        } else {
            movePlayer('right');
        }
        if (!isJumping) {
            isJumping = true; 
            jumpStartY = playerY;
        }
    });

    // Key controls for desktop
    window.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowLeft') {
            movePlayer('left');
        } else if (event.key === 'ArrowRight') {
            movePlayer('right');
        } else if (event.key === ' ') {
            if (!isJumping) {
                isJumping = true;
                jumpStartY = playerY;
            }
        }
    });

    // Start the game
    resetGame();
});
