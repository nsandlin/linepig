<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title')</title>
  <meta name="description" content="A visual aid for identifying the difficult spiders in family Linyphiidae.">
  <meta name="author" content="LinEpig, Field Museum of Natural History">
  <link rel="icon" type="image" href="images/favicon.ico">
  <link href="https://fonts.googleapis.com/css?family=Noto+Sans|Noto+Serif" rel="stylesheet">
  <link rel="stylesheet" href="{{ mix('css/app.css') }}">
  <script src="{{ mix('js/app.js') }}"></script>
</head>
<body class="home">
  <div class="container container-top">
    <p style="float:right;">
      <a href="https://fieldmuseum.org" target="_blank">
        <img src="/images/field-logo.svg" class="fieldmuseum-logo">
      </a>
    </p>
    <br clear="both" />
    <h1>Sorry - your image was not found</h1>
    <p>One of these tools might help you locate it</p>
    <p style="padding:0 0 0 40px;"> 
       &bull; <a href="/search">Search LinEpig</a> <br><br>
       &bull; <a href="/">Visually browse the LineEpig main page:</a><br>
       <a href="/"><img src="/images/mainpage2.png"></a> <br><br>
       &bull; <a href="http://www.fieldmuseum.org/science/special-projects/dwarf-spider-id-gallery">Refer to "About LineEpig"</a> <br><br>
       &bull; <a href="http://www.wsc.nmbe.ch/family/48/Linyphiidae">Refer to "Linyphiidae" in the World Spider Catalog</a> <br> &nbsp;
    </p>
    <p style="padding:0 0 50px 0; border-bottom:dotted 1px blue;"> 
       If you feel you have discovered a broken link or other problem on this site, please
       let us know at nsandlin [at] fieldmuseum [dot] org.
    </p>

    <p>LineEpig helps institutions identify the erigonines (family Linyphiidae) languishing in their collection.
    <br>We have images for <b>296 species</b> so far. Read more
    <a href="https://www.fieldmuseum.org/science/special-projects/dwarf-spider-id-gallery" target="_blank">about LinEpig</a> and
    <a href="https://www.fieldmuseum.org/science/special-projects/dwarf-spider-id-gallery/can-you-help" target="_blank">help us grow</a>.</p>
  </div><!-- container -->

  <div id="bottomnav" class="container container-bottom">
    <a href="/">Home</a> - 
    <a href="http://www.fieldmuseum.org/science/special-projects/dwarf-spider-id-gallery" target="_blank">About</a> - 
    <a href="/search">Search</a> - 
    <a href="http://blogs.scientificamerican.com/guest-blog/internet-porn-fills-gap-in-spider-taxonomy/" target="_blank">At SciAm</a> - 
    <a href="http://www.fieldmuseum.org/science/special-projects/dwarf-spider-id-gallery/can-you-help" target="_blank">Contribute specimens/images</a> - 
    <a href="https://github.com/nsandlin/linepig" target="_blank">GitHub</a>
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
