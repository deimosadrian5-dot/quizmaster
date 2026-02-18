<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'category',
        'difficulty',
        'image',
        'time_per_question',
    ];

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function attempts()
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function getDifficultyColorAttribute()
    {
        return match ($this->difficulty) {
            'easy' => 'green',
            'medium' => 'yellow',
            'hard' => 'red',
            default => 'gray',
        };
    }

    public function getDifficultyEmojiAttribute()
    {
        return match ($this->difficulty) {
            'easy' => '🟢',
            'medium' => '🟡',
            'hard' => '🔴',
            default => '⚪',
        };
    }

    public function getCategoryEmojiAttribute()
    {
        return match (strtolower($this->category)) {
            'science' => '🔬',
            'history' => '📜',
            'geography' => '🌍',
            'sports' => '⚽',
            'movies' => '🎬',
            'music' => '🎵',
            'technology' => '💻',
            'food' => '🍕',
            'animals' => '🐾',
            'general knowledge' => '🧠',
            default => '📚',
        };
    }

    public function getCategoryIconAttribute()
    {
        return match (strtolower($this->category)) {
            'science' => 'flask-conical',
            'science & nature' => 'flask-conical',
            'science: computers' => 'cpu',
            'science: mathematics' => 'calculator',
            'science: gadgets' => 'monitor',
            'history' => 'landmark',
            'geography' => 'globe',
            'sports' => 'medal',
            'movies', 'entertainment: film' => 'clapperboard',
            'music', 'entertainment: music' => 'music',
            'technology' => 'monitor',
            'food' => 'utensils',
            'animals' => 'paw-print',
            'general knowledge' => 'lightbulb',
            'mathematics' => 'calculator',
            'entertainment: television' => 'tv',
            'entertainment: video games' => 'gamepad-2',
            'entertainment: board games' => 'gamepad-2',
            'mythology' => 'swords',
            'art' => 'palette',
            'celebrities' => 'star',
            'vehicles' => 'car',
            'entertainment: comics' => 'book-open',
            'entertainment: japanese anime & manga' => 'sparkles',
            'entertainment: cartoon & animations' => 'smile',
            'entertainment: books' => 'book-open',
            'entertainment: musicals & theatres' => 'music',
            'politics' => 'landmark',
            default => 'book-open',
        };
    }
}
