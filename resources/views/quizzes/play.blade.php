@extends('layouts.app')

@section('title', 'Playing: ' . $quiz->title)

@section('content')
<div class="max-w-3xl mx-auto px-4 py-12" id="quiz-app">

    <!-- Name Entry -->
    <div id="name-screen" class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 text-center fade-in">
        <div class="w-16 h-16 bg-indigo-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <i data-lucide="{{ $quiz->category_icon }}" class="w-8 h-8 text-indigo-600"></i>
        </div>
        <h2 class="text-2xl font-bold text-gray-800 mb-2">{{ $quiz->title }}</h2>
        <p class="text-gray-500 mb-8">Enter your name to begin!</p>

        <div class="max-w-sm mx-auto">
            <input type="text" id="player-name" placeholder="Your Name" maxlength="50"
                   class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl text-center text-lg font-medium focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition">
            <button onclick="startQuiz()"
                    class="w-full mt-4 gradient-bg text-white py-3.5 rounded-xl font-bold text-lg hover:opacity-90 transition inline-flex items-center justify-center gap-2">
                <i data-lucide="play" class="w-5 h-5"></i> Let's Go!
            </button>
        </div>
    </div>

    <!-- Quiz Screen -->
    <div id="quiz-screen" class="hidden">
        <!-- Progress -->
        <div class="mb-6">
            <div class="flex justify-between items-center mb-2">
                <span class="text-sm font-medium text-gray-500" id="question-counter">Question 1 of {{ $quiz->questions->count() }}</span>
                <span class="text-sm font-medium text-gray-500 flex items-center gap-1" id="score-display">
                    <i data-lucide="star" class="w-3.5 h-3.5 text-amber-500"></i> Score: 0
                </span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2.5">
                <div class="gradient-bg h-2.5 rounded-full progress-bar" id="progress-bar" style="width: 0%"></div>
            </div>
        </div>

        <!-- Timer -->
        <div class="flex justify-center mb-6">
            <div class="relative">
                <svg class="w-20 h-20 transform -rotate-90" viewBox="0 0 100 100">
                    <circle cx="50" cy="50" r="45" stroke="#e5e7eb" stroke-width="8" fill="none"/>
                    <circle cx="50" cy="50" r="45" style="stroke: var(--t-500)" stroke-width="8" fill="none"
                            stroke-dasharray="283" stroke-dashoffset="0" stroke-linecap="round"
                            id="timer-circle" class="timer-ring"/>
                </svg>
                <div class="absolute inset-0 flex items-center justify-center">
                    <span class="text-xl font-bold" id="timer-text">{{ $quiz->time_per_question }}</span>
                </div>
            </div>
        </div>

        <!-- Question Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 fade-in" id="question-card">
            <h3 class="text-lg font-bold text-gray-800 mb-6 text-center leading-relaxed" id="question-text"></h3>
            <div class="space-y-3" id="options-container"></div>
        </div>

        <!-- Next Button -->
        <div class="text-center mt-6">
            <button onclick="nextQuestion()" id="next-btn"
                    class="hidden gradient-bg text-white px-8 py-3 rounded-xl font-bold hover:opacity-90 transition inline-flex items-center gap-2">
                Next <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </button>
        </div>
    </div>

    <!-- Hidden Form -->
    <form id="submit-form" method="POST" action="{{ route('quizzes.submit', $quiz) }}" class="hidden">
        @csrf
        <input type="hidden" name="player_name" id="form-player-name">
        <input type="hidden" name="time_taken" id="form-time-taken">
        <div id="form-answers"></div>
    </form>
</div>

