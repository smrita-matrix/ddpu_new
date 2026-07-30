
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
        background: url('{{ asset('generalpractice/banner/' . ($generalpractice->banner_image ?? 'default.webp')) }}') center center no-repeat;
        background-size: cover;
    ">      <div class="container">
        <div class="row">
          <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
            <h1>General Practice (NHS)</h1>
            <ul class="bread-list">
              <li><a href="./">Home<i class="fa fa-angle-right"></i></a></li>
              <li><a href="#">Services<i class="fa fa-angle-right"></i></a></li>
              <li class="active"><a href="javascript:void(0)">General Practice (NHS)</a></li>
            </ul>
          </div>
        </div>
      </div>
    </section>



<section class="general-practice-one-sec">
  <div class="container">
    <div class="row">

      <!-- IMAGE -->
      <div class="col-lg-6 col-md-12">
        <div class="general-practice-one-img-sec">
          <img src="{{ asset('/generalpractice/main/' . ($generalpractice->main_image ?? 'default.webp')) }}"
               class="img-fluid" alt="">
        </div>
      </div>

      <!-- CONTENT -->
      <div class="col-lg-6 col-md-12">
        <div class="general-practice-content-sec">

            {{-- description from DB --}}
            {!! $generalpractice->description ?? '' !!}

        </div>
      </div>

    </div>
  </div>
</section>

<section class="general-practice-two-sec">
  <div class="container">
    <div class="row">

      @php
        $items = is_array($generalpractice->items) 
                 ? $generalpractice->items 
                 : json_decode($generalpractice->items, true);
      @endphp

      @if(!empty($items))
        @foreach($items as $item)
          <div class="col-lg-6 col-md-12">
            <div class="general-practice-content-sub-one-sec">
              <div class="row">

                <!-- ICON -->
                <div class="col-12 col-md-2">
                  <div class="gpcso-img-sec">
                    <img src="{{ asset('generalpractice/icons/' . ($item['icon'] ?? 'default.webp')) }}" 
                         alt="" class="img-fluid">
                  </div>
                </div>

                <!-- TEXT -->
                <div class="col-12 col-md-10">
                  <div class="gpcso-para-sec">
                    <span>{{ $item['heading'] ?? '' }}</span>
                    <p>{{ $item['description'] ?? '' }}</p>
                  </div>
                </div>

              </div>
            </div>
          </div>
        @endforeach
      @endif

    </div>
  </div>
</section>

<section class="general-practice-benefits-sec">
  <div class="container">
    <div class="row">
      <div class="col-md-8">
        <div class="general-practice-benefits-content-sec">

          <!-- Heading -->
          <h2 class="general-practice-benefits-title">
            {{ $generalpractice->benefits_heading ?? 'List Of Benefits' }}
          </h2>

          <!-- Description (HTML List) -->
          {!! $generalpractice->benefits_description ?? '' !!}

        </div>
      </div>
    </div>
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