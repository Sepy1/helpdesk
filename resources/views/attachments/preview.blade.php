@php
  // expects: $url (public URL to file), $mime
  $isImage = str_starts_with($mime, 'image/');
  $isPdf = $mime === 'application/pdf';
@endphp
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Preview Lampiran</title>
  <style>
    html,body{height:100%;margin:0}
    .wrap{display:flex;align-items:center;justify-content:center;height:100%;background:#111}
    img{max-width:100%;max-height:100%;height:auto;width:auto}
    iframe{width:100%;height:100vh;border:0}
    .pdf-wrap{width:100%;height:100vh}
    .open-link{position:fixed;left:8px;top:8px;z-index:50;background:#fff;padding:6px 10px;border-radius:6px;box-shadow:0 2px 6px rgba(0,0,0,.12);text-decoration:none;color:#111;font-weight:600}
  </style>
</head>
<body>
  <a class="open-link" href="{{ $url }}" target="_blank" rel="noopener">Open original</a>
  @if($isImage)
    <div class="wrap"><img src="{{ $url }}" alt="Lampiran"></div>
  @elseif($isPdf)
    <div class="pdf-wrap"><iframe src="{{ $url }}"></iframe></div>
  @else
    {{-- Generic fallback: try iframe, else show link --}}
    <div class="pdf-wrap"><iframe src="{{ $url }}"></iframe></div>
  @endif
</body>
</html>