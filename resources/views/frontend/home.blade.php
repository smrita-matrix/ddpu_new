
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
    <section id="hero" class="hero section dark-background">
            <div id="hero-carousel" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="5000">

                @foreach($banners as $key => $banner)
    <div class="carousel-item {{ $key == 0 ? 'active' : '' }}">
        
        <img src="{{ asset('home/banner/' . $banner->banner_images) }}" alt="banner-img">

        <div class="carousel-container ddpu-home-banner-cont">

            @if($key == 0)
                <h1>{{ $banner->banner_heading }}</h1>
            @else
                <h2>{{ $banner->banner_heading }}</h2>
            @endif

            <a href="{{ route('joinmembership.form') }}" class="btn-dark btn-lg">
                <span>Join Membership</span>
            </a>

        </div>
    </div>
@endforeach

                <!-- Carousel controls -->
                <a class="carousel-control-prev" href="#hero-carousel" role="button" data-bs-slide="prev">
                <span class="carousel-control-prev-icon bi bi-chevron-left"></span>
                </a>

                <a class="carousel-control-next" href="#hero-carousel" role="button" data-bs-slide="next">
                <span class="carousel-control-next-icon bi bi-chevron-right"></span>
                </a>

                <ol class="carousel-indicators"></ol>
            </div>
    </section>

    <section class="aim-member-unique-sec">
    <div class="container">
        <div class="row">

        <!-- LEFT SIDE IMAGE -->
        <div class="col-12 col-md-12 col-lg-5">
            <div class="about-img">
            <img src="{{ asset('aim-images/' . $aim->aim_image) }}" class="img-fluid" alt="">
            </div>
        </div>

        <!-- RIGHT SIDE CONTENT -->
        <div class="col-12 col-md-12 col-lg-7">
    <div class="vision-mission-content-one-sec">
        @foreach($aim->details as $key => $item)
            <div class="vision-mission-content-sub-one-sec" data-aos="fade-up" data-aos-delay="{{ ($key+1) * 200 }}">
                <div class="row">
                    <!-- ICON -->
                    <div class="col-12 col-md-2">
                        <div class="vmcs-img-sec">
                            <img src="{{ asset('aim-icons/' . $item['icon']) }}" class="img-fluid" alt="">
                        </div>
                    </div>

                    <!-- HEADING + DESCRIPTION -->
                    <div class="col-12 col-md-10">
                        <div class="vmcs-para-sec">
                            <h3>{{ $item['heading'] }}</h3>
                            <p>{{ $item['description'] }}</p>
                        </div>
                    </div>

                </div>
            </div>
        @endforeach
    </div>
</div>


        </div>
    </div>
    </section>

   <section class="why-choose-ddpu-sec section">

        <!-- Main Heading -->
        <div class="container section-title" data-aos="fade-up">
            <h2>{{ $whychoose->main_heading }}</h2>
        </div>

        <div class="container">
            <div class="row gy-4">

            @foreach($whychoose->details as $index => $item)
                <div class="col-lg-4 col-md-6" 
                    data-aos="fade-up" 
                    data-aos-delay="{{ ($index + 1) * 200 }}">
                    
                <div class="service-item position-relative">
                    <div class="icon">
                    <img src="{{ asset('whychoose-icons/' . $item['icon']) }}" alt="">
                    </div>

                    <p>{{ $item['title'] }}</p>

                </div>
                </div>
            @endforeach

            <!-- JOIN NOW BUTTON -->
            <div class="col-lg-12 col-md-12" data-aos="fade-up" data-aos-delay="700">
                <div class="why-choose-ddpu-btn-sec">
                <a href="{{ route('joinmembership.form') }}" class="btn-blue btn-lg">
    <span>Join Now</span>
</a>

                </div>
            </div>

            </div>
        </div>

   </section>

<section id="testimonials" class="testimonials section">
    <!-- Section Title -->
    <div class="container section-title" data-aos="fade-up">
        <h2>{{ $testimonials->main_heading }}</h2>
    </div>

    <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="swiper init-swiper">
            <script type="application/json" class="swiper-config">
            {
                "loop": true,
                "speed": 600,
                "autoplay": { "delay": 5000 },
                "slidesPerView": "auto",
                "pagination": {
                    "el": ".swiper-pagination",
                    "type": "bullets",
                    "clickable": true
                },
                "breakpoints": {
                    "320": { "slidesPerView": 1, "spaceBetween": 40 },
                    "1200": { "slidesPerView": 3, "spaceBetween": 20 }
                }
            }
            </script>

            <div class="swiper-wrapper">
                @foreach($testimonials->testimonials as $item)
                <div class="swiper-slide">
                    <div class="testimonial-item">
                        <p>
                            <i class="bi bi-quote quote-icon-left"></i>
                            <span>{{ $item['description'] }}</span>
                        </p>
                        <h3>{{ $item['name'] }}</h3>
                        <h4>{{ $item['designation'] }}</h4>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="swiper-pagination"></div>
        </div>

        <!-- JOIN NOW BUTTON -->
        <div class="row mt-4">
            <div class="col-lg-12 text-center" data-aos="fade-up" data-aos-delay="700">
                <div class="why-choose-ddpu-btn-sec">
                    <a href="{{ route('frontend.testimonials') }}" class="btn-blue btn-lg">
                        <span>Join Now</span>
                    </a>
                </div>
            </div>
        </div>

    </div>
</section>

     @include('frontend.includes.join-membership')

  </main>
      @include('components.frontend.footer')


  <!-- Scroll Top -->
  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i
      class="bi bi-arrow-up-short"></i></a>

  <!-- Preloader -->
  <!--<div id="preloader"></div>-->
    @include('components.frontend.main-js')

  

</body>

</html>