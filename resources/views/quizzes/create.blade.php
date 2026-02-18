@extends('layouts.app')

@section('title', 'Create a Quiz')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-12">

    <!-- Header -->
    <div class="text-center mb-10">
        <div class="w-16 h-16 bg-indigo-100 rounded-2xl flex items-center justify-center mx-auto mb-4 bounce-in">
            <i data-lucide="pen-line" class="w-8 h-8 text-indigo-600"></i>
        </div>
        <h1 class="text-4xl font-extrabold text-gray-800 mb-2">Create Your Own Quiz</h1>
        <p class="text-gray-500">Fill in the details and add your questions. Share the fun!</p>
    </div>

    <!-- Validation Errors -->
    @if($errors->any())
    <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-8 flex items-start gap-3">
        <i data-lucide="alert-triangle" class="w-5 h-5 text-red-500 mt-0.5 flex-shrink-0"></i>
        <div>
            <h3 class="text-red-700 font-bold text-sm mb-1">Please fix the following:</h3>
            <ul class="list-disc list-inside text-sm text-red-600 space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    <form method="POST" action="{{ route('quizzes.store') }}" id="quiz-form">
        @csrf

        <!-- Quiz Details -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 mb-8 fade-in">
            <h2 class="text-lg font-bold text-gray-800 mb-6 flex items-center gap-2">
                <i data-lucide="settings" class="w-5 h-5 text-indigo-500"></i> Quiz Details
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Quiz Title *</label>
                    <input type="text" name="title" value="{{ old('title') }}" required
                           placeholder="e.g. Ultimate Movie Trivia"
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition text-sm">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Description *</label>
                    <textarea name="description" required rows="3" placeholder="What's this quiz about? Make it fun!"
                              class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition text-sm">{{ old('description') }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Category *</label>
                    <select name="category" required
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition bg-white text-sm">
                        <option value="">Select a category...</option>
                        @php
                            $cats = ['Science','History','Geography','Sports','Movies','Music','Technology','Food','Animals','General Knowledge'];
                        @endphp
                        @foreach($cats as $cat)
                            <option value="{{ $cat }}" {{ old('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Difficulty *</label>
                    <select name="difficulty" required
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition bg-white text-sm">
                        <option value="easy" {{ old('difficulty') == 'easy' ? 'selected' : '' }}>Easy</option>
                        <option value="medium" {{ old('difficulty', 'medium') == 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="hard" {{ old('difficulty') == 'hard' ? 'selected' : '' }}>Hard</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Time Per Question (seconds) *</label>
                    <input type="number" name="time_per_question" value="{{ old('time_per_question', 30) }}" required min="10" max="120"
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition text-sm">
                </div>
            </div>
        </div>

        <!-- Questions Section -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                    <i data-lucide="help-circle" class="w-5 h-5 text-indigo-500"></i> Questions
                </h2>
                <span class="text-sm text-gray-400" id="question-count">1 question</span>
            </div>

            <div id="questions-container">
                <!-- Question 1 -->
                <div class="question-block bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-5 fade-in" data-index="0">
                    <div class="flex items-center justify-between mb-5">
                        <h3 class="text-sm font-bold text-indigo-700 flex items-center gap-1.5">
                            <span class="w-7 h-7 bg-indigo-100 rounded-md flex items-center justify-center">
                                <span class="question-number-num text-xs">1</span>
                            </span>
                            <span class="question-number">Question 1</span>
                        </h3>
                        <button type="button" onclick="removeQuestion(this)" class="delete-btn hidden text-gray-400 hover:text-red-500 transition p-2 rounded-lg hover:bg-red-50" title="Remove">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Question *</label>
                        <input type="text" name="questions[0][question_text]" required
                               placeholder="e.g. What is the capital of France?"
                               class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition text-sm">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        @foreach(['A','B','C','D'] as $letter)
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1 flex items-center gap-1.5">
                                <span class="w-5 h-5 bg-indigo-100 text-indigo-700 rounded text-center text-xs leading-5 font-bold">{{ $letter }}</span>
                                Option {{ $letter }} *
                            </label>
                            <input type="text" name="questions[0][option_{{ strtolower($letter) }}]" required placeholder="Option {{ $letter }}"
                                   class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition text-sm">
                        </div>
                        @endforeach
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Correct Answer *</label>
                            <select name="questions[0][correct_answer]" required
                                    class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition bg-white text-sm">
                                <option value="a">A</option><option value="b">B</option><option value="c">C</option><option value="d">D</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Points *</label>
                            <input type="number" name="questions[0][points]" value="10" required min="1" max="100"
                                   class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Explanation <span class="text-gray-400 font-normal">(optional)</span></label>
                            <input type="text" name="questions[0][explanation]" placeholder="Why is this correct?"
                                   class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition text-sm">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add Question -->
            <button type="button" onclick="addQuestion()"
                    class="w-full py-4 border-2 border-dashed border-gray-200 rounded-xl text-gray-400 font-semibold hover:border-indigo-400 hover:text-indigo-600 hover:bg-indigo-50 transition flex items-center justify-center gap-2 text-sm">
                <i data-lucide="plus" class="w-5 h-5"></i> Add Another Question
            </button>
        </div>

        <!-- Submit -->
        <div class="text-center">
            <button type="submit"
                    class="inline-flex items-center gap-2 gradient-bg text-white px-10 py-4 rounded-xl font-bold text-lg hover:opacity-90 transition shadow-lg pulse-glow">
                <i data-lucide="rocket" class="w-5 h-5"></i> Create Quiz
            </button>
            <p class="text-gray-400 text-sm mt-3">Your quiz will be available for everyone to play!</p>
        </div>
    </form>
</div>

@push('scripts')
<script>
    let questionIndex = 1;

    function addQuestion() {
        const container = document.getElementById('questions-container');
        const idx = questionIndex;

        const html = `
        <div class="question-block bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-5 fade-in" data-index="${idx}">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-sm font-bold text-indigo-700 flex items-center gap-1.5">
                    <span class="w-7 h-7 bg-indigo-100 rounded-md flex items-center justify-center">
                        <span class="question-number-num text-xs">${idx + 1}</span>
                    </span>
                    <span class="question-number">Question ${idx + 1}</span>
                </h3>
                <button type="button" onclick="removeQuestion(this)" class="delete-btn text-gray-400 hover:text-red-500 transition p-2 rounded-lg hover:bg-red-50" title="Remove">
                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                </button>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Question *</label>
                <input type="text" name="questions[${idx}][question_text]" required placeholder="e.g. What is the capital of France?"
                       class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition text-sm">
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                ${['A','B','C','D'].map(l => `<div>
                    <label class="block text-sm font-medium text-gray-600 mb-1 flex items-center gap-1.5">
                        <span class="w-5 h-5 bg-indigo-100 text-indigo-700 rounded text-center text-xs leading-5 font-bold">${l}</span>
                        Option ${l} *
                    </label>
                    <input type="text" name="questions[${idx}][option_${l.toLowerCase()}]" required placeholder="Option ${l}"
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition text-sm">
                </div>`).join('')}
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Correct Answer *</label>
                    <select name="questions[${idx}][correct_answer]" required
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition bg-white text-sm">
                        <option value="a">A</option><option value="b">B</option><option value="c">C</option><option value="d">D</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Points *</label>
                    <input type="number" name="questions[${idx}][points]" value="10" required min="1" max="100"
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Explanation <span class="text-gray-400 font-normal">(optional)</span></label>
                    <input type="text" name="questions[${idx}][explanation]" placeholder="Why is this correct?"
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition text-sm">
                </div>
            </div>
        </div>`;

        container.insertAdjacentHTML('beforeend', html);
        questionIndex++;
        updateQuestionNumbers();
        container.lastElementChild.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function removeQuestion(btn) {
        const blocks = document.querySelectorAll('.question-block');
        if (blocks.length <= 1) {
            showAlert('file-text', 'amber', "Can't Remove", 'You need at least one question in your quiz!', 'OK', 'modal-btn-warning');
            return;
        }
        const block = btn.closest('.question-block');
        const qNum = block.querySelector('.question-number').textContent;

        showConfirm('trash-2', 'red', 'Delete Question?',
            `Are you sure you want to remove <strong>${qNum}</strong>?<br>This action cannot be undone.`,
            'Delete', 'modal-btn-danger',
            function() {
                block.style.transition = 'all 0.3s ease';
                block.style.opacity = '0';
                block.style.transform = 'translateX(-20px)';
                setTimeout(() => { block.remove(); updateQuestionNumbers(); }, 300);
            }
        );
    }

    function updateQuestionNumbers() {
        const blocks = document.querySelectorAll('.question-block');
        blocks.forEach((block, i) => {
            block.querySelector('.question-number').textContent = `Question ${i + 1}`;
            const numEl = block.querySelector('.question-number-num');
            if (numEl) numEl.textContent = i + 1;
            const del = block.querySelector('.delete-btn');
            if (blocks.length > 1) del.classList.remove('hidden');
            else del.classList.add('hidden');
        });
        document.getElementById('question-count').textContent = blocks.length === 1 ? '1 question' : `${blocks.length} questions`;
    }

    let createConfirmed = false;
    document.getElementById('quiz-form').addEventListener('submit', function(e) {
        if (!createConfirmed) {
            e.preventDefault();
            const title = document.querySelector('input[name="title"]').value.trim();
            const qCount = document.querySelectorAll('.question-block').length;
            if (!title) {
                showAlert('hand', 'amber', 'Missing Title', 'Please give your quiz a title before submitting!', 'OK', 'modal-btn-warning');
                return;
            }
            showConfirm('rocket', 'green', 'Create Quiz?',
                `Your quiz "<strong>${title}</strong>" with <strong>${qCount}</strong> question(s) will be published and available for everyone to play!`,
                'Create Quiz', 'modal-btn-success',
                function() { createConfirmed = true; document.getElementById('quiz-form').submit(); }
            );
        }
    });
</script>
@endpush
@endsection
