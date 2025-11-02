<?php
return [

    // 汎用ルール
    'required'  => ':attributeを入力してください',
    'email'     => ':attributeはメール形式で入力してください',
    'unique'    => 'その:attributeは既に使用されています',
    'string'    => ':attributeは文字列で入力してください',
    'image'     => ':attributeには画像ファイルを指定してください',
    'mimes'     => ':attributeは:values形式を指定してください',
    'regex'     => ':attributeの形式が正しくありません',

    // 文字数・サイズ
    'min' => [
        'string' => ':attributeは:min文字以上で入力してください',
    ],
    'max' => [
        'string' => ':attributeは:max文字以内で入力してください',
        'file'   => ':attributeは:max KB以下のファイルを指定してください',
    ],
    'size' => [
        'string' => ':attributeは:size文字で入力してください',
    ],

    // 確認用（要件票の文言に合わせて語尾を調整）
    'confirmed' => ':attributeと一致しません',

    // 項目名の日本語置換（表示文言そのまま）
    'attributes' => [
        // 認証
        'name'                  => 'お名前',
        'username'              => 'ユーザー名',
        'email'                 => 'メールアドレス',
        'password'              => 'パスワード',
        'password_confirmation' => '確認用パスワード',

        // プロフィール
        'avatar'        => 'プロフィール画像',
        'postal_code'   => '郵便番号',
        'address_line1' => '住所',
        'address_line2' => '建物名',
        'phone'         => '電話番号',
        'bio'           => '自己紹介',

        // コメント
        'body' => 'コメント',
    ],
];