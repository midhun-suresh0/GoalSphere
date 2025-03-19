<?php
session_start();
require_once 'includes/language.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Matches - GoalSphere</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="css/styles.css" rel="stylesheet">
</head>
<body class="bg-black min-h-screen">
    <!-- Include navbar -->
    <?php include 'includes/navbar.php'; ?>

    <!-- Include header -->
    <?php include 'includes/matches_header.php'; ?>

    <!-- Main Content -->
    <div class="pt-16">
        <!-- Match Scores Section -->
        <section class="py-12 text-white min-h-screen">
            <div class="container mx-auto px-4">
                <!-- Toggle Buttons -->
                <div class="flex space-x-4 mb-8">
                    <button id="resultsBtn" class="bg-gray-800 text-white px-6 py-2 rounded-lg hover:bg-gray-700 transition-colors active">Results</button>
                    <button id="upcomingBtn" class="bg-gray-800 text-white px-6 py-2 rounded-lg hover:bg-gray-700 transition-colors">Upcoming</button>
                </div>

                <!-- Results Section -->
                <div id="resultsSection" class="mb-8">
                    <h3 class="text-2xl font-bold mb-4">Results</h3>
                    <div id="loading" class="text-center text-gray-400 text-lg py-8">Loading matches...</div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="matchesContainer">
                        <!-- Match cards will be inserted here by JavaScript -->
                    </div>
                </div>

                <!-- Upcoming Matches Section -->
                <div id="upcomingSection" class="mb-8 hidden">
                    <h3 class="text-2xl font-bold mb-4">Upcoming Matches</h3>
                    <div class="space-y-4" id="upcomingMatches">
                        <!-- Upcoming match cards will be inserted here by JavaScript -->
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8">
        <?php include 'includes/footer.php'; ?>
    </footer>

    <script src="js/matches.js"></script>
    <script>
    function toggleSettings(event) {
        event.preventDefault();
        const panel = document.getElementById('settingsPanel');
        if (panel) {
            panel.classList.toggle('hidden');
            setTimeout(() => {
                document.addEventListener('click', closeSettingsOnClickOutside);
            }, 0);
        }
    }

    function closeSettingsOnClickOutside(event) {
        const panel = document.getElementById('settingsPanel');
        const settingsButton = event.target.closest('button');
        
        if (panel && !panel.contains(event.target) && !settingsButton?.contains(event.target)) {
            panel.classList.add('hidden');
            document.removeEventListener('click', closeSettingsOnClickOutside);
        }
    }

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            const panel = document.getElementById('settingsPanel');
            if (panel && !panel.classList.contains('hidden')) {
                panel.classList.add('hidden');
            }
        }
    });

    function updateSettings(type, value) {
        fetch('update_settings.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ type, value })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }
    </script>
</body>
</html>
