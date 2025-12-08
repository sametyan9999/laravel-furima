<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Requests\TradeMessageRequest;

class TradeMessageRequestTest extends TestCase
{
    /** @test */
    public function rules_are_exactly_as_specified()
    {
        $request = new TradeMessageRequest();

        $this->assertSame(
            [
                'body'  => ['required', 'string', 'max:400'],
                'image' => ['nullable', 'image', 'mimes:jpeg,png'],
            ],
            $request->rules()
        );
    }

    /** @test */
    public function messages_are_exactly_as_specified()
    {
        $request = new TradeMessageRequest();

        $this->assertSame(
            [
                'body.required' => '本文を入力してください',
                'body.max'      => '本文は400文字以内で入力してください',
                'image.image'   => '「.png」または「.jpeg」形式でアップロードしてください',
                'image.mimes'   => '「.png」または「.jpeg」形式でアップロードしてください',
            ],
            $request->messages()
        );
    }
}