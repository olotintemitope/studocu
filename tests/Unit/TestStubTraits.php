<?php


namespace Tests\Unit;


trait TestStubTraits
{
    public function getQuestionStubs(): array
    {
        return [
            'question' => 'What is the capital city of US',
            'created_at' => now(),
        ];
    }

    public function getQuestionOptionsStubs(int $questionId): array
    {
        return [
            [
                'option' => 'New York',
                'question_id' => $questionId,
                'created_at' => now(),
            ],
            [
                'option' => 'Chicago',
                'question_id' => $questionId,
                'created_at' => now(),
            ],
            [
                'option' => 'Houston',
                'question_id' => $questionId,
                'created_at' => now(),
            ]
        ];
    }

    public function getQuestionAnswerStubs(int $questionId): array
    {
        return [
            [
                'answer' => 'New York',
                'question_id' => $questionId,
                'created_at' => now(),
            ],
        ];
    }

}