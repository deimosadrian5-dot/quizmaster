@extends('layouts.app')

@section('title', 'Leaderboard')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-12">
    <!-- Header -->
    <div class="text-center mb-10">
        <div class="w-16 h-16 bg-amber-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <i data-lucide="trophy" class="w-8 h-8 text-amber-600"></i>
        </div>
        <h1 class="text-4xl font-extrabold text-gray-800 mb-2">Leaderboard</h1>
        <p class="text-gray-500">The best quiz masters of all time!</p>
    </div>

    <!-- Quiz Filter -->
    <div class="flex flex-wrap justify-center gap-2 mb-8">
        <a href="{{ route('quizzes.leaderboard') }}"
           class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-sm font-medium transition {{ !$quizId ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
            <i data-lucide="layers" class="w-3.5 h-3.5"></i> All Quizzes
        </a>
        @foreach($quizzes as $q)
        <a href="{{ route('quizzes.leaderboard', ['quiz' => $q->id]) }}"
           class="px-4 py-2 rounded-full text-sm font-medium transition {{ $quizId == $q->id ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
            {{ $q->title }}
        </a>
        @endforeach
    </div>

    @if($attempts->isEmpty())
        <div class="text-center py-16">
            <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <i data-lucide="target" class="w-8 h-8 text-gray-400"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-600 mt-2">No scores yet!</h2>
            <p class="text-gray-400 mt-2">Be the first to take a quiz and claim the top spot!</p>
            <a href="{{ route('quizzes.index') }}" class="inline-flex items-center gap-2 mt-6 gradient-bg text-white px-6 py-3 rounded-xl font-bold hover:opacity-90 transition text-sm">
                Take a Quiz <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </a>
        </div>
    @else
        <!-- Top 3 Podium -->
        @if($attempts->count() >= 3)
        <div class="grid grid-cols-3 gap-4 mb-10 items-end">
            <!-- 2nd Place -->
            <div class="text-center fade-in">
                <div class="w-12 h-12 bg-gray-300 rounded-full flex items-center justify-center text-white mx-auto mb-3 shadow-md">
                    <span class="text-lg font-bold">2</span>
                </div>
                <div class="bg-gray-100 rounded-t-xl p-4 border border-gray-200" style="height: 120px;">
                    <div class="font-bold text-gray-700 text-sm">{{ $attempts[1]->player_name }}</div>
                    <div class="text-2xl font-extrabold text-gray-600">{{ $attempts[1]->score }}</div>
                    <div class="text-xs text-gray-400 mt-1 truncate">{{ $attempts[1]->quiz->title ?? '' }}</div>
                </div>
            </div>
            <!-- 1st Place -->
            <div class="text-center fade-in">
                <div class="w-14 h-14 bg-amber-400 rounded-full flex items-center justify-center text-white mx-auto mb-3 shadow-lg">
                    <i data-lucide="crown" class="w-7 h-7"></i>
                </div>
                <div class="gradient-bg rounded-t-xl p-4 text-white" style="height: 160px;">
                    <div class="font-bold text-sm">{{ $attempts[0]->player_name }}</div>
                    <div class="text-3xl font-extrabold">{{ $attempts[0]->score }}</div>
                    <div class="text-xs text-indigo-200 mt-1 truncate">{{ $attempts[0]->quiz->title ?? '' }}</div>
                </div>
            </div>
            <!-- 3rd Place -->
            <div class="text-center fade-in">
                <div class="w-12 h-12 bg-orange-400 rounded-full flex items-center justify-center text-white mx-auto mb-3 shadow-md">
                    <span class="text-lg font-bold">3</span>
                </div>
                <div class="bg-orange-50 rounded-t-xl p-4 border border-orange-200" style="height: 100px;">
                    <div class="font-bold text-orange-700 text-sm">{{ $attempts[2]->player_name }}</div>
                    <div class="text-2xl font-extrabold text-orange-600">{{ $attempts[2]->score }}</div>
                    <div class="text-xs text-orange-400 mt-1 truncate">{{ $attempts[2]->quiz->title ?? '' }}</div>
                </div>
            </div>
        </div>
        @endif

        <!-- Ranking Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Rank</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Player</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Quiz</th>
                        <th class="px-6 py-3.5 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Score</th>
                        <th class="px-6 py-3.5 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Accuracy</th>
                        <th class="px-6 py-3.5 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Time</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($attempts as $index => $attempt)
                    <tr class="hover:bg-gray-50 transition {{ $index < 3 ? 'bg-amber-50/30' : '' }}">
                        <td class="px-6 py-4">
                            @if($index === 0)
                                <span class="w-8 h-8 bg-amber-400 rounded-full flex items-center justify-center text-white font-bold text-xs">1</span>
                            @elseif($index === 1)
                                <span class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center text-white font-bold text-xs">2</span>
                            @elseif($index === 2)
                                <span class="w-8 h-8 bg-orange-400 rounded-full flex items-center justify-center text-white font-bold text-xs">3</span>
                            @else
                                <span class="text-gray-400 font-bold text-sm pl-2">{{ $index + 1 }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-semibold text-gray-800 text-sm">{{ $attempt->player_name }}</div>
                            <div class="text-xs text-gray-400">{{ $attempt->created_at->diffForHumans() }}</div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $attempt->quiz->title ?? 'Unknown' }}</td>
                        <td class="px-6 py-4 text-center">
                            <span class="font-bold text-indigo-700">{{ $attempt->score }}</span>
                            <span class="text-gray-400 text-xs">/{{ $attempt->total_points }}</span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-block px-2.5 py-1 rounded-full text-xs font-bold
                                {{ $attempt->percentage >= 70 ? 'bg-emerald-100 text-emerald-700' : ($attempt->percentage >= 40 ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">
                                {{ $attempt->percentage }}%
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center text-sm text-gray-500 flex items-center justify-center gap-1">
                            <i data-lucide="clock" class="w-3 h-3"></i> {{ $attempt->time_taken }}s
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
