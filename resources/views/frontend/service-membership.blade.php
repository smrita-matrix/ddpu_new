
<!DOCTYPE html>
<html lang="en">

<head>
 @include('components.frontend.head')
</head>


<body class="index-page">
<header id="header" class="header sticky-top">
   @include('components.frontend.header')
 </header>

  <main class="main">
  <section class="ddpu-breadcrumb-sec"
    style="
        background: url('{{ asset('/membership/banner/' . ($membership->banner_image ?? 'default.webp')) }}') center center no-repeat;
        background-size: cover;
    ">      <div class="container">
        <div class="row">
          <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
            <h1>Membership</h1>
            <ul class="bread-list">
              <li><a href="./">Home<i class="fa fa-angle-right"></i></a></li>
              <li><a href="#">Services<i class="fa fa-angle-right"></i></a></li>
              <li class="active"><a href="javascript:void(0)">Membership</a></li>
            </ul>
          </div>
        </div>
      </div>
    </section>


   <section class="membership-page-one-sec">
  <div class="container">

    <!-- TITLE -->
    <div class="row">
      <div class="col-md-12">
        <div class="mp-one-para-sec">
          <p>{!! $membership->title !!}</p>
        </div>
      </div>
    </div>

    @php
    $items = is_array($membership->items) ? $membership->items : json_decode($membership->items, true);
@endphp

@if(!empty($items))
    @foreach($items as $item)
        <div class="mp-one-content-sec">
            <div class="row">
                <div class="col-lg-4 col-md-4">
                    <img src="{{ asset('membership/items/' . ($item['image'] ?? 'default.webp')) }}">
                </div>
                <div class="col-lg-8 col-md-8">
                    <div class="mp-one-content-para-sec">
                    <h2>{{ $item['heading'] ?? '' }}</h2>
                    <p>{{ $item['description'] ?? '' }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endif


  </div>
</section>



    @include('frontend.includes.join-membership')

  </main>
  @include('components.frontend.footer')  <!-- Scroll Top -->
  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i
      class="bi bi-arrow-up-short"></i></a>

    @include('components.frontend.main-js')



</body>

</html>