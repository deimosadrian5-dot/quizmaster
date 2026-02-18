<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\Question;
use App\Models\QuizAttempt;
use App\Services\QuestionBank;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    /**
     * Show all available quizzes.
     */
    public function index()
    {
        $quizzes = Quiz::withCount(['questions', 'attempts'])
            ->orderBy('created_at', 'desc')
            ->get();

        $categories = Quiz::distinct()->pluck('category');

        return view('quizzes.index', compact('quizzes', 'categories'));
    }

    /**
     * Show quiz details before starting.
     */
    public function show(Quiz $quiz)
    {
        $quiz->loadCount(['questions', 'attempts']);
        $topScores = $quiz->attempts()
            ->orderByDesc('score')
            ->orderBy('time_taken')
            ->limit(5)
            ->get();

        return view('quizzes.show', compact('quiz', 'topScores'));
    }

    /**
     * Start taking a quiz - show questions one at a time via JS.
     */
    public function play(Quiz $quiz)
    {
        $quiz->load('questions');

        if ($quiz->questions->isEmpty()) {
            return redirect()->route('quizzes.show', $quiz)
                ->with('error', 'This quiz has no questions yet!');
        }

        return view('quizzes.play', compact('quiz'));
    }

    /**
     * Submit quiz answers and calculate score.
     */
    public function submit(Request $request, Quiz $quiz)
    {
        $request->validate([
            'player_name' => 'required|string|max:50',
            'answers' => 'required|array',
            'time_taken' => 'required|integer|min:0',
        ]);

        $quiz->load('questions');
        $answers = $request->input('answers');
        $score = 0;
        $correctCount = 0;
        $totalPoints = 0;
        $results = [];

        foreach ($quiz->questions as $question) {
            $totalPoints += $question->points;
            $userAnswer = $answers[$question->id] ?? null;
            $isCorrect = $userAnswer === $question->correct_answer;

            if ($isCorrect) {
                $score += $question->points;
                $correctCount++;
            }

            $results[] = [
                'question' => $question->question_text,
                'options' => $question->options,
                'user_answer' => $userAnswer,
                'correct_answer' => $question->correct_answer,
                'correct_text' => $question->correct_option_text,
                'is_correct' => $isCorrect,
                'explanation' => $question->explanation,
                'points' => $question->points,
            ];
        }

        $attempt = QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'player_name' => $request->input('player_name'),
            'score' => $score,
            'total_points' => $totalPoints,
            'correct_answers' => $correctCount,
            'total_questions' => $quiz->questions->count(),
            'time_taken' => $request->input('time_taken'),
        ]);

        return view('quizzes.results', compact('quiz', 'attempt', 'results'));
    }

    /**
     * Show the create quiz form.
     */
    public function create()
    {
        return view('quizzes.create');
    }

    /**
     * Store a new quiz with its questions.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'category' => 'required|string|max:100',
            'difficulty' => 'required|in:easy,medium,hard',
            'time_per_question' => 'required|integer|min:10|max:120',
            'questions' => 'required|array|min:1',
            'questions.*.question_text' => 'required|string|max:500',
            'questions.*.option_a' => 'required|string|max:255',
            'questions.*.option_b' => 'required|string|max:255',
            'questions.*.option_c' => 'required|string|max:255',
            'questions.*.option_d' => 'required|string|max:255',
            'questions.*.correct_answer' => 'required|in:a,b,c,d',
            'questions.*.explanation' => 'nullable|string|max:500',
            'questions.*.points' => 'required|integer|min:1|max:100',
        ]);

        $quiz = Quiz::create([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'category' => $request->input('category'),
            'difficulty' => $request->input('difficulty'),
            'time_per_question' => $request->input('time_per_question'),
        ]);

        foreach ($request->input('questions') as $questionData) {
            $quiz->questions()->create($questionData);
        }

        return redirect()->route('quizzes.show', $quiz)
            ->with('success', 'Quiz created successfully! 🎉');
    }

    /**
     * Show the leaderboard for all quizzes or a specific quiz.
     */
    public function leaderboard(Request $request)
    {
        $quizId = $request->query('quiz');

        $query = QuizAttempt::with('quiz')
            ->orderByDesc('score')
            ->orderBy('time_taken');

        if ($quizId) {
            $query->where('quiz_id', $quizId);
        }

        $attempts = $query->limit(50)->get();
        $quizzes = Quiz::orderBy('title')->get();

        return view('quizzes.leaderboard', compact('attempts', 'quizzes', 'quizId'));
    }

    /**
     * Show the instant quiz generator form.
     */
    public function generateForm()
    {
        $offlineCategories = QuestionBank::categories();
        $onlineCategories = QuestionBank::onlineCategories();
        $availableCounts = QuestionBank::availableCounts();

        return view('quizzes.generate', compact('offlineCategories', 'onlineCategories', 'availableCounts'));
    }

    /**
     * Generate a quiz instantly from the question bank.
     */
    public function generate(Request $request)
    {
        $request->validate([
            'category' => 'required|string',
            'num_questions' => 'required|integer|min:3|max:50',
            'difficulty' => 'required|in:easy,medium,hard',
            'source' => 'required|in:online,offline',
        ]);

        $category = $request->input('category');
        $numQuestions = $request->input('num_questions');
        $difficulty = $request->input('difficulty');
        $source = $request->input('source');

        if ($source === 'online') {
            $result = QuestionBank::smartFetch($category, $numQuestions, $difficulty);
        } else {
            $questions = QuestionBank::random($category, $numQuestions);
            $result = ['questions' => $questions, 'source' => 'offline'];
        }

        $questions = $result['questions'];
        $actualSource = $result['source'];

        if (empty($questions)) {
            $errorMsg = $source === 'online'
                ? 'Couldn\'t fetch questions online and no offline backup for this category. Try another category or switch to offline mode!'
                : 'No questions available for that category. Try another one!';
            return redirect()->route('quizzes.generate')
                ->with('error', $errorMsg);
        }

        // Create the quiz
        $sourceLabel = $actualSource === 'online' ? '🌐 Online' : '📦 Offline';
        $quiz = Quiz::create([
            'title' => $category . ' Challenge — ' . count($questions) . ' Questions',
            'description' => 'A ' . strtolower($difficulty) . ' quiz with ' . count($questions) . ' random ' . $category . ' questions. Source: ' . $sourceLabel . '. Good luck! 🎯',
            'category' => $category,
            'difficulty' => $difficulty,
            'time_per_question' => $difficulty === 'easy' ? 30 : ($difficulty === 'medium' ? 20 : 15),
        ]);

        // Assign points based on difficulty
        $points = $difficulty === 'easy' ? 10 : ($difficulty === 'medium' ? 15 : 20);

        foreach ($questions as $q) {
            $quiz->questions()->create([
                'question_text' => $q['question_text'],
                'option_a' => $q['option_a'],
                'option_b' => $q['option_b'],
                'option_c' => $q['option_c'],
                'option_d' => $q['option_d'],
                'correct_answer' => $q['correct_answer'],
                'explanation' => $q['explanation'] ?? '',
                'points' => $points,
            ]);
        }

        $sourceMsg = $actualSource === 'online'
            ? '🌐 Fetched ' . count($questions) . ' fresh questions from the internet!'
            : '📦 Generated from our built-in question bank.';

        return redirect()->route('quizzes.show', $quiz)
            ->with('success', '⚡ Quiz generated! ' . $sourceMsg . ' Let\'s go!');
    }
}
