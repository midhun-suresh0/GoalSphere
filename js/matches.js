// // Matches handling
// document.addEventListener('DOMContentLoaded', async () => {
//     const todayMatchesContainer = document.getElementById('todayMatches');
//     const upcomingMatchesContainer = document.getElementById('upcomingMatches');

//     // Football Data API Configuration
//     const CONFIG = {
//         API_TOKEN: 'f2cba0b9d78d4250abc2ebf831a26b58',
//         BASE_URL: 'https://api.football-data.org/v4'
//     };
    
//     const headers = new Headers({
//         'X-Auth-Token': CONFIG.API_TOKEN
//     });

//     const requestOptions = {
//         method: 'GET',
//         headers: headers,
//         mode: 'cors'
//     };

//     // Fetch matches
//     const fetchMatches = async () => {
//         try {
//             // Fetch today's matches
//             const response = await fetch(`${CONFIG.BASE_URL}/matches`, {
//                 headers: headers
//             });
//             const data = await response.json();
            
//             if (data.matches) {
//                 const matches = data.matches;
//                 const todayMatches = matches.filter(match => isToday(match.utcDate));
//                 const upcomingMatches = matches.filter(match => isFuture(match.utcDate));
                
//                 renderTodayMatches(todayMatches);
//                 renderUpcomingMatches(upcomingMatches);
//             }
//         } catch (error) {
//             console.error('Error fetching matches:', error);
//             showErrorMessage(error.message);
//         }
//     };

//     // Render today's matches
//     const renderTodayMatches = (matches) => {
//         todayMatchesContainer.innerHTML = matches.length ? '' : 
//             '<div class="text-gray-400 text-center py-4">No matches scheduled for today</div>';

//         matches.forEach(match => {
//             const matchCard = createMatchCard(match);
//             todayMatchesContainer.innerHTML += matchCard;
//         });
//     };

//     // Render upcoming matches
//     const renderUpcomingMatches = (matches) => {
//         upcomingMatchesContainer.innerHTML = matches.length ? '' : 
//             '<div class="text-gray-400 text-center py-4">No upcoming matches scheduled</div>';

//         matches.forEach(match => {
//             const matchCard = createMatchCard(match, true);
//             upcomingMatchesContainer.innerHTML += matchCard;
//         });
//     };

//     // Create match card HTML
//     const createMatchCard = (match, isUpcoming = false) => {
//         return `
//             <div class="match-card bg-gradient-to-br from-gray-900 to-black p-6 rounded-lg">
//                 <div class="flex justify-between items-center mb-2">
//                     <div class="text-sm text-gray-400">${match.competition.name}</div>
//                     <div class="text-sm ${getStatusColor(match.status)}">
//                         ${formatMatchStatus(match.status)}
//                     </div>
//                 </div>
//                 <div class="flex justify-between items-center mb-4">
//                     <div class="team flex items-center">
//                         <img src="${match.homeTeam.crest || 'images/default-team.png'}" 
//                             alt="${match.homeTeam.name}" 
//                             class="w-10 h-10 object-contain">
//                         <span class="ml-3 font-semibold">${match.homeTeam.name}</span>
//                     </div>
//                     <span class="text-2xl font-bold">${match.score?.fullTime?.home || '0'}</span>
//                 </div>
//                 <div class="flex justify-between items-center">
//                     <div class="team flex items-center">
//                         <img src="${match.awayTeam.crest || 'images/default-team.png'}" 
//                             alt="${match.awayTeam.name}" 
//                             class="w-10 h-10 object-contain">
//                         <span class="ml-3 font-semibold">${match.awayTeam.name}</span>
//                     </div>
//                     <span class="text-2xl font-bold">${match.score?.fullTime?.away || '0'}</span>
//                 </div>
//                 ${match.status === 'IN_PLAY' ? `
//                     <div class="mt-4 text-sm">
//                         <span class="text-green-500">● LIVE</span>
//                         <span class="text-gray-400 ml-2">${match.minute || ''}'</span>
//                     </div>
//                 ` : ''}
//             </div>
//         `;
//     };

//     // Helper function to get status color
//     const getStatusColor = (status) => {
//         switch (status) {
//             case 'IN_PLAY':
//                 return 'text-green-500';
//             case 'PAUSED':
//                 return 'text-yellow-500';
//             case 'FINISHED':
//                 return 'text-gray-400';
//             default:
//                 return 'text-gray-400';
//         }
//     };