@push('scripts')
<script>
    const questions = @json($quiz->questions);
    const timePerQuestion = {{ $quiz->time_per_question }};
    const totalQuestions = questions.length;

    let currentQuestion = 0, score = 0, answers = {};
    let timerInterval = null, timeLeft = timePerQuestion, totalTimeTaken = 0;
    let questionStartTime = 0, answered = false;
    let quizStarted = false, quizSubmitting = false, hurryShown = false;

    // Prevent accidental leave
    window.addEventListener('beforeunload', function(e) {
        if (quizStarted && !quizSubmitting) {
            e.preventDefault();
            e.returnValue = 'You have a quiz in progress. Your answers will be lost!';
        }
    });

    // Intercept nav clicks
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('nav a').forEach(link => {
            link.addEventListener('click', function(e) {
                if (quizStarted && !quizSubmitting) {
                    e.preventDefault();
                    const href = this.href;
                    showConfirm(
                        'log-out', 'red', 'Leave Quiz?',
                        'You have a quiz in progress!<br>All your progress will be <strong>lost</strong> if you leave now.',
                        'Leave Anyway', 'modal-btn-danger',
                        function() { quizSubmitting = true; window.location.href = href; }
                    );
                }
            });
        });
    });

    function startQuiz() {
        const name = document.getElementById('player-name').value.trim();
        if (!name) {
            showAlert('hand', 'amber', 'Hold On!', 'Please enter your name before starting the quiz.<br>We need it for the leaderboard!', 'Got it', 'modal-btn-warning');
            document.getElementById('player-name').focus();
            return;
        }
        document.getElementById('form-player-name').value = name;
        document.getElementById('name-screen').classList.add('hidden');
        document.getElementById('quiz-screen').classList.remove('hidden');
        quizStarted = true;
        showToast('Good luck, ' + name + '!', 'success', 2000);
        showQuestion();
    }

    document.getElementById('player-name').addEventListener('keypress', e => { if (e.key === 'Enter') startQuiz(); });

    function showQuestion() {
        const q = questions[currentQuestion];
        answered = false;
        timeLeft = timePerQuestion;
        questionStartTime = Date.now();
        hurryShown = false;

        document.getElementById('question-counter').textContent = `Question ${currentQuestion + 1} of ${totalQuestions}`;
        document.getElementById('score-display').innerHTML = `<svg class="w-3.5 h-3.5 text-amber-500 inline-block" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg> Score: ${score}`;
        document.getElementById('progress-bar').style.width = `${((currentQuestion) / totalQuestions) * 100}%`;
        document.getElementById('question-text').textContent = q.question_text;
        document.getElementById('next-btn').classList.add('hidden');

        const container = document.getElementById('options-container');
        container.innerHTML = '';
        const labels = ['A', 'B', 'C', 'D'];
        const keys = ['a', 'b', 'c', 'd'];
        const vals = [q.option_a, q.option_b, q.option_c, q.option_d];

        keys.forEach((key, i) => {
            const div = document.createElement('div');
            div.className = 'quiz-option flex items-center p-4 border-2 border-gray-200 rounded-xl';
            div.dataset.answer = key;
            div.onclick = () => selectAnswer(key, div);
            div.innerHTML = `
                <span class="flex-shrink-0 w-9 h-9 rounded-lg bg-indigo-100 text-indigo-700 font-bold flex items-center justify-center mr-4 text-sm">${labels[i]}</span>
                <span class="text-gray-700 font-medium text-sm">${vals[i]}</span>`;
            container.appendChild(div);
        });

        startTimer();
        document.getElementById('question-card').classList.add('fade-in');
    }

    function startTimer() {
        clearInterval(timerInterval);
        updateTimerDisplay();
        timerInterval = setInterval(() => {
            timeLeft--;
            updateTimerDisplay();
            if (timeLeft <= 0) { clearInterval(timerInterval); if (!answered) timeUp(); }
        }, 1000);
    }

    function updateTimerDisplay() {
        const tt = document.getElementById('timer-text');
        const tc = document.getElementById('timer-circle');
        tt.textContent = timeLeft;
        tc.style.strokeDashoffset = 283 - (283 * timeLeft / timePerQuestion);

        if (timeLeft <= 5) {
            tc.style.stroke = '#ef4444'; tt.style.color = '#ef4444';
            tt.parentElement.parentElement.classList.add('timer-shake');
        } else if (timeLeft <= 10) {
            tc.style.stroke = '#f59e0b'; tt.style.color = '#f59e0b';
            tt.parentElement.parentElement.classList.remove('timer-shake');
        } else {
            tc.style.stroke = getComputedStyle(document.documentElement).getPropertyValue('--t-500'); tt.style.color = '#1f2937';
            tt.parentElement.parentElement.classList.remove('timer-shake');
        }

        if (!hurryShown && timePerQuestion > 6 && timeLeft === Math.floor(timePerQuestion / 2)) {
            hurryShown = true; showToast('Halfway! Keep going!', 'warning');
        }
        if (timeLeft === 5 && !answered) { showToast('Hurry up! 5 seconds left!', 'danger'); }
    }

    function selectAnswer(key, element) {
        if (answered) return;
        answered = true;
        clearInterval(timerInterval);

        const q = questions[currentQuestion];
        totalTimeTaken += Math.round((Date.now() - questionStartTime) / 1000);
        answers[q.id] = key;

        document.querySelectorAll('.quiz-option').forEach(opt => {
            opt.style.cursor = 'default';
            if (opt.dataset.answer === q.correct_answer) {
                opt.classList.add('correct');
                opt.innerHTML += '<span class="ml-auto text-green-600 font-bold"><svg class="w-5 h-5 inline" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg></span>';
            }
            if (opt.dataset.answer === key && key !== q.correct_answer) {
                opt.classList.add('incorrect');
                opt.innerHTML += '<span class="ml-auto text-red-600 font-bold"><svg class="w-5 h-5 inline" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></span>';
            }
        });

        if (key === q.correct_answer) {
            score += q.points;
            document.getElementById('score-display').innerHTML = `<svg class="w-3.5 h-3.5 text-amber-500 inline-block" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg> Score: ${score}`;
        }

        if (q.explanation) {
            const card = document.getElementById('question-card');
            const explDiv = document.createElement('div');
            explDiv.className = 'mt-4 p-4 bg-blue-50 rounded-xl text-sm text-blue-800 fade-in flex items-start gap-2 border border-blue-100';
            explDiv.innerHTML = `<svg class="w-4 h-4 mt-0.5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="9" y1="18" x2="15" y2="18"></line><line x1="10" y1="22" x2="14" y2="22"></line><path d="M15.09 14c.18-.98.65-1.74 1.41-2.5A4.65 4.65 0 0 0 18 8 6 6 0 0 0 6 8c0 1 .23 2.23 1.5 3.5A4.61 4.61 0 0 1 8.91 14"></path></svg><span><strong>Explanation:</strong> ${q.explanation}</span>`;
            card.appendChild(explDiv);
        }

        setTimeout(() => {
            const btn = document.getElementById('next-btn');
            btn.classList.remove('hidden');
            if (currentQuestion === totalQuestions - 1) {
                btn.innerHTML = '<svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"></path><line x1="4" y1="22" x2="4" y2="15"></line></svg> See Results';
            }
        }, 500);
    }

    function timeUp() {
        answered = true;
        const q = questions[currentQuestion];
        totalTimeTaken += timePerQuestion;
        answers[q.id] = null;

        document.querySelectorAll('.quiz-option').forEach(opt => {
            opt.style.cursor = 'default';
            if (opt.dataset.answer === q.correct_answer) {
                opt.classList.add('correct');
                opt.innerHTML += '<span class="ml-auto text-green-600 font-bold"><svg class="w-5 h-5 inline" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg></span>';
            }
        });

        const card = document.getElementById('question-card');
        const div = document.createElement('div');
        div.className = 'mt-4 p-4 bg-red-50 rounded-xl text-sm text-red-700 fade-in text-center font-bold flex items-center justify-center gap-2 border border-red-100';
        div.innerHTML = '<svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg> Time\'s Up!';
        card.appendChild(div);

        setTimeout(() => {
            const btn = document.getElementById('next-btn');
            btn.classList.remove('hidden');
            if (currentQuestion === totalQuestions - 1) {
                btn.innerHTML = '<svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"></path><line x1="4" y1="22" x2="4" y2="15"></line></svg> See Results';
            }
        }, 500);
    }

    function nextQuestion() {
        currentQuestion++;
        if (currentQuestion >= totalQuestions) {
            const answeredCount = Object.values(answers).filter(a => a !== null).length;
            const unansweredCount = totalQuestions - answeredCount;
            let msg = `You answered <strong>${answeredCount}</strong> out of <strong>${totalQuestions}</strong> questions.`;
            if (unansweredCount > 0) msg += `<br><span style="color:#ef4444;">${unansweredCount} unanswered or timed out.</span>`;
            msg += '<br><br>Ready to see your results?';

            showConfirm('flag', 'green', 'Submit Quiz?', msg, 'See My Results', 'modal-btn-success', () => submitQuiz());
            return;
        }
        showQuestion();
    }

    function submitQuiz() {
        quizSubmitting = true;
        document.getElementById('form-time-taken').value = totalTimeTaken;
        const formAnswers = document.getElementById('form-answers');
        formAnswers.innerHTML = '';
        for (const [qId, answer] of Object.entries(answers)) {
            const input = document.createElement('input');
            input.type = 'hidden'; input.name = `answers[${qId}]`; input.value = answer || '';
            formAnswers.appendChild(input);
        }
        document.getElementById('submit-form').submit();
    }
</script>
@endpush
@endsection
