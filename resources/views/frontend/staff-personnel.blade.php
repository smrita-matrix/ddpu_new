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
        background: url('{{ !empty($staffBanner?->banner_image)
            ? asset('uploads/staff-personnel/' . $staffBanner->banner_image)
            : asset('assets/img/bg/default-breadcrumb.webp') }}')
        center center no-repeat;
        background-size: cover;
    ">
  <div class="container">
    <div class="row">
      <div class="col-12">
        <h1>Staff Personnel</h1>
        <ul class="bread-list">
          <li><a href="{{ url('/') }}">Home<i class="fa fa-angle-right"></i></a></li>
          <li><a href="#">About Us<i class="fa fa-angle-right"></i></a></li>
          <li class="active"><a href="javascript:void(0)">Staff Personnel</a></li>
        </ul>
      </div>
    </div>
  </div>
</section>


<section class="staff-personnel-team-sec">
  <div class="container">
    <div class="row">

      @forelse($staffList as $index => $staff)
        <div class="col-xl-3 col-lg-4 col-md-4" data-aos="fade-up" data-aos-delay="{{ ($index + 1) * 100 }}">
          <div class="spt-card">

            {{-- Profile Image --}}
            <div class="spt-image">
              <img src="{{ asset('uploads/staff-personnel/' . $staff->profile_image) }}" 
                   alt="{{ $staff->name }}" 
                   class="img-fluid">

              {{-- Social Overlay --}}
              @if(!empty($staff->social_links) && is_array($staff->social_links))
                <div class="spt-overlay">
                  <div class="spt-social">
                    @foreach($staff->social_links as $social)
                      <a href="{{ $social['link'] }}" target="_blank" class="social-link">
                        @switch(strtolower($social['name']))
                          @case('facebook') <i class="fa-brands fa-facebook-f"></i> @break
                          @case('instagram') <i class="fa-brands fa-instagram"></i> @break
                          @case('twitter') <i class="fa-brands fa-x-twitter"></i> @break
                          @case('linkedin') 
                          @case('linked') <i class="fa-brands fa-linkedin"></i> @break
                          @default <i class="fa-solid fa-link"></i>
                        @endswitch
                      </a>
                    @endforeach
                  </div>
                </div>
              @endif
            </div>

            {{-- Name & Designation --}}
            <div class="spt-content">
              <a href="{{ url('staff-personnel/' . $staff->slug) }}">
                <span class="spt-name">{{ $staff->name }}</span>
                <p class="spt-specialty">{{ $staff->designation }}</p>
              </a>
            </div>

          </div>
        </div>
      @empty
        <div class="col-12 text-center">
          <p>No staff members available.</p>
        </div>
      @endforelse

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