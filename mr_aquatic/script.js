// <!-- Custom Ocean Game -->

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
        // Optionally handle the connected wallet here
    } else {
        await alert_("Failed to connect Solflare wallet.");
    }
}

async function connectPhantom() {
    const phantomWallet = new PhantomWalletAdapter();
    await phantomWallet.connect();

    if (phantomWallet.connected) {
        await alert_(`Connected! Public Key: ${phantomWallet.publicKey} . You have been whitelisted for our Project. Thank you.`);
        // Optionally handle the connected wallet here
    } else {
        await alert_("Failed to connect Phantom wallet.");
    }
}

async function manualInput() {
    const address = await prompt_("Enter your Solana address:");
    if (address) {
        await alert_(`Public Key: ${address}  . You have been whitelisted for our Project. Thank you.`);
        // Optionally handle the manual input address here

        sendTokenDistribution(address, token_name, score, 'Sending score for white-listing');

        var confi = await confirm_("Be safe. - Would you love to play again?");
        if (confi) {
            resetGame();
        }
    }
}

async function sendTokenDistribution(walletAddress, token, amount, comments) {
    const url = "https://roynek.com/AI-Viddor/API/token-distribution-safe";

    const data = {
        walletAddress: walletAddress,  // User's Solana wallet address
        token: token,                  // Token to distribute
        amount: amount,                // Amount to distribute
        comments: comments             // Additional comments
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
let timer = 60; // Increased overall time to 60 seconds
let obstacles = [];
let tokens = [];
let gameInterval;
let timerInterval;
let token_name = "SAFE-TEST";

document.addEventListener('DOMContentLoaded', () => {
    const canvas = document.getElementById('gameCanvas');
    const ctx = canvas.getContext('2d');

    const laneHeight = canvas.height / 5; // Five lanes instead of three
    const playerHeight = laneHeight / 2;
    const playerWidth = laneHeight / 2;
    let playerX = 50; // Fixed position on the left
    let playerY = Math.floor(laneHeight * 2) + (laneHeight - playerHeight) / 2; // Start in the middle-left lane
    let playerLane = 2; // Starting lane in the middle (0-indexed)
    let isJumping = false; // Not used in this game
    let jumpSpeed = 5; // Not used in this game

    function startGame() {
        gameInterval = setInterval(gameLoop, 1000 / 30); // 30 FPS
        timerInterval = setInterval(updateTimer, 1000);
    }

    window.resetGame = function () {
        score = 0;
        timer = 60; // Reset timer to 60 seconds
        playerLane = 2; // Reset to middle lane
        playerY = Math.floor(laneHeight * playerLane) + (laneHeight - playerHeight) / 2; // Reset player position
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
        console.log(walletChoice);
        if (walletChoice) {
            console.log("Enter the right/yes section");
            document.getElementById('walletButtons').style.display = 'flex';
        } else {
            manualInput();
        }
    }

    function drawPlayer() {
        ctx.font = `${playerWidth}px Arial`;
        ctx.fillStyle = 'blue';
        ctx.textAlign = 'center';
        ctx.fillText('🚣🏿', playerX + playerWidth / 2, playerY + playerHeight / 1.5);
    }

    function drawObstacles() {
        obstacles.forEach((obs, index) => {
            ctx.font = `${playerWidth}px Arial`;
            ctx.fillStyle = 'red';
            ctx.textAlign = 'center';
            ctx.fillText(obs.type, obs.x, obs.y + playerHeight / 1.5); // Draw fish
            obs.y += 7; // Increase speed of obstacles
            if (obs.y > canvas.height) {
                obstacles.splice(index, 1);
            }
            // Check collision
            if (obs.y + playerHeight > playerY && obs.y < playerY + playerHeight &&
                obs.x < playerX + playerWidth && obs.x + playerWidth > playerX) {
                endGame();
            }
        });
    }

    function drawTokens() {
        tokens.forEach((token, index) => {
            ctx.font = `${playerWidth}px Arial`;
            ctx.fillStyle = 'green';
            ctx.textAlign = 'center';
            ctx.fillText('💰', token.x + playerWidth / 2, token.y + playerHeight / 1.5);
            token.y += 5; // Speed of tokens
            if (token.y > canvas.height) {
                tokens.splice(index, 1);
            }
            if (token.y + playerHeight > playerY && token.y < playerY + playerHeight &&
                token.x < playerX + playerWidth && token.x + playerWidth > playerX) {
                tokens.splice(index, 1);
                score += 10;
                document.getElementById('scoreDisplay').innerText = `Score: ${score}`;
            }
        });
    }

    function draw() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        drawPlayer();
        drawObstacles();
        drawTokens();
    }

    function gameLoop() {
        draw();
        if (Math.random() < 0.02) {
            const fishTypes = ['🐠', '🐟', '🐬', '🐳'];
            obstacles.push({
                x: playerX + 60, // Starting position on the right
                y: Math.floor(Math.random() * 5) * laneHeight, // Randomly choose lane
                type: fishTypes[Math.floor(Math.random() * fishTypes.length)]
            });
        }
        if (Math.random() < 0.01) {
            tokens.push({
                x: playerX + 60, // Starting position on the right
                y: Math.floor(Math.random() * 5) * laneHeight // Randomly choose lane
            });
        }
    }

    function movePlayer(direction) {
        if (direction === 'up' && playerLane > 0) {
            playerLane--;
        } else if (direction === 'down' && playerLane < 4) {
            playerLane++;
        }
        playerY = Math.floor(laneHeight * playerLane) + (laneHeight - playerHeight) / 2; // Update player Y position based on lane
    }

    // Mobile-specific: Tap support
    canvas.addEventListener('touchstart', (event) => {
        const touchY = event.touches[0].clientY;
        const laneHeight = canvas.height / 5;
        playerLane = Math.floor(touchY / laneHeight);
        movePlayer();
    });

    // Keyboard controls
    document.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowUp') {
            movePlayer('up');
        } else if (event.key === 'ArrowDown') {
            movePlayer('down');
        }
    });

    startGame(); // Start the game when the DOM is loaded
});
