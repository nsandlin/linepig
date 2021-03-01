<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title')</title>
  <meta name="description" content="@yield('description')">
  <meta name="author" content="LinEpig, Field Museum of Natural History">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="msvalidate.01" content="D512583DCEDCD8C1A6782DA0D384235F" />
  <link rel="icon" type="image" href="/images/favicon.ico">
  <link href="https://fonts.googleapis.com/css?family=Noto+Sans|Noto+Serif" rel="stylesheet">
  <link rel="stylesheet" href="{{ mix('css/app.css') }}">
  <script src="{{ mix('js/app.js') }}"></script>
</head>
<body class="home">
  <div class="container container-top">
    <p style="float:right;">
      <a href="https://fieldmuseum.org" target="_blank">
        <img src="/images/fmnh-logo.png" class="fieldmuseum-logo">
      </a>
    </p>
    <br clear="both">
    <h1>LinEpig: An ID Gallery for Female Erigoninae</h1>
    <p>
      Get help identifying the erigonines languishing in your collection. We have epigynal images for <b>@yield('count') species</b> of Erigoninae so far.
      <br>We also have some palps, habitus, and other non-epigynal images. Check them out via the <a href="/search">Search</a> page.
    </p>
  </div><!--.container container-top-->

  <div id="topnav" class="container container-top">
    <a href="/">Home</a> - 
    <a href="http://www.fieldmuseum.org/science/special-projects/dwarf-spider-id-gallery" target="_blank">About</a> - 
    <a href="/search">Search</a> - 
    <a href="http://blogs.scientificamerican.com/guest-blog/internet-porn-fills-gap-in-spider-taxonomy/" target="_blank">At SciAm</a> - 
    <a href="http://www.fieldmuseum.org/science/special-projects/dwarf-spider-id-gallery/can-you-help" target="_blank">Contribute specimens/images</a> - 
    <a href="https://github.com/nsandlin/linepig" target="_blank">GitHub</a>
  </div><!--topnav-->

  @yield('content')

  <div id="bottomnav" class="container container-bottom">
    <a href="/">Home</a> - 
    <a href="http://www.fieldmuseum.org/science/special-projects/dwarf-spider-id-gallery" target="_blank">About</a> - 
    <a href="/search">Search</a> - 
    <a href="http://blogs.scientificamerican.com/guest-blog/internet-porn-fills-gap-in-spider-taxonomy/" target="_blank">At SciAm</a> - 
    <a href="http://www.fieldmuseum.org/science/special-projects/dwarf-spider-id-gallery/can-you-help" target="_blank">Contribute specimens/images</a> - 
    <a href="https://github.com/nsandlin/linepig" target="_blank">GitHub</a><br>
    <p style="font-size:80%;">Cite: <b>LinEpig:</b> An ID Gallery for Female Erigoninae. Field Museum of Natural History, Chicago. Online at https://linepig.fieldmuseum.org/, accessed on {date of access}.</p> 
  </div><!--bottomnav-->

<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-88512602-1', 'auto');
  ga('send', 'pageview');

</script>
</body>
</html>