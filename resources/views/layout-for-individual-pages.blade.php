<!DOCTYPE html>
<html lang="en">
<head>
  <!-- Matomo -->
  <script>
    var _paq = window._paq = window._paq || [];
    /* tracker methods like "setCustomDimension" should be called before "trackPageView" */
    _paq.push(['trackPageView']);
    _paq.push(['enableLinkTracking']);
    (function() {
      var u="https://matomo.fieldmuseum.org/";
      _paq.push(['setTrackerUrl', u+'matomo.php']);
      _paq.push(['setSiteId', '2']);
      var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
      g.async=true; g.src=u+'matomo.js'; s.parentNode.insertBefore(g,s);
    })();
  </script>
  <!-- End Matomo Code -->

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
@yield('bodyclass')
  <div class="container container-top">
    <p style="float:right;">
      <a href="https://fieldmuseum.org" target="_blank">
        <img src="/images/fmnh-logo.png" class="fieldmuseum-logo">
      </a>
    </p>
    <br clear="both">
    <h1><a href="/">LinEpig</a>: <em>@yield('species_name')</em></h1>
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
    <p style="font-size:80%;">
      Cite: <b>LinEpig:</b> An ID Gallery for Female Erigoninae. Field Museum of Natural History, Chicago.
      Online at https://linepig.fieldmuseum.org/,
      accessed on
      @php
        print(date("Y-m-d"));
      @endphp.
    </p>
  </div><!--bottomnav-->
</body>
</html>