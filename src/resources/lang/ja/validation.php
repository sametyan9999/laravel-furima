<?php
return [

    // 汎用ルール
    'required'  => ':attribute を入力してください',
    'email'     => ':attribute はメール形式で入力してください',
    'unique'    => 'その :attribute は既に使用されています',
    'string'    => ':attribute は文字列で入力してください',
    'image'     => ':attribute には画像ファイルを指定してください',
    'mimes'     => ':attribute は :values 形式を指定してください',
    'regex'     => ':attribute の形式が正しくありません',

    // 文字数・サイズ
    'min' => [
        'string' => ':attribute は :min 文字以上で入力してください',
    ],
    'max' => [
        'string' => ':attribute は :max 文字以内で入力してください',
        'file'   => ':attribute は :max KB 以下のファイルを指定してください',
    ],
    'size' => [
        'string' => ':attribute は :size 文字で入力してください',
    ],

    // 確認用
    'confirmed' => ':attribute が一致しません',

    // 項目名の日本語置換
    'attributes' => [
        // 認証
        'name'                  => '名前',
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
    ],
];