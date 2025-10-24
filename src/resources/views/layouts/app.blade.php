<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>@yield('title', 'COACHTECHフリマ')</title>
  <link rel="preconnect" href="https://fonts.gstatic.com" />
  <style>
    body{margin:0;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Hiragino Kaku Gothic ProN","Noto Sans JP",Arial,"Helvetica Neue",Meiryo,sans-serif;color:#222}
    .container{max-width:1120px;margin:0 auto;padding:0 20px}
    header{background:#111;color:#fff}
    header .bar{display:flex;align-items:center;gap:16px;height:56px}
    .brand{font-weight:800;letter-spacing:1px}
    .search{flex:1}
    .search input{width:100%;height:34px;border-radius:6px;border:1px solid #999;padding:0 12px}
    .nav a,.nav form button{color:#fff;text-decoration:none;margin-left:16px;background:transparent;border:none;cursor:pointer}
    .nav .sell{background:#fff;color:#111;padding:6px 14px;border-radius:6px}
    .tabs{display:flex;gap:28px;border-bottom:1px solid #ccc;margin-top:12px}
    .tabs a{padding:12px 0;text-decoration:none;color:#777}
    .tabs a.active{color:#e2504e;font-weight:700;border-bottom:2px solid #e2504e;margin-bottom:-1px}
    .grid{display:grid;grid-template-columns:repeat(4,1fr);gap:28px}
    .card{border:1px solid #ddd;border-radius:6px;padding:8px;text-decoration:none;color:inherit;display:block}
    .thumb{width:100%;aspect-ratio:1/1;background:#ddd;border-radius:6px;display:flex;align-items:center;justify-content:center;color:#555}
    .price{font-weight:700}
    .btn{display:inline-block;background:#e2504e;color:#fff;border:none;border-radius:6px;padding:10px 16px;cursor:pointer}
    .btn-block{width:100%}
    .muted{color:#888}
    .flex{display:flex;gap:16px}
    .mt16{margin-top:16px}.mt24{margin-top:24px}.mt32{margin-top:32px}.mb24{margin-bottom:24px}
    .section-title{font-weight:800;margin:24px 0 12px}
    .badge{display:inline-block;background:#eee;border-radius:999px;padding:4px 10px;margin-right:8px;font-size:12px}
    textarea,input[type="text"],input[type="number"],input[type='email'],input[type='password'],select{width:100%;height:40px;border:1px solid #bbb;border-radius:6px;padding:0 10px}
    textarea{height:140px;padding:10px}
    .spacer{height:24px}
    footer{height:40px}
  </style>
  @yield('head')
</head>
<body>
  @include('components.header')

  <main class="container">
    @yield('content')
  </main>

  <footer></footer>
</body>
</html>