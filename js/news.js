// News handling
document.addEventListener('DOMContentLoaded', () => {
    const newsContainer = document.getElementById('newsContainer');

    // Sample news data - Replace with real API data
    const news = [
        {
            title: 'Champions League Quarter-Finals Draw',
            summary: 'Exciting matchups ahead as Europe\'s elite teams learn their fate',
            image: 'https://example.com/news1.jpg',
            date: '2024-03-15'
        },
        {
            title: 'Transfer News: Star Player on the Move',
            summary: 'Major transfer development as top club makes record-breaking bid',
            image: 'https://example.com/news2.jpg',
            date: '2024-03-14'
        },
        // Add more news items
    ];

    // Render news
    news.forEach(item => {
        const newsCard = `
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                <img src="${item.image}" alt="${item.title}" class="w-full h-48 object-cover">
                <div class="p-6">
                    <h3 class="text-xl font-bold mb-2">${item.title}</h3>
                    <p class="text-gray-600 mb-4">${item.summary}</p>
                    <div class="text-sm text-gray-500">${item.date}</div>
                </div>
            </div>
        `;
        newsContainer.innerHTML += newsCard;
    });
}); 