//     // Helper function to format match status
//     const formatMatchStatus = (status) => {
//         switch (status) {
//             case 'IN_PLAY':
//                 return 'LIVE';
//             case 'PAUSED':
//                 return 'Half Time';
//             case 'FINISHED':
//                 return 'Full Time';
//             case 'TIMED':
//                 return 'Scheduled';
//             default:
//                 return status;
//         }
//     };

//     // Helper function to check if date is today
//     const isToday = (dateString) => {
//         const today = new Date();
//         const matchDate = new Date(dateString);
//         return matchDate.toDateString() === today.toDateString();
//     };

//     // Helper function to check if date is in the future
//     const isFuture = (dateString) => {
//         const today = new Date();
//         const matchDate = new Date(dateString);
//         return matchDate > today;
//     };

//     // Update error message to show specific error
//     const showErrorMessage = (message) => {
//         const errorMessage = `
//             <div class="bg-red-900/20 border border-red-500 text-red-100 p-4 rounded-lg">
//                 Unable to load matches. ${message || 'Please try again later.'}
//             </div>
//         `;
//         todayMatchesContainer.innerHTML = errorMessage;
//         upcomingMatchesContainer.innerHTML = errorMessage;
//     };

//     // Calendar navigation functionality
//     const calendarButtons = document.querySelectorAll('#matches button');
//     calendarButtons.forEach(button => {
//         button.addEventListener('click', () => {
//             calendarButtons.forEach(btn => btn.classList.remove('bg-green-600'));
//             button.classList.add('bg-green-600');
//             // Fetch matches for selected date
//             fetchMatches();
//         });
//     });

//     // Add this function after your existing code
//     const testAPIConnection = async () => {
//         try {
//             const response = await fetch(`${CONFIG.BASE_URL}/matches`, {
//                 method: 'GET',
//                 headers: headers,
//                 mode: 'cors'
//             });

//             const data = await response.json();
            
//             // Log the response status and data
//             console.log('API Response Status:', response.status);
//             console.log('API Response:', data);

//             if (!response.ok) {
//                 console.error('API Error:', data);
//                 return false;
//             }

//             return true;
//         } catch (error) {
//             console.error('API Test Error:', error);
//             return false;
//         }
//     };

//     // Call the test function when the page loads
//     const isAPIWorking = await testAPIConnection();
//     console.log('API Working:', isAPIWorking);
    
//     if (isAPIWorking) {
//         fetchMatches();
//         // Refresh matches every 5 minutes
//         setInterval(fetchMatches, 300000);
//     } else {
//         showErrorMessage('API connection failed. Please check your API key.');
//     }
// }); 
const url = 'https://raw.githubusercontent.com/openfootball/football.json/master/2024-25/en.1.json';
const apiKey = 'f2cba0b9d78d4250abc2ebf831a26b58';  // Replace with your actual API key

// Make the GET request
fetch(url, {
  method: 'GET',
  headers: {
    'X-Auth-Token': apiKey,  // Pass the API key in the headers
    'x-response-control': 'minified'  // Example of another custom header
  },
  // No need for 'mode: no-cors' because CORS is handled by the browser.
})
  .then(response => {
    if (response.ok) {
      return response.json();  // Parse JSON if the response is okay
    } else {
      throw new Error('Failed to fetch data');
    }
  })
  .then(data => {
    console.log('Match Data:', data);  // Handle the API data here
  })
  .catch(error => {
    console.error('Error fetching data:', error);  // Handle errors
  });

document.addEventListener('DOMContentLoaded', () => {
    const matchesContainer = document.getElementById('matchesContainer');
    const loadingElement = document.getElementById('loading');

    const url = 'https://raw.githubusercontent.com/openfootball/football.json/master/2024-25/en.1.json';

    // Fetch matches data
    const fetchMatches = async () => {
        try {
            const response = await fetch(url);
            const data = await response.json();
            
            // Hide loading message
            loadingElement.style.display = 'none';

            // Get matches data
            const matches = data.matches;

            // Clear existing content
            matchesContainer.innerHTML = '';

            // Loop through matches and create a card for each match
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

                matchesContainer.innerHTML += card;
            });
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

    // Add these styles to your existing styles.css
    const addStyles = () => {
        const styles = `
            .match-card {
                transition: transform 0.2s ease-in-out;
            }
            .match-card:hover {
                transform: translateY(-5px);
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
