<?php

namespace App\Services;

class QuestionBank
{
    /**
     * Open Trivia DB category IDs mapped to friendly names.
     */
    protected static array $onlineCategories = [
        'General Knowledge' => 9,
        'Science & Nature' => 17,
        'Science: Computers' => 18,
        'Mathematics' => 19,
        'History' => 23,
        'Geography' => 22,
        'Movies' => 11,
        'Music' => 12,
        'Television' => 14,
        'Video Games' => 15,
        'Sports' => 21,
        'Animals' => 27,
        'Mythology' => 20,
        'Art' => 25,
        'Celebrities' => 26,
        'Vehicles' => 28,
        'Comics' => 29,
        'Anime & Manga' => 31,
        'Cartoons' => 32,
    ];

    /**
     * Get all available online categories.
     */
    public static function onlineCategories(): array
    {
        return array_keys(static::$onlineCategories);
    }

    /**
     * Get all available offline categories.
     */
    public static function categories(): array
    {
        return array_keys(static::bank());
    }

    /**
     * Fetch questions from Open Trivia Database API.
     * Returns formatted questions or empty array on failure.
     */
    public static function fetchOnline(string $category, int $count, string $difficulty = 'medium'): array
    {
        $categoryId = static::$onlineCategories[$category] ?? null;

        if (!$categoryId) {
            return [];
        }

        // Build API URL
        $url = 'https://opentdb.com/api.php?' . http_build_query([
            'amount' => min($count, 50),
            'category' => $categoryId,
            'difficulty' => $difficulty,
            'type' => 'multiple',
        ]);

        try {
            // Use cURL for reliable HTTP requests
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_USERAGENT => 'QuizMaster/1.0',
            ]);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200 || empty($response)) {
                return [];
            }

            $data = json_decode($response, true);

            if (!$data || $data['response_code'] !== 0 || empty($data['results'])) {
                return [];
            }

            return static::formatApiQuestions($data['results']);

        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Format API questions to match our internal structure.
     */
    protected static function formatApiQuestions(array $apiQuestions): array
    {
        $formatted = [];

        foreach ($apiQuestions as $q) {
            // Decode HTML entities from the API
            $correctAnswer = html_entity_decode($q['correct_answer'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $incorrectAnswers = array_map(function ($a) {
                return html_entity_decode($a, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            }, $q['incorrect_answers']);

            // Build options array: put correct + incorrect together, then shuffle
            $options = $incorrectAnswers;
            $options[] = $correctAnswer;
            shuffle($options);

            // Find which letter (a/b/c/d) has the correct answer
            $correctLetter = 'a';
            foreach (['a', 'b', 'c', 'd'] as $i => $letter) {
                if (isset($options[$i]) && $options[$i] === $correctAnswer) {
                    $correctLetter = $letter;
                    break;
                }
            }

            $questionText = html_entity_decode($q['question'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $categoryName = html_entity_decode($q['category'], ENT_QUOTES | ENT_HTML5, 'UTF-8');

            $formatted[] = [
                'question_text' => $questionText,
                'option_a' => $options[0] ?? '',
                'option_b' => $options[1] ?? '',
                'option_c' => $options[2] ?? '',
                'option_d' => $options[3] ?? '',
                'correct_answer' => $correctLetter,
                'explanation' => 'The correct answer is: ' . $correctAnswer . ' (Category: ' . $categoryName . ')',
            ];
        }

        return $formatted;
    }

    /**
     * Smart fetch: try online first, fall back to offline bank.
     * Returns ['questions' => [...], 'source' => 'online'|'offline']
     */
    public static function smartFetch(string $category, int $count, string $difficulty = 'medium'): array
    {
        // Try online first if category exists in online list
        if (isset(static::$onlineCategories[$category])) {
            $questions = static::fetchOnline($category, $count, $difficulty);
            if (!empty($questions)) {
                return ['questions' => $questions, 'source' => 'online'];
            }
        }

        // Fall back to offline bank
        $questions = static::random($category, $count);
        if (!empty($questions)) {
            return ['questions' => $questions, 'source' => 'offline'];
        }

        return ['questions' => [], 'source' => 'none'];
    }

    /**
     * Get random questions from the offline bank.
     */
    public static function random(string $category, int $count): array
    {
        $bank = static::bank();

        // Try exact match first
        if (isset($bank[$category])) {
            $questions = $bank[$category];
            shuffle($questions);
            return array_slice($questions, 0, min($count, count($questions)));
        }

        // Try case-insensitive partial match
        foreach ($bank as $cat => $questions) {
            if (stripos($cat, $category) !== false || stripos($category, $cat) !== false) {
                shuffle($questions);
                return array_slice($questions, 0, min($count, count($questions)));
            }
        }

        return [];
    }

    /**
     * Get number of available offline questions per category.
     */
    public static function availableCounts(): array
    {
        $counts = [];
        foreach (static::bank() as $cat => $questions) {
            $counts[$cat] = count($questions);
        }
        return $counts;
    }

    /**
     * The massive question bank.
     */
    protected static function bank(): array
    {
        return [
            'Science' => [
                ['question_text' => 'What is the chemical symbol for water?', 'option_a' => 'H2O', 'option_b' => 'CO2', 'option_c' => 'NaCl', 'option_d' => 'O2', 'correct_answer' => 'a', 'explanation' => 'Water is made up of two hydrogen atoms and one oxygen atom.'],
                ['question_text' => 'What planet is closest to the Sun?', 'option_a' => 'Venus', 'option_b' => 'Mercury', 'option_c' => 'Earth', 'option_d' => 'Mars', 'correct_answer' => 'b', 'explanation' => 'Mercury orbits the Sun at an average distance of about 58 million km.'],
                ['question_text' => 'What is the powerhouse of the cell?', 'option_a' => 'Nucleus', 'option_b' => 'Ribosome', 'option_c' => 'Mitochondria', 'option_d' => 'Golgi apparatus', 'correct_answer' => 'c', 'explanation' => 'Mitochondria generate most of the cell\'s ATP energy.'],
                ['question_text' => 'How many chromosomes do humans have?', 'option_a' => '23', 'option_b' => '44', 'option_c' => '46', 'option_d' => '48', 'correct_answer' => 'c', 'explanation' => 'Humans have 23 pairs, totaling 46 chromosomes.'],
                ['question_text' => 'What is the hardest natural substance on Earth?', 'option_a' => 'Gold', 'option_b' => 'Iron', 'option_c' => 'Diamond', 'option_d' => 'Quartz', 'correct_answer' => 'c', 'explanation' => 'Diamond scores 10 on the Mohs hardness scale.'],
                ['question_text' => 'What gas makes up most of Earth\'s atmosphere?', 'option_a' => 'Oxygen', 'option_b' => 'Carbon Dioxide', 'option_c' => 'Hydrogen', 'option_d' => 'Nitrogen', 'correct_answer' => 'd', 'explanation' => 'Nitrogen makes up about 78% of Earth\'s atmosphere.'],
                ['question_text' => 'What is the boiling point of water in Celsius?', 'option_a' => '90°C', 'option_b' => '100°C', 'option_c' => '110°C', 'option_d' => '120°C', 'correct_answer' => 'b', 'explanation' => 'Water boils at 100°C (212°F) at standard atmospheric pressure.'],
                ['question_text' => 'Which planet has the most moons?', 'option_a' => 'Jupiter', 'option_b' => 'Saturn', 'option_c' => 'Uranus', 'option_d' => 'Neptune', 'correct_answer' => 'b', 'explanation' => 'Saturn has over 140 known moons, surpassing Jupiter.'],
                ['question_text' => 'What type of animal is a dolphin?', 'option_a' => 'Fish', 'option_b' => 'Reptile', 'option_c' => 'Mammal', 'option_d' => 'Amphibian', 'correct_answer' => 'c', 'explanation' => 'Dolphins are marine mammals that breathe air and nurse their young.'],
                ['question_text' => 'What is the closest star to Earth?', 'option_a' => 'Proxima Centauri', 'option_b' => 'Alpha Centauri', 'option_c' => 'The Sun', 'option_d' => 'Sirius', 'correct_answer' => 'c', 'explanation' => 'The Sun is about 93 million miles from Earth.'],
                ['question_text' => 'What vitamin does the Sun help your body produce?', 'option_a' => 'Vitamin A', 'option_b' => 'Vitamin B', 'option_c' => 'Vitamin C', 'option_d' => 'Vitamin D', 'correct_answer' => 'd', 'explanation' => 'UVB rays from sunlight trigger Vitamin D synthesis in the skin.'],
                ['question_text' => 'What is the most abundant element in the universe?', 'option_a' => 'Oxygen', 'option_b' => 'Carbon', 'option_c' => 'Hydrogen', 'option_d' => 'Helium', 'correct_answer' => 'c', 'explanation' => 'Hydrogen makes up about 75% of all normal matter by mass.'],
                ['question_text' => 'How many teeth does an adult human typically have?', 'option_a' => '28', 'option_b' => '30', 'option_c' => '32', 'option_d' => '34', 'correct_answer' => 'c', 'explanation' => 'Adults have 32 teeth including 4 wisdom teeth.'],
                ['question_text' => 'What is the pH of pure water?', 'option_a' => '5', 'option_b' => '7', 'option_c' => '9', 'option_d' => '10', 'correct_answer' => 'b', 'explanation' => 'Pure water has a neutral pH of 7.'],
                ['question_text' => 'Which blood type is a universal donor?', 'option_a' => 'A+', 'option_b' => 'B+', 'option_c' => 'AB+', 'option_d' => 'O-', 'correct_answer' => 'd', 'explanation' => 'O-negative blood can be given to patients of any blood type.'],
                ['question_text' => 'What force keeps planets in orbit around the Sun?', 'option_a' => 'Magnetism', 'option_b' => 'Gravity', 'option_c' => 'Friction', 'option_d' => 'Inertia', 'correct_answer' => 'b', 'explanation' => 'Gravity is the force of attraction between massive objects.'],
                ['question_text' => 'What is the chemical formula for table salt?', 'option_a' => 'NaCl', 'option_b' => 'KCl', 'option_c' => 'CaCl2', 'option_d' => 'MgCl2', 'correct_answer' => 'a', 'explanation' => 'Table salt is sodium chloride (NaCl).'],
                ['question_text' => 'Which organ filters blood in the human body?', 'option_a' => 'Heart', 'option_b' => 'Lungs', 'option_c' => 'Kidneys', 'option_d' => 'Liver', 'correct_answer' => 'c', 'explanation' => 'The kidneys filter about 200 liters of blood daily.'],
                ['question_text' => 'What is the study of earthquakes called?', 'option_a' => 'Meteorology', 'option_b' => 'Geology', 'option_c' => 'Seismology', 'option_d' => 'Volcanology', 'correct_answer' => 'c', 'explanation' => 'Seismology comes from the Greek word "seismos" meaning earthquake.'],
                ['question_text' => 'How long does it take light from the Sun to reach Earth?', 'option_a' => '8 seconds', 'option_b' => '8 minutes', 'option_c' => '8 hours', 'option_d' => '80 minutes', 'correct_answer' => 'b', 'explanation' => 'Light travels from the Sun to Earth in about 8 minutes and 20 seconds.'],
            ],
            'History' => [
                ['question_text' => 'In what year did World War II end?', 'option_a' => '1943', 'option_b' => '1944', 'option_c' => '1945', 'option_d' => '1946', 'correct_answer' => 'c', 'explanation' => 'WWII ended in 1945 with the surrender of Japan.'],
                ['question_text' => 'Who was the first President of the United States?', 'option_a' => 'John Adams', 'option_b' => 'Thomas Jefferson', 'option_c' => 'George Washington', 'option_d' => 'Benjamin Franklin', 'correct_answer' => 'c', 'explanation' => 'George Washington served from 1789 to 1797.'],
                ['question_text' => 'What ancient civilization built the pyramids?', 'option_a' => 'Romans', 'option_b' => 'Greeks', 'option_c' => 'Egyptians', 'option_d' => 'Persians', 'correct_answer' => 'c', 'explanation' => 'The Great Pyramid of Giza was built around 2560 BC.'],
                ['question_text' => 'Who discovered America in 1492?', 'option_a' => 'Vasco da Gama', 'option_b' => 'Ferdinand Magellan', 'option_c' => 'Christopher Columbus', 'option_d' => 'Amerigo Vespucci', 'correct_answer' => 'c', 'explanation' => 'Columbus landed in the Bahamas on October 12, 1492.'],
                ['question_text' => 'What year did the Berlin Wall fall?', 'option_a' => '1987', 'option_b' => '1988', 'option_c' => '1989', 'option_d' => '1990', 'correct_answer' => 'c', 'explanation' => 'The Berlin Wall fell on November 9, 1989.'],
                ['question_text' => 'Who was the first person to walk on the Moon?', 'option_a' => 'Buzz Aldrin', 'option_b' => 'Neil Armstrong', 'option_c' => 'John Glenn', 'option_d' => 'Yuri Gagarin', 'correct_answer' => 'b', 'explanation' => 'Neil Armstrong stepped onto the Moon on July 20, 1969.'],
                ['question_text' => 'What was the name of the ship that sank in 1912?', 'option_a' => 'Lusitania', 'option_b' => 'Titanic', 'option_c' => 'Britannic', 'option_d' => 'Olympic', 'correct_answer' => 'b', 'explanation' => 'The Titanic sank on April 15, 1912 after hitting an iceberg.'],
                ['question_text' => 'Which empire was ruled by Julius Caesar?', 'option_a' => 'Greek Empire', 'option_b' => 'Persian Empire', 'option_c' => 'Roman Empire', 'option_d' => 'Ottoman Empire', 'correct_answer' => 'c', 'explanation' => 'Caesar was a Roman dictator assassinated in 44 BC.'],
                ['question_text' => 'What year did World War I begin?', 'option_a' => '1912', 'option_b' => '1914', 'option_c' => '1916', 'option_d' => '1918', 'correct_answer' => 'b', 'explanation' => 'WWI began on July 28, 1914 and lasted until November 11, 1918.'],
                ['question_text' => 'Who painted the Mona Lisa?', 'option_a' => 'Michelangelo', 'option_b' => 'Raphael', 'option_c' => 'Leonardo da Vinci', 'option_d' => 'Donatello', 'correct_answer' => 'c', 'explanation' => 'Da Vinci painted the Mona Lisa between 1503 and 1519.'],
                ['question_text' => 'What ancient wonder was located in Alexandria?', 'option_a' => 'Colossus', 'option_b' => 'Lighthouse', 'option_c' => 'Hanging Gardens', 'option_d' => 'Temple of Artemis', 'correct_answer' => 'b', 'explanation' => 'The Lighthouse of Alexandria was one of the Seven Wonders of the Ancient World.'],
                ['question_text' => 'Who wrote the Declaration of Independence?', 'option_a' => 'George Washington', 'option_b' => 'Benjamin Franklin', 'option_c' => 'Thomas Jefferson', 'option_d' => 'John Adams', 'correct_answer' => 'c', 'explanation' => 'Thomas Jefferson drafted it in June 1776.'],
                ['question_text' => 'What country was formerly known as Persia?', 'option_a' => 'Iraq', 'option_b' => 'Iran', 'option_c' => 'Turkey', 'option_d' => 'Afghanistan', 'correct_answer' => 'b', 'explanation' => 'Iran was officially known as Persia until 1935.'],
                ['question_text' => 'Who was the famous queen of ancient Egypt?', 'option_a' => 'Nefertiti', 'option_b' => 'Cleopatra', 'option_c' => 'Hatshepsut', 'option_d' => 'Isis', 'correct_answer' => 'b', 'explanation' => 'Cleopatra VII was the last active ruler of the Ptolemaic Kingdom.'],
                ['question_text' => 'What war was fought between the North and South in the US?', 'option_a' => 'Revolutionary War', 'option_b' => 'Civil War', 'option_c' => 'War of 1812', 'option_d' => 'Mexican-American War', 'correct_answer' => 'b', 'explanation' => 'The American Civil War lasted from 1861 to 1865.'],
                ['question_text' => 'What year did humans first fly in an airplane?', 'option_a' => '1899', 'option_b' => '1901', 'option_c' => '1903', 'option_d' => '1905', 'correct_answer' => 'c', 'explanation' => 'The Wright brothers flew at Kitty Hawk on December 17, 1903.'],
                ['question_text' => 'Who was the leader of the Soviet Union during WWII?', 'option_a' => 'Lenin', 'option_b' => 'Stalin', 'option_c' => 'Khrushchev', 'option_d' => 'Trotsky', 'correct_answer' => 'b', 'explanation' => 'Joseph Stalin led the USSR from the mid-1920s until his death in 1953.'],
                ['question_text' => 'What civilization invented paper?', 'option_a' => 'Roman', 'option_b' => 'Egyptian', 'option_c' => 'Chinese', 'option_d' => 'Indian', 'correct_answer' => 'c', 'explanation' => 'Paper was invented in China around 105 AD by Cai Lun.'],
                ['question_text' => 'What famous structure was built to keep invaders out of China?', 'option_a' => 'Forbidden City', 'option_b' => 'Great Wall of China', 'option_c' => 'Terracotta Army', 'option_d' => 'Temple of Heaven', 'correct_answer' => 'b', 'explanation' => 'The Great Wall stretches over 13,000 miles.'],
                ['question_text' => 'In which city were the first modern Olympic Games held?', 'option_a' => 'Rome', 'option_b' => 'London', 'option_c' => 'Athens', 'option_d' => 'Paris', 'correct_answer' => 'c', 'explanation' => 'The first modern Olympics were held in Athens, Greece in 1896.'],
            ],
            'Geography' => [
                ['question_text' => 'What is the largest continent by area?', 'option_a' => 'Africa', 'option_b' => 'Asia', 'option_c' => 'North America', 'option_d' => 'Europe', 'correct_answer' => 'b', 'explanation' => 'Asia covers about 44.58 million square kilometers.'],
                ['question_text' => 'What is the longest river in the world?', 'option_a' => 'Amazon', 'option_b' => 'Mississippi', 'option_c' => 'Nile', 'option_d' => 'Yangtze', 'correct_answer' => 'c', 'explanation' => 'The Nile is approximately 6,650 km long.'],
                ['question_text' => 'What ocean is the largest?', 'option_a' => 'Atlantic', 'option_b' => 'Indian', 'option_c' => 'Arctic', 'option_d' => 'Pacific', 'correct_answer' => 'd', 'explanation' => 'The Pacific Ocean covers about 165.25 million square kilometers.'],
                ['question_text' => 'What is the capital of Japan?', 'option_a' => 'Osaka', 'option_b' => 'Kyoto', 'option_c' => 'Tokyo', 'option_d' => 'Nagoya', 'correct_answer' => 'c', 'explanation' => 'Tokyo has been the capital of Japan since 1868.'],
                ['question_text' => 'Which country has the largest population?', 'option_a' => 'India', 'option_b' => 'China', 'option_c' => 'USA', 'option_d' => 'Indonesia', 'correct_answer' => 'a', 'explanation' => 'India surpassed China as the most populous country in 2023.'],
                ['question_text' => 'What is the tallest mountain in the world?', 'option_a' => 'K2', 'option_b' => 'Kangchenjunga', 'option_c' => 'Mount Everest', 'option_d' => 'Lhotse', 'correct_answer' => 'c', 'explanation' => 'Mount Everest stands at 8,849 meters above sea level.'],
                ['question_text' => 'What country is known as the Land of the Rising Sun?', 'option_a' => 'China', 'option_b' => 'South Korea', 'option_c' => 'Japan', 'option_d' => 'Thailand', 'correct_answer' => 'c', 'explanation' => 'Japan\'s name in Japanese (Nihon) literally means "origin of the sun."'],
                ['question_text' => 'What is the smallest continent?', 'option_a' => 'Europe', 'option_b' => 'Antarctica', 'option_c' => 'Australia', 'option_d' => 'South America', 'correct_answer' => 'c', 'explanation' => 'Australia/Oceania is the smallest continent at about 8.5 million square km.'],
                ['question_text' => 'Through how many countries does the Amazon River flow?', 'option_a' => '3', 'option_b' => '5', 'option_c' => '7', 'option_d' => '9', 'correct_answer' => 'd', 'explanation' => 'The Amazon flows through 9 South American countries.'],
                ['question_text' => 'What strait separates Europe from Africa?', 'option_a' => 'Strait of Hormuz', 'option_b' => 'Strait of Gibraltar', 'option_c' => 'Bosphorus', 'option_d' => 'Strait of Malacca', 'correct_answer' => 'b', 'explanation' => 'The Strait of Gibraltar is only 14.3 km wide at its narrowest point.'],
                ['question_text' => 'What is the driest continent?', 'option_a' => 'Africa', 'option_b' => 'Australia', 'option_c' => 'Antarctica', 'option_d' => 'Asia', 'correct_answer' => 'c', 'explanation' => 'Antarctica receives very little precipitation, making it a polar desert.'],
                ['question_text' => 'What is the capital of Brazil?', 'option_a' => 'Rio de Janeiro', 'option_b' => 'São Paulo', 'option_c' => 'Brasília', 'option_d' => 'Salvador', 'correct_answer' => 'c', 'explanation' => 'Brasília was built in the 1960s to be the new capital.'],
                ['question_text' => 'Which African country is the largest by area?', 'option_a' => 'Nigeria', 'option_b' => 'Sudan', 'option_c' => 'Algeria', 'option_d' => 'Congo', 'correct_answer' => 'c', 'explanation' => 'Algeria covers about 2.38 million square kilometers.'],
                ['question_text' => 'What sea is the saltiest in the world?', 'option_a' => 'Red Sea', 'option_b' => 'Dead Sea', 'option_c' => 'Caspian Sea', 'option_d' => 'Black Sea', 'correct_answer' => 'b', 'explanation' => 'The Dead Sea has a salinity of about 34%, nearly 10 times saltier than the ocean.'],
                ['question_text' => 'What country has the most time zones?', 'option_a' => 'Russia', 'option_b' => 'USA', 'option_c' => 'France', 'option_d' => 'China', 'correct_answer' => 'c', 'explanation' => 'France has 12 time zones due to its overseas territories.'],
                ['question_text' => 'What is the capital of Canada?', 'option_a' => 'Toronto', 'option_b' => 'Vancouver', 'option_c' => 'Montreal', 'option_d' => 'Ottawa', 'correct_answer' => 'd', 'explanation' => 'Ottawa was chosen as capital by Queen Victoria in 1857.'],
                ['question_text' => 'Which island is the largest in the world?', 'option_a' => 'Borneo', 'option_b' => 'Madagascar', 'option_c' => 'Greenland', 'option_d' => 'New Guinea', 'correct_answer' => 'c', 'explanation' => 'Greenland covers about 2.16 million square km (Australia is a continent).'],
                ['question_text' => 'What European country is shaped like a boot?', 'option_a' => 'Spain', 'option_b' => 'Italy', 'option_c' => 'Greece', 'option_d' => 'Portugal', 'correct_answer' => 'b', 'explanation' => 'Italy\'s distinctive boot shape is one of the most recognizable in the world.'],
                ['question_text' => 'What is the deepest ocean trench?', 'option_a' => 'Tonga Trench', 'option_b' => 'Java Trench', 'option_c' => 'Mariana Trench', 'option_d' => 'Puerto Rico Trench', 'correct_answer' => 'c', 'explanation' => 'The Mariana Trench reaches about 11,034 meters deep.'],
                ['question_text' => 'How many countries are in Africa?', 'option_a' => '44', 'option_b' => '48', 'option_c' => '54', 'option_d' => '60', 'correct_answer' => 'c', 'explanation' => 'Africa has 54 recognized sovereign countries.'],
            ],
            'Movies' => [
                ['question_text' => 'What is the name of the wizard school in Harry Potter?', 'option_a' => 'Beauxbatons', 'option_b' => 'Hogwarts', 'option_c' => 'Durmstrang', 'option_d' => 'Ilvermorny', 'correct_answer' => 'b', 'explanation' => 'Hogwarts School of Witchcraft and Wizardry is located in Scotland.'],
                ['question_text' => 'Who plays Iron Man in the Marvel Cinematic Universe?', 'option_a' => 'Chris Evans', 'option_b' => 'Chris Hemsworth', 'option_c' => 'Robert Downey Jr.', 'option_d' => 'Mark Ruffalo', 'correct_answer' => 'c', 'explanation' => 'RDJ played Tony Stark/Iron Man from 2008 to 2019.'],
                ['question_text' => 'What movie features a character named Forrest Gump?', 'option_a' => 'Cast Away', 'option_b' => 'Forrest Gump', 'option_c' => 'The Green Mile', 'option_d' => 'Big', 'correct_answer' => 'b', 'explanation' => 'Tom Hanks starred in the 1994 classic directed by Robert Zemeckis.'],
                ['question_text' => 'In Finding Nemo, what type of fish is Nemo?', 'option_a' => 'Blue Tang', 'option_b' => 'Goldfish', 'option_c' => 'Clownfish', 'option_d' => 'Angelfish', 'correct_answer' => 'c', 'explanation' => 'Nemo is an orange clownfish (amphiprion ocellaris).'],
                ['question_text' => 'Who directed Jurassic Park?', 'option_a' => 'James Cameron', 'option_b' => 'Steven Spielberg', 'option_c' => 'George Lucas', 'option_d' => 'Ridley Scott', 'correct_answer' => 'b', 'explanation' => 'Spielberg directed the 1993 blockbuster based on Michael Crichton\'s novel.'],
                ['question_text' => 'What is the name of Batman\'s butler?', 'option_a' => 'Jarvis', 'option_b' => 'Alfred', 'option_c' => 'Winston', 'option_d' => 'Godfrey', 'correct_answer' => 'b', 'explanation' => 'Alfred Pennyworth has been Batman\'s loyal butler since 1943.'],
                ['question_text' => 'Which animated Disney movie features a character named Elsa?', 'option_a' => 'Tangled', 'option_b' => 'Moana', 'option_c' => 'Frozen', 'option_d' => 'Brave', 'correct_answer' => 'c', 'explanation' => 'Frozen (2013) became the highest-grossing animated film at the time.'],
                ['question_text' => 'What is the name of the shark in Jaws?', 'option_a' => 'Jaws', 'option_b' => 'Bruce', 'option_c' => 'Sharky', 'option_d' => 'Megalodon', 'correct_answer' => 'b', 'explanation' => 'The mechanical shark was nicknamed Bruce after Spielberg\'s lawyer.'],
                ['question_text' => 'In which movie does the quote "May the Force be with you" appear?', 'option_a' => 'Star Trek', 'option_b' => 'Star Wars', 'option_c' => 'Guardians of the Galaxy', 'option_d' => 'Interstellar', 'correct_answer' => 'b', 'explanation' => 'This iconic quote is from the Star Wars franchise, first said in 1977.'],
                ['question_text' => 'What year was the first animated Disney feature film released?', 'option_a' => '1932', 'option_b' => '1935', 'option_c' => '1937', 'option_d' => '1940', 'correct_answer' => 'c', 'explanation' => 'Snow White and the Seven Dwarfs was released in 1937.'],
                ['question_text' => 'Who played the Joker in The Dark Knight?', 'option_a' => 'Jack Nicholson', 'option_b' => 'Jared Leto', 'option_c' => 'Joaquin Phoenix', 'option_d' => 'Heath Ledger', 'correct_answer' => 'd', 'explanation' => 'Heath Ledger won a posthumous Oscar for his portrayal of the Joker.'],
                ['question_text' => 'What animated movie features a rat who wants to be a chef?', 'option_a' => 'Ratatouille', 'option_b' => 'Flushed Away', 'option_c' => 'Stuart Little', 'option_d' => 'The Tale of Despereaux', 'correct_answer' => 'a', 'explanation' => 'Ratatouille (2007) features Remy, a rat with culinary dreams in Paris.'],
                ['question_text' => 'In The Lion King, what is Simba\'s father\'s name?', 'option_a' => 'Scar', 'option_b' => 'Mufasa', 'option_c' => 'Zazu', 'option_d' => 'Rafiki', 'correct_answer' => 'b', 'explanation' => 'Mufasa was voiced by James Earl Jones in the original 1994 film.'],
                ['question_text' => 'What superhero is also known as Diana Prince?', 'option_a' => 'Black Widow', 'option_b' => 'Captain Marvel', 'option_c' => 'Wonder Woman', 'option_d' => 'Supergirl', 'correct_answer' => 'c', 'explanation' => 'Wonder Woman/Diana Prince is an Amazonian warrior princess.'],
                ['question_text' => 'Which movie won Best Picture at the Oscars in 2020?', 'option_a' => '1917', 'option_b' => 'Joker', 'option_c' => 'Parasite', 'option_d' => 'Once Upon a Time in Hollywood', 'correct_answer' => 'c', 'explanation' => 'Parasite was the first non-English film to win Best Picture.'],
                ['question_text' => 'What is Shrek?', 'option_a' => 'A troll', 'option_b' => 'An ogre', 'option_c' => 'A goblin', 'option_d' => 'A giant', 'correct_answer' => 'b', 'explanation' => 'Shrek is a green ogre who lives in a swamp.'],
                ['question_text' => 'Who voices Woody in Toy Story?', 'option_a' => 'Tom Hanks', 'option_b' => 'Tim Allen', 'option_c' => 'Billy Crystal', 'option_d' => 'Robin Williams', 'correct_answer' => 'a', 'explanation' => 'Tom Hanks has voiced Woody in all four Toy Story films.'],
                ['question_text' => 'What fictional metal is Captain America\'s shield made of?', 'option_a' => 'Adamantium', 'option_b' => 'Vibranium', 'option_c' => 'Kryptonite', 'option_d' => 'Mythril', 'correct_answer' => 'b', 'explanation' => 'Vibranium comes from the fictional nation of Wakanda.'],
                ['question_text' => 'In which movie does a tornado transport a girl to Oz?', 'option_a' => 'Alice in Wonderland', 'option_b' => 'Peter Pan', 'option_c' => 'The Wizard of Oz', 'option_d' => 'The NeverEnding Story', 'correct_answer' => 'c', 'explanation' => 'The Wizard of Oz (1939) stars Judy Garland as Dorothy Gale.'],
                ['question_text' => 'What 1994 movie features a group of escaped zoo animals?', 'option_a' => 'Madagascar', 'option_b' => 'The Lion King', 'option_c' => 'Jumanji', 'option_d' => 'Ace Ventura', 'correct_answer' => 'b', 'explanation' => 'The Lion King was released in 1994 and set in the African savanna.'],
            ],
            'Technology' => [
                ['question_text' => 'What does "CPU" stand for?', 'option_a' => 'Central Processing Unit', 'option_b' => 'Computer Personal Unit', 'option_c' => 'Central Program Utility', 'option_d' => 'Core Processing Unit', 'correct_answer' => 'a', 'explanation' => 'The CPU is the brain of a computer that executes instructions.'],
                ['question_text' => 'Who co-founded Apple Inc.?', 'option_a' => 'Bill Gates', 'option_b' => 'Steve Jobs', 'option_c' => 'Elon Musk', 'option_d' => 'Jeff Bezos', 'correct_answer' => 'b', 'explanation' => 'Steve Jobs co-founded Apple with Steve Wozniak and Ronald Wayne in 1976.'],
                ['question_text' => 'What does "URL" stand for?', 'option_a' => 'Universal Resource Link', 'option_b' => 'Uniform Resource Locator', 'option_c' => 'United Reference Library', 'option_d' => 'Universal Reference Locator', 'correct_answer' => 'b', 'explanation' => 'A URL is the address used to access resources on the internet.'],
                ['question_text' => 'What programming language is known for its coffee cup logo?', 'option_a' => 'Python', 'option_b' => 'C++', 'option_c' => 'Java', 'option_d' => 'Ruby', 'correct_answer' => 'c', 'explanation' => 'Java was named after Java coffee and features a coffee cup logo.'],
                ['question_text' => 'What does "RAM" stand for?', 'option_a' => 'Random Access Memory', 'option_b' => 'Read And Memorize', 'option_c' => 'Rapid Application Module', 'option_d' => 'Real-time Access Memory', 'correct_answer' => 'a', 'explanation' => 'RAM is volatile memory used for temporary data storage while programs run.'],
                ['question_text' => 'Who created Facebook?', 'option_a' => 'Jack Dorsey', 'option_b' => 'Mark Zuckerberg', 'option_c' => 'Larry Page', 'option_d' => 'Evan Spiegel', 'correct_answer' => 'b', 'explanation' => 'Zuckerberg launched Facebook from his Harvard dorm room in 2004.'],
                ['question_text' => 'What does "HTML" stand for?', 'option_a' => 'HyperText Markup Language', 'option_b' => 'High Tech Modern Language', 'option_c' => 'Home Tool Markup Language', 'option_d' => 'Hyper Transfer Meta Language', 'correct_answer' => 'a', 'explanation' => 'HTML is the standard markup language for creating web pages.'],
                ['question_text' => 'What company makes the PlayStation?', 'option_a' => 'Nintendo', 'option_b' => 'Microsoft', 'option_c' => 'Sony', 'option_d' => 'Sega', 'correct_answer' => 'c', 'explanation' => 'Sony launched the first PlayStation in Japan on December 3, 1994.'],
                ['question_text' => 'What does "Wi-Fi" stand for?', 'option_a' => 'Wireless Fidelity', 'option_b' => 'Wide Frequency', 'option_c' => 'Wireless Function', 'option_d' => 'It doesn\'t stand for anything', 'correct_answer' => 'd', 'explanation' => 'Wi-Fi is a trademark name and doesn\'t officially stand for anything.'],
                ['question_text' => 'In what year was the World Wide Web invented?', 'option_a' => '1985', 'option_b' => '1989', 'option_c' => '1993', 'option_d' => '1995', 'correct_answer' => 'b', 'explanation' => 'Tim Berners-Lee invented the Web at CERN in 1989.'],
                ['question_text' => 'What is the most popular programming language in the world?', 'option_a' => 'Java', 'option_b' => 'Python', 'option_c' => 'JavaScript', 'option_d' => 'C++', 'correct_answer' => 'c', 'explanation' => 'JavaScript is used by nearly every website and is the most widely deployed language.'],
                ['question_text' => 'What does "GPU" stand for?', 'option_a' => 'General Processing Unit', 'option_b' => 'Graphics Processing Unit', 'option_c' => 'Game Performance Utility', 'option_d' => 'Graphical Pixel Unit', 'correct_answer' => 'b', 'explanation' => 'GPUs are specialized processors for rendering graphics and parallel computing.'],
                ['question_text' => 'Which company developed Windows?', 'option_a' => 'Apple', 'option_b' => 'Google', 'option_c' => 'Microsoft', 'option_d' => 'IBM', 'correct_answer' => 'c', 'explanation' => 'Microsoft released the first version of Windows (1.0) in 1985.'],
                ['question_text' => 'What is the largest social media platform by users?', 'option_a' => 'Instagram', 'option_b' => 'TikTok', 'option_c' => 'Facebook', 'option_d' => 'YouTube', 'correct_answer' => 'c', 'explanation' => 'Facebook (Meta) has over 3 billion monthly active users.'],
                ['question_text' => 'What does "SSD" stand for?', 'option_a' => 'Super Speed Drive', 'option_b' => 'Solid State Drive', 'option_c' => 'System Storage Device', 'option_d' => 'Static Speed Disk', 'correct_answer' => 'b', 'explanation' => 'SSDs use flash memory and are much faster than traditional hard drives.'],
                ['question_text' => 'What does "IoT" stand for?', 'option_a' => 'Internet of Things', 'option_b' => 'Integration of Technology', 'option_c' => 'Internal Operating Tool', 'option_d' => 'Intelligent Online Tracking', 'correct_answer' => 'a', 'explanation' => 'IoT refers to the network of physical devices connected to the internet.'],
                ['question_text' => 'What was the first search engine on the internet?', 'option_a' => 'Google', 'option_b' => 'Yahoo', 'option_c' => 'Archie', 'option_d' => 'AltaVista', 'correct_answer' => 'c', 'explanation' => 'Archie was created in 1990 at McGill University in Montreal.'],
                ['question_text' => 'How many bytes are in a kilobyte?', 'option_a' => '100', 'option_b' => '512', 'option_c' => '1000', 'option_d' => '1024', 'correct_answer' => 'd', 'explanation' => 'In binary terms, 1 KB = 1024 bytes (2^10).'],
                ['question_text' => 'What does "API" stand for?', 'option_a' => 'Application Programming Interface', 'option_b' => 'Automated Program Integration', 'option_c' => 'Application Process Index', 'option_d' => 'Active Protocol Interface', 'correct_answer' => 'a', 'explanation' => 'APIs allow different software systems to communicate with each other.'],
                ['question_text' => 'What company owns Instagram?', 'option_a' => 'Google', 'option_b' => 'Twitter', 'option_c' => 'Meta (Facebook)', 'option_d' => 'Snapchat', 'correct_answer' => 'c', 'explanation' => 'Facebook (now Meta) acquired Instagram in 2012 for $1 billion.'],
            ],
            'Sports' => [
                ['question_text' => 'How many players are in a basketball team on court?', 'option_a' => '4', 'option_b' => '5', 'option_c' => '6', 'option_d' => '7', 'correct_answer' => 'b', 'explanation' => 'Each team has 5 players on the court at a time.'],
                ['question_text' => 'In which sport do you use a shuttlecock?', 'option_a' => 'Tennis', 'option_b' => 'Badminton', 'option_c' => 'Squash', 'option_d' => 'Table Tennis', 'correct_answer' => 'b', 'explanation' => 'A shuttlecock (birdie) is used in badminton.'],
                ['question_text' => 'How many holes are there in a standard golf course?', 'option_a' => '9', 'option_b' => '12', 'option_c' => '18', 'option_d' => '21', 'correct_answer' => 'c', 'explanation' => 'A standard golf course has 18 holes.'],
                ['question_text' => 'What country invented cricket?', 'option_a' => 'India', 'option_b' => 'Australia', 'option_c' => 'England', 'option_d' => 'South Africa', 'correct_answer' => 'c', 'explanation' => 'Cricket originated in England in the 16th century.'],
                ['question_text' => 'What is the national sport of Japan?', 'option_a' => 'Karate', 'option_b' => 'Judo', 'option_c' => 'Sumo Wrestling', 'option_d' => 'Baseball', 'correct_answer' => 'c', 'explanation' => 'Sumo wrestling is considered Japan\'s national sport with centuries of tradition.'],
                ['question_text' => 'How many sets does a player need to win a men\'s tennis Grand Slam match?', 'option_a' => '2', 'option_b' => '3', 'option_c' => '4', 'option_d' => '5', 'correct_answer' => 'b', 'explanation' => 'Men\'s Grand Slam matches are best of 5 sets, so you need to win 3.'],
                ['question_text' => 'Which country hosted the 2016 Olympics?', 'option_a' => 'China', 'option_b' => 'Brazil', 'option_c' => 'Japan', 'option_d' => 'UK', 'correct_answer' => 'b', 'explanation' => 'The 2016 Summer Olympics were held in Rio de Janeiro, Brazil.'],
                ['question_text' => 'What sport is played at Wimbledon?', 'option_a' => 'Cricket', 'option_b' => 'Golf', 'option_c' => 'Tennis', 'option_d' => 'Rugby', 'correct_answer' => 'c', 'explanation' => 'Wimbledon is the oldest tennis tournament, held annually since 1877.'],
                ['question_text' => 'How long is a marathon in miles?', 'option_a' => '20.2 miles', 'option_b' => '24.2 miles', 'option_c' => '26.2 miles', 'option_d' => '28.2 miles', 'correct_answer' => 'c', 'explanation' => 'A marathon is 26.2 miles (42.195 km).'],
                ['question_text' => 'Which sport uses a puck?', 'option_a' => 'Lacrosse', 'option_b' => 'Ice Hockey', 'option_c' => 'Field Hockey', 'option_d' => 'Curling', 'correct_answer' => 'b', 'explanation' => 'Ice hockey uses a hard rubber puck.'],
                ['question_text' => 'What color cards does a soccer referee use?', 'option_a' => 'Red and Blue', 'option_b' => 'Yellow and Red', 'option_c' => 'Green and Red', 'option_d' => 'Yellow and Blue', 'correct_answer' => 'b', 'explanation' => 'Yellow means a warning; red means ejection from the game.'],
                ['question_text' => 'How many points is a touchdown worth in American football?', 'option_a' => '3', 'option_b' => '5', 'option_c' => '6', 'option_d' => '7', 'correct_answer' => 'c', 'explanation' => 'A touchdown is worth 6 points, plus an extra point attempt.'],
                ['question_text' => 'Which sport is also known as "ping pong"?', 'option_a' => 'Badminton', 'option_b' => 'Table Tennis', 'option_c' => 'Squash', 'option_d' => 'Racquetball', 'correct_answer' => 'b', 'explanation' => 'Table tennis originated in England in the 1880s.'],
                ['question_text' => 'What is the height of a standard basketball hoop?', 'option_a' => '8 feet', 'option_b' => '9 feet', 'option_c' => '10 feet', 'option_d' => '11 feet', 'correct_answer' => 'c', 'explanation' => 'The rim is 10 feet (3.05 meters) above the ground.'],
                ['question_text' => 'Which Grand Slam is played on clay courts?', 'option_a' => 'Australian Open', 'option_b' => 'French Open', 'option_c' => 'Wimbledon', 'option_d' => 'US Open', 'correct_answer' => 'b', 'explanation' => 'The French Open (Roland Garros) is played on red clay courts.'],
                ['question_text' => 'How many innings are in a standard baseball game?', 'option_a' => '7', 'option_b' => '8', 'option_c' => '9', 'option_d' => '10', 'correct_answer' => 'c', 'explanation' => 'A standard baseball game consists of 9 innings.'],
                ['question_text' => 'What sport does Usain Bolt compete in?', 'option_a' => 'Swimming', 'option_b' => 'Long Jump', 'option_c' => 'Sprinting', 'option_d' => 'Cycling', 'correct_answer' => 'c', 'explanation' => 'Bolt holds the world records in the 100m and 200m sprint.'],
                ['question_text' => 'How many periods are in a hockey game?', 'option_a' => '2', 'option_b' => '3', 'option_c' => '4', 'option_d' => '5', 'correct_answer' => 'b', 'explanation' => 'An ice hockey game has 3 periods of 20 minutes each.'],
                ['question_text' => 'In which country did the sport of rugby originate?', 'option_a' => 'Ireland', 'option_b' => 'Scotland', 'option_c' => 'England', 'option_d' => 'Wales', 'correct_answer' => 'c', 'explanation' => 'Rugby originated at Rugby School in Warwickshire, England in 1823.'],
                ['question_text' => 'What is a perfect score in bowling?', 'option_a' => '200', 'option_b' => '250', 'option_c' => '300', 'option_d' => '400', 'correct_answer' => 'c', 'explanation' => 'A perfect game of 12 consecutive strikes scores 300 points.'],
            ],
            'Music' => [
                ['question_text' => 'Which instrument has 88 keys?', 'option_a' => 'Guitar', 'option_b' => 'Piano', 'option_c' => 'Organ', 'option_d' => 'Accordion', 'correct_answer' => 'b', 'explanation' => 'A standard piano has 88 keys — 52 white and 36 black.'],
                ['question_text' => 'Who is known as the "King of Pop"?', 'option_a' => 'Elvis Presley', 'option_b' => 'Prince', 'option_c' => 'Michael Jackson', 'option_d' => 'Freddie Mercury', 'correct_answer' => 'c', 'explanation' => 'Michael Jackson earned this title with hits like Thriller and Billie Jean.'],
                ['question_text' => 'What band was John Lennon a part of?', 'option_a' => 'The Rolling Stones', 'option_b' => 'The Beatles', 'option_c' => 'Pink Floyd', 'option_d' => 'The Who', 'correct_answer' => 'b', 'explanation' => 'The Beatles are considered the most influential band in history.'],
                ['question_text' => 'How many strings does a standard guitar have?', 'option_a' => '4', 'option_b' => '5', 'option_c' => '6', 'option_d' => '8', 'correct_answer' => 'c', 'explanation' => 'A standard guitar has 6 strings tuned E-A-D-G-B-E.'],
                ['question_text' => 'What musical term means "gradually getting louder"?', 'option_a' => 'Piano', 'option_b' => 'Forte', 'option_c' => 'Crescendo', 'option_d' => 'Staccato', 'correct_answer' => 'c', 'explanation' => 'Crescendo comes from the Italian word for "growing."'],
                ['question_text' => 'Who composed the "Moonlight Sonata"?', 'option_a' => 'Mozart', 'option_b' => 'Beethoven', 'option_c' => 'Chopin', 'option_d' => 'Bach', 'correct_answer' => 'b', 'explanation' => 'Beethoven composed it in 1801 — its real name is Piano Sonata No. 14.'],
                ['question_text' => 'What genre of music originated in Jamaica?', 'option_a' => 'Jazz', 'option_b' => 'Blues', 'option_c' => 'Reggae', 'option_d' => 'Samba', 'correct_answer' => 'c', 'explanation' => 'Reggae originated in Jamaica in the late 1960s, popularized by Bob Marley.'],
                ['question_text' => 'What is the highest female singing voice?', 'option_a' => 'Alto', 'option_b' => 'Mezzo-soprano', 'option_c' => 'Soprano', 'option_d' => 'Contralto', 'correct_answer' => 'c', 'explanation' => 'Soprano is the highest vocal range, typically from C4 to C6.'],
                ['question_text' => 'Which instrument is the largest in a standard orchestra?', 'option_a' => 'Cello', 'option_b' => 'Tuba', 'option_c' => 'Double Bass', 'option_d' => 'Harp', 'correct_answer' => 'c', 'explanation' => 'The double bass (contrabass) is the largest and lowest-pitched string instrument.'],
                ['question_text' => 'What is the best-selling album of all time?', 'option_a' => 'Back in Black', 'option_b' => 'Thriller', 'option_c' => 'The Dark Side of the Moon', 'option_d' => 'Abbey Road', 'correct_answer' => 'b', 'explanation' => 'Michael Jackson\'s Thriller has sold over 70 million copies worldwide.'],
                ['question_text' => 'What does "tempo" refer to in music?', 'option_a' => 'Volume', 'option_b' => 'Key', 'option_c' => 'Speed', 'option_d' => 'Pitch', 'correct_answer' => 'c', 'explanation' => 'Tempo indicates how fast or slow a piece of music should be played.'],
                ['question_text' => 'Who is known as the "Queen of Soul"?', 'option_a' => 'Whitney Houston', 'option_b' => 'Diana Ross', 'option_c' => 'Aretha Franklin', 'option_d' => 'Tina Turner', 'correct_answer' => 'c', 'explanation' => 'Aretha Franklin earned this title with hits like "Respect" and "Natural Woman."'],
                ['question_text' => 'Which woodwind instrument is the smallest?', 'option_a' => 'Flute', 'option_b' => 'Piccolo', 'option_c' => 'Clarinet', 'option_d' => 'Oboe', 'correct_answer' => 'b', 'explanation' => 'The piccolo is half the size of a flute and plays an octave higher.'],
                ['question_text' => 'What legendary festival took place in 1969?', 'option_a' => 'Coachella', 'option_b' => 'Glastonbury', 'option_c' => 'Woodstock', 'option_d' => 'Lollapalooza', 'correct_answer' => 'c', 'explanation' => 'Woodstock attracted 400,000 people to a dairy farm in New York.'],
                ['question_text' => 'How many notes are in a musical octave?', 'option_a' => '6', 'option_b' => '7', 'option_c' => '8', 'option_d' => '12', 'correct_answer' => 'c', 'explanation' => 'An octave spans 8 notes (do-re-mi-fa-sol-la-ti-do).'],
                ['question_text' => 'What country is K-pop from?', 'option_a' => 'Japan', 'option_b' => 'South Korea', 'option_c' => 'China', 'option_d' => 'Thailand', 'correct_answer' => 'b', 'explanation' => 'K-pop (Korean pop) originated in South Korea and became a global phenomenon.'],
                ['question_text' => 'Who sang "Bohemian Rhapsody"?', 'option_a' => 'Led Zeppelin', 'option_b' => 'The Beatles', 'option_c' => 'Queen', 'option_d' => 'Pink Floyd', 'correct_answer' => 'c', 'explanation' => 'Queen released Bohemian Rhapsody in 1975, written by Freddie Mercury.'],
                ['question_text' => 'What is the name of the clef used for higher-pitched notes?', 'option_a' => 'Bass Clef', 'option_b' => 'Treble Clef', 'option_c' => 'Alto Clef', 'option_d' => 'Tenor Clef', 'correct_answer' => 'b', 'explanation' => 'The treble clef (G clef) is used for notes above middle C.'],
                ['question_text' => 'Which music streaming service was founded in Sweden?', 'option_a' => 'Apple Music', 'option_b' => 'Pandora', 'option_c' => 'Spotify', 'option_d' => 'Tidal', 'correct_answer' => 'c', 'explanation' => 'Spotify was founded in Stockholm in 2006 by Daniel Ek.'],
                ['question_text' => 'What instrument does a drummer primarily play?', 'option_a' => 'Xylophone', 'option_b' => 'Timpani', 'option_c' => 'Drum Kit', 'option_d' => 'Bongos', 'correct_answer' => 'c', 'explanation' => 'A drum kit typically includes a bass drum, snare, toms, hi-hat, and cymbals.'],
            ],
            'Food' => [
                ['question_text' => 'What country is pizza originally from?', 'option_a' => 'USA', 'option_b' => 'France', 'option_c' => 'Italy', 'option_d' => 'Greece', 'correct_answer' => 'c', 'explanation' => 'Modern pizza originated in Naples, Italy in the 18th century.'],
                ['question_text' => 'What is tofu made from?', 'option_a' => 'Rice', 'option_b' => 'Corn', 'option_c' => 'Soybeans', 'option_d' => 'Wheat', 'correct_answer' => 'c', 'explanation' => 'Tofu is made by coagulating soy milk and pressing the curds.'],
                ['question_text' => 'What nut is used to make marzipan?', 'option_a' => 'Walnut', 'option_b' => 'Cashew', 'option_c' => 'Almond', 'option_d' => 'Pistachio', 'correct_answer' => 'c', 'explanation' => 'Marzipan is made from ground almonds and sugar.'],
                ['question_text' => 'What is the most consumed fruit in the world?', 'option_a' => 'Apple', 'option_b' => 'Orange', 'option_c' => 'Banana', 'option_d' => 'Grape', 'correct_answer' => 'c', 'explanation' => 'Over 100 billion bananas are eaten worldwide every year.'],
                ['question_text' => 'What food is the main ingredient in hummus?', 'option_a' => 'Lentils', 'option_b' => 'Chickpeas', 'option_c' => 'Black beans', 'option_d' => 'Peas', 'correct_answer' => 'b', 'explanation' => 'Hummus is made from mashed chickpeas blended with tahini, lemon, and garlic.'],
                ['question_text' => 'What vitamin are oranges famous for?', 'option_a' => 'Vitamin A', 'option_b' => 'Vitamin B', 'option_c' => 'Vitamin C', 'option_d' => 'Vitamin D', 'correct_answer' => 'c', 'explanation' => 'One orange provides about 70% of the daily recommended Vitamin C.'],
                ['question_text' => 'What is the world\'s most popular spice?', 'option_a' => 'Cinnamon', 'option_b' => 'Pepper', 'option_c' => 'Cumin', 'option_d' => 'Garlic', 'correct_answer' => 'b', 'explanation' => 'Black pepper is the most traded spice in the world.'],
                ['question_text' => 'Which cheese is traditionally used on pizza?', 'option_a' => 'Cheddar', 'option_b' => 'Gouda', 'option_c' => 'Mozzarella', 'option_d' => 'Parmesan', 'correct_answer' => 'c', 'explanation' => 'Mozzarella melts well and is the classic pizza cheese.'],
                ['question_text' => 'What grain is sake made from?', 'option_a' => 'Wheat', 'option_b' => 'Barley', 'option_c' => 'Rice', 'option_d' => 'Corn', 'correct_answer' => 'c', 'explanation' => 'Sake is a Japanese rice wine made from fermented rice.'],
                ['question_text' => 'What is the most expensive mushroom?', 'option_a' => 'Portobello', 'option_b' => 'Shiitake', 'option_c' => 'Truffle', 'option_d' => 'Morel', 'correct_answer' => 'c', 'explanation' => 'White truffles can cost over $3,000 per pound.'],
                ['question_text' => 'What country is pad thai from?', 'option_a' => 'Vietnam', 'option_b' => 'Thailand', 'option_c' => 'China', 'option_d' => 'Indonesia', 'correct_answer' => 'b', 'explanation' => 'Pad thai is a stir-fried rice noodle dish, Thailand\'s national dish.'],
                ['question_text' => 'What vegetable makes your eyes water when cut?', 'option_a' => 'Garlic', 'option_b' => 'Pepper', 'option_c' => 'Onion', 'option_d' => 'Ginger', 'correct_answer' => 'c', 'explanation' => 'Onions release syn-Propanethial-S-oxide gas which irritates your eyes.'],
                ['question_text' => 'What is the main ingredient in chocolate?', 'option_a' => 'Coffee beans', 'option_b' => 'Cocoa beans', 'option_c' => 'Vanilla beans', 'option_d' => 'Sugar cane', 'correct_answer' => 'b', 'explanation' => 'Chocolate is made from roasted and ground cocoa beans.'],
                ['question_text' => 'Which berry is the most produced worldwide?', 'option_a' => 'Blueberry', 'option_b' => 'Raspberry', 'option_c' => 'Strawberry', 'option_d' => 'Cranberry', 'correct_answer' => 'c', 'explanation' => 'Over 9 million tonnes of strawberries are produced globally each year.'],
                ['question_text' => 'What type of food is a baguette?', 'option_a' => 'Pasta', 'option_b' => 'Bread', 'option_c' => 'Pastry', 'option_d' => 'Cake', 'correct_answer' => 'b', 'explanation' => 'A baguette is a long, thin French bread known for its crispy crust.'],
                ['question_text' => 'What meat is used in a traditional Reuben sandwich?', 'option_a' => 'Turkey', 'option_b' => 'Ham', 'option_c' => 'Corned Beef', 'option_d' => 'Roast Beef', 'correct_answer' => 'c', 'explanation' => 'A Reuben has corned beef, Swiss cheese, sauerkraut, and Russian dressing.'],
                ['question_text' => 'What fruit is used to make wine?', 'option_a' => 'Apple', 'option_b' => 'Grape', 'option_c' => 'Berry', 'option_d' => 'Plum', 'correct_answer' => 'b', 'explanation' => 'Wine is primarily made from fermented grapes.'],
                ['question_text' => 'What dish consists of raw fish served over rice?', 'option_a' => 'Tempura', 'option_b' => 'Ramen', 'option_c' => 'Sashimi', 'option_d' => 'Sushi', 'correct_answer' => 'd', 'explanation' => 'Sushi is vinegared rice topped with fish. Sashimi is fish without rice.'],
                ['question_text' => 'What country is feta cheese from?', 'option_a' => 'Italy', 'option_b' => 'France', 'option_c' => 'Greece', 'option_d' => 'Turkey', 'correct_answer' => 'c', 'explanation' => 'Feta is a Greek cheese made from sheep\'s or goat\'s milk.'],
                ['question_text' => 'What is the hottest chili pepper in the world?', 'option_a' => 'Habanero', 'option_b' => 'Ghost Pepper', 'option_c' => 'Carolina Reaper', 'option_d' => 'Scotch Bonnet', 'correct_answer' => 'c', 'explanation' => 'The Carolina Reaper averages over 1.6 million Scoville Heat Units.'],
            ],
            'Animals' => [
                ['question_text' => 'What is the largest animal on Earth?', 'option_a' => 'African Elephant', 'option_b' => 'Blue Whale', 'option_c' => 'Giraffe', 'option_d' => 'Colossal Squid', 'correct_answer' => 'b', 'explanation' => 'Blue whales can grow up to 100 feet long and weigh up to 200 tons.'],
                ['question_text' => 'What is the fastest land animal?', 'option_a' => 'Lion', 'option_b' => 'Cheetah', 'option_c' => 'Gazelle', 'option_d' => 'Horse', 'correct_answer' => 'b', 'explanation' => 'Cheetahs can reach speeds of up to 70 mph (112 km/h).'],
                ['question_text' => 'How many legs does a spider have?', 'option_a' => '6', 'option_b' => '8', 'option_c' => '10', 'option_d' => '12', 'correct_answer' => 'b', 'explanation' => 'Spiders are arachnids and have 8 legs, unlike insects which have 6.'],
                ['question_text' => 'What animal is known for changing its color?', 'option_a' => 'Iguana', 'option_b' => 'Gecko', 'option_c' => 'Chameleon', 'option_d' => 'Frog', 'correct_answer' => 'c', 'explanation' => 'Chameleons change color for communication, temperature regulation, and camouflage.'],
                ['question_text' => 'What is a group of lions called?', 'option_a' => 'Pack', 'option_b' => 'Herd', 'option_c' => 'Pride', 'option_d' => 'Flock', 'correct_answer' => 'c', 'explanation' => 'A pride typically consists of related females, their cubs, and a few males.'],
                ['question_text' => 'What mammal can fly?', 'option_a' => 'Flying Squirrel', 'option_b' => 'Bat', 'option_c' => 'Sugar Glider', 'option_d' => 'Colugo', 'correct_answer' => 'b', 'explanation' => 'Bats are the only mammals capable of true sustained flight.'],
                ['question_text' => 'What is the tallest animal in the world?', 'option_a' => 'Elephant', 'option_b' => 'Giraffe', 'option_c' => 'Ostrich', 'option_d' => 'Moose', 'correct_answer' => 'b', 'explanation' => 'Giraffes can grow up to 18 feet (5.5 meters) tall.'],
                ['question_text' => 'How many hearts does an octopus have?', 'option_a' => '1', 'option_b' => '2', 'option_c' => '3', 'option_d' => '4', 'correct_answer' => 'c', 'explanation' => 'An octopus has 3 hearts — one main heart and two gill hearts.'],
                ['question_text' => 'What bird can fly backwards?', 'option_a' => 'Sparrow', 'option_b' => 'Eagle', 'option_c' => 'Hummingbird', 'option_d' => 'Kingfisher', 'correct_answer' => 'c', 'explanation' => 'Hummingbirds can fly backwards, upside down, and hover in place.'],
                ['question_text' => 'What is the largest species of shark?', 'option_a' => 'Great White', 'option_b' => 'Hammerhead', 'option_c' => 'Whale Shark', 'option_d' => 'Tiger Shark', 'correct_answer' => 'c', 'explanation' => 'Whale sharks can grow up to 40 feet long. They eat plankton, not humans!'],
                ['question_text' => 'What animal has the longest lifespan?', 'option_a' => 'Elephant', 'option_b' => 'Giant Tortoise', 'option_c' => 'Bowhead Whale', 'option_d' => 'Greenland Shark', 'correct_answer' => 'd', 'explanation' => 'Greenland sharks can live over 400 years, the longest of any vertebrate.'],
                ['question_text' => 'Which animal has black and white stripes?', 'option_a' => 'Tiger', 'option_b' => 'Zebra', 'option_c' => 'Panda', 'option_d' => 'Skunk', 'correct_answer' => 'b', 'explanation' => 'Each zebra\'s stripe pattern is unique, like a fingerprint.'],
                ['question_text' => 'What is a baby kangaroo called?', 'option_a' => 'Cub', 'option_b' => 'Pup', 'option_c' => 'Joey', 'option_d' => 'Kit', 'correct_answer' => 'c', 'explanation' => 'A joey is born the size of a grape and develops in its mother\'s pouch.'],
                ['question_text' => 'What animal produces silk?', 'option_a' => 'Spider', 'option_b' => 'Silkworm', 'option_c' => 'Caterpillar', 'option_d' => 'Both A and B', 'correct_answer' => 'd', 'explanation' => 'Both spiders and silkworms produce silk, but commercial silk comes from silkworms.'],
                ['question_text' => 'What is the only bird that can swim but cannot fly?', 'option_a' => 'Ostrich', 'option_b' => 'Penguin', 'option_c' => 'Emu', 'option_d' => 'Kiwi', 'correct_answer' => 'b', 'explanation' => 'Penguins are excellent swimmers but their wings are adapted as flippers.'],
                ['question_text' => 'How many stomachs does a cow have?', 'option_a' => '1', 'option_b' => '2', 'option_c' => '3', 'option_d' => '4', 'correct_answer' => 'd', 'explanation' => 'Cows have 4 stomach compartments to digest tough plant material.'],
                ['question_text' => 'What is the world\'s most venomous spider?', 'option_a' => 'Black Widow', 'option_b' => 'Brazilian Wandering Spider', 'option_c' => 'Brown Recluse', 'option_d' => 'Funnel-web Spider', 'correct_answer' => 'b', 'explanation' => 'The Brazilian wandering spider holds the Guinness record for most venomous.'],
                ['question_text' => 'What type of animal is a Komodo dragon?', 'option_a' => 'Dinosaur', 'option_b' => 'Lizard', 'option_c' => 'Crocodile', 'option_d' => 'Snake', 'correct_answer' => 'b', 'explanation' => 'Komodo dragons are the world\'s largest living lizards, growing up to 10 feet.'],
                ['question_text' => 'Which animal sleeps the most hours per day?', 'option_a' => 'Cat', 'option_b' => 'Sloth', 'option_c' => 'Koala', 'option_d' => 'Bear', 'correct_answer' => 'c', 'explanation' => 'Koalas sleep up to 22 hours per day due to their low-energy eucalyptus diet.'],
                ['question_text' => 'What is the national animal of Australia?', 'option_a' => 'Koala', 'option_b' => 'Kangaroo', 'option_c' => 'Platypus', 'option_d' => 'Emu', 'correct_answer' => 'b', 'explanation' => 'The kangaroo appears on Australia\'s coat of arms along with the emu.'],
            ],
            'General Knowledge' => [
                ['question_text' => 'How many continents are there?', 'option_a' => '5', 'option_b' => '6', 'option_c' => '7', 'option_d' => '8', 'correct_answer' => 'c', 'explanation' => 'The 7 continents are Africa, Antarctica, Asia, Australia, Europe, North and South America.'],
                ['question_text' => 'What is the most spoken language in the world?', 'option_a' => 'English', 'option_b' => 'Spanish', 'option_c' => 'Mandarin Chinese', 'option_d' => 'Hindi', 'correct_answer' => 'c', 'explanation' => 'Mandarin Chinese has over 900 million native speakers.'],
                ['question_text' => 'How many colors are in a rainbow?', 'option_a' => '5', 'option_b' => '6', 'option_c' => '7', 'option_d' => '8', 'correct_answer' => 'c', 'explanation' => 'Red, orange, yellow, green, blue, indigo, and violet.'],
                ['question_text' => 'What is the currency of Japan?', 'option_a' => 'Won', 'option_b' => 'Yuan', 'option_c' => 'Yen', 'option_d' => 'Ringgit', 'correct_answer' => 'c', 'explanation' => 'The Japanese Yen (¥/JPY) has been Japan\'s currency since 1871.'],
                ['question_text' => 'Who wrote "Romeo and Juliet"?', 'option_a' => 'Charles Dickens', 'option_b' => 'William Shakespeare', 'option_c' => 'Jane Austen', 'option_d' => 'Mark Twain', 'correct_answer' => 'b', 'explanation' => 'Shakespeare wrote Romeo and Juliet around 1594-1596.'],
                ['question_text' => 'How many days are in a leap year?', 'option_a' => '364', 'option_b' => '365', 'option_c' => '366', 'option_d' => '367', 'correct_answer' => 'c', 'explanation' => 'Leap years have an extra day (February 29) every 4 years.'],
                ['question_text' => 'What is the largest organ in the human body?', 'option_a' => 'Heart', 'option_b' => 'Liver', 'option_c' => 'Brain', 'option_d' => 'Skin', 'correct_answer' => 'd', 'explanation' => 'The skin covers about 20 square feet in adults.'],
                ['question_text' => 'What planet is known for its rings?', 'option_a' => 'Jupiter', 'option_b' => 'Saturn', 'option_c' => 'Uranus', 'option_d' => 'Neptune', 'correct_answer' => 'b', 'explanation' => 'Saturn\'s rings are made of ice and rock particles.'],
                ['question_text' => 'How many sides does a hexagon have?', 'option_a' => '5', 'option_b' => '6', 'option_c' => '7', 'option_d' => '8', 'correct_answer' => 'b', 'explanation' => 'Hexa comes from the Greek word for "six."'],
                ['question_text' => 'What is the speed of sound approximately?', 'option_a' => '343 m/s', 'option_b' => '500 m/s', 'option_c' => '768 m/s', 'option_d' => '1000 m/s', 'correct_answer' => 'a', 'explanation' => 'Sound travels at approximately 343 m/s (1,235 km/h) in air at 20°C.'],
                ['question_text' => 'What is the smallest prime number?', 'option_a' => '0', 'option_b' => '1', 'option_c' => '2', 'option_d' => '3', 'correct_answer' => 'c', 'explanation' => '2 is the smallest and only even prime number.'],
                ['question_text' => 'How many bones does a newborn baby have?', 'option_a' => '206', 'option_b' => '250', 'option_c' => '270', 'option_d' => '300', 'correct_answer' => 'd', 'explanation' => 'Babies have about 300 bones; many fuse together as they grow, leaving 206 in adults.'],
                ['question_text' => 'What is the most common blood type?', 'option_a' => 'A+', 'option_b' => 'B+', 'option_c' => 'O+', 'option_d' => 'AB+', 'correct_answer' => 'c', 'explanation' => 'O+ is the most common blood type worldwide.'],
                ['question_text' => 'How many Great Wonders of the Ancient World exist?', 'option_a' => '5', 'option_b' => '7', 'option_c' => '9', 'option_d' => '12', 'correct_answer' => 'b', 'explanation' => 'The Seven Wonders of the Ancient World were listed by ancient Greek travelers.'],
                ['question_text' => 'What is the hardest rock?', 'option_a' => 'Granite', 'option_b' => 'Marble', 'option_c' => 'Diamond', 'option_d' => 'Obsidian', 'correct_answer' => 'c', 'explanation' => 'Diamond is the hardest known natural material.'],
                ['question_text' => 'What does DNA stand for?', 'option_a' => 'Deoxyribonucleic Acid', 'option_b' => 'Deoxyribose Nucleic Acid', 'option_c' => 'Dynamic Natural Acid', 'option_d' => 'Dinitrogen Acid', 'correct_answer' => 'a', 'explanation' => 'DNA carries the genetic instructions used in the development of all living organisms.'],
                ['question_text' => 'What is the tallest building in the world?', 'option_a' => 'Shanghai Tower', 'option_b' => 'Burj Khalifa', 'option_c' => 'One World Trade Center', 'option_d' => 'Taipei 101', 'correct_answer' => 'b', 'explanation' => 'The Burj Khalifa in Dubai stands at 828 meters (2,717 feet).'],
                ['question_text' => 'How many minutes are in a day?', 'option_a' => '1,200', 'option_b' => '1,440', 'option_c' => '1,560', 'option_d' => '1,680', 'correct_answer' => 'b', 'explanation' => '24 hours × 60 minutes = 1,440 minutes in a day.'],
                ['question_text' => 'What language has the most words?', 'option_a' => 'Chinese', 'option_b' => 'Spanish', 'option_c' => 'English', 'option_d' => 'Arabic', 'correct_answer' => 'c', 'explanation' => 'English has over 170,000 words in current use.'],
                ['question_text' => 'What is the national flower of Japan?', 'option_a' => 'Rose', 'option_b' => 'Cherry Blossom', 'option_c' => 'Lotus', 'option_d' => 'Chrysanthemum', 'correct_answer' => 'b', 'explanation' => 'Cherry blossoms (sakura) are a symbol of renewal and the fleeting nature of life.'],
            ],
        ];
    }
}
