@extends('layouts.app')

@section('title', 'All Quizzes')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-12">
    <!-- Header -->
    <div class="text-center mb-12">
        <h1 class="text-4xl md:text-5xl font-extrabold text-gray-800 mb-4">Choose Your Challenge</h1>
        <p class="text-lg text-gray-500 max-w-2xl mx-auto">Pick a quiz, test your knowledge, and climb the leaderboard!</p>
        <div class="flex flex-wrap justify-center gap-3 mt-8">
            <a href="{{ route('quizzes.create') }}"
               class="inline-flex items-center gap-2 gradient-bg text-white px-5 py-2.5 rounded-lg font-semibold text-sm hover:opacity-90 transition">
                <i data-lucide="plus-circle" class="w-4 h-4"></i> Create Your Own
            </a>
            <a href="{{ route('quizzes.generate') }}"
               class="inline-flex items-center gap-2 gradient-warm text-white px-5 py-2.5 rounded-lg font-semibold text-sm hover:opacity-90 transition">
                <i data-lucide="zap" class="w-4 h-4"></i> Instant Generate
            </a>
        </div>
    </div>

    <!-- Category Filter -->
    @if($categories->count() > 1)
    <div class="flex flex-wrap justify-center gap-2 mb-10" id="category-filters">
        <button onclick="filterCategory('all')" class="filter-btn active inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-indigo-600 text-white font-medium text-sm transition" data-category="all">
            <i data-lucide="layers" class="w-3.5 h-3.5"></i> All
        </button>
        @foreach($categories as $category)
        <button onclick="filterCategory('{{ $category }}')" class="filter-btn px-4 py-2 rounded-full bg-gray-100 text-gray-600 font-medium text-sm transition hover:bg-gray-200" data-category="{{ $category }}">
            {{ $category }}
        </button>
        @endforeach
    </div>
    @endif

    <!-- Quiz Grid -->
    @if($quizzes->isEmpty())
        <div class="text-center py-20">
            <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <i data-lucide="inbox" class="w-8 h-8 text-gray-400"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-600 mt-2">No quizzes yet!</h2>
            <p class="text-gray-400 mt-2">Quizzes will appear here once they're added.</p>
        </div>
    @else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="quiz-grid">
        @foreach($quizzes as $quiz)
        <div class="quiz-card card-hover bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden" data-category="{{ $quiz->category }}">
            <!-- Card Header -->
            <div class="gradient-bg p-5 text-white relative overflow-hidden">
                <div class="absolute top-0 right-0 opacity-10 -mr-3 -mt-3">
                    <i data-lucide="{{ $quiz->category_icon }}" class="w-24 h-24"></i>
                </div>
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-7 h-7 bg-white/20 rounded-md flex items-center justify-center">
                        <i data-lucide="{{ $quiz->category_icon }}" class="w-4 h-4"></i>
                    </div>
                    <span class="text-xs font-medium text-indigo-200">{{ $quiz->category }}</span>
                </div>
                <h3 class="text-lg font-bold leading-tight">{{ $quiz->title }}</h3>
            </div>

            <!-- Card Body -->
            <div class="p-5">
                <p class="text-gray-500 text-sm mb-4 line-clamp-2">{{ $quiz->description }}</p>

                <div class="flex items-center justify-between text-sm text-gray-400 mb-4">
                    <span class="inline-flex items-center gap-1">
                        <i data-lucide="file-text" class="w-3.5 h-3.5"></i> {{ $quiz->questions_count }} Questions
                    </span>
                    <span class="inline-flex items-center gap-1">
                        <i data-lucide="clock" class="w-3.5 h-3.5"></i> {{ $quiz->time_per_question }}s each
                    </span>
                </div>

                <div class="flex items-center justify-between mb-5">
                    <span class="inline-flex items-center gap-1.5 text-sm font-medium">
                        <span class="w-2.5 h-2.5 rounded-full bg-{{ $quiz->difficulty_color }}-500"></span>
                        <span class="capitalize text-gray-600">{{ $quiz->difficulty }}</span>
                    </span>
                    <span class="inline-flex items-center gap-1 text-xs text-gray-400">
                        <i data-lucide="users" class="w-3 h-3"></i>
                        {{ $quiz->attempts_count }} {{ Str::plural('attempt', $quiz->attempts_count) }}
                    </span>
                </div>

                <a href="{{ route('quizzes.show', $quiz) }}"
                   class="flex items-center justify-center gap-2 w-full gradient-bg text-white py-2.5 rounded-lg font-semibold text-sm hover:opacity-90 transition">
                    Start Quiz <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>

@push('scripts')
<script>
function filterCategory(category) {
    const cards = document.querySelectorAll('.quiz-card');
    const buttons = document.querySelectorAll('.filter-btn');

    buttons.forEach(btn => {
        btn.classList.remove('bg-indigo-600', 'text-white', 'active');
        btn.classList.add('bg-gray-100', 'text-gray-600');
    });

    const activeBtn = document.querySelector(`[data-category="${category}"]`);
    activeBtn.classList.add('bg-indigo-600', 'text-white', 'active');
    activeBtn.classList.remove('bg-gray-100', 'text-gray-600');

    cards.forEach(card => {
        if (category === 'all' || card.dataset.category === category) {
            card.style.display = '';
            card.classList.add('fade-in');
        } else {
            card.style.display = 'none';
        }
    });
}
</script>
@endpush
@endsection
