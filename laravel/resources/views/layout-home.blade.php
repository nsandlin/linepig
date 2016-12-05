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
  <link rel="icon" type="image" href="/images/favicon.ico">
  <link href="https://fonts.googleapis.com/css?family=Noto+Sans|Noto+Serif" rel="stylesheet">
  <link rel="stylesheet" href="{{ elixir('css/all.css') }}">
  <script src="{{ elixir('js/all.js') }}"></script>
</head>
<body class="home">
  <div class="container container-top">
    <p style="float:right;">
      <a href="https://fieldmuseum.org" target="_blank">
        <img src="/images/field-logo.svg" class="fieldmuseum-logo">
      </a>
    </p>
    <br clear="both">
    <h1>Welcome to LinEpig</h1>
    <p>
      Get help identifying the erigonines languishing in your collection.
      <br>We have epigynal images for <b>@yield('count') species</b> of Erigoninae so far. Read more
      <a href="https://www.fieldmuseum.org/science/special-projects/dwarf-spider-id-gallery" target="_blank">
      about LinEpig</a> and 
      <a href="https://www.fieldmuseum.org/science/special-projects/dwarf-spider-id-gallery/can-you-help" target="_blank">help us grow</a>.
    </p>
  </div><!--.container container-top-->

  @yield('content')

  <div id="bottomnav" class="container container-bottom">
    <a href="/">LinEpig main page</a> - 
    <a href="http://www.fieldmuseum.org/science/special-projects/dwarf-spider-id-gallery" target="_blank">About</a> - 
    <a href="http://blogs.scientificamerican.com/guest-blog/internet-porn-fills-gap-in-spider-taxonomy/" target="_blank">At SciAm</a> - 
    <a href="http://www.fieldmuseum.org/science/special-projects/dwarf-spider-id-gallery/can-you-help" target="_blank">Contribute specimens/images</a> - 
    <a href="https://github.com/nsandlin/linepig" target="_blank">GitHub</a>
  </div><!--bottomnav-->

</body>
</html>