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
        background: url('{{ asset('uploads/aboutustestimonials/' . ($testimonials->banner_image ?? 'default.webp')) }}') center center no-repeat;
        background-size: cover;
    ">      <div class="container">
        <div class="row">
          <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
            <h1> Testimonials</h1>
            <ul class="bread-list">
              <li><a href="./">Home<i class="fa fa-angle-right"></i></a></li>
              <li><a href="#">About Us<i class="fa fa-angle-right"></i></a></li>
              <li class="active"><a href="javascript:void(0)">Testimonials</a></li>
            </ul>
          </div>
        </div>
      </div>
    </section>

<section class="testimonial-one-sec">
  <div class="container">
    <div class="row" id="testimonialRow">

      @if(!empty($items))
        @foreach($items as $index => $item)
          <div class="col-md-4 testimonial-item {{ $index >= 3 ? 'd-none extra-item' : '' }}" 
               data-aos="fade-up" 
               data-aos-delay="{{ ($index + 1) * 100 }}">

            <div class="testi-one-card-sec">
              <div class="to-upper-sec">
                <img src="{{ asset('uploads/aboutustestimonials/' . $item['image']) }}" 
                     class="img-fluid" 
                     alt="Testimonials Image">
                <span>{{ $item['name'] }}</span>
                <p>{{ $item['profession'] }}</p>
              </div>
              <hr>
              <div class="to-lower-sec">
                <p>{{ $item['description'] }}</p>
              </div>
            </div>

          </div>
        @endforeach
      @endif

      @if(!empty($items) && count($items) > 3)
      <div class="col-12 text-center testimonials-one-btn-sec mt-4">
        <a href="javascript:void(0);" class="btn-blue btn-lg" id="viewMoreBtn">
          <span>View More</span>
        </a>
      </div>
      @endif

    </div>
  </div>
</section>
       @include('frontend.includes.join-membership')

  </main>
         @include('components.frontend.footer')

  <!-- Scroll Top -->
  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i
      class="bi bi-arrow-up-short"></i></a>

         @include('components.frontend.main-js')

<script>
document.getElementById("viewMoreBtn")?.addEventListener("click", function() {
    document.querySelectorAll(".extra-item").forEach(function(item) {
        item.classList.remove("d-none");
    });

    this.style.display = "none"; // hide button after click
});
</script>


</body>

</html>