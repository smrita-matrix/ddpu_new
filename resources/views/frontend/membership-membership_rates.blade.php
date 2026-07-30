<!DOCTYPE html>
<html lang="en">

<head>
 @include('components.frontend.head')
</head>


<body class="index-page">
<header id="header" class="header sticky-top">
   @include('components.frontend.header')
 </header>

<style>.pricingTable .pricing-content {
    padding: 0 0 16px;
    margin: 0;
    list-style: none;
    /* border-top: 1px solid #113063b0; */
    border-bottom: 1px solid #113063b0;
    margin-bottom: 16px;
    color: black;
}</style>

  <main class="main">
    <section class="ddpu-breadcrumb-sec"
    style="
        background: url('{{ asset('memberships/banner/' . ($membershipratesrates->banner_image ?? 'default.webp')) }}') center center no-repeat;
        background-size: cover;
    ">      <div class="container">
        <div class="row">
          <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
            <h1>Membership Rates and Options</h1>
            <ul class="bread-list">
              <li><a href="./">Home<i class="fa fa-angle-right"></i></a></li>
              <li><a href="#">Services<i class="fa fa-angle-right"></i></a></li>
              <li class="active"><a href="javascript:void(0)">Membership Rates and Options</a></li>
            </ul>
          </div>
        </div>
      </div>
    </section>

 


<section class="membership-rates-options-one">
  <div class="container">
    
    <!-- HEADING AND DESCRIPTION -->
    <div class="membership-rates-options-content-sec">
      <div class="membership-rates-options-title-sec">
        <h2>{{ $membershiprates->subscription_heading }}</h2>
        <p>{{ $membershiprates->subscription_description }}</p>
      </div>
    </div>

    <!-- PRICING OPTIONS -->
    <div class="mro-pricing-sec">
      <div class="row">
        @php
            $options = json_decode($membershiprates->options, true) ?? [];
        @endphp

        @foreach($options as $option)
        <div class="col-md-6 col-sm-6">
          <div class="pricingTable">
            <h3 class="title">{{ $option['title'] }}</h3>

            <div class="price-value">
              <h3>{{ $option['heading'] }}</h3>
            </div>

            <!-- Amount title -->
            <h4 class="price-sub-title">{{ $option['amount_title'] ?? 'Amount' }}</h4>

            <!-- Pricing content list -->
            @if(isset($option['list']) && is_array($option['list']))
              <ul class="pricing-content">
                @foreach($option['list'] as $item)
                  <li>{!! $item !!}</li>
                @endforeach
              </ul>
            @elseif(isset($option['description']))
              <ul class="pricing-content">
                {!! $option['description'] !!}
              </ul>
            @endif

            <a href="{{ route('joinmembership.form') }}" class="pricingTable-signup">Join now</a>
          </div>
        </div>
        @endforeach

      </div>
    </div>

  </div>
</section>


   
  @include('frontend.includes.join-membership')  
  </main>
  @include('components.frontend.footer')  <!-- Scroll Top -->
  <!-- Scroll Top -->
  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i
      class="bi bi-arrow-up-short"></i></a>

     @include('components.frontend.main-js')


</body>

</html>