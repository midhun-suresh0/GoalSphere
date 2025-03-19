document.addEventListener('DOMContentLoaded', () => {
    const matchesContainer = document.getElementById('matchesContainer');
    const upcomingMatchesContainer = document.getElementById('upcomingMatches');
    const loadingElement = document.getElementById('loading');

    const url = 'https://raw.githubusercontent.com/openfootball/football.json/master/2024-25/en.1.json';
console.log(url);
    // Fetch matches data
    const fetchMatches = async () => {
        try {
            const response = await fetch(url);
            const data = await response.json();
            
            // Hide loading message
            loadingElement.style.display = 'none';

            // Get matches data and sort them by date (latest first)
            const matches = data.matches.sort((a, b) => {
                const dateA = new Date(a.date);
                const dateB = new Date(b.date);
                return dateB - dateA; // Sort in descending order (latest first)
            });

            // Clear existing content
            matchesContainer.innerHTML = '';
            upcomingMatchesContainer.innerHTML = '';

            // Loop through sorted matches and create a card for each match
            matches.forEach(match => {
                const card = `
                    <div class="match-card bg-gradient-to-br from-gray-900 to-black p-6 rounded-lg hover:transform hover:scale-105 transition-all duration-300">
                        <div class="flex justify-between items-center mb-2">
                            <div class="text-sm text-gray-400">Round ${match.round}</div>
                            <div class="text-sm text-green-500">${getMatchStatus(match)}</div>
                        </div>
                        
                        <div class="match-header text-xl font-bold mb-4 text-white">
                            ${match.team1} vs ${match.team2}
                        </div>

                        ${getScoreSection(match)}

                        <div class="match-details space-y-2 mt-4">
                            <div class="text-sm text-gray-400">
                                Date: ${new Date(match.date).toLocaleDateString()}
                            </div>
                            <div class="text-sm text-gray-400">
                                Time: ${match.time}
                            </div>
                        </div>
                    </div>
                `;

                // Check if match is scheduled (no score) and add to appropriate container
                console.log(getMatchStatus(match));
                if (getMatchStatus(match) == 'Scheduled') {
                    upcomingMatchesContainer.innerHTML += card;
                } else {
                    matchesContainer.innerHTML += card;
                }
            });

            // Show message if no matches in either section
            if (matchesContainer.innerHTML === '') {
                matchesContainer.innerHTML = '<div class="text-gray-400 text-center py-4">No matches today</div>';
            }
            if (upcomingMatchesContainer.innerHTML === '') {
                upcomingMatchesContainer.innerHTML = '<div class="text-gray-400 text-center py-4">No upcoming matches</div>';
            }
        } catch (error) {
            console.error('Error fetching matches:', error);
            loadingElement.innerHTML = 'Error loading matches. Please try again later.';
            loadingElement.classList.add('text-red-500');
        }
    };

    // Helper function to get match status
    const getMatchStatus = (match) => {
        if (match.score && match.score.ft) {
            return 'Full Time';
        }
        return 'Scheduled';
    };

    // Helper function to generate score section HTML
    const getScoreSection = (match) => {
        if (match.score && match.score.ft) {
            return `
                <div class="scores space-y-2">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-300">Full Time Score:</span>
                        <span class="text-xl font-bold text-white">${match.score.ft[0]} - ${match.score.ft[1]}</span>
                    </div>
                    ${match.score.ht ? `
                        <div class="flex justify-between items-center">
                            <span class="text-gray-300">Half Time:</span>
                            <span class="text-lg text-gray-400">${match.score.ht[0]} - ${match.score.ht[1]}</span>
                        </div>
                    ` : ''}
                </div>
            `;
        }
        return '<div class="text-gray-400 text-center py-2">Match not started</div>';
    };

    // Add toggle functionality
    const resultsBtn = document.getElementById('resultsBtn');
    const upcomingBtn = document.getElementById('upcomingBtn');
    const resultsSection = document.getElementById('resultsSection');
    const upcomingSection = document.getElementById('upcomingSection');

    resultsBtn.addEventListener('click', () => {
        resultsBtn.classList.add('bg-gray-800');
        resultsBtn.classList.remove('bg-gray-700');
        upcomingBtn.classList.add('bg-gray-700');
        upcomingBtn.classList.remove('bg-gray-800');
        
        resultsSection.classList.remove('hidden');
        upcomingSection.classList.add('hidden');
    });

    upcomingBtn.addEventListener('click', () => {
        upcomingBtn.classList.add('bg-gray-800');
        upcomingBtn.classList.remove('bg-gray-700');
        resultsBtn.classList.add('bg-gray-700');
        resultsBtn.classList.remove('bg-gray-800');
        
        upcomingSection.classList.remove('hidden');
        resultsSection.classList.add('hidden');
    });

    // Add styles for the active state
    const addStyles = () => {
        const styles = `
            .match-card {
                transition: transform 0.2s ease-in-out;
            }
            .match-card:hover {
                transform: translateY(-5px);
            }
            .active {
                background-color: rgb(31, 41, 55);
                font-weight: 600;
            }
        `;

        const styleSheet = document.createElement("style");
        styleSheet.innerText = styles;
        document.head.appendChild(styleSheet);
    };

    // Initialize
    addStyles();
    fetchMatches();

    // Refresh matches every 5 minutes
    setInterval(fetchMatches, 300000);
});
