<?php
session_start();
require_once 'includes/language.php';

// Get news ID from URL
$news_id = isset($_GET['id']) ? $_GET['id'] : '';

// In a real application, you would fetch the news data from a database
// For now, we'll use a static array of news items
$newsItems = [
    '1' => [
        'title' => 'Manchester United Secures Dramatic Win in Champions League',
        'date' => 'December 12, 2023',
        'image' => 'images/news1.jpg',
        'description' => 'In a night of high drama at Old Trafford, Manchester United secured their place in the Champions League knockout stages with a stunning 3-2 victory. Marcus Rashford\'s 90th-minute strike proved to be the difference in what will go down as one of the most memorable European nights in recent history.',
        'content' => 'In a night of high drama at Old Trafford, Manchester United secured their place in the Champions League knockout stages with a stunning 3-2 victory. Marcus Rashford\'s 90th-minute strike proved to be the difference in what will go down as one of the most memorable European nights in recent history.

        The match began with United on the front foot, taking an early lead through a well-worked goal from Bruno Fernandes. However, the visitors fought back, equalizing before halftime through a spectacular long-range effort.

        The second half saw both teams trading blows, with United taking the lead again through Rashford, only to be pegged back once more. As the clock ticked down and tensions rose, it was Rashford who emerged as the hero, latching onto a perfect through-ball from Fernandes to slot home the winner.

        The victory ensures United\'s progression to the knockout stages, where they will face one of Europe\'s elite clubs in the next round. Manager Erik ten Hag praised his team\'s resilience and fighting spirit, particularly highlighting Rashford\'s contribution to the crucial victory.'
    ],
    '2' => [
        'title' => 'Liverpool Announces New Signing',
        'date' => 'December 11, 2023',
        'image' => 'images/news2.jpg',
        'description' => 'Liverpool Football Club has announced the signing of a promising young midfielder in a deal worth £35 million.',
        'content' => 'Liverpool Football Club has announced the signing of a promising young midfielder in a deal worth £35 million. The 21-year-old arrives from the Bundesliga with a reputation for creative playmaking and exceptional vision.

        The new signing, who will wear the number 8 shirt, has already impressed in training sessions and is expected to make an immediate impact in the team\'s midfield. Manager Jürgen Klopp expressed his excitement about the new addition, praising the player\'s technical abilities and tactical understanding of the game.

        "We are delighted to welcome him to Liverpool," said Klopp. "He is exactly the type of player we were looking for, and his style fits perfectly with our system. He has shown great potential in the Bundesliga, and we believe he can develop even further here at Liverpool."

        The player will be available for selection in this weekend\'s Premier League fixture, pending international clearance.'
    ],
    '3' => [
        'title' => 'Manchester City Breaks Premier League Record',
        'date' => 'December 10, 2023',
        'image' => 'images/news3.jpg',
        'description' => 'Manchester City sets new Premier League record with 15 consecutive home wins.',
        'content' => 'Manchester City has etched their name into the Premier League history books once again by securing their 15th consecutive home victory, breaking the previous record set in 2019. The champions achieved this milestone with a commanding 4-0 win over Crystal Palace at the Etihad Stadium.

        Erling Haaland continued his remarkable scoring form with a hat-trick, while Kevin De Bruyne marked his return from injury with a stunning free-kick goal. The Norwegian striker\'s three goals took his season tally to an impressive 20 goals in just 15 appearances.

        Manager Pep Guardiola praised his team\'s consistency and determination: "This record shows the mentality of this team. To win 15 consecutive games at home in the Premier League is not easy. The players deserve all the credit for this achievement."

        The victory not only secured the record but also strengthened City\'s position in the title race as they continue their defense of the Premier League crown.'
    ],
    '4' => [
        'title' => 'PSG Signs Rising Brazilian Star',
        'date' => 'December 9, 2023',
        'image' => 'images/news4.jpg',
        'description' => 'Paris Saint-Germain completes the signing of Brazilian wonderkid in a €45 million deal.',
        'content' => 'Paris Saint-Germain has completed the signing of Brazil\'s latest football sensation in a deal worth €45 million. The 19-year-old forward arrives from Santos, following in the footsteps of Brazilian legends like Neymar and Pelé.

        The teenager has already made waves in Brazilian football, scoring 15 goals in his debut season and earning his first international cap for Brazil. PSG\'s sporting director highlighted the club\'s long-term vision in securing one of football\'s most promising talents.

        "This signing represents our commitment to building for the future," said the PSG president. "We believe he has all the qualities to become one of the world\'s best players, and we\'re excited to help him develop at PSG."

        The young star will wear the number 11 shirt and is expected to join the squad after the winter break, pending medical examinations and international clearance.'
    ],
    '5' => [
        'title' => 'Champions League Draw Revealed',
        'date' => 'December 8, 2023',
        'image' => 'images/news5.jpg',
        'description' => 'UEFA Champions League knockout stage draw produces several exciting matchups.',
        'content' => 'The UEFA Champions League round of 16 draw has produced several mouth-watering ties, setting up some intriguing battles between Europe\'s elite clubs. The highlight of the draw sees defending champions Manchester City face Real Madrid in a repeat of last year\'s semi-final.

        Other notable matchups include Bayern Munich versus Arsenal, PSG against Inter Milan, and Barcelona taking on Napoli. The first legs of these ties will be played in February, with the return fixtures scheduled for March.

        UEFA\'s deputy general secretary commented on the draw: "These matchups promise to deliver some fantastic football. The balance between traditional powerhouses and emerging forces makes this knockout stage particularly interesting."

        The road to Wembley Stadium, which will host this year\'s final, is now set. Teams will be looking to strengthen their squads during the January transfer window in preparation for these crucial encounters.'
    ]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>News Detail - GoalSphere</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="css/styles.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <!-- Reuse your existing navbar here -->
    
    <div class="pt-16">
        <div class="container mx-auto px-4 py-8">
            <?php if (isset($newsItems[$news_id])): ?>
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <img src="<?php echo htmlspecialchars($newsItems[$news_id]['image']); ?>" 
                         alt="<?php echo htmlspecialchars($newsItems[$news_id]['title']); ?>"
                         class="w-full h-96 object-cover">
                    
                    <div class="p-8">
                        <h1 class="text-4xl font-bold mb-4">
                            <?php echo htmlspecialchars($newsItems[$news_id]['title']); ?>
                        </h1>
                        
                        <div class="text-gray-600 mb-6">
                            <?php echo htmlspecialchars($newsItems[$news_id]['date']); ?>
                        </div>
                        
                        <div class="prose max-w-none">
                            <?php echo nl2br(htmlspecialchars($newsItems[$news_id]['content'])); ?>
                        </div>
                        
                        <div class="mt-8">
                            <a href="index.php" class="inline-block bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition-colors">
                                Back to News
                            </a>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="text-center py-12">
                    <h2 class="text-2xl font-bold mb-4">News article not found</h2>
                    <a href="index.php" class="inline-block bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition-colors">
                        Back to News
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Reuse your existing footer here -->
</body>
</html> 