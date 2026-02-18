<?php

namespace Database\Seeders;

use App\Models\Quiz;
use App\Models\Question;
use Illuminate\Database\Seeder;

class QuizSeeder extends Seeder
{
    public function run()
    {
        // ============================================
        // QUIZ 1: Science
        // ============================================
        $science = Quiz::create([
            'title' => 'Mind-Blowing Science',
            'description' => 'Think you know your atoms from your galaxies? Prove it!',
            'category' => 'Science',
            'difficulty' => 'medium',
            'time_per_question' => 30,
        ]);

        $science->questions()->createMany([
            [
                'question_text' => 'What is the chemical symbol for gold?',
                'option_a' => 'Go',
                'option_b' => 'Au',
                'option_c' => 'Ag',
                'option_d' => 'Gd',
                'correct_answer' => 'b',
                'explanation' => 'Au comes from the Latin word "aurum" meaning gold.',
                'points' => 10,
            ],
            [
                'question_text' => 'How many bones are in the adult human body?',
                'option_a' => '206',
                'option_b' => '215',
                'option_c' => '186',
                'option_d' => '300',
                'correct_answer' => 'a',
                'explanation' => 'Babies are born with about 300 bones, but many fuse together as they grow.',
                'points' => 10,
            ],
            [
                'question_text' => 'What planet is known as the Red Planet?',
                'option_a' => 'Venus',
                'option_b' => 'Jupiter',
                'option_c' => 'Mars',
                'option_d' => 'Saturn',
                'correct_answer' => 'c',
                'explanation' => 'Mars appears red because of iron oxide (rust) on its surface.',
                'points' => 10,
            ],
            [
                'question_text' => 'What is the speed of light approximately?',
                'option_a' => '300,000 km/s',
                'option_b' => '150,000 km/s',
                'option_c' => '500,000 km/s',
                'option_d' => '1,000,000 km/s',
                'correct_answer' => 'a',
                'explanation' => 'Light travels at approximately 299,792 km/s in a vacuum.',
                'points' => 10,
            ],
            [
                'question_text' => 'What gas do plants absorb from the atmosphere?',
                'option_a' => 'Oxygen',
                'option_b' => 'Nitrogen',
                'option_c' => 'Carbon Dioxide',
                'option_d' => 'Hydrogen',
                'correct_answer' => 'c',
                'explanation' => 'Plants absorb CO2 and release oxygen through photosynthesis.',
                'points' => 10,
            ],
            [
                'question_text' => 'What is the largest organ in the human body?',
                'option_a' => 'Liver',
                'option_b' => 'Brain',
                'option_c' => 'Lungs',
                'option_d' => 'Skin',
                'correct_answer' => 'd',
                'explanation' => 'The skin covers about 20 square feet and weighs about 8 pounds in adults.',
                'points' => 10,
            ],
            [
                'question_text' => 'What does DNA stand for?',
                'option_a' => 'Deoxyribose Nucleic Acid',
                'option_b' => 'Deoxyribonucleic Acid',
                'option_c' => 'Dinitrogen Acid',
                'option_d' => 'Dynamic Natural Acid',
                'correct_answer' => 'b',
                'explanation' => 'DNA is the molecule that carries genetic instructions for development and functioning.',
                'points' => 10,
            ],
            [
                'question_text' => 'Which element has the atomic number 1?',
                'option_a' => 'Helium',
                'option_b' => 'Oxygen',
                'option_c' => 'Hydrogen',
                'option_d' => 'Carbon',
                'correct_answer' => 'c',
                'explanation' => 'Hydrogen is the lightest and most abundant element in the universe.',
                'points' => 10,
            ],
        ]);

        // ============================================
        // QUIZ 2: Movies
        // ============================================
        $movies = Quiz::create([
            'title' => 'Ultimate Movie Buff',
            'description' => 'Lights, camera, action! How well do you know your movies?',
            'category' => 'Movies',
            'difficulty' => 'easy',
            'time_per_question' => 25,
        ]);

        $movies->questions()->createMany([
            [
                'question_text' => 'Who directed the movie "Inception"?',
                'option_a' => 'Steven Spielberg',
                'option_b' => 'Christopher Nolan',
                'option_c' => 'James Cameron',
                'option_d' => 'Ridley Scott',
                'correct_answer' => 'b',
                'explanation' => 'Christopher Nolan also directed The Dark Knight trilogy and Interstellar.',
                'points' => 10,
            ],
            [
                'question_text' => 'What is the highest-grossing film of all time (not adjusted for inflation)?',
                'option_a' => 'Avengers: Endgame',
                'option_b' => 'Titanic',
                'option_c' => 'Avatar',
                'option_d' => 'Star Wars: The Force Awakens',
                'correct_answer' => 'c',
                'explanation' => 'Avatar (2009) holds the record at over $2.9 billion worldwide.',
                'points' => 10,
            ],
            [
                'question_text' => 'In "The Matrix", what color pill does Neo take?',
                'option_a' => 'Blue',
                'option_b' => 'Green',
                'option_c' => 'Red',
                'option_d' => 'Yellow',
                'correct_answer' => 'c',
                'explanation' => 'The red pill reveals the truth about the Matrix.',
                'points' => 10,
            ],
            [
                'question_text' => 'What year was the first "Toy Story" released?',
                'option_a' => '1993',
                'option_b' => '1995',
                'option_c' => '1997',
                'option_d' => '1999',
                'correct_answer' => 'b',
                'explanation' => 'Toy Story was the first feature-length computer-animated film.',
                'points' => 10,
            ],
            [
                'question_text' => 'Who played Jack in the movie "Titanic"?',
                'option_a' => 'Brad Pitt',
                'option_b' => 'Tom Cruise',
                'option_c' => 'Leonardo DiCaprio',
                'option_d' => 'Matt Damon',
                'correct_answer' => 'c',
                'explanation' => 'DiCaprio was 22 years old when Titanic was released in 1997.',
                'points' => 10,
            ],
            [
                'question_text' => 'What is the name of the fictional country in "Black Panther"?',
                'option_a' => 'Zamunda',
                'option_b' => 'Wakanda',
                'option_c' => 'Genosha',
                'option_d' => 'Latveria',
                'correct_answer' => 'b',
                'explanation' => 'Wakanda is a technologically advanced African nation rich in vibranium.',
                'points' => 10,
            ],
            [
                'question_text' => 'Which movie features the quote "Here\'s looking at you, kid"?',
                'option_a' => 'Gone with the Wind',
                'option_b' => 'The Godfather',
                'option_c' => 'Casablanca',
                'option_d' => 'Citizen Kane',
                'correct_answer' => 'c',
                'explanation' => 'This iconic line is spoken by Humphrey Bogart in the 1942 classic.',
                'points' => 10,
            ],
        ]);

        // ============================================
        // QUIZ 3: Geography
        // ============================================
        $geography = Quiz::create([
            'title' => 'World Explorer',
            'description' => 'Travel the world from your screen! Test your geography knowledge.',
            'category' => 'Geography',
            'difficulty' => 'medium',
            'time_per_question' => 30,
        ]);

        $geography->questions()->createMany([
            [
                'question_text' => 'What is the smallest country in the world?',
                'option_a' => 'Monaco',
                'option_b' => 'Vatican City',
                'option_c' => 'San Marino',
                'option_d' => 'Liechtenstein',
                'correct_answer' => 'b',
                'explanation' => 'Vatican City covers only about 44 hectares (110 acres).',
                'points' => 10,
            ],
            [
                'question_text' => 'Which river is the longest in the world?',
                'option_a' => 'Amazon',
                'option_b' => 'Mississippi',
                'option_c' => 'Yangtze',
                'option_d' => 'Nile',
                'correct_answer' => 'd',
                'explanation' => 'The Nile stretches about 6,650 km through northeastern Africa.',
                'points' => 10,
            ],
            [
                'question_text' => 'What is the capital of Australia?',
                'option_a' => 'Sydney',
                'option_b' => 'Melbourne',
                'option_c' => 'Canberra',
                'option_d' => 'Perth',
                'correct_answer' => 'c',
                'explanation' => 'Canberra was chosen as a compromise between rivals Sydney and Melbourne.',
                'points' => 10,
            ],
            [
                'question_text' => 'Mount Everest is located on the border of which two countries?',
                'option_a' => 'India and China',
                'option_b' => 'Nepal and Tibet/China',
                'option_c' => 'Pakistan and India',
                'option_d' => 'Bhutan and Nepal',
                'correct_answer' => 'b',
                'explanation' => 'Mount Everest stands at 8,849 meters (29,032 feet) above sea level.',
                'points' => 10,
            ],
            [
                'question_text' => 'Which desert is the largest in the world?',
                'option_a' => 'Sahara',
                'option_b' => 'Arabian',
                'option_c' => 'Antarctic',
                'option_d' => 'Gobi',
                'correct_answer' => 'c',
                'explanation' => 'Antarctica is technically the largest desert — a desert is defined by low precipitation, not heat!',
                'points' => 10,
            ],
            [
                'question_text' => 'Which country has the most natural lakes?',
                'option_a' => 'United States',
                'option_b' => 'Russia',
                'option_c' => 'Canada',
                'option_d' => 'Brazil',
                'correct_answer' => 'c',
                'explanation' => 'Canada has over 2 million lakes — more than all other countries combined!',
                'points' => 10,
            ],
            [
                'question_text' => 'What is the only continent without a desert?',
                'option_a' => 'Europe',
                'option_b' => 'South America',
                'option_c' => 'Antarctica',
                'option_d' => 'None — all continents have deserts',
                'correct_answer' => 'a',
                'explanation' => 'Europe is the only continent that does not have a desert.',
                'points' => 10,
            ],
        ]);

        // ============================================
        // QUIZ 4: Technology
        // ============================================
        $tech = Quiz::create([
            'title' => 'Tech Genius',
            'description' => 'Are you a true techie? From coding to gadgets, let\'s find out!',
            'category' => 'Technology',
            'difficulty' => 'hard',
            'time_per_question' => 35,
        ]);

        $tech->questions()->createMany([
            [
                'question_text' => 'What does "HTTP" stand for?',
                'option_a' => 'HyperText Transfer Protocol',
                'option_b' => 'High Tech Transfer Process',
                'option_c' => 'Hyper Transfer Text Protocol',
                'option_d' => 'Home Tool Transfer Protocol',
                'correct_answer' => 'a',
                'explanation' => 'HTTP is the foundation of data communication for the World Wide Web.',
                'points' => 10,
            ],
            [
                'question_text' => 'Who is considered the father of computer science?',
                'option_a' => 'Bill Gates',
                'option_b' => 'Alan Turing',
                'option_c' => 'Steve Jobs',
                'option_d' => 'Tim Berners-Lee',
                'correct_answer' => 'b',
                'explanation' => 'Alan Turing created the concept of the Turing machine and helped crack the Enigma code in WWII.',
                'points' => 10,
            ],
            [
                'question_text' => 'What programming language was created by Guido van Rossum?',
                'option_a' => 'Java',
                'option_b' => 'Ruby',
                'option_c' => 'Python',
                'option_d' => 'PHP',
                'correct_answer' => 'c',
                'explanation' => 'Python was first released in 1991 and named after Monty Python\'s Flying Circus.',
                'points' => 10,
            ],
            [
                'question_text' => 'What year was the first iPhone released?',
                'option_a' => '2005',
                'option_b' => '2006',
                'option_c' => '2007',
                'option_d' => '2008',
                'correct_answer' => 'c',
                'explanation' => 'Steve Jobs announced the iPhone on January 9, 2007.',
                'points' => 10,
            ],
            [
                'question_text' => 'What does "SQL" stand for?',
                'option_a' => 'Structured Query Language',
                'option_b' => 'Simple Question Language',
                'option_c' => 'Sequential Query Logic',
                'option_d' => 'Standard Query Language',
                'correct_answer' => 'a',
                'explanation' => 'SQL is the standard language for managing relational databases.',
                'points' => 10,
            ],
            [
                'question_text' => 'How many bits are in a byte?',
                'option_a' => '4',
                'option_b' => '8',
                'option_c' => '16',
                'option_d' => '32',
                'correct_answer' => 'b',
                'explanation' => '1 byte = 8 bits. A bit is the smallest unit of data in computing.',
                'points' => 10,
            ],
            [
                'question_text' => 'What company developed the Android operating system?',
                'option_a' => 'Apple',
                'option_b' => 'Microsoft',
                'option_c' => 'Google',
                'option_d' => 'Samsung',
                'correct_answer' => 'c',
                'explanation' => 'Android was originally developed by Android Inc., which Google bought in 2005.',
                'points' => 10,
            ],
            [
                'question_text' => 'What does "CSS" stand for?',
                'option_a' => 'Computer Style Sheets',
                'option_b' => 'Cascading Style Sheets',
                'option_c' => 'Creative Style System',
                'option_d' => 'Colorful Style Sheets',
                'correct_answer' => 'b',
                'explanation' => 'CSS is used to style and layout web pages alongside HTML.',
                'points' => 10,
            ],
        ]);

        // ============================================
        // QUIZ 5: Food & Drink
        // ============================================
        $food = Quiz::create([
            'title' => 'Foodie Challenge',
            'description' => 'A delicious quiz for food lovers! How well do you know your cuisine?',
            'category' => 'Food',
            'difficulty' => 'easy',
            'time_per_question' => 25,
        ]);

        $food->questions()->createMany([
            [
                'question_text' => 'What country is sushi originally from?',
                'option_a' => 'China',
                'option_b' => 'Korea',
                'option_c' => 'Japan',
                'option_d' => 'Thailand',
                'correct_answer' => 'c',
                'explanation' => 'Sushi originated in Japan, though a form of preserved fish and rice existed in Southeast Asia.',
                'points' => 10,
            ],
            [
                'question_text' => 'What is the main ingredient in guacamole?',
                'option_a' => 'Tomato',
                'option_b' => 'Avocado',
                'option_c' => 'Onion',
                'option_d' => 'Pepper',
                'correct_answer' => 'b',
                'explanation' => 'The word "guacamole" comes from the Aztec word "ahuacamolli" meaning avocado sauce.',
                'points' => 10,
            ],
            [
                'question_text' => 'Which spice is the most expensive in the world by weight?',
                'option_a' => 'Vanilla',
                'option_b' => 'Cardamom',
                'option_c' => 'Saffron',
                'option_d' => 'Cinnamon',
                'correct_answer' => 'c',
                'explanation' => 'Saffron can cost up to $5,000 per pound because each flower produces only 3 stigmas.',
                'points' => 10,
            ],
            [
                'question_text' => 'What type of pasta is shaped like little ears?',
                'option_a' => 'Farfalle',
                'option_b' => 'Orecchiette',
                'option_c' => 'Penne',
                'option_d' => 'Rotini',
                'correct_answer' => 'b',
                'explanation' => '"Orecchiette" literally means "little ears" in Italian.',
                'points' => 10,
            ],
            [
                'question_text' => 'What fruit is known as the "king of fruits"?',
                'option_a' => 'Mango',
                'option_b' => 'Pineapple',
                'option_c' => 'Durian',
                'option_d' => 'Jackfruit',
                'correct_answer' => 'c',
                'explanation' => 'Durian is called the king of fruits in Southeast Asia despite its strong smell!',
                'points' => 10,
            ],
            [
                'question_text' => 'Which country produces the most coffee in the world?',
                'option_a' => 'Colombia',
                'option_b' => 'Ethiopia',
                'option_c' => 'Vietnam',
                'option_d' => 'Brazil',
                'correct_answer' => 'd',
                'explanation' => 'Brazil produces about one-third of the world\'s coffee supply.',
                'points' => 10,
            ],
        ]);

        // ============================================
        // QUIZ 6: Sports
        // ============================================
        $sports = Quiz::create([
            'title' => 'Sports Fanatic',
            'description' => 'From football to Formula 1 — test your sports trivia!',
            'category' => 'Sports',
            'difficulty' => 'medium',
            'time_per_question' => 25,
        ]);

        $sports->questions()->createMany([
            [
                'question_text' => 'How many players are on a standard soccer team on the field?',
                'option_a' => '9',
                'option_b' => '10',
                'option_c' => '11',
                'option_d' => '12',
                'correct_answer' => 'c',
                'explanation' => 'Each team fields 11 players including the goalkeeper.',
                'points' => 10,
            ],
            [
                'question_text' => 'In which sport is the term "love" used to mean zero?',
                'option_a' => 'Badminton',
                'option_b' => 'Tennis',
                'option_c' => 'Table Tennis',
                'option_d' => 'Squash',
                'correct_answer' => 'b',
                'explanation' => 'The origin of "love" for zero is debated — it may come from the French "l\'oeuf" (the egg, shaped like a zero).',
                'points' => 10,
            ],
            [
                'question_text' => 'Which country has won the most FIFA World Cup titles?',
                'option_a' => 'Germany',
                'option_b' => 'Argentina',
                'option_c' => 'Brazil',
                'option_d' => 'Italy',
                'correct_answer' => 'c',
                'explanation' => 'Brazil has won the FIFA World Cup 5 times (1958, 1962, 1970, 1994, 2002).',
                'points' => 10,
            ],
            [
                'question_text' => 'What is the diameter of a basketball hoop in inches?',
                'option_a' => '16 inches',
                'option_b' => '18 inches',
                'option_c' => '20 inches',
                'option_d' => '22 inches',
                'correct_answer' => 'b',
                'explanation' => 'The rim is 18 inches (46 cm) in diameter — almost twice the ball\'s diameter.',
                'points' => 10,
            ],
            [
                'question_text' => 'How long is an Olympic swimming pool?',
                'option_a' => '25 meters',
                'option_b' => '50 meters',
                'option_c' => '75 meters',
                'option_d' => '100 meters',
                'correct_answer' => 'b',
                'explanation' => 'An Olympic pool is 50 meters long, 25 meters wide, and at least 2 meters deep.',
                'points' => 10,
            ],
            [
                'question_text' => 'Which sport uses the largest playing field?',
                'option_a' => 'American Football',
                'option_b' => 'Cricket',
                'option_c' => 'Polo',
                'option_d' => 'Rugby',
                'correct_answer' => 'c',
                'explanation' => 'A polo field can be up to 300 yards long and 160 yards wide — about 9 football fields!',
                'points' => 10,
            ],
        ]);
    }
}
