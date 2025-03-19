<?php
session_start();
require_once 'includes/language.php';

$team = $_GET['team'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Details - GoalSphere</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="css/styles.css" rel="stylesheet">
</head>
<body class="bg-black min-h-screen flex flex-col">
    <!-- Navbar (same as teams.php) -->
    <?php include 'includes/navbar.php'; ?>

    <!-- Main Content -->
    <div class="pt-16 flex-grow">
        <div class="container mx-auto px-4 py-8">
            <h1 class="text-3xl font-bold text-white mb-8">LaLiga Standings</h1>
            
            <!-- Standings Table -->
            <div class="bg-gray-900 rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-white">
                        <thead class="bg-gray-800">
                            <tr>
                                <th class="text-left p-4">Pos</th>
                                <th class="text-left p-4">Team</th>
                                <th class="text-right p-4">PL</th>
                                <th class="text-right p-4">W</th>
                                <th class="text-right p-4">D</th>
                                <th class="text-right p-4">L</th>
                                <th class="text-right p-4">GD</th>
                                <th class="text-right p-4">PTS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="border-b border-gray-800">
                                <td class="p-4">1</td>
                                <td class="p-4 flex items-center">
                                    <img src="images/barcelona.png" alt="Barcelona" class="w-6 h-6 mr-2">
                                    Barcelona
                                </td>
                                <td class="p-4 text-right">25</td>
                                <td class="p-4 text-right">17</td>
                                <td class="p-4 text-right">3</td>
                                <td class="p-4 text-right">5</td>
                                <td class="p-4 text-right">42</td>
                                <td class="p-4 text-right">54</td>
                            </tr>
                            <tr class="border-b border-gray-800">
                                <td class="p-4">2</td>
                                <td class="p-4 flex items-center">
                                    <img src="images/real-madrid.png" alt="Real Madrid" class="w-6 h-6 mr-2">
                                    Real Madrid
                                </td>
                                <td class="p-4 text-right">25</td>
                                <td class="p-4 text-right">16</td>
                                <td class="p-4 text-right">6</td>
                                <td class="p-4 text-right">3</td>
                                <td class="p-4 text-right">31</td>
                                <td class="p-4 text-right">54</td>
                            </tr>
                            <tr class="border-b border-gray-800">
                                <td class="p-4">3</td>
                                <td class="p-4 flex items-center">
                                    <img src="images/atletico.png" alt="Atlético de Madrid" class="w-6 h-6 mr-2">
                                    Atlético de Madrid
                                </td>
                                <td class="p-4 text-right">25</td>
                                <td class="p-4 text-right">15</td>
                                <td class="p-4 text-right">8</td>
                                <td class="p-4 text-right">2</td>
                                <td class="p-4 text-right">26</td>
                                <td class="p-4 text-right">53</td>
                            </tr>
                            <tr class="border-b border-gray-800">
                                <td class="p-4">4</td>
                                <td class="p-4 flex items-center">
                                    <img src="images/athletic.png" alt="Athletic Club" class="w-6 h-6 mr-2">
                                    Athletic Club
                                </td>
                                <td class="p-4 text-right">25</td>
                                <td class="p-4 text-right">13</td>
                                <td class="p-4 text-right">9</td>
                                <td class="p-4 text-right">3</td>
                                <td class="p-4 text-right">22</td>
                                <td class="p-4 text-right">48</td>
                            </tr>
                            <tr>
                                <td class="p-4">5</td>
                                <td class="p-4 flex items-center">
                                    <img src="images/villarreal.png" alt="Villarreal" class="w-6 h-6 mr-2">
                                    Villarreal
                                </td>
                                <td class="p-4 text-right">25</td>
                                <td class="p-4 text-right">12</td>
                                <td class="p-4 text-right">8</td>
                                <td class="p-4 text-right">5</td>
                                <td class="p-4 text-right">13</td>
                                <td class="p-4 text-right">44</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8 mt-auto">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-xl font-bold mb-4">GoalSphere</h3>
                    <p>Your ultimate football destination</p>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Quick Links</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="hover:text-green-400">About Us</a></li>
                        <li><a href="#" class="hover:text-green-400">Contact</a></li>
                        <li><a href="#" class="hover:text-green-400">Privacy Policy</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>
</body>
</html> 