// document.addEventListener('DOMContentLoaded', () => {
//     const tabs = document.querySelectorAll('.tab');
//     const contentSections = document.querySelectorAll('.content-section');

//     tabs.forEach((tab, index) => {
//         tab.addEventListener('click', () => {
//             tabs.forEach(tab => tab.classList.remove('active'));
//             tab.classList.add('active');
            
//             contentSections.forEach(section => section.classList.remove('active'));
//             contentSections[index].classList.add('active');
//         });
//     });

//     // Activate the first tab and content section by default
//     tabs[0].classList.add('active');
//     contentSections[0].classList.add('active');
// });



document.addEventListener('DOMContentLoaded', function () {
    const tabs = document.querySelectorAll('.tab');
    const tabContents = document.querySelectorAll('.tab-content');

    tabs.forEach((tab, index) => {
        tab.addEventListener('click', () => {
            tabContents.forEach(content => content.classList.remove('active'));
            tabContents[index].classList.add('active');
        });
    });

    // Load games
    loadGames();

    // Load tasks from JSON
    loadTasks();

    // Load stats
    loadStats();

    // Load news
    loadNews();
});

function loadGames() {
    const gameList = document.querySelector('.game-list');
    const games = [
        { title: 'Game 1', img: 'game1.jpg' },
        { title: 'Game 2', img: 'game2.jpg' },
        // Add more games
    ];

    games.forEach(game => {
        const li = document.createElement('li');
        li.innerHTML = `<img src="${game.img}" alt="${game.title}"><span>${game.title}</span>`;
        gameList.appendChild(li);
    });
}

function loadTasks() {
    const taskList = document.querySelector('.task-list');
    fetch('tasks.php')
        .then(response => response.json())
        .then(tasks => {
            tasks.forEach(task => {
                const li = document.createElement('li');
                li.innerHTML = `<span>${task.description}</span><span>${task.reward} Coins</span>
                                <button class="start-btn">Start</button>
                                <button class="claim-btn" disabled>Claim</button>`;
                taskList.appendChild(li);

                const startBtn = li.querySelector('.start-btn');
                const claimBtn = li.querySelector('.claim-btn');

                startBtn.addEventListener('click', () => {
                    // Simulate redirect to social media and back
                    document.cookie = `task_${task.id}=started`;
                    startBtn.disabled = true;
                    claimBtn.disabled = false;
                });

                claimBtn.addEventListener('click', () => {
                    if (document.cookie.includes(`task_${task.id}=started`)) {
                        // Process claim
                        alert('Task claimed!');
                        claimBtn.disabled = true;
                    }
                });
            });
        });
}

function loadStats() {
    const topPlayersToday = document.getElementById('top-players-today');
    const allTimeTopPlayers = document.getElementById('all-time-top-players');

    const playersToday = [
        { name: 'Player 1', score: 500 },
        { name: 'Player 2', score: 450 },
        // Add more players
    ];

    const allTimePlayers = [
        { name: 'Player A', score: 5000 },
        { name: 'Player B', score: 4500 },
        // Add more players
    ];

    playersToday.forEach(player => {
        const li = document.createElement('li');
        li.textContent = `${player.name}: ${player.score} Coins`;
        topPlayersToday.appendChild(li);
    });

    allTimePlayers.forEach(player => {
        const li = document.createElement('li');
        li.textContent = `${player.name}: ${player.score} Coins`;
        allTimeTopPlayers.appendChild(li);
    });
}

function loadNews() {
    fetch('theWorld.php', {
        method: 'POST',
    })
        .then(response => response.text())
        .then(newsContent => {
            document.getElementById('news-list').innerHTML = newsContent;
        })
        .catch(error => console.error('Error:', error));
}